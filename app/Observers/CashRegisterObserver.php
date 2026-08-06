<?php

namespace App\Observers;

use App\Models\CashRegister;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class CashRegisterObserver
{
    /**
     * Handle the CashRegister "created" event.
     */
    public function created(CashRegister $cashRegister): void
    {
        ActivityLog::create([
            'log_name'    => 'cash_register',
            'description' => 'created',
            'subject_type'=> CashRegister::class,
            'subject_id'  => $cashRegister->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $cashRegister->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the CashRegister "updated" event.
     */
    public function updated(CashRegister $cashRegister): void
    {
        ActivityLog::create([
            'log_name'    => 'cash_register',
            'description' => 'updated',
            'subject_type'=> CashRegister::class,
            'subject_id'  => $cashRegister->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $cashRegister->getOriginal(),
                'attributes' => $cashRegister->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the CashRegister "deleted" event.
     */
    public function deleted(CashRegister $cashRegister): void
    {
        ActivityLog::create([
            'log_name'    => 'cash_register',
            'description' => 'deleted',
            'subject_type'=> CashRegister::class,
            'subject_id'  => $cashRegister->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $cashRegister->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the CashRegister "restored" event.
     */
    public function restored(CashRegister $cashRegister): void
    {
        ActivityLog::create([
            'log_name'    => 'cash_register',
            'description' => 'restored',
            'subject_type'=> CashRegister::class,
            'subject_id'  => $cashRegister->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $cashRegister->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the CashRegister "force deleted" event.
     */
    public function forceDeleted(CashRegister $cashRegister): void
    {
        ActivityLog::create([
            'log_name'    => 'cash_register',
            'description' => 'force deleted',
            'subject_type'=> CashRegister::class,
            'subject_id'  => $cashRegister->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $cashRegister->getAttributes(),
            ],
        ]);
    }
}