<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductAdjustment;
use Illuminate\Support\Facades\Auth;

class ProductAdjustmentObserver
{
    /**
     * Handle the ProductAdjustment "created" event.
     */
    public function created(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_adjustment',
            'description' => 'created',
            'subject_type'=> ProductAdjustment::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductAdjustment "updated" event.
     */
    public function updated(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_adjustment',
            'description' => 'updated',
            'subject_type'=> ProductAdjustment::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductAdjustment "deleted" event.
     */
    public function deleted(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_adjustment',
            'description' => 'deleted',
            'subject_type'=> ProductAdjustment::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductAdjustment "restored" event.
     */
    public function restored(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_adjustment',
            'description' => 'restored',
            'subject_type'=> ProductAdjustment::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductAdjustment "force deleted" event.
     */
    public function forceDeleted(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_adjustment',
            'description' => 'force deleted',
            'subject_type'=> ProductAdjustment::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
