<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable= ['name', 'applicable_for', 'product_list', 'valid_from', 'valid_till', 'type', 'value', 'minimum_qty', 'maximum_qty', 'days', 'is_active'];

    // in Discount.php
    public function discountPlans()
    {
        return $this->belongsToMany(DiscountPlan::class, 'discount_plan_discounts')
                    ->using(DiscountPlanDiscount::class);
    }
}
