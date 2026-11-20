<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class VariantType extends Model
{
    use HasFactory;

    protected $table = 'variant_types';

    /** Explicit fillable instead of $guarded = [] (safer mass assignment) */
    protected $fillable = ['name', 'order', 'active'];

    protected $casts = [
        'order'  => 'integer',
        'active' => 'boolean',
    ];

    /* -------------------------------------------------
     | Relations
     |-------------------------------------------------*/

    /** Variants belonging directly to this type. */
    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    /**
     * All product-variant rows that use any variant of this type.
     * variant_types -> variants -> product_variants
     *
     * This is what the controller's destroy() needs.
     */
    public function productVariants(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductVariant::class,
            Variant::class,
            'variant_type_id', // FK on `variants`
            'variant_id',      // FK on `product_variants`
            'id',              // local key on `variant_types`
            'id'               // local key on `variants`
        );
    }

    /* -------------------------------------------------
     | Scopes
     |-------------------------------------------------*/

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
