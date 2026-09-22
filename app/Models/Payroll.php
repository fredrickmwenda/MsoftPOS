<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no', 'employee_id', 'payroll_template_id', 'account_id', 'user_id',
        'employee_name', 'bank_name', 'account_number', 'description', 'comments',
        'work_duration', 'duration_unit', 'amount_per_duration', 'total_duration_amount',
        'amount', 'gross_amount', 'total_allowances', 'total_deductions',
        'date', 'year', 'month',
        'recurring', 'recur_frequency', 'recur_start_date', 'recur_end_date', 'recur_next_date', 'recur_type',
        'paying_method', 'note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'work_duration' => 'decimal:2',
        'amount_per_duration' => 'decimal:2',
        'total_duration_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'date' => 'date',
        'recurring' => 'boolean',
        'recur_start_date' => 'date',
        'recur_end_date' => 'date',
        'recur_next_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PayrollPayment::class);
    }
}