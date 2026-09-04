<?php

namespace App\Observers;

use App\Models\Coupon;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CouponObserver
{
    /**
     * Build a structured context array for the coupon.
     * This pulls in related data like the User who created it.
     */
    protected function buildContext(Coupon $coupon): array
    {
        // Safely get the user name if a user_id exists
        $createdBy = 'System';
        if ($coupon->user_id) {
            $user = User::find($coupon->user_id);
            if ($user) {
                $createdBy = $user->name;
            }
        }

        return [
            'coupon_code'    => $coupon->code,
            'type'           => $coupon->type, // e.g., 'fixed' or 'percentage'
            'amount'         => $coupon->amount,
            'minimum_amount' => $coupon->minimum_amount,
            'quantity'       => $coupon->quantity,
            'used'           => $coupon->used,
            'expired_date'   => $coupon->expired_date ? \Carbon\Carbon::parse($coupon->expired_date)->format('Y-m-d') : 'N/A',
            'is_active'      => (bool) $coupon->is_active,
            'created_by'     => $createdBy,
        ];
    }

    /**
     * Handle the Coupon "created" event.
     */
    public function created(Coupon $coupon): void
    {
        Cache::forget('coupon_list');

        ActivityLog::create([
            'log_name'     => 'coupon',
            'description'  => 'created',
            'subject_type' => Coupon::class,
            'subject_id'   => $coupon->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($coupon),
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
            'log_name'     => 'coupon',
            'description'  => 'updated',
            'subject_type' => Coupon::class,
            'subject_id'   => $coupon->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($coupon),
                'old'        => $coupon->getOriginal(),
                'attributes' => $coupon->getChanges(), // Only the fields that changed
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
            'log_name'     => 'coupon',
            'description'  => 'deleted',
            'subject_type' => Coupon::class,
            'subject_id'   => $coupon->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($coupon),
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
            'log_name'     => 'coupon',
            'description'  => 'restored',
            'subject_type' => Coupon::class,
            'subject_id'   => $coupon->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($coupon),
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
            'log_name'     => 'coupon',
            'description'  => 'force deleted',
            'subject_type' => Coupon::class,
            'subject_id'   => $coupon->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($coupon),
                'attributes' => $coupon->getAttributes(),
            ],
        ]);
    }
}