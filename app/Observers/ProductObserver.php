<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->invalidateProductCaches();

        ActivityLog::create([
            'log_name'    => 'product',
            'description' => 'created',
            'subject_type'=> Product::class,
            'subject_id'  => $product->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'product',
            'description' => 'updated',
            'subject_type'=> Product::class,
            'subject_id'  => $product->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $product->getOriginal(),
                'attributes' => $product->getChanges(),
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
            'log_name'    => 'product',
            'description' => 'deleted',
            'subject_type'=> Product::class,
            'subject_id'  => $product->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'product',
            'description' => 'restored',
            'subject_type'=> Product::class,
            'subject_id'  => $product->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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
            'log_name'    => 'product',
            'description' => 'force deleted',
            'subject_type'=> Product::class,
            'subject_id'  => $product->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
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