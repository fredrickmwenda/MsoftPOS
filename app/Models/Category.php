<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable =[

        "name", 'image', "is_active", "is_sync_disable", "woocommerce_category_id", "department_id"
    ];

    public function product()
    {
    	return $this->hasMany(Product::class);
    }
    //belongs to department
    public function department()
    {
        return $this->belongsTo('App\Models\CategoryDepartment', 'department_id');  
    }
}
