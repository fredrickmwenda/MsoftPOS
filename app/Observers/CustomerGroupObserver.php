<?php

namespace App\Observers;

use App\Models\CustomerGroup;
use Illuminate\Support\Facades\Cache;

class CustomerGroupObserver
{
    /**
     * Handle the CustomerGroup "created" event.
     */
    public function created(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');
    }

    /**
     * Handle the CustomerGroup "updated" event.
     */
    public function updated(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');
    }

    /**
     * Handle the CustomerGroup "deleted" event.
     */
    public function deleted(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');
    }

    /**
     * Handle the CustomerGroup "restored" event.
     */
    public function restored(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');
    }

    /**
     * Handle the CustomerGroup "force deleted" event.
     */
    public function forceDeleted(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');
    }
}
