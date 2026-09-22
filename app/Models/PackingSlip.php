<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingSlip extends Model
{
    use HasFactory;

    protected $fillable = ["reference_no", "sale_id", "delivery_id", "amount", "status"];

    public function sale()
    {
    	return $this->belongsTo(Sale::class);
    }

    public function delivery()
    {
    	return $this->belongsTo(Delivery::class);
    }

    public function products()
    {
    	return $this->belongsToMany(Product::class, 'packing_slip_products');
    }
}
