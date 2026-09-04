<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        "purchase_id", "user_id", "sale_id", "cash_register_id", "account_id", 
        "payment_reference", "amount", "used_points", "change", "paying_method", 
        "payment_note", "mobile_money_operator", "mobile_number"
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}