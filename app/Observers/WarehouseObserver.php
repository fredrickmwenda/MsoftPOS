<?php

namespace App\Observers;

use App\Models\Warehouse;
use Illuminate\Support\Facades\Cache;

class WarehouseObserver
{
    /**
     * Handle the Warehouse "created" event.
     */
    public function created(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');
    }

    /**
     * Handle the Warehouse "updated" event.
     */
    public function updated(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');
    }

    /**
     * Handle the Warehouse "deleted" event.
     */
    public function deleted(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');
    }

    /**
     * Handle the Warehouse "restored" event.
     */
    public function restored(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');
    }

    /**
     * Handle the Warehouse "force deleted" event.
     */
    public function forceDeleted(Warehouse $warehouse): void
    {
        Cache::forget('warehouse_list');
    }
}
