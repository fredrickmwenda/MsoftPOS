<?php

namespace App\Services;

use App\Models\JournalEntry;

class AccountingResult
{
    public function __construct(
        public ?JournalEntry $entry = null,
        public bool $success = true,
        public ?string $message = null
    ) {}

    public static function success(?JournalEntry $entry): self
    {
        return new self($entry, true);
    }

    public static function failed(string $message): self
    {
        return new self(null, false, $message);
    }

    public function isSuccessful(): bool      { return $this->success; }
    public function getEntry(): ?JournalEntry { return $this->entry; }
    public function getMessage(): ?string     { return $this->message; }

    /**
     * NEW: alias so callers can read $result->error safely.
     * Returns the failure message (or null) instead of an
     * "Undefined property" error.
     */
    public function __get(string $name)
    {
        if ($name === 'error') {
            return $this->message;
        }

        // preserve normal PHP behaviour for genuinely unknown properties
        throw new \Exception("Undefined property: " . static::class . "::$name");
    }

    public function __isset(string $name): bool
    {
        return $name === 'error' ? $this->message !== null : false;
    }

    public function __toString(): string
    {
        return $this->success
            ? 'Accounting OK' . ($this->entry ? ' (journal #' . $this->entry->id . ')' : ' (skipped)')
            : 'Accounting failed: ' . $this->message;
    }
}
