<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductAdjustment;
use Illuminate\Support\Facades\Auth;

class ProductAdjustmentObserver
{
    /**
     * Build a structured context array for the product adjustment (line item).
     * This pulls in related data like Adjustment Reference, Warehouse, Product, and Variant.
     */
    protected function buildContext(ProductAdjustment $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['adjustment.warehouse', 'product', 'variant']);

        $adjustment = $model->adjustment;

        return [
            'adjustment_reference' => $adjustment ? $adjustment->reference_no : 'N/A',
            'warehouse'           => $adjustment && $adjustment->warehouse ? $adjustment->warehouse->name : 'N/A',
            'product_name'        => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'        => $model->product ? $model->product->code : 'N/A',
            'variant'             => $model->variant ? $model->variant->name : 'None',
            'qty'                 => $model->qty,
            'action'              => $model->action, // Usually '+' (add) or '-' (remove)
        ];
    }

    /**
     * Handle the ProductAdjustment "created" event.
     */
    public function created(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_adjustment',
            'description'  => 'created',
            'subject_type' => ProductAdjustment::class,
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
     * Handle the ProductAdjustment "updated" event.
     */
    public function updated(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_adjustment',
            'description'  => 'updated',
            'subject_type' => ProductAdjustment::class,
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
     * Handle the ProductAdjustment "deleted" event.
     */
    public function deleted(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_adjustment',
            'description'  => 'deleted',
            'subject_type' => ProductAdjustment::class,
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
     * Handle the ProductAdjustment "restored" event.
     */
    public function restored(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_adjustment',
            'description'  => 'restored',
            'subject_type' => ProductAdjustment::class,
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
     * Handle the ProductAdjustment "force deleted" event.
     */
    public function forceDeleted(ProductAdjustment $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_adjustment',
            'description'  => 'force deleted',
            'subject_type' => ProductAdjustment::class,
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