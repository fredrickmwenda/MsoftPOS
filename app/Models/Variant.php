<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'variant_type_id'];

    public function variantType(): BelongsTo
    {
        return $this->belongsTo(VariantType::class);
    }

    /** Direct rows in the pivot-style table. */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_variants')
            ->withPivot([
                'position',
                'item_code',
                'additional_cost',
                'additional_price',
                'qty',
            ]);
    }
}
