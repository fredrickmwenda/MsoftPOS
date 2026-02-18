<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product_Sale extends Model
{
	protected $table = 'product_sales';
    protected $fillable =[
        "sale_id", "product_id", "product_batch_id", 
        "variant_id", 'imei_number', "qty", "return_qty",
         "sale_unit_id", "net_unit_price", "discount",
          "tax_rate", "tax", "total"
    ];


    public function calculateTotals($price, $qty, $taxRate = 0, $discount = 0)
{
    $tax = round(($price * $qty) * ($taxRate / 100), 2);
    $total = round(($price * $qty) + $tax - $discount, 2);

    return [
        'tax' => $tax,
        'total' => $total,
    ];
}
    public function sale()
    {
        return $this->belongsTo('App\Models\Sale');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function variant()
    {
        return $this->belongsTo('App\Models\Variant');
    }

    public function product_batch()
    {
        return $this->belongsTo('App\Models\Product_Batch');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'sale_unit_id');
    }
}
