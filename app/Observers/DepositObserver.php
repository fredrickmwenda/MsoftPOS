<?php

namespace App\Observers;

use App\Models\Deposit;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DepositObserver
{
    /**
     * Build a structured context array for the deposit.
     * This pulls in related data like Customer and Creator.
     */
    protected function buildContext(Deposit $deposit): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $deposit->loadMissing(['customer', 'user']);

        return [
            'amount'          => $deposit->amount,
            'customer_name'   => $deposit->customer ? $deposit->customer->name : 'Unknown Customer',
            'customer_phone'  => $deposit->customer ? $deposit->customer->phone_number : 'N/A',
            'created_by'      => $deposit->user ? $deposit->user->name : 'System',
            'note'            => $deposit->note ?? 'N/A',
        ];
    }

    /**
     * Handle the Deposit "created" event.
     */
    public function created(Deposit $deposit): void
    {
        ActivityLog::create([
            'log_name'     => 'deposit',
            'description'  => 'created',
            'subject_type' => Deposit::class,
            'subject_id'   => $deposit->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($deposit),
                'attributes' => $deposit->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Deposit "updated" event.
     */
    public function updated(Deposit $deposit): void
    {
        ActivityLog::create([
            'log_name'     => 'deposit',
            'description'  => 'updated',
            'subject_type' => Deposit::class,
            'subject_id'   => $deposit->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($deposit),
                'old'        => $deposit->getOriginal(),
                'attributes' => $deposit->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the Deposit "deleted" event.
     */
    public function deleted(Deposit $deposit): void
    {
        ActivityLog::create([
            'log_name'     => 'deposit',
            'description'  => 'deleted',
            'subject_type' => Deposit::class,
            'subject_id'   => $deposit->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($deposit),
                'attributes' => $deposit->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Deposit "restored" event (if SoftDeletes is used).
     */
    public function restored(Deposit $deposit): void
    {
        ActivityLog::create([
            'log_name'     => 'deposit',
            'description'  => 'restored',
            'subject_type' => Deposit::class,
            'subject_id'   => $deposit->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($deposit),
                'attributes' => $deposit->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Deposit "force deleted" event.
     */
    public function forceDeleted(Deposit $deposit): void
    {
        ActivityLog::create([
            'log_name'     => 'deposit',
            'description'  => 'force deleted',
            'subject_type' => Deposit::class,
            'subject_id'   => $deposit->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($deposit),
                'attributes' => $deposit->getAttributes(),
            ],
        ]);
    }
}