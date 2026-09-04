<?php

namespace App\Observers;

use App\Models\DiscountPlan;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DiscountPlanDiscountObserver
{
    /**
     * Build a structured context array for the discount plan.
     * This pulls in related data like the count of assigned customers.
     */
    protected function buildContext(DiscountPlan $plan): array
    {
        // Load relationship count safely
        $plan->loadCount(['customers']);

        return [
            'plan_name'        => $plan->name,
            'is_active'        => (bool) $plan->is_active,
            'customers_count'  => $plan->customers_count ?? 0,
        ];
    }

    /**
     * Handle the DiscountPlan "created" event.
     */
    public function created(DiscountPlan $plan): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan',
            'description'  => 'created',
            'subject_type' => DiscountPlan::class,
            'subject_id'   => $plan->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($plan),
                'attributes' => $plan->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the DiscountPlan "updated" event.
     */
    public function updated(DiscountPlan $plan): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan',
            'description'  => 'updated',
            'subject_type' => DiscountPlan::class,
            'subject_id'   => $plan->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($plan),
                'old'        => $plan->getOriginal(),
                'attributes' => $plan->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the DiscountPlan "deleted" event.
     */
    public function deleted(DiscountPlan $plan): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan',
            'description'  => 'deleted',
            'subject_type' => DiscountPlan::class,
            'subject_id'   => $plan->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($plan),
                'attributes' => $plan->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the DiscountPlan "restored" event.
     */
    public function restored(DiscountPlan $plan): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan',
            'description'  => 'restored',
            'subject_type' => DiscountPlan::class,
            'subject_id'   => $plan->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($plan),
                'attributes' => $plan->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the DiscountPlan "force deleted" event.
     */
    public function forceDeleted(DiscountPlan $plan): void
    {
        ActivityLog::create([
            'log_name'     => 'discount_plan',
            'description'  => 'force deleted',
            'subject_type' => DiscountPlan::class,
            'subject_id'   => $plan->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($plan),
                'attributes' => $plan->getAttributes(),
            ],
        ]);
    }
}