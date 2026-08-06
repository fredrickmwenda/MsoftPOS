<?php

namespace App\Observers;

use App\Models\PosSetting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PosSettingObserver
{
    /**
     * Handle the PosSetting "created" event.
     */
    public function created(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');

        ActivityLog::create([
            'log_name'    => 'pos_setting',
            'description' => 'created',
            'subject_type'=> PosSetting::class,
            'subject_id'  => $posSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $posSetting->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the PosSetting "updated" event.
     */
    public function updated(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');

        ActivityLog::create([
            'log_name'    => 'pos_setting',
            'description' => 'updated',
            'subject_type'=> PosSetting::class,
            'subject_id'  => $posSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $posSetting->getOriginal(),
                'attributes' => $posSetting->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the PosSetting "deleted" event.
     */
    public function deleted(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');

        ActivityLog::create([
            'log_name'    => 'pos_setting',
            'description' => 'deleted',
            'subject_type'=> PosSetting::class,
            'subject_id'  => $posSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $posSetting->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the PosSetting "restored" event.
     */
    public function restored(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');

        ActivityLog::create([
            'log_name'    => 'pos_setting',
            'description' => 'restored',
            'subject_type'=> PosSetting::class,
            'subject_id'  => $posSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $posSetting->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the PosSetting "force deleted" event.
     */
    public function forceDeleted(PosSetting $posSetting): void
    {
        Cache::forget('pos_setting');

        ActivityLog::create([
            'log_name'    => 'pos_setting',
            'description' => 'force deleted',
            'subject_type'=> PosSetting::class,
            'subject_id'  => $posSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $posSetting->getAttributes(),
            ],
        ]);
    }
}