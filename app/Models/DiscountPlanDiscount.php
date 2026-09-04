<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountPlanDiscount extends Model
{
    use HasFactory;

    protected $table = 'discount_plan_discounts';

    protected $fillable = ['discount_plan_id', 'discount_id'];

    public function discountPlan()
    {
        return $this->belongsTo(DiscountPlan::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}