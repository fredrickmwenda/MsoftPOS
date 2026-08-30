<?php

namespace App\Observers;

use App\Models\BlackListCustomer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class BlackListCustomerObserver
{
    /**
     * Handle the BlackListCustomer "created" event.
     */
    public function created(BlackListCustomer $blackListCustomer): void
    {
        ActivityLog::create([
            'log_name'    => 'blacklist_customer',
            'description' => 'created',
            'subject_type'=> BlackListCustomer::class,
            'subject_id'  => $blackListCustomer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $blackListCustomer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the BlackListCustomer "updated" event.
     */
    public function updated(BlackListCustomer $blackListCustomer): void
    {
        ActivityLog::create([
            'log_name'    => 'blacklist_customer',
            'description' => 'updated',
            'subject_type'=> BlackListCustomer::class,
            'subject_id'  => $blackListCustomer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $blackListCustomer->getOriginal(),
                'attributes' => $blackListCustomer->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the BlackListCustomer "deleted" event.
     */
    public function deleted(BlackListCustomer $blackListCustomer): void
    {
        ActivityLog::create([
            'log_name'    => 'blacklist_customer',
            'description' => 'deleted',
            'subject_type'=> BlackListCustomer::class,
            'subject_id'  => $blackListCustomer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $blackListCustomer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the BlackListCustomer "restored" event (if SoftDeletes is used).
     */
    public function restored(BlackListCustomer $blackListCustomer): void
    {
        ActivityLog::create([
            'log_name'    => 'blacklist_customer',
            'description' => 'restored',
            'subject_type'=> BlackListCustomer::class,
            'subject_id'  => $blackListCustomer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $blackListCustomer->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the BlackListCustomer "force deleted" event.
     */
    public function forceDeleted(BlackListCustomer $blackListCustomer): void
    {
        ActivityLog::create([
            'log_name'    => 'blacklist_customer',
            'description' => 'force deleted',
            'subject_type'=> BlackListCustomer::class,
            'subject_id'  => $blackListCustomer->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $blackListCustomer->getAttributes(),
            ],
        ]);
    }
}
