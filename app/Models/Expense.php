<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        "reference_no", "name", "expense_category_id", "warehouse_id", "account_id", 
        "user_id", "cash_register_id", "amount", "note", "created_at", "status"
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    } 

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }
}