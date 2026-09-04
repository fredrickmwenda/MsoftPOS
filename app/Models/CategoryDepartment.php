<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryDepartment extends Model
{
    use HasFactory;
    public $table = "category_departments";
    
    protected $fillable =[
        "name", 'image', "is_active"
    ];
    // has many relationship with Category
    public function category()
    {
        return $this->hasMany('App\Models\Category');
    }
}
