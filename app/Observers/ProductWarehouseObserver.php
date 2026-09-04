<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Product_Warehouse as ProductWarehouse;
use Illuminate\Support\Facades\Auth;

class ProductWarehouseObserver
{
    /**
     * Build a structured context array for the product-warehouse inventory record.
     * This pulls in related data like Product, Warehouse, and Variant.
     */
    protected function buildContext(ProductWarehouse $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['product', 'warehouse', 'variant']);

        return [
            'product_name'      => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'      => $model->product ? $model->product->code : 'N/A',
            'warehouse_name'    => $model->warehouse ? $model->warehouse->name : 'Unknown Warehouse',
            'variant_name'      => $model->variant ? $model->variant->name : 'None',
            'product_batch_id'  => $model->product_batch_id ?: 'None',
            'imei_number'       => $model->imei_number ?: 'None',
            'qty'               => $model->qty,
            'price'             => $model->price,
        ];
    }

    /**
     * Handle the ProductWarehouse "created" event.
     */
    public function created(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_warehouse',
            'description'  => 'created',
            'subject_type' => ProductWarehouse::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the ProductWarehouse "updated" event.
     */
    public function updated(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_warehouse',
            'description'  => 'updated',
            'subject_type' => ProductWarehouse::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the ProductWarehouse "deleted" event.
     */
    public function deleted(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_warehouse',
            'description'  => 'deleted',
            'subject_type' => ProductWarehouse::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the ProductWarehouse "restored" event.
     */
    public function restored(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_warehouse',
            'description'  => 'restored',
            'subject_type' => ProductWarehouse::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the ProductWarehouse "force deleted" event.
     */
    public function forceDeleted(ProductWarehouse $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_warehouse',
            'description'  => 'force deleted',
            'subject_type' => ProductWarehouse::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }
}