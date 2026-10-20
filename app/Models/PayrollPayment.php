<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id', 'user_id', 'account_id', 'amount',
        'paying_method', 'payment_date', 'reference_no', 'note',
    ];

    protected $casts = ['amount' => 'decimal:2', 'payment_date' => 'date'];

    public function payroll() { return $this->belongsTo(Payroll::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function account() { return $this->belongsTo(Account::class); }
}
