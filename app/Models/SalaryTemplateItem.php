<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = ['salary_template_id', 'name', 'type', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SalaryTemplate::class, 'salary_template_id');
    }
}
