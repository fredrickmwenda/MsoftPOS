<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        "customer_group_id", "region", "community", "location", "status", "user_id", 
        "name", "company_name", "email", "phone_number", "tax_no", "address", 
        "city", "state", "postal_code", "country", "points", "deposit", "expense", 
        "is_active", 'whatsapp_number'
    ];

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discountPlans()
    {
        return $this->belongsToMany(DiscountPlan::class, 'discount_plan_customers');
    }
}