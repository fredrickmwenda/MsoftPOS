<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSetting extends Model
{
    protected $table = 'pos_setting';
    protected $fillable = [
        "customer_id", "warehouse_id", "biller_id", "product_number", 
        "stripe_public_key", "stripe_secret_key", "paypal_live_api_username", 
        "paypal_live_api_password", "paypal_live_api_secret", "payment_options", 
        "invoice_option", "keybord_active", "is_table"
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function biller()
    {
        return $this->belongsTo(Biller::class);
    }
}