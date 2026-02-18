<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HirePurchaseInstallment extends Model
{
    protected $table = 'hire_purchase_installments';

    protected $fillable = [
        'sale_id',
        'installment_number',
        'due_date',
        'amount',
        'paid_amount',
        'payment_status',
        'payment_date',
        'payment_method',
        'notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the sale associated with this installment
     */
    public function sale()
    {
        return $this->belongsTo('App\Models\Sale', 'sale_id', 'id');
    }

    /**
     * Get remaining amount for this installment
     */
    public function getRemainingAmountAttribute()
    {
        return round($this->amount - ($this->paid_amount ?? 0), 2);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdueAttribute()
    {
        if ($this->payment_status === 'paid' || $this->due_date->isFuture()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Get status badge (overdue, due soon, pending, paid)
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->payment_status === 'paid') {
            return 'success';
        }

        $daysUntilDue = now()->diffInDays($this->due_date);

        if ($this->due_date->isPast()) {
            return 'danger'; // Overdue
        }

        if ($daysUntilDue <= 3) {
            return 'warning'; // Due soon
        }

        return 'info'; // Pending
    }
}
