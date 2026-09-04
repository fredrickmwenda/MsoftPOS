<?php

namespace App\Observers;

use App\Models\Warehouse;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WarehouseObserver
{
    /**
     * Build a structured context array for the warehouse.
     * This pulls in related data like assigned users count.
     */
    protected function buildContext(Warehouse $warehouse): array
    {
        // Load relationship count safely
        $warehouse->loadCount(['users']);

        return [
            'warehouse_name' => $warehouse->name,
            'email'          => $warehouse->email ?? 'N/A',
            'phone'          => $warehouse->phone ?? 'N/A',
            'address'        => $warehouse->address ?? 'N/A',
            'is_active'      => (bool) $warehouse->is_active,
            'users_count'    => $warehouse->users_count ?? 0,
        ];
    }

    /**
     * Handle the Warehouse "created" event.
     */
    public function created(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');

        ActivityLog::create([
            'log_name'     => 'warehouse',
            'description'  => 'created',
            'subject_type' => Warehouse::class,
            'subject_id'   => $warehouse->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($warehouse),
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
        } 
        // Check if it's a restoration (is_active turned back to true)
        elseif ($warehouse->isDirty('is_active') && $warehouse->is_active == true) {
            $description = 'restored';
        }
        else {
            $description = 'updated';
        }

        ActivityLog::create([
            'log_name'     => 'warehouse',
            'description'  => $description,
            'subject_type' => Warehouse::class,
            'subject_id'   => $warehouse->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($warehouse),
                'old'        => $warehouse->getOriginal(),
                'attributes' => $warehouse->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the Warehouse "deleted" event. (Hard delete)
     */
    public function deleted(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');

        ActivityLog::create([
            'log_name'     => 'warehouse',
            'description'  => 'permanently deleted',
            'subject_type' => Warehouse::class,
            'subject_id'   => $warehouse->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($warehouse),
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
            'log_name'     => 'warehouse',
            'description'  => 'restored',
            'subject_type' => Warehouse::class,
            'subject_id'   => $warehouse->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($warehouse),
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
            'log_name'     => 'warehouse',
            'description'  => 'force deleted',
            'subject_type' => Warehouse::class,
            'subject_id'   => $warehouse->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($warehouse),
                'attributes' => $warehouse->getAttributes(),
            ],
        ]);
    }
}