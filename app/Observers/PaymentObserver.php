<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        ActivityLog::create([
            'log_name'    => 'payment',
            'description' => 'created',
            'subject_type'=> Payment::class,
            'subject_id'  => $payment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $payment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        ActivityLog::create([
            'log_name'    => 'payment',
            'description' => 'updated',
            'subject_type'=> Payment::class,
            'subject_id'  => $payment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $payment->getOriginal(),
                'attributes' => $payment->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        ActivityLog::create([
            'log_name'    => 'payment',
            'description' => 'deleted',
            'subject_type'=> Payment::class,
            'subject_id'  => $payment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $payment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Payment "restored" event (if SoftDeletes is used).
     */
    public function restored(Payment $payment): void
    {
        ActivityLog::create([
            'log_name'    => 'payment',
            'description' => 'restored',
            'subject_type'=> Payment::class,
            'subject_id'  => $payment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $payment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        ActivityLog::create([
            'log_name'    => 'payment',
            'description' => 'force deleted',
            'subject_type'=> Payment::class,
            'subject_id'  => $payment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $payment->getAttributes(),
            ],
        ]);
    }
}