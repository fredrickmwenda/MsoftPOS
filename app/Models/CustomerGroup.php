<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    protected $fillable =[
        "name", "percentage", "is_active"
    ];

    public function customers(){
        $this->hasMany(Customer::class);
    }
}
