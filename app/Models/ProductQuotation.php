<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuotation extends Model
{
    protected $table = 'product_quotation';
    
    protected $fillable = [
        "quotation_id", 
        "product_id", 
        "product_batch_id", 
        "variant_id", 
        "qty", 
        "sale_unit_id", 
        "net_unit_price", 
        "discount", 
        "tax_rate", 
        "tax_names", 
        "tax", 
        "total"
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function saleUnit()
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }
}