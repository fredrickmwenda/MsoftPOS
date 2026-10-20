<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use App\Models\Returns;
use App\Models\ReturnPurchase;
use App\Models\MoneyTransfer;
use App\Models\Expense;
use App\Models\Payroll;

class Account extends Model
{
    protected $fillable = [
        "account_no", "name", "initial_balance", "total_balance", "note", "is_default", "is_active"
    ];

    /* ----------------------------------------------------------
     |  Relationships
     +--------------------------------------------------------- */
    public function expenses()
    {
        return $this->hasMany('App\Models\Expense');
    }

    /* ==========================================================
     |  LEDGER BALANCE — same calculation as the Accounts page
     |  IN : initial balance + sale payments + purchase returns
     |       + received money transfers
     |  OUT: purchase payments + sale returns + expenses
     |       + payrolls + sent money transfers
     +========================================================= */
    public function calculateBalance(): float
    {
        // IN
        $payment_received   = Payment::whereNotNull('sale_id')->where('account_id', $this->id)->sum('amount');
        $purchase_return    = ReturnPurchase::where('account_id', $this->id)->sum('grand_total');
        $received_transfers = MoneyTransfer::where('to_account_id', $this->id)->sum('amount');

        // OUT
        $payment_sent   = Payment::whereNotNull('purchase_id')->where('account_id', $this->id)->sum('amount');
        $returns        = Returns::where('account_id', $this->id)->sum('grand_total');
        $expenses       = Expense::where('account_id', $this->id)->sum('amount');
        $payrolls       = Payroll::where('account_id', $this->id)->sum('amount');
        $sent_transfers = MoneyTransfer::where('from_account_id', $this->id)->sum('amount');

        return round(
            (float) $this->initial_balance
            + $payment_received + $purchase_return + $received_transfers
            - $payment_sent - $returns - $expenses - $payrolls - $sent_transfers,
            2
        );
    }

    /**
     * Auto-set total_balance in the accounts table to the real ledger value.
     * Returns the synced balance.
     */
    public function syncTotalBalance(): float
    {
        $this->total_balance = $this->calculateBalance();
        $this->save();

        return (float) $this->total_balance;
    }

    /** Rebuild total_balance for every active account (maintenance) */
    public static function syncAllBalances(): void
    {
        static::where('is_active', true)->get()->each->syncTotalBalance();
    }
}
