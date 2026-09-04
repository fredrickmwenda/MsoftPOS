<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class ProductVariantObserver
{
    /**
     * Build a structured context array for the product variant.
     * This pulls in related data like Product Name and Variant Name.
     */
    protected function buildContext(ProductVariant $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['product', 'variant']);

        return [
            'product_name'     => $model->product ? $model->product->name : 'Unknown Product',
            'variant_name'     => $model->variant ? $model->variant->name : 'Unknown Variant',
            'item_code'        => $model->item_code,
            'additional_cost'  => $model->additional_cost,
            'additional_price' => $model->additional_price,
            'qty'              => $model->qty,
            'position'         => $model->position,
        ];
    }

    /**
     * Handle the ProductVariant "created" event.
     */
    public function created(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_variant',
            'description'  => 'created',
            'subject_type' => ProductVariant::class,
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
     * Handle the ProductVariant "updated" event.
     */
    public function updated(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_variant',
            'description'  => 'updated',
            'subject_type' => ProductVariant::class,
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
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_variant',
            'description'  => 'deleted',
            'subject_type' => ProductVariant::class,
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
     * Handle the ProductVariant "restored" event.
     */
    public function restored(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_variant',
            'description'  => 'restored',
            'subject_type' => ProductVariant::class,
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
     * Handle the ProductVariant "force deleted" event.
     */
    public function forceDeleted(ProductVariant $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_variant',
            'description'  => 'force deleted',
            'subject_type' => ProductVariant::class,
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