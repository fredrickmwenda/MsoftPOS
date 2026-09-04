<?php

namespace App\Observers;

use App\Models\MoneyTransfer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class MoneyTransferObserver
{
    /**
     * Build a structured context array for the money transfer.
     * This pulls in related data like the Source and Destination Accounts.
     */
    protected function buildContext(MoneyTransfer $moneyTransfer): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $moneyTransfer->loadMissing(['fromAccount', 'toAccount']);

        return [
            'reference_no'   => $moneyTransfer->reference_no,
            'amount'         => $moneyTransfer->amount,
            'from_account'   => $moneyTransfer->fromAccount ? $moneyTransfer->fromAccount->name : 'Unknown Account',
            'to_account'     => $moneyTransfer->toAccount ? $moneyTransfer->toAccount->name : 'Unknown Account',
            'date'           => $moneyTransfer->date ? \Carbon\Carbon::parse($moneyTransfer->date)->format('Y-m-d') : 'N/A',
            'note'           => $moneyTransfer->note ?? 'N/A',
        ];
    }

    /**
     * Handle the MoneyTransfer "created" event.
     */
    public function created(MoneyTransfer $moneyTransfer): void
    {
        ActivityLog::create([
            'log_name'     => 'money_transfer',
            'description'  => 'created',
            'subject_type' => MoneyTransfer::class,
            'subject_id'   => $moneyTransfer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($moneyTransfer),
                'attributes' => $moneyTransfer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the MoneyTransfer "updated" event.
     */
    public function updated(MoneyTransfer $moneyTransfer): void
    {
        ActivityLog::create([
            'log_name'     => 'money_transfer',
            'description'  => 'updated',
            'subject_type' => MoneyTransfer::class,
            'subject_id'   => $moneyTransfer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($moneyTransfer),
                'old'        => $moneyTransfer->getOriginal(),
                'attributes' => $moneyTransfer->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the MoneyTransfer "deleted" event.
     */
    public function deleted(MoneyTransfer $moneyTransfer): void
    {
        ActivityLog::create([
            'log_name'     => 'money_transfer',
            'description'  => 'deleted',
            'subject_type' => MoneyTransfer::class,
            'subject_id'   => $moneyTransfer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($moneyTransfer),
                'attributes' => $moneyTransfer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the MoneyTransfer "restored" event (if SoftDeletes is used).
     */
    public function restored(MoneyTransfer $moneyTransfer): void
    {
        ActivityLog::create([
            'log_name'     => 'money_transfer',
            'description'  => 'restored',
            'subject_type' => MoneyTransfer::class,
            'subject_id'   => $moneyTransfer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($moneyTransfer),
                'attributes' => $moneyTransfer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the MoneyTransfer "force deleted" event.
     */
    public function forceDeleted(MoneyTransfer $moneyTransfer): void
    {
        ActivityLog::create([
            'log_name'     => 'money_transfer',
            'description'  => 'force deleted',
            'subject_type' => MoneyTransfer::class,
            'subject_id'   => $moneyTransfer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($moneyTransfer),
                'attributes' => $moneyTransfer->getAttributes(),
            ],
        ]);
    }
}