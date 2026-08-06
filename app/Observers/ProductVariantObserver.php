<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "created" event.
     */
    public function created(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_variant',
            'description' => 'created',
            'subject_type'=> ProductVariant::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductVariant "updated" event.
     */
    public function updated(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_variant',
            'description' => 'updated',
            'subject_type'=> ProductVariant::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_variant',
            'description' => 'deleted',
            'subject_type'=> ProductVariant::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductVariant "restored" event.
     */
    public function restored(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_variant',
            'description' => 'restored',
            'subject_type'=> ProductVariant::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductVariant "force deleted" event.
     */
    public function forceDeleted(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_variant',
            'description' => 'force deleted',
            'subject_type'=> ProductVariant::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
