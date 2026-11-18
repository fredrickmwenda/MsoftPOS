<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantType extends Model
{
    use HasFactory;
    public $table = "variant_types";
    public $guarded = [];

    public $primaryKey = "id";


    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
