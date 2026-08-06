<?php

namespace App\Observers;

use App\Models\DiscountPlanCustomer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DiscountPlanCustomerObserver
{
    /**
     * Handle the DiscountPlanCustomer "created" event (customer attached to plan).
     */
    public function created(DiscountPlanCustomer $assignment): void
    {
        ActivityLog::create([
            'log_name'    => 'discount_plan_customer_assignment',
            'description' => 'customer assigned to plan',
            'subject_type'=> DiscountPlanCustomer::class,
            'subject_id'  => $assignment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'discount_plan_id' => $assignment->discount_plan_id,
                'customer_id'      => $assignment->customer_id,
                'attributes'       => $assignment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the DiscountPlanCustomer "updated" event (optional – if pivot has extra fields).
     */
    public function updated(DiscountPlanCustomer $assignment): void
    {
        ActivityLog::create([
            'log_name'    => 'discount_plan_customer_assignment',
            'description' => 'assignment updated',
            'subject_type'=> DiscountPlanCustomer::class,
            'subject_id'  => $assignment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $assignment->getOriginal(),
                'attributes' => $assignment->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the DiscountPlanCustomer "deleted" event (customer removed from plan).
     */
    public function deleted(DiscountPlanCustomer $assignment): void
    {
        ActivityLog::create([
            'log_name'    => 'discount_plan_customer_assignment',
            'description' => 'customer removed from plan',
            'subject_type'=> DiscountPlanCustomer::class,
            'subject_id'  => $assignment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'discount_plan_id' => $assignment->discount_plan_id,
                'customer_id'      => $assignment->customer_id,
                'attributes'       => $assignment->getAttributes(),
            ],
        ]);
    }
}