<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\DiscountPlanDiscount;
use Illuminate\Support\Facades\Auth;

class DiscountPlanDiscountObserver
{
    protected function buildContext(DiscountPlanDiscount $link): array
    {
        $plan = $link->discountPlan;

        if (! $plan) {
            return [
                'discount_plan_id' => $link->discount_plan_id,
                'discount_id' => $link->discount_id,
            ];
        }

        $plan->loadCount('customers');

        return [
            'discount_plan_id' => $link->discount_plan_id,
            'discount_id' => $link->discount_id,
            'plan_name' => $plan->name,
            'is_active' => (bool) $plan->is_active,
            'customers_count' => $plan->customers_count ?? 0,
        ];
    }

    protected function record(string $description, DiscountPlanDiscount $link, array $properties = []): void
    {
        ActivityLog::create([
            'log_name' => 'discount_plan_discount',
            'description' => $description,
            'subject_type' => DiscountPlanDiscount::class,
            'subject_id' => $link->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id' => Auth::id(),
            'properties' => array_merge([
                'context' => $this->buildContext($link),
            ], $properties),
        ]);
    }

    public function created(DiscountPlanDiscount $link): void
    {
        $this->record('created', $link, ['attributes' => $link->getAttributes()]);
    }

    public function updated(DiscountPlanDiscount $link): void
    {
        $this->record('updated', $link, [
            'old' => $link->getOriginal(),
            'attributes' => $link->getChanges(),
        ]);
    }

    public function deleted(DiscountPlanDiscount $link): void
    {
        $this->record('deleted', $link, ['attributes' => $link->getAttributes()]);
    }

    public function restored(DiscountPlanDiscount $link): void
    {
        $this->record('restored', $link, ['attributes' => $link->getAttributes()]);
    }

    public function forceDeleted(DiscountPlanDiscount $link): void
    {
        $this->record('force deleted', $link, ['attributes' => $link->getAttributes()]);
    }
}
