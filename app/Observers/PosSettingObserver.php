<?php

namespace App\Observers;

use App\Models\PosSetting;
use Illuminate\Support\Facades\Cache;

class PosSettingObserver
{
    /**
     * Handle the PosSetting "created" event.
     */
    public function created(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');
    }

    /**
     * Handle the PosSetting "updated" event.
     */
    public function updated(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');
    }

    /**
     * Handle the PosSetting "deleted" event.
     */
    public function deleted(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');
    }

    /**
     * Handle the PosSetting "restored" event.
     */
    public function restored(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');
    }

    /**
     * Handle the PosSetting "force deleted" event.
     */
    public function forceDeleted(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');
    }
}
