<?php

namespace App\Observers;

use App\Models\Warehouse;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WarehouseObserver
{
    /**
     * Handle the Warehouse "created" event.
     */
    public function created(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');

        ActivityLog::create([
            'log_name'    => 'warehouse',
            'description' => 'created',
            'subject_type'=> Warehouse::class,
            'subject_id'  => $warehouse->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $warehouse->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Warehouse "updated" event.
     */
    public function updated(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');

        // Check if this update is actually a "soft delete" (is_active turned to false)
        if ($warehouse->isDirty('is_active') && $warehouse->is_active == false) {
            $description = 'deleted';
        } else {
            $description = 'updated';
        }

        ActivityLog::create([
            'log_name'    => 'warehouse',
            'description' => $description,
            'subject_type'=> Warehouse::class,
            'subject_id'  => $warehouse->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $warehouse->getOriginal(),
                'attributes' => $warehouse->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the Warehouse "deleted" event. (Only fires if you use ->delete() – you currently don’t)
     */
    public function deleted(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');

        ActivityLog::create([
            'log_name'    => 'warehouse',
            'description' => 'permanently deleted',
            'subject_type'=> Warehouse::class,
            'subject_id'  => $warehouse->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $warehouse->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Warehouse "restored" event.
     */
    public function restored(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');

        ActivityLog::create([
            'log_name'    => 'warehouse',
            'description' => 'restored',
            'subject_type'=> Warehouse::class,
            'subject_id'  => $warehouse->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $warehouse->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Warehouse "force deleted" event.
     */
    public function forceDeleted(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');

        ActivityLog::create([
            'log_name'    => 'warehouse',
            'description' => 'force deleted',
            'subject_type'=> Warehouse::class,
            'subject_id'  => $warehouse->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $warehouse->getAttributes(),
            ],
        ]);
    }
}