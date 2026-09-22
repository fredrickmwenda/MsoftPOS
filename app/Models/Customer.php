<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // Change this
use Illuminate\Notifications\Notifiable; // Add this for password resets

class Customer extends Authenticatable // Change this
{
    use Notifiable; // Add this trait

    protected $fillable = [
        "customer_group_id", "region", 
        "community", "location", "status", 
        "user_id", 
        "name", "company_name", "email",
         "phone_number", "tax_no", "address", 
        "city", "state", "postal_code",
        "country", "points", "deposit", "expense", 
        "is_active", 'whatsapp_number', 'password',
        "pay_term_no", "pay_term_period", "opening_balance", "credit_limit", "wishlist",
        
        // Add password here
    ];

    // Hide sensitive data from JSON responses
    protected $hidden = [
        'password',
        'remember_token',
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