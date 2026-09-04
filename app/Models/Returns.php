<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    protected $table = 'returns';
    protected $fillable = [
        "reference_no", "user_id", "sale_id", "cash_register_id", "customer_id", 
        "warehouse_id", "biller_id", "account_id", "currency_id", "exchange_rate", 
        "item", "total_qty", "total_discount", "total_tax", "total_price", 
        "order_tax_rate", "order_tax", "grand_total", "document", "return_note", 
        "staff_note"
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function biller()
    {
        return $this->belongsTo(Biller::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
}