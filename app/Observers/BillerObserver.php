<?php

namespace App\Observers;

use App\Models\Biller;
use Illuminate\Support\Facades\Cache;

class BillerObserver
{
    /**
     * Handle the Biller "created" event.
     */
    public function created(Biller $biller): void
    {
        Cache::forget('biller_list');
    }

    /**
     * Handle the Biller "updated" event.
     */
    public function updated(Biller $biller): void
    {
        Cache::forget('biller_list');
    }

    /**
     * Handle the Biller "deleted" event.
     */
    public function deleted(Biller $biller): void
    {
        Cache::forget('biller_list');
    }

    /**
     * Handle the Biller "restored" event.
     */
    public function restored(Biller $biller): void
    {
        Cache::forget('biller_list');
    }

    /**
     * Handle the Biller "force deleted" event.
     */
    public function forceDeleted(Biller $biller): void
    {
        Cache::forget('biller_list');
    }
}
