<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Product_Supplier as ProductSupplier ;
use Illuminate\Support\Facades\Auth;


class ProductSupplierObserver
{
    /**
     * Handle the ProductSupplier "created" event.
     */
    public function created(ProductSupplier $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_supplier',
            'description' => 'created',
            'subject_type'=> ProductSupplier::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductSupplier "updated" event.
     */
    public function updated(ProductSupplier $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_supplier',
            'description' => 'updated',
            'subject_type'=> ProductSupplier::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductSupplier "deleted" event.
     */
    public function deleted(ProductSupplier $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_supplier',
            'description' => 'deleted',
            'subject_type'=> ProductSupplier::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductSupplier "restored" event.
     */
    public function restored(ProductSupplier $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_supplier',
            'description' => 'restored',
            'subject_type'=> ProductSupplier::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductSupplier "force deleted" event.
     */
    public function forceDeleted(ProductSupplier $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_supplier',
            'description' => 'force deleted',
            'subject_type'=> ProductSupplier::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
