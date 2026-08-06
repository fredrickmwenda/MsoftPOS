<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductPurchase;
use Illuminate\Support\Facades\Auth;

class ProductPurchaseObserver
{
    /**
     * Handle the ProductPurchase "created" event.
     */
    public function created(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_purchase',
            'description' => 'created',
            'subject_type'=> ProductPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductPurchase "updated" event.
     */
    public function updated(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_purchase',
            'description' => 'updated',
            'subject_type'=> ProductPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductPurchase "deleted" event.
     */
    public function deleted(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_purchase',
            'description' => 'deleted',
            'subject_type'=> ProductPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductPurchase "restored" event.
     */
    public function restored(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_purchase',
            'description' => 'restored',
            'subject_type'=> ProductPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductPurchase "force deleted" event.
     */
    public function forceDeleted(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_purchase',
            'description' => 'force deleted',
            'subject_type'=> ProductPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
