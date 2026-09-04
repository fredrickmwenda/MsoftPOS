<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductTransfer;
use Illuminate\Support\Facades\Auth;

class ProductTransferObserver
{
    /**
     * Build a structured context array for the product transfer (line item).
     * This pulls in related data like Transfer, From/To Warehouses, Product, Variant, Batch, and Unit.
     */
    protected function buildContext(ProductTransfer $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing([
            'transfer.fromWarehouse', 
            'transfer.toWarehouse', 
            'product', 
            'variant', 
            'productBatch', 
            'purchaseUnit'
        ]);

        $transfer = $model->transfer;

        return [
            'transfer_reference' => $transfer ? $transfer->reference_no : 'N/A',
            'from_warehouse'      => $transfer && $transfer->fromWarehouse ? $transfer->fromWarehouse->name : 'N/A',
            'to_warehouse'        => $transfer && $transfer->toWarehouse ? $transfer->toWarehouse->name : 'N/A',
            'product_name'        => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'        => $model->product ? $model->product->code : 'N/A',
            'variant'             => $model->variant ? $model->variant->name : 'None',
            'batch_no'            => $model->productBatch ? $model->productBatch->batch_no : 'None',
            'unit'                => $model->purchaseUnit ? $model->purchaseUnit->unit_name : 'N/A',
            'imei_number'         => $model->imei_number ? $model->imei_number : 'None',
            'qty'                 => $model->qty,
            'net_unit_cost'       => $model->net_unit_cost,
            'total'               => $model->total,
        ];
    }

    /**
     * Handle the ProductTransfer "created" event.
     */
    public function created(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_transfer',
            'description'  => 'created',
            'subject_type' => ProductTransfer::class,
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
     * Handle the ProductTransfer "updated" event.
     */
    public function updated(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_transfer',
            'description'  => 'updated',
            'subject_type' => ProductTransfer::class,
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
     * Handle the ProductTransfer "deleted" event.
     */
    public function deleted(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_transfer',
            'description'  => 'deleted',
            'subject_type' => ProductTransfer::class,
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
     * Handle the ProductTransfer "restored" event.
     */
    public function restored(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_transfer',
            'description'  => 'restored',
            'subject_type' => ProductTransfer::class,
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
     * Handle the ProductTransfer "force deleted" event.
     */
    public function forceDeleted(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_transfer',
            'description'  => 'force deleted',
            'subject_type' => ProductTransfer::class,
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