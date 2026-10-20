<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        "name",
        "image",
        "department_id",
        "email",
        "phone_number",
        "user_id",
        "shift_id",
        "staff_id",
        "address",
        "city",
        "country",
        "is_active",
        //added all the others
        "is_sale_agent",
        "sale_commission_percent",
        "sales_target",
        "warehouse_id",              // <-- ADD THIS HERE
        "salary_template_id",        // <-- assigned salary/hourly template (integer selection)
    ];


    protected $casts = [
        'sales_target' => 'array',   // auto json_encode on save, json_decode on read
    ];

    /* ----------------------------------------------------------
     |  Relationships
     +--------------------------------------------------------- */
    public function payroll()
    {
        return $this->hasMany(Payroll::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // employee's own login account
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id'); // supervisor/manager user
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * The assigned salary template (monthly) or hourly template.
     * Integer column `salary_template_id` — no FK constraint needed.
     */
    public function salaryTemplate(): BelongsTo
    {
        return $this->belongsTo(SalaryTemplate::class, 'salary_template_id');
    }

    /* ----------------------------------------------------------
     |  Computed helpers (template is the source of truth)
     +--------------------------------------------------------- */

    /**
     * $employee->salary — returns the template value when a template
     * is assigned; falls back to 0.0 otherwise.
     *   monthly → basic_salary
     *   hourly  → daily rate (hourly_rate × hours_per_day)
     */
    public function getSalaryAttribute($value): float
    {
        if ($this->salaryTemplate) {
            return (float) ($this->salaryTemplate->type === SalaryTemplate::TYPE_HOURLY
                ? $this->salaryTemplate->daily_rate      // pay per day
                : $this->salaryTemplate->basic_salary);  // monthly basic
        }

        return (float) $value; // legacy salary column fallback (if it exists)
    }

    /** Is this employee on an hourly (daily-paid) template? */
    public function getIsHourlyPaidAttribute(): bool
    {
        return $this->salaryTemplate?->type === SalaryTemplate::TYPE_HOURLY;
    }
}
