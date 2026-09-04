<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CustomerObserver
{
    /**
     * Build a structured context array for the customer.
     * This pulls in related data like Customer Group, Creator, and Discount Plans.
     */
    protected function buildContext(Customer $customer): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $customer->loadMissing(['customerGroup', 'user', 'discountPlans']);

        return [
            'customer_name'    => $customer->name,
            'company_name'     => $customer->company_name ?? 'N/A',
            'email'            => $customer->email ?? 'N/A',
            'phone_number'     => $customer->phone_number ?? 'N/A',
            'customer_group'   => $customer->customerGroup ? $customer->customerGroup->name : 'None',
            'discount_plans'   => $customer->discountPlans->isNotEmpty() ? $customer->discountPlans->pluck('name')->implode(', ') : 'None',
            'points'           => $customer->points ?? 0,
            'deposit'          => $customer->deposit ?? 0,
            'is_active'        => (bool) $customer->is_active,
            'created_by'       => $customer->user ? $customer->user->name : 'System',
        ];
    }

    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        $this->invalidateCustomerCaches();

        ActivityLog::create([
            'log_name'     => 'customer',
            'description'  => 'created',
            'subject_type' => Customer::class,
            'subject_id'   => $customer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customer),
                'attributes' => $customer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        $this->invalidateCustomerCaches();

        ActivityLog::create([
            'log_name'     => 'customer',
            'description'  => 'updated',
            'subject_type' => Customer::class,
            'subject_id'   => $customer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customer),
                'old'        => $customer->getOriginal(),
                'attributes' => $customer->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        $this->invalidateCustomerCaches();

        ActivityLog::create([
            'log_name'     => 'customer',
            'description'  => 'deleted',
            'subject_type' => Customer::class,
            'subject_id'   => $customer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customer),
                'attributes' => $customer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        $this->invalidateCustomerCaches();

        ActivityLog::create([
            'log_name'     => 'customer',
            'description'  => 'restored',
            'subject_type' => Customer::class,
            'subject_id'   => $customer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customer),
                'attributes' => $customer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        $this->invalidateCustomerCaches();

        ActivityLog::create([
            'log_name'     => 'customer',
            'description'  => 'force deleted',
            'subject_type' => Customer::class,
            'subject_id'   => $customer->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customer),
                'attributes' => $customer->getAttributes(),
            ],
        ]);
    }

    /**
     * Invalidate all customer-related caches
     */
    private function invalidateCustomerCaches(): void
    {
        Cache::forget('customer_list');
    }
}