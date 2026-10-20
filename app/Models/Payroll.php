<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no', 'employee_id', 'salary_template_id', 'account_id', 'user_id',
        'employee_name', 'template_type',
        'basic_salary', 'overtime_hours', 'overtime_rate', 'overtime_amount',
        'hourly_rate', 'hours_per_day', 'daily_rate', 'days_worked',
        'gross_amount', 'total_allowance', 'total_deduction', 'amount', 'paid_amount',
        'date', 'year', 'month', 'paying_method', 'payment_status', 'note',
    ];

    protected $casts = [
        'basic_salary'    => 'decimal:2',
        'overtime_hours'  => 'decimal:2',
        'overtime_rate'   => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'hourly_rate'     => 'decimal:2',
        'hours_per_day'   => 'decimal:2',
        'daily_rate'      => 'decimal:2',
        'gross_amount'    => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'amount'          => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'date'            => 'date',
    ];

    /* ---------------- Relationships ---------------- */
    public function employee()      { return $this->belongsTo(Employee::class); }
    public function salaryTemplate(){ return $this->belongsTo(SalaryTemplate::class, 'salary_template_id'); }
    public function account()       { return $this->belongsTo(Account::class); }
    public function user()          { return $this->belongsTo(User::class); }
    public function items()         { return $this->hasMany(PayrollItem::class); }
    public function payments()      { return $this->hasMany(PayrollPayment::class); }

    /* ---------------- Helpers ---------------- */
    public function recalculate(): void
    {
        if ($this->template_type === SalaryTemplate::TYPE_HOURLY) {
            $gross = (float) $this->daily_rate * (int) $this->days_worked;
            $this->update([
                'gross_amount' => $gross, 'total_allowance' => 0,
                'total_deduction' => 0, 'amount' => $gross,
            ]);
            return;
        }

        $allowances = (float) $this->items()->where('type', 'allowance')->sum('amount');
        $deductions = (float) $this->items()->where('type', 'deduction')->sum('amount');
        $overtime   = (float) $this->items()->where('type', 'overtime')->sum('amount');

        $gross = (float) $this->basic_salary + $allowances + $overtime;

        $this->update([
            'total_allowance' => $allowances,
            'total_deduction' => $deductions,
            'overtime_amount' => $overtime,
            'gross_amount'    => $gross,
            'amount'          => $gross - $deductions, // net
        ]);
    }

    public function refreshPaymentStatus(): void
    {
        $paid   = (float) $this->payments()->sum('amount');
        $status = $paid <= 0 ? 'due'
            : ($paid + 0.001 >= (float) $this->amount ? 'paid' : 'partial');

        $this->update(['paid_amount' => $paid, 'payment_status' => $status]);
    }

    public function getDueAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}
