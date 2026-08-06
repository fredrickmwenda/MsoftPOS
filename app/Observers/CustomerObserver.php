<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        $this->invalidateCustomerCaches();

        ActivityLog::create([
            'log_name'    => 'customer',
            'description' => 'created',
            'subject_type'=> Customer::class,
            'subject_id'  => $customer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'customer',
            'description' => 'updated',
            'subject_type'=> Customer::class,
            'subject_id'  => $customer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $customer->getOriginal(),
                'attributes' => $customer->getChanges(),
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
            'log_name'    => 'customer',
            'description' => 'deleted',
            'subject_type'=> Customer::class,
            'subject_id'  => $customer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'customer',
            'description' => 'restored',
            'subject_type'=> Customer::class,
            'subject_id'  => $customer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'customer',
            'description' => 'force deleted',
            'subject_type'=> Customer::class,
            'subject_id'  => $customer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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