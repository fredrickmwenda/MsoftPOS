<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    protected $table = 'product_returns';
    protected $fillable = [
        "return_id", "product_id", "variant_id", "imei_number", "product_batch_id", 
        "qty", "sale_unit_id", "net_unit_price", "discount", "tax_rate", "tax", "total"
    ];

    public function saleReturn()
    {
        return $this->belongsTo(Returns::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function saleUnit()
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }
}