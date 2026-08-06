<?php

namespace App\Observers;

use App\Models\Coupon;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CouponObserver
{
    /**
     * Handle the Coupon "created" event.
     */
    public function created(Coupon $coupon): void
    {
        Cache::forget('coupon_list');

        ActivityLog::create([
            'log_name'    => 'coupon',
            'description' => 'created',
            'subject_type'=> Coupon::class,
            'subject_id'  => $coupon->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $coupon->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Coupon "updated" event.
     */
    public function updated(Coupon $coupon): void
    {
        Cache::forget('coupon_list');

        ActivityLog::create([
            'log_name'    => 'coupon',
            'description' => 'updated',
            'subject_type'=> Coupon::class,
            'subject_id'  => $coupon->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $coupon->getOriginal(),
                'attributes' => $coupon->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the Coupon "deleted" event.
     */
    public function deleted(Coupon $coupon): void
    {
        Cache::forget('coupon_list');

        ActivityLog::create([
            'log_name'    => 'coupon',
            'description' => 'deleted',
            'subject_type'=> Coupon::class,
            'subject_id'  => $coupon->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $coupon->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Coupon "restored" event.
     */
    public function restored(Coupon $coupon): void
    {
        Cache::forget('coupon_list');

        ActivityLog::create([
            'log_name'    => 'coupon',
            'description' => 'restored',
            'subject_type'=> Coupon::class,
            'subject_id'  => $coupon->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $coupon->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Coupon "force deleted" event.
     */
    public function forceDeleted(Coupon $coupon): void
    {
        Cache::forget('coupon_list');

        ActivityLog::create([
            'log_name'    => 'coupon',
            'description' => 'force deleted',
            'subject_type'=> Coupon::class,
            'subject_id'  => $coupon->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $coupon->getAttributes(),
            ],
        ]);
    }
}