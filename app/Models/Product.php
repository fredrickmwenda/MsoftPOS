<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable =[
        "name", 
        "code",
        "type", 
        "barcode_symbology", "brand_id",
        "category_id", "unit_id",
        "purchase_unit_id", "sale_unit_id", 
        "cost",
        "price",
        "wholesale_price",
        'shelf', 
        "qty", 
        "alert_quantity", 
        "daily_sale_objective", 
        "promotion", 
        "promotion_price", 
        "starting_date", 
        "last_date", 
        "tax_method", 
        "image", "file", 
        "is_embeded", 
        "is_batch", 
        "is_variant", 
        "is_diffPrice", 
        "is_imei", 
        "featured", 
        "product_list", "variant_list", "qty_list", "price_list",
        "product_details", 
        "variant_option", "variant_value", "is_active", "is_sync_disable", "woocommerce_product_id","woocommerce_media_id","tags","meta_title","meta_description"
    ];

    //payment status 1=Due, 2=partial, 3=due, 4=waiting approval

    public function category()
    {
    	return $this->belongsTo(Category::class);
    }

    public function brand()
    {
    	return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variant()
    {
        return $this->belongsToMany(Variant::class, 'product_variants')->withPivot('id', 'item_code', 'additional_cost', 'additional_price');
    }

    public function scopeActiveStandard($query)
    {
        return $query->where([
            ['is_active', true],
            ['type', 'standard']
        ]);
    }

    public function scopeActiveFeatured($query)
    {
        return $query->where([
            ['is_active', true],
            ['featured', 1]
        ]);
    }


    public function product_warehouse()
    {
        return $this->hasMany(Product_Warehouse::class);
    }

    public function product_taxes()
    {
        return $this->belongsToMany(Tax::class, 'product_tax', 'product_id', 'tax_id');
    }

    public function product_batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

 
}
