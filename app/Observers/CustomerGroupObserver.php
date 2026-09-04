<?php

namespace App\Observers;

use App\Models\CustomerGroup;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CustomerGroupObserver
{
    /**
     * Build a structured context array for the customer group.
     */
    protected function buildContext(CustomerGroup $customerGroup): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $customerGroup->loadMissing(['customers']);

        return [
            'group_name'      => $customerGroup->name,
            'percentage'      => $customerGroup->percentage,
            'is_active'       => (bool) $customerGroup->is_active,
            'customer_count'  => $customerGroup->customers ? $customerGroup->customers->count() : 0,
        ];
    }

    /**
     * Handle the CustomerGroup "created" event.
     */
    public function created(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');

        ActivityLog::create([
            'log_name'     => 'customer_group',
            'description'  => 'created',
            'subject_type' => CustomerGroup::class,
            'subject_id'   => $customerGroup->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customerGroup),
                'attributes' => $customerGroup->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the CustomerGroup "updated" event.
     */
    public function updated(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');

        ActivityLog::create([
            'log_name'     => 'customer_group',
            'description'  => 'updated',
            'subject_type' => CustomerGroup::class,
            'subject_id'   => $customerGroup->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customerGroup),
                'old'        => $customerGroup->getOriginal(),
                'attributes' => $customerGroup->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the CustomerGroup "deleted" event.
     */
    public function deleted(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');

        ActivityLog::create([
            'log_name'     => 'customer_group',
            'description'  => 'deleted',
            'subject_type' => CustomerGroup::class,
            'subject_id'   => $customerGroup->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customerGroup),
                'attributes' => $customerGroup->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the CustomerGroup "restored" event.
     */
    public function restored(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');

        ActivityLog::create([
            'log_name'     => 'customer_group',
            'description'  => 'restored',
            'subject_type' => CustomerGroup::class,
            'subject_id'   => $customerGroup->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customerGroup),
                'attributes' => $customerGroup->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the CustomerGroup "force deleted" event.
     */
    public function forceDeleted(CustomerGroup $customerGroup): void
    {
        Cache::forget('customer_group_list');

        ActivityLog::create([
            'log_name'     => 'customer_group',
            'description'  => 'force deleted',
            'subject_type' => CustomerGroup::class,
            'subject_id'   => $customerGroup->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($customerGroup),
                'attributes' => $customerGroup->getAttributes(),
            ],
        ]);
    }
}