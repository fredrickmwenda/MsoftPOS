<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductReturn;
use Illuminate\Support\Facades\Auth;

class ProductReturnObserver
{
    /**
     * Handle the ProductReturn "created" event.
     */
    public function created(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_return',
            'description' => 'created',
            'subject_type'=> ProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductReturn "updated" event.
     */
    public function updated(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_return',
            'description' => 'updated',
            'subject_type'=> ProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductReturn "deleted" event.
     */
    public function deleted(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_return',
            'description' => 'deleted',
            'subject_type'=> ProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductReturn "restored" event.
     */
    public function restored(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_return',
            'description' => 'restored',
            'subject_type'=> ProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductReturn "force deleted" event.
     */
    public function forceDeleted(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_return',
            'description' => 'force deleted',
            'subject_type'=> ProductReturn::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
