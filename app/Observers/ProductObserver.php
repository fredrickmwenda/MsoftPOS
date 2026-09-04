<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Build a structured context array for the product.
     * This pulls in related data like Category, Brand, Unit, Variants, and Taxes.
     */
    protected function buildContext(Product $product): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $product->loadMissing(['category', 'brand', 'unit', 'variant', 'product_taxes']);

        return [
            'product_name'  => $product->name,
            'product_code'  => $product->code,
            'type'          => $product->type,
            'category'      => $product->category ? $product->category->name : 'Uncategorized',
            'brand'         => $product->brand ? $product->brand->title : 'No Brand',
            'unit'          => $product->unit ? $product->unit->unit_name : 'N/A',
            'variants'      => $product->variant->isNotEmpty() ? $product->variant->pluck('name')->implode(', ') : 'None',
            'taxes'         => $product->product_taxes->isNotEmpty() ? $product->product_taxes->pluck('name')->implode(', ') : 'None',
            'cost'          => $product->cost,
            'price'         => $product->price,
            'is_active'     => (bool) $product->is_active,
        ];
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->invalidateProductCaches();

        ActivityLog::create([
            'log_name'     => 'product',
            'description'  => 'created',
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($product),
                'attributes' => $product->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->invalidateProductCaches();

        ActivityLog::create([
            'log_name'     => 'product',
            'description'  => 'updated',
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($product),
                'old'        => $product->getOriginal(),
                'attributes' => $product->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the Product "deleted" event. (Soft‑delete via Laravel's SoftDeletes)
     */
    public function deleted(Product $product): void
    {
        $this->invalidateProductCaches();

        ActivityLog::create([
            'log_name'     => 'product',
            'description'  => 'deleted',
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($product),
                'attributes' => $product->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->invalidateProductCaches();

        ActivityLog::create([
            'log_name'     => 'product',
            'description'  => 'restored',
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($product),
                'attributes' => $product->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->invalidateProductCaches();

        ActivityLog::create([
            'log_name'     => 'product',
            'description'  => 'force deleted',
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($product),
                'attributes' => $product->getAttributes(),
            ],
        ]);
    }

    /**
     * Invalidate all product-related caches
     */
    private function invalidateProductCaches(): void
    {
        Cache::forget('product_list');
        Cache::forget('product_list_with_variant');
    }
}