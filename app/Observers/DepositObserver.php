<?php

namespace App\Observers;

use App\Models\Deposit;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DepositObserver
{
    /**
     * Handle the Deposit "created" event.
     */
    public function created(Deposit $deposit): void
    {
        ActivityLog::create([
            'log_name'    => 'deposit',
            'description' => 'created',
            'subject_type'=> Deposit::class,
            'subject_id'  => $deposit->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'deposit',
            'description' => 'updated',
            'subject_type'=> Deposit::class,
            'subject_id'  => $deposit->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $deposit->getOriginal(),
                'attributes' => $deposit->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the Deposit "deleted" event.
     */
    public function deleted(Deposit $deposit): void
    {
        ActivityLog::create([
            'log_name'    => 'deposit',
            'description' => 'deleted',
            'subject_type'=> Deposit::class,
            'subject_id'  => $deposit->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'deposit',
            'description' => 'restored',
            'subject_type'=> Deposit::class,
            'subject_id'  => $deposit->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'deposit',
            'description' => 'force deleted',
            'subject_type'=> Deposit::class,
            'subject_id'  => $deposit->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $deposit->getAttributes(),
            ],
        ]);
    }
}