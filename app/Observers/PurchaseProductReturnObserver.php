<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\PurchaseProductReturn;
use Illuminate\Support\Facades\Auth;

class PurchaseProductReturnObserver
{
    /**
     * Build a structured context array for the purchase return (line item).
     * This pulls in related data like Return Reference, Product, Variant, Batch, and Unit.
     */
    protected function buildContext(PurchaseProductReturn $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['purchaseReturn', 'product', 'variant', 'productBatch', 'purchaseUnit']);

        return [
            'return_reference' => $model->purchaseReturn ? $model->purchaseReturn->reference_no : 'N/A',
            'product_name'     => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'     => $model->product ? $model->product->code : 'N/A',
            'variant'          => $model->variant ? $model->variant->name : 'None',
            'batch_no'         => $model->productBatch ? $model->productBatch->batch_no : 'None',
            'unit'             => $model->purchaseUnit ? $model->purchaseUnit->unit_name : 'N/A',
            'imei_number'      => $model->imei_number ?: 'None',
            'qty'              => $model->qty,
            'net_unit_cost'    => $model->net_unit_cost,
            'total'            => $model->total,
        ];
    }

    /**
     * Handle the PurchaseProductReturn "created" event.
     */
    public function created(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase_product_return',
            'description'  => 'created',
            'subject_type' => PurchaseProductReturn::class,
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
     * Handle the PurchaseProductReturn "updated" event.
     */
    public function updated(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase_product_return',
            'description'  => 'updated',
            'subject_type' => PurchaseProductReturn::class,
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
     * Handle the PurchaseProductReturn "deleted" event.
     */
    public function deleted(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase_product_return',
            'description'  => 'deleted',
            'subject_type' => PurchaseProductReturn::class,
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
     * Handle the PurchaseProductReturn "restored" event.
     */
    public function restored(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase_product_return',
            'description'  => 'restored',
            'subject_type' => PurchaseProductReturn::class,
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
     * Handle the PurchaseProductReturn "force deleted" event.
     */
    public function forceDeleted(PurchaseProductReturn $model): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase_product_return',
            'description'  => 'force deleted',
            'subject_type' => PurchaseProductReturn::class,
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