<?php

namespace App\Observers;

use App\Models\Adjustment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class AdjustmentObserver
{
    /**
     * Handle the Adjustment "created" event.
     */
    public function created(Adjustment $adjustment): void
    {
        ActivityLog::create([
            'log_name'    => 'adjustment',
            'description' => 'created',
            'subject_type'=> Adjustment::class,
            'subject_id'  => $adjustment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $adjustment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Adjustment "updated" event.
     */
    public function updated(Adjustment $adjustment): void
    {
        ActivityLog::create([
            'log_name'    => 'adjustment',
            'description' => 'updated',
            'subject_type'=> Adjustment::class,
            'subject_id'  => $adjustment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $adjustment->getOriginal(),
                'attributes' => $adjustment->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the Adjustment "deleted" event.
     */
    public function deleted(Adjustment $adjustment): void
    {
        ActivityLog::create([
            'log_name'    => 'adjustment',
            'description' => 'deleted',
            'subject_type'=> Adjustment::class,
            'subject_id'  => $adjustment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $adjustment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Adjustment "restored" event (only if using SoftDeletes).
     */
    public function restored(Adjustment $adjustment): void
    {
        ActivityLog::create([
            'log_name'    => 'adjustment',
            'description' => 'restored',
            'subject_type'=> Adjustment::class,
            'subject_id'  => $adjustment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $adjustment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Adjustment "force deleted" event (only if using SoftDeletes).
     */
    public function forceDeleted(Adjustment $adjustment): void
    {
        ActivityLog::create([
            'log_name'    => 'adjustment',
            'description' => 'force deleted',
            'subject_type'=> Adjustment::class,
            'subject_id'  => $adjustment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $adjustment->getAttributes(),
            ],
        ]);
    }
}