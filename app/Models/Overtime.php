<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'salary_template_id', 'year', 'month',
        'hours', 'rate', 'amount', 'date', 'note',
    ];

    protected $casts = [
        'hours' => 'decimal:2', 'rate' => 'decimal:2',
        'amount' => 'decimal:2', 'date' => 'date',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function template() { return $this->belongsTo(SalaryTemplate::class, 'salary_template_id'); }
}
