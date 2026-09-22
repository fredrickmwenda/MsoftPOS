<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'work_duration', 'duration_unit', 'amount_per_duration', 'items', 'description', 'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'work_duration' => 'integer',
        'amount_per_duration' => 'decimal:2',
        'items' => 'array'
    ];

public function items()
{
    return $this->hasMany(PayrollTemplateItem::class, 'payroll_template_id'); // Explicitly state 'payroll_template_id'
}


        public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'payroll_template_tax');
    }
}
