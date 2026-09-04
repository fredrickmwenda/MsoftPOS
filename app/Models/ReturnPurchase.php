<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnPurchase extends Model
{
    protected $table = 'return_purchases';
    protected $fillable = [
        "reference_no", "purchase_id", "user_id", "supplier_id", "warehouse_id", 
        "account_id", "currency_id", "exchange_rate", "item", "total_qty", 
        "total_discount", "total_tax", "total_cost", "order_tax_rate", "order_tax", 
        "grand_total", "document", "return_note", "staff_note"
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function purchaseProductReturns()
    {
        return $this->hasMany(PurchaseProductReturn::class, 'return_id');
    }
}