<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no', 'employee_id', 'payroll_template_id', 'account_id', 'user_id',
        'employee_name', 'bank_name', 'account_number',
        'work_duration', 'duration_unit', 'amount_per_duration', 'total_duration_amount',
        'gross_amount', 'total_allowances', 'total_deductions', 'amount',
        'date', 'year', 'month',
        'paying_method', 'note', 'description', 'comments',
        'recurring', 'recur_frequency', 'recur_start_date', 'recur_end_date',
        'recur_next_date', 'recur_type',
    ];

    protected $casts = [
        'work_duration'         => 'decimal:2',
        'amount_per_duration'   => 'decimal:2',
        'total_duration_amount' => 'decimal:2',
        'gross_amount'          => 'decimal:2',
        'total_allowances'      => 'decimal:2',
        'total_deductions'      => 'decimal:2',
        'amount'                => 'decimal:2',
        'date'                  => 'date',
        'recurring'             => 'boolean',
        'recur_start_date'      => 'date',
        'recur_end_date'        => 'date',
        'recur_next_date'       => 'date',
    ];

    /* ----------------------------------------------------------
     |  Relationships
     +--------------------------------------------------------- */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PayrollTemplate::class, 'payroll_template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PayrollPayment::class);
    }

    public function metas(): HasMany
    {
        return $this->hasMany(PayrollItemMeta::class);
    }

    /* ----------------------------------------------------------
     |  Helpers
     +--------------------------------------------------------- */
    public function recalculate(): void
    {
        $allowances = $this->items()->where('type', 'allowance')->sum('amount');
        $deductions = $this->items()->where('type', 'deduction')->sum('amount');
        $gross      = $allowances - $deductions;

        $this->update([
            'total_allowances'   => $allowances,
            'total_deductions'   => $deductions,
            'gross_amount'       => $gross,
            'amount'             => $gross,    // adjust for tax etc. as needed
        ]);
    }
}