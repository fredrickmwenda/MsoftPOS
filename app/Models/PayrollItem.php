<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id', 'payroll_template_item_id', 'name', 'type', 'amount_type', 'amount', 'taxable', 'description', 'meta'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'taxable' => 'boolean',
        'meta' => 'array'
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(PayrollTemplateItem::class, 'payroll_template_item_id');
    }
}
