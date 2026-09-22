<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Purchase extends Model
{
    protected $fillable =[

        "reference_no", "user_id", "warehouse_id",
         "supplier_id", "currency_id", "exchange_rate", "item",
          "total_qty", "total_discount", "total_tax", "total_cost",
           "order_tax_rate", "order_tax", "order_discount", "shipping_cost", 
           "grand_total","paid_amount", "status", "payment_status",
            "document", "note", "purchase_type",  "created_at",
            "deleted_by",'pay_term_no','pay_term_period','due_date',
    ];

    public function supplier()
    {
    	return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
    	return $this->belongsTo(Warehouse::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }
      // 🔹 Relationship to ProductPurchase (items in purchase)
    public function products()
    {
        return $this->hasMany(ProductPurchase::class, 'purchase_id');
    }

    
    public function getCreatedAtFormattedAttribute()
    {
        $dateFormat = GeneralSetting::first()->date_format;
        return Carbon::parse($this->attributes['created_at'])->format($dateFormat);
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by')->withDefault([
            'name' => 'System/Unknown'
        ]);
    }

    
}
