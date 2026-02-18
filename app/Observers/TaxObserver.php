<?php

namespace App\Observers;

use App\Models\Tax;
use Illuminate\Support\Facades\Cache;

class TaxObserver
{
    /**
     * Handle the Tax "created" event.
     */
    public function created(Tax $tax): void
    {
        Cache::forget('tax_list');
    }

    /**
     * Handle the Tax "updated" event.
     */
    public function updated(Tax $tax): void
    {
        Cache::forget('tax_list');
    }

    /**
     * Handle the Tax "deleted" event.
     */
    public function deleted(Tax $tax): void
    {
        Cache::forget('tax_list');
    }

    /**
     * Handle the Tax "restored" event.
     */
    public function restored(Tax $tax): void
    {
        Cache::forget('tax_list');
    }

    /**
     * Handle the Tax "force deleted" event.
     */
    public function forceDeleted(Tax $tax): void
    {
        Cache::forget('tax_list');
    }
}
