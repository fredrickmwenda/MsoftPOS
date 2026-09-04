<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductQuotation;
use Illuminate\Support\Facades\Auth;

class ProductQuotationObserver
{
    /**
     * Build a structured context array for the product quotation (line item).
     * This pulls in related data like Quotation, Product, Variant, Batch, and Unit.
     */
    protected function buildContext(ProductQuotation $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['quotation', 'product', 'variant', 'productBatch', 'saleUnit']);

        return [
            'quotation_reference' => $model->quotation ? $model->quotation->reference_no : 'N/A',
            'product_name'        => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'        => $model->product ? $model->product->code : 'N/A',
            'variant'             => $model->variant ? $model->variant->name : 'None',
            'batch_no'            => $model->productBatch ? $model->productBatch->batch_no : 'None',
            'unit'                => $model->saleUnit ? $model->saleUnit->unit_name : 'N/A',
            'qty'                 => $model->qty,
            'net_unit_price'      => $model->net_unit_price,
            'total'               => $model->total,
        ];
    }

    /**
     * Handle the ProductQuotation "created" event.
     */
    public function created(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_quotation',
            'description'  => 'created',
            'subject_type' => ProductQuotation::class,
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
     * Handle the ProductQuotation "updated" event.
     */
    public function updated(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_quotation',
            'description'  => 'updated',
            'subject_type' => ProductQuotation::class,
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
     * Handle the ProductQuotation "deleted" event.
     */
    public function deleted(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_quotation',
            'description'  => 'deleted',
            'subject_type' => ProductQuotation::class,
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
     * Handle the ProductQuotation "restored" event.
     */
    public function restored(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_quotation',
            'description'  => 'restored',
            'subject_type' => ProductQuotation::class,
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
     * Handle the ProductQuotation "force deleted" event.
     */
    public function forceDeleted(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_quotation',
            'description'  => 'force deleted',
            'subject_type' => ProductQuotation::class,
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