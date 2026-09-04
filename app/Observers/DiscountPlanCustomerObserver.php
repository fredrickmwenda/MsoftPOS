<?php

namespace App\Observers;

use App\Models\DiscountPlanCustomer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DiscountPlanCustomerObserver
{
    /**
     * Build a structured context array for the pivot record.
     * This pulls in related data like Discount Plan Name and Customer Name.
     */
    protected function buildContext(DiscountPlanCustomer $assignment): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $assignment->loadMissing(['discountPlan', 'customer']);

        return [
            'discount_plan'   => $assignment->discountPlan ? $assignment->discountPlan->name : 'Unknown Plan',
            'customer_name'   => $assignment->customer ? $assignment->customer->name : 'Unknown Customer',
            'customer_phone'  => $assignment->customer ? $assignment->customer->phone_number : 'N/A',
        ];
    }

    /**
     * Handle the DiscountPlanCustomer "created" event (customer attached to plan).
     */
    public function created(DiscountPlanCustomer $assignment): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan_customer_assignment',
            'description'  => 'customer assigned to plan',
            'subject_type' => DiscountPlanCustomer::class,
            'subject_id'   => $assignment->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($assignment),
                'attributes' => $assignment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the DiscountPlanCustomer "updated" event (optional – if pivot has extra fields).
     */
    public function updated(DiscountPlanCustomer $assignment): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan_customer_assignment',
            'description'  => 'assignment updated',
            'subject_type' => DiscountPlanCustomer::class,
            'subject_id'   => $assignment->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($assignment),
                'old'        => $assignment->getOriginal(),
                'attributes' => $assignment->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the DiscountPlanCustomer "deleted" event (customer removed from plan).
     */
    public function deleted(DiscountPlanCustomer $assignment): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan_customer_assignment',
            'description'  => 'customer removed from plan',
            'subject_type' => DiscountPlanCustomer::class,
            'subject_id'   => $assignment->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($assignment),
                'attributes' => $assignment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the DiscountPlanCustomer "restored" event.
     */
    public function restored(DiscountPlanCustomer $assignment): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan_customer_assignment',
            'description'  => 'assignment restored',
            'subject_type' => DiscountPlanCustomer::class,
            'subject_id'   => $assignment->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($assignment),
                'attributes' => $assignment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the DiscountPlanCustomer "force deleted" event.
     */
    public function forceDeleted(DiscountPlanCustomer $assignment): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan_customer_assignment',
            'description'  => 'assignment force deleted',
            'subject_type' => DiscountPlanCustomer::class,
            'subject_id'   => $assignment->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($assignment),
                'attributes' => $assignment->getAttributes(),
            ],
        ]);
    }
}