<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable =[

        "name", "phone", "email", "address", "is_active"
    ];

    public function product()
    {
    	return $this->hasMany(Product::class);

    }


    public function stockCounts()
    {
        return $this->hasMany( StockCount::class);
    }

    public function adjustments(){
        return $this->hasMany(Adjustment::class);
    }
 
    public function users(){
        return $this->hasMany(User::class);
    }
}
