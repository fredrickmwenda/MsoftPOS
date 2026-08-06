<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\PurchaseProductReturn;
use Illuminate\Support\Facades\Auth;

class PurchaseProductReturnObserver
{
    /**
     * Handle the PurchaseProductReturn "created" event.
     */
    public function created(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'purchase_product_return',
            'description' => 'created',
            'subject_type'=> PurchaseProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the PurchaseProductReturn "updated" event.
     */
    public function updated(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'purchase_product_return',
            'description' => 'updated',
            'subject_type'=> PurchaseProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the PurchaseProductReturn "deleted" event.
     */
    public function deleted(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'purchase_product_return',
            'description' => 'deleted',
            'subject_type'=> PurchaseProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the PurchaseProductReturn "restored" event.
     */
    public function restored(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'purchase_product_return',
            'description' => 'restored',
            'subject_type'=> PurchaseProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the PurchaseProductReturn "force deleted" event.
     */
    public function forceDeleted(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'purchase_product_return',
            'description' => 'force deleted',
            'subject_type'=> PurchaseProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
