<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'name',
        'type',
        'amount_type',
        'amount',
        'taxable',
        'description',
        'meta',
        'source',           // 'template' | 'manual'
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'taxable' => 'boolean',
        'meta'    => 'array',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function metas(): HasMany
    {
        return $this->hasMany(PayrollItemMeta::class);
    }
}