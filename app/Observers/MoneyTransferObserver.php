<?php

namespace App\Observers;

use App\Models\MoneyTransfer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class MoneyTransferObserver
{
    /**
     * Handle the MoneyTransfer "created" event.
     */
    public function created(MoneyTransfer $moneyTransfer): void
    {
        ActivityLog::create([
            'log_name'    => 'money_transfer',
            'description' => 'created',
            'subject_type'=> MoneyTransfer::class,
            'subject_id'  => $moneyTransfer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'money_transfer',
            'description' => 'updated',
            'subject_type'=> MoneyTransfer::class,
            'subject_id'  => $moneyTransfer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $moneyTransfer->getOriginal(),
                'attributes' => $moneyTransfer->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the MoneyTransfer "deleted" event.
     */
    public function deleted(MoneyTransfer $moneyTransfer): void
    {
        ActivityLog::create([
            'log_name'    => 'money_transfer',
            'description' => 'deleted',
            'subject_type'=> MoneyTransfer::class,
            'subject_id'  => $moneyTransfer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'money_transfer',
            'description' => 'restored',
            'subject_type'=> MoneyTransfer::class,
            'subject_id'  => $moneyTransfer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'money_transfer',
            'description' => 'force deleted',
            'subject_type'=> MoneyTransfer::class,
            'subject_id'  => $moneyTransfer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $moneyTransfer->getAttributes(),
            ],
        ]);
    }
}