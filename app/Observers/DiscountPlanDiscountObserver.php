<?php

namespace App\Observers;

use App\Models\DiscountPlanDiscount;
use App\Models\ActivityLog;
use App\Models\Discount;
use Illuminate\Support\Facades\Auth;

class DiscountPlanDiscountObserver
{
    public function created(DiscountPlanDiscount $pivot): void
    {
        ActivityLog::create([
            'log_name'    => 'discount_plan_assignment',
            'description' => 'plan attached',
            'subject_type'=> Discount::class,   // log against the discount
            'subject_id'  => $pivot->discount_id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'discount_plan_id' => $pivot->discount_plan_id,
                'attributes'       => $pivot->getAttributes(),
            ],
        ]);
    }

    public function deleted(DiscountPlanDiscount $pivot): void
    {
        ActivityLog::create([
            'log_name'    => 'discount_plan_assignment',
            'description' => 'plan detached',
            'subject_type'=> Discount::class,
            'subject_id'  => $pivot->discount_id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'discount_plan_id' => $pivot->discount_plan_id,
                'attributes'       => $pivot->getAttributes(),
            ],
        ]);
    }

    // If you need to log updates on the pivot (rare), add 'updated' as well.
}