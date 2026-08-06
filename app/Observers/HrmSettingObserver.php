<?php

namespace App\Observers;

use App\Models\HrmSetting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class HrmSettingObserver
{
    /**
     * Handle the HrmSetting "created" event.
     */
    public function created(HrmSetting $hrmSetting): void
    {
        ActivityLog::create([
            'log_name'    => 'hrm_setting',
            'description' => 'created',
            'subject_type'=> HrmSetting::class,
            'subject_id'  => $hrmSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $hrmSetting->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the HrmSetting "updated" event.
     */
    public function updated(HrmSetting $hrmSetting): void
    {
        ActivityLog::create([
            'log_name'    => 'hrm_setting',
            'description' => 'updated',
            'subject_type'=> HrmSetting::class,
            'subject_id'  => $hrmSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $hrmSetting->getOriginal(),
                'attributes' => $hrmSetting->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the HrmSetting "deleted" event.
     */
    public function deleted(HrmSetting $hrmSetting): void
    {
        ActivityLog::create([
            'log_name'    => 'hrm_setting',
            'description' => 'deleted',
            'subject_type'=> HrmSetting::class,
            'subject_id'  => $hrmSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $hrmSetting->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the HrmSetting "restored" event.
     */
    public function restored(HrmSetting $hrmSetting): void
    {
        ActivityLog::create([
            'log_name'    => 'hrm_setting',
            'description' => 'restored',
            'subject_type'=> HrmSetting::class,
            'subject_id'  => $hrmSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $hrmSetting->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the HrmSetting "force deleted" event.
     */
    public function forceDeleted(HrmSetting $hrmSetting): void
    {
        ActivityLog::create([
            'log_name'    => 'hrm_setting',
            'description' => 'force deleted',
            'subject_type'=> HrmSetting::class,
            'subject_id'  => $hrmSetting->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $hrmSetting->getAttributes(),
            ],
        ]);
    }
}