<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryTemplate extends Model
{
    use HasFactory;

    public const TYPE_MONTHLY = 'monthly';
    public const TYPE_HOURLY  = 'hourly';

    protected $fillable = [
        'user_id', 'name', 'type', 'basic_salary', 'overtime_rate',
        'hourly_rate', 'hours_per_day', 'is_default', 'description',
    ];

    protected $casts = [
        'basic_salary'  => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'hourly_rate'   => 'decimal:2',
        'hours_per_day' => 'decimal:2',
        'is_default'    => 'boolean',
    ];

    /* ---------------- Relationships ---------------- */
    public function items(): HasMany
    {
        return $this->hasMany(SalaryTemplateItem::class);
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(SalaryTemplateItem::class)->where('type', 'allowance');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(SalaryTemplateItem::class)->where('type', 'deduction');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /* ---------------- Computed ---------------- */
    public function getTotalAllowanceAttribute(): float
    {
        return $this->type === self::TYPE_MONTHLY
            ? (float) $this->items->where('type', 'allowance')->sum('amount') : 0.0;
    }

    public function getTotalDeductionAttribute(): float
    {
        return $this->type === self::TYPE_MONTHLY
            ? (float) $this->items->where('type', 'deduction')->sum('amount') : 0.0;
    }

    /** Monthly: basic + allowances | Hourly: rate × hours/day (daily pay) */
    public function getGrossSalaryAttribute(): float
    {
        return $this->type === self::TYPE_HOURLY
            ? $this->daily_rate
            : (float) $this->basic_salary + $this->total_allowance;
    }

    public function getNetSalaryAttribute(): float
    {
        return $this->type === self::TYPE_HOURLY
            ? $this->gross_salary
            : $this->gross_salary - $this->total_deduction;
    }

    /** Hourly employees are paid daily: hourly_rate × hours_per_day */
    public function getDailyRateAttribute(): float
    {
        return (float) $this->hourly_rate * (float) $this->hours_per_day;
    }

    public function scopeMonthly($q) { return $q->where('type', self::TYPE_MONTHLY); }
    public function scopeHourly($q)  { return $q->where('type', self::TYPE_HOURLY); }
}
