<?php

namespace App\Observers;

use App\Models\ProductReturn;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ProductReturnObserver
{
    /**
     * Build a structured context array for the product return (line item).
     * This pulls in related data like Return Reference, Product, Variant, Batch, and Unit.
     */
    protected function buildContext(ProductReturn $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['saleReturn', 'product', 'variant', 'productBatch', 'saleUnit']);

        return [
            'return_reference' => $model->saleReturn ? $model->saleReturn->reference_no : 'N/A',
            'product_name'     => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'     => $model->product ? $model->product->code : 'N/A',
            'variant'          => $model->variant ? $model->variant->name : 'None',
            'batch_no'         => $model->productBatch ? $model->productBatch->batch_no : 'None',
            'unit'             => $model->saleUnit ? $model->saleUnit->unit_name : 'N/A',
            'imei_number'      => $model->imei_number ?: 'None',
            'qty'              => $model->qty,
            'net_unit_price'   => $model->net_unit_price,
            'total'            => $model->total,
        ];
    }

    /**
     * Handle the ProductReturn "created" event.
     */
    public function created(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_return',
            'description'  => 'created',
            'subject_type' => ProductReturn::class,
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
     * Handle the ProductReturn "updated" event.
     */
    public function updated(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_return',
            'description'  => 'updated',
            'subject_type' => ProductReturn::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the ProductReturn "deleted" event.
     */
    public function deleted(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_return',
            'description'  => 'deleted',
            'subject_type' => ProductReturn::class,
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
     * Handle the ProductReturn "restored" event.
     */
    public function restored(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_return',
            'description'  => 'restored',
            'subject_type' => ProductReturn::class,
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
     * Handle the ProductReturn "force deleted" event.
     */
    public function forceDeleted(ProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_return',
            'description'  => 'force deleted',
            'subject_type' => ProductReturn::class,
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