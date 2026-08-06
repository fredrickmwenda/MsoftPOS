<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Product_Warehouse as ProductWarehouse;
use Illuminate\Support\Facades\Auth;

class ProductWarehouseObserver
{
    /**
     * Handle the ProductWarehouse "created" event.
     */
    public function created(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_warehouse',
            'description' => 'created',
            'subject_type'=> ProductWarehouse::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductWarehouse "updated" event.
     */
    public function updated(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_warehouse',
            'description' => 'updated',
            'subject_type'=> ProductWarehouse::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductWarehouse "deleted" event.
     */
    public function deleted(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_warehouse',
            'description' => 'deleted',
            'subject_type'=> ProductWarehouse::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductWarehouse "restored" event.
     */
    public function restored(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_warehouse',
            'description' => 'restored',
            'subject_type'=> ProductWarehouse::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductWarehouse "force deleted" event.
     */
    public function forceDeleted(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_warehouse',
            'description' => 'force delete',
            'subject_type'=> ProductWarehouse::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
