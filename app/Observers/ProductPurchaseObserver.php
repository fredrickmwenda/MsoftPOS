<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductPurchase;
use Illuminate\Support\Facades\Auth;

class ProductPurchaseObserver
{
    /**
     * Build a structured context array for the product purchase (line item).
     * This pulls in related data like Purchase, Product, Variant, Batch, and Unit.
     */
    protected function buildContext(ProductPurchase $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['purchase', 'product', 'variant', 'productBatch', 'purchaseUnit']);

        return [
            'purchase_reference' => $model->purchase ? $model->purchase->reference_no : 'N/A',
            'product_name'       => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'       => $model->product ? $model->product->code : 'N/A',
            'variant'            => $model->variant ? $model->variant->name : 'None',
            'batch_no'           => $model->productBatch ? $model->productBatch->batch_no : 'None',
            'unit'               => $model->purchaseUnit ? $model->purchaseUnit->unit_name : 'N/A',
            'qty'                => $model->qty,
            'net_unit_cost'      => $model->net_unit_cost,
            'total'              => $model->total,
        ];
    }

    /**
     * Handle the ProductPurchase "created" event.
     */
    public function created(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_purchase',
            'description'  => 'created',
            'subject_type' => ProductPurchase::class,
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
     * Handle the ProductPurchase "updated" event.
     */
    public function updated(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_purchase',
            'description'  => 'updated',
            'subject_type' => ProductPurchase::class,
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
     * Handle the ProductPurchase "deleted" event.
     */
    public function deleted(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_purchase',
            'description'  => 'deleted',
            'subject_type' => ProductPurchase::class,
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
     * Handle the ProductPurchase "restored" event.
     */
    public function restored(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_purchase',
            'description'  => 'restored',
            'subject_type' => ProductPurchase::class,
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
     * Handle the ProductPurchase "force deleted" event.
     */
    public function forceDeleted(ProductPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_purchase',
            'description'  => 'force deleted',
            'subject_type' => ProductPurchase::class,
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