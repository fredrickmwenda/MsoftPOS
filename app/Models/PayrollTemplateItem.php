<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTemplateItem extends Model
{
    protected $table = 'payroll_template_items';

    // Add this array to allow mass assignment
    protected $fillable = [
        'payroll_template_id',
        'name',
        'type',
        'amount_type',
        'amount',
        'taxable',
        'description'
    ];

    public function template()
    {
        return $this->belongsTo(PayrollTemplate::class);
    }

    // public function item(): BelongsTo
    // {
    //     return $this->belongsTo(PayrollItem::class, 'payroll_item_id');
    // }
}
