<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable =[

        "name","region","community",
        "location","status", "image",
         "company_name", "vat_number",
        "email", "phone_number",
         "address", "city",
        "state", "postal_code",
         "country", "is_active",
         "opening_balance",
            "pay_term_no", "pay_term_period"

    ];

    public function product()
    {
    	return $this->hasMany('App\Models\Product');
    }



    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function returnPurchases()
    {
        return $this->hasMany(ReturnPurchase::class, 'supplier_id');
    }
}
