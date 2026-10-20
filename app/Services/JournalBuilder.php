<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Exceptions\UnbalancedJournalException;
use App\Exceptions\ClosedPeriodException;
use App\Exceptions\DuplicateJournalException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class JournalBuilder
{
    private $reference_no;
    private $entry_date;
    private $source_type;
    private $source_id;
    private $source_subtype;
    private $event_type;
    private $note;
    private $created_by;
    private $related_journal_entry_id;
    private $lines = [];

    public function __construct()
    {
        $this->entry_date = Carbon::now()->toDateString();
        $this->created_by = auth()->id();
    }

    public static function create(): self
    {
        return new self();
    }

    public function setReference(?string $reference_no): self
    {
        $this->reference_no = $reference_no;
        return $this;
    }

    public function setDate($date): self
    {
        $this->entry_date = $date
            ? Carbon::parse($date)->toDateString()
            : Carbon::now()->toDateString();
        return $this;
    }

    public function setSource(string $source_type, int $source_id): self
    {
        $this->source_type = $source_type;
        $this->source_id = $source_id;
        return $this;
    }

    // CHANGED: nullable — service passes null when a subtype isn't relevant
    public function setSourceSubtype(?string $source_subtype): self
    {
        $this->source_subtype = $source_subtype;
        return $this;
    }

    public function setEventType(?string $event_type): self
    {
        $this->event_type = $event_type;
        return $this;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function setCreatedBy(int $userId): self
    {
        $this->created_by = $userId;
        return $this;
    }

    // NEW: links a reversal to the entry it reverses (written in one query)
    public function setRelatedEntryId(?int $entryId): self
    {
        $this->related_journal_entry_id = $entryId;
        return $this;
    }

    public function addDebit(int $accountId, $amount, ?string $description = null): self
    {
        if ((float) $amount > 0) {
            $this->lines[] = [
                'accounting_account_id' => $accountId,
                'debit' => number_format((float) $amount, 4, '.', ''),
                'credit' => '0.0000',
                'description' => $description,
            ];
        }
        return $this;
    }

    public function addCredit(int $accountId, $amount, ?string $description = null): self
    {
        if ((float) $amount > 0) {
            $this->lines[] = [
                'accounting_account_id' => $accountId,
                'debit' => '0.0000',
                'credit' => number_format((float) $amount, 4, '.', ''),
                'description' => $description,
            ];
        }
        return $this;
    }

    public function save(): JournalEntry
    {
        // CHANGED: self-healing — guarantees tables/config/chart exist on first hit
        AccountingBootstrap::ensure();

        // NEW: never persist a hollow entry
        if (empty($this->lines)) {
            throw new UnbalancedJournalException('Cannot save a journal entry with no lines.');
        }

        $this->validatePeriod();
        $this->validateBalance();
        $this->validateIdempotency();

        return DB::transaction(function () {
            if (!$this->reference_no) {
                $this->reference_no = 'JE-' . date('YmdHis') . '-' . rand(1000, 9999);
            }

            $attributes = [
                'reference_no'             => $this->reference_no,
                'entry_date'               => $this->entry_date,
                'source_type'              => $this->source_type,
                'source_id'                => $this->source_id,
                'source_subtype'           => $this->source_subtype,
                'event_type'               => $this->event_type,
                'note'                     => $this->note,
                'created_by'               => $this->created_by,
                'related_journal_entry_id' => $this->related_journal_entry_id,
            ];

            // CHANGED: drop keys for columns that don't exist on this installation
            $entryColumns = array_flip(Schema::getColumnListing((new JournalEntry)->getTable()));
            $entry = JournalEntry::create(array_intersect_key($attributes, $entryColumns));

            $lineColumns = array_flip(Schema::getColumnListing((new JournalLine)->getTable()));

            foreach ($this->lines as $line) {
                $entry->lines()->create(array_intersect_key($line, $lineColumns));
            }

            return $entry;
        });
    }

    private function validatePeriod(): void
    {
        // CHANGED: tolerate installations without the periods feature
        if (!Schema::hasTable('accounting_periods')) {
            return;
        }

        $period = AccountingPeriod::where('start_date', '<=', $this->entry_date)
            ->where('end_date', '>=', $this->entry_date)
            ->first();

        if ($period && $period->is_closed) {
            throw new ClosedPeriodException();
        }
    }

    private function validateBalance(): void
    {
        $totalDebit = '0.0000';
        $totalCredit = '0.0000';

        foreach ($this->lines as $line) {
            $totalDebit = bcadd($totalDebit, $line['debit'], 4);
            $totalCredit = bcadd($totalCredit, $line['credit'], 4);
        }

        $diff = round((float) bcsub($totalDebit, $totalCredit, 4), 4);

        if (abs($diff) > 0.01) {
            throw new UnbalancedJournalException("Journal entry unbalanced. Debits: {$totalDebit}, Credits: {$totalCredit}");
        }

        // NEW: auto-balance penny-level rounding noise so strict ledgers stay exact
        if ($diff != 0.0) {
            $firstAccountId = $this->lines[0]['accounting_account_id'];

            if ($diff > 0) {
                // debits exceed credits → top up the credit side
                $this->lines[] = [
                    'accounting_account_id' => $firstAccountId,
                    'debit'  => '0.0000',
                    'credit' => number_format($diff, 4, '.', ''),
                    'description' => 'Rounding adjustment',
                ];
            } else {
                // credits exceed debits → top up the debit side
                $this->lines[] = [
                    'accounting_account_id' => $firstAccountId,
                    'debit'  => number_format(abs($diff), 4, '.', ''),
                    'credit' => '0.0000',
                    'description' => 'Rounding adjustment',
                ];
            }
        }
    }

    private function validateIdempotency(): void
    {
        if (!$this->source_type || !$this->source_id || !$this->event_type) {
            return;
        }

        // CHANGED: an entry that was already REVERSED no longer blocks re-posting.
        // Only an unreversed entry with the same source + event_type is a duplicate.
        $exists = JournalEntry::where('source_type', $this->source_type)
            ->where('source_id', $this->source_id)
            ->where('event_type', $this->event_type)
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('journal_entries as reversals')
                    ->whereColumn('reversals.source_type', 'journal_entries.source_type')
                    ->whereColumn('reversals.source_id', 'journal_entries.source_id')
                    ->whereColumn('reversals.event_type', DB::raw("CONCAT(journal_entries.event_type, '_reversed')"));
            })
            ->exists();

        if ($exists) {
            throw new DuplicateJournalException();
        }
    }

    /**
     * Strictly enforce line-by-line reversal.
     */
    public static function reverse(JournalEntry $originalEntry, string $eventSuffix = '_reversed', ?string $note = null): JournalEntry
    {
        $builder = self::create()
            ->setSource($originalEntry->source_type, (int) $originalEntry->source_id)
            ->setSourceSubtype($originalEntry->source_subtype)   // nullable-safe now
            ->setEventType($originalEntry->event_type . $eventSuffix)
            ->setReference(($originalEntry->reference_no ?? ('JE-' . $originalEntry->id)) . '-REV')
            ->setDate(now()->toDateString())
            ->setNote($note ?? ('Reversal of ' . ($originalEntry->reference_no ?? ('entry #' . $originalEntry->id))))
            ->setRelatedEntryId($originalEntry->id);             // NEW: single-query write

        foreach ($originalEntry->lines as $line) {
            if ((float) $line->debit > 0) {
                $builder->addCredit($line->accounting_account_id, $line->debit, 'Reversal: ' . $line->description);
            }
            if ((float) $line->credit > 0) {
                $builder->addDebit($line->accounting_account_id, $line->credit, 'Reversal: ' . $line->description);
            }
        }

        return $builder->save();
    }
}
