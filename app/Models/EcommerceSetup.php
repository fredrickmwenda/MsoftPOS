<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceSetup extends Model
{
    use HasFactory;
    protected $table = 'ecommerce_setup';   // explicit — Laravel would pluralize otherwise

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    /** ['contact' => [...], 'address' => [...], ...] */
    public static function allGrouped(): array
    {
        return self::all()
            ->mapWithKeys(fn ($row) => [$row->key => $row->value ?? []])
            ->toArray();
    }
}
