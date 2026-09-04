<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountPlanCustomer extends Model
{
    use HasFactory;

    protected $fillable = ['discount_plan_id', 'customer_id'];

    public function discountPlan()
    {
        return $this->belongsTo(DiscountPlan::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}