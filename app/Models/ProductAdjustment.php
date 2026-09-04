<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAdjustment extends Model
{
    protected $table = 'product_adjustments';
    
    protected $fillable = [
        "adjustment_id", "product_id", "variant_id", "qty", "action"
    ];

    public function adjustment()
    {
        return $this->belongsTo(Adjustment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}