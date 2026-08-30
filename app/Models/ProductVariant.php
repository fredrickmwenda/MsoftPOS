<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'variant_id', 'position', 'item_code', 'additional_cost', 'additional_price', 'qty'];

    /**
     * Get the product that owns this variant link.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant that owns this product link.
     */
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function scopeFindExactProduct($query, $product_id, $variant_id)
    {
        return $query->where([
            ['product_id', $product_id],
            ['variant_id', $variant_id]
        ]);
    }

    public function scopeFindExactProductWithCode($query, $product_id, $item_code)
    {
        return $query->where([
            ['product_id', $product_id],
            ['item_code', $item_code],
        ]);
    }
}