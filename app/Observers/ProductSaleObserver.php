<?php

namespace App\Observers;

use App\Models\Product_Sale as ProductSale;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ProductSaleObserver
{
    /**
     * Build a structured context array for the product sale (line item).
     * This pulls in related data like Sale, Product, Variant, Batch, and Unit.
     */
    protected function buildContext(ProductSale $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['sale', 'product', 'variant', 'productBatch', 'saleUnit']);

        return [
            'sale_reference'  => $model->sale ? $model->sale->reference_no : 'N/A',
            'product_name'   => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'   => $model->product ? $model->product->code : 'N/A',
            'variant'        => $model->variant ? $model->variant->name : 'None',
            'batch_no'       => $model->productBatch ? $model->productBatch->batch_no : 'None',
            'unit'           => $model->saleUnit ? $model->saleUnit->unit_name : 'N/A',
            'imei_number'    => $model->imei_number ?: 'None',
            'qty'            => $model->qty,
            'net_unit_price' => $model->net_unit_price,
            'total'          => $model->total,
        ];
    }

    public function created(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_sale',
            'description'  => 'created',
            'subject_type' => ProductSale::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function updated(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_sale',
            'description'  => 'updated',
            'subject_type' => ProductSale::class,
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

    public function deleted(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_sale',
            'description'  => 'deleted',
            'subject_type' => ProductSale::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function restored(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_sale',
            'description'  => 'restored',
            'subject_type' => ProductSale::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function forceDeleted(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_sale',
            'description'  => 'force deleted',
            'subject_type' => ProductSale::class,
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