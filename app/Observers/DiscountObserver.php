<?php

namespace App\Observers;

use App\Models\Discount;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DiscountObserver
{
    /**
     * Build a structured context array for the discount.
     * This pulls in related data like Discount Plans.
     */
    protected function buildContext(Discount $discount): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $discount->loadMissing(['discountPlans']);

        return [
            'discount_name'  => $discount->name,
            'applicable_for' => $discount->applicable_for,
            'type'           => $discount->type, // e.g., 'flat' or 'percentage'
            'value'          => $discount->value,
            'valid_from'     => $discount->valid_from ? \Carbon\Carbon::parse($discount->valid_from)->format('Y-m-d') : 'N/A',
            'valid_till'     => $discount->valid_till ? \Carbon\Carbon::parse($discount->valid_till)->format('Y-m-d') : 'N/A',
            'minimum_qty'    => $discount->minimum_qty ?? 0,
            'maximum_qty'    => $discount->maximum_qty ?? 'Unlimited',
            'discount_plans' => $discount->discountPlans->isNotEmpty() ? $discount->discountPlans->pluck('name')->implode(', ') : 'None',
            'is_active'      => (bool) $discount->is_active,
        ];
    }

    public function created(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'     => 'discount',
            'description'  => 'created',
            'subject_type' => Discount::class,
            'subject_id'   => $discount->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($discount),
                'attributes' => $discount->getAttributes(),
            ],
        ]);
    }

    public function updated(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'     => 'discount',
            'description'  => 'updated',
            'subject_type' => Discount::class,
            'subject_id'   => $discount->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($discount),
                'old'        => $discount->getOriginal(),
                'attributes' => $discount->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    public function deleted(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'     => 'discount',
            'description'  => 'deleted',
            'subject_type' => Discount::class,
            'subject_id'   => $discount->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($discount),
                'attributes' => $discount->getAttributes(),
            ],
        ]);
    }

    public function restored(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'     => 'discount',
            'description'  => 'restored',
            'subject_type' => Discount::class,
            'subject_id'   => $discount->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($discount),
                'attributes' => $discount->getAttributes(),
            ],
        ]);
    }

    public function forceDeleted(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'     => 'discount',
            'description'  => 'force deleted',
            'subject_type' => Discount::class,
            'subject_id'   => $discount->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($discount),
                'attributes' => $discount->getAttributes(),
            ],
        ]);
    }
}