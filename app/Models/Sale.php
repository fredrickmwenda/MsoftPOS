<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable =[
        "reference_no", "user_id", "cash_register_id", "table_id",
         "queue", "customer_id", "warehouse_id", "biller_id", "item", 
         "total_qty", "total_discount", "total_tax", "total_price", 
         "order_tax_rate","order_tax", "order_discount_type", "order_discount_value", 
        "order_discount", "coupon_id", "coupon_discount", "shipping_cost", "grand_total",
         "currency_id", "exchange_rate", "sale_status", "payment_status", "paid_amount", 
         "document", "sale_note", "staff_note", "created_at", "woocommerce_order_id",
         // Hire Purchase fields
         "is_hire_purchase", "hire_purchase_down_payment", "hire_purchase_terms", 
         "hire_purchase_interest_rate", "hire_purchase_status", "hire_purchase_start_date", 
         "hire_purchase_end_date"
    ];

    protected $dates = [
        'hire_purchase_start_date',
        'hire_purchase_end_date',
        'created_at',
        'updated_at'
    ];

    public function biller()
    {
        return $this->belongsTo('App\Models\Biller');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function table()
    {
        return $this->belongsTo('App\Models\Table');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }

    public function product_sales()
    {
        return $this->hasMany('App\Models\Product_Sale');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Payment');
    }

    public function sale_additional_costs()
    {
        return $this->hasMany(SaleAdditionalCost::class);
    }

    public function hire_purchase_installments()
    {
        return $this->hasMany('App\Models\HirePurchaseInstallment', 'sale_id', 'id');
    }

    /**
     * Get total amount with hire purchase interest included
     */
    public function getHirePurchaseTotalWithInterestAttribute()
    {
        if (!$this->is_hire_purchase) {
            return $this->grand_total;
        }

        $balance = $this->grand_total - $this->hire_purchase_down_payment;
        $interest = $balance * ($this->hire_purchase_interest_rate / 100);
        return $this->grand_total + $interest;
    }

    /**
     * Get monthly installment amount
     */
    public function getHirePurchaseMonthlyInstallmentAttribute()
    {
        if (!$this->is_hire_purchase || !$this->hire_purchase_terms) {
            return 0;
        }

        $balance = $this->grand_total - $this->hire_purchase_down_payment;
        $interest = $balance * ($this->hire_purchase_interest_rate / 100);
        $totalWithInterest = $balance + $interest;
        
        return round($totalWithInterest / $this->hire_purchase_terms, 2);
    }

    /**
     * Get remaining balance (amount still to be paid)
     */
    public function getHirePurchaseRemainingBalanceAttribute()
    {
        if (!$this->is_hire_purchase) {
            return 0;
        }

        $balance = $this->grand_total - $this->hire_purchase_down_payment;
        $interest = $balance * ($this->hire_purchase_interest_rate / 100);
        $totalWithInterest = $balance + $interest;
        
        // Subtract all paid installments
        $paidInstallments = $this->hire_purchase_installments()
            ->where('payment_status', 'paid')
            ->sum('amount');
        
        return round($totalWithInterest - $paidInstallments, 2);
    }

    /**
     * Get hire purchase summary details
     */
    public function getHirePurchaseDetailsAttribute()
    {
        if (!$this->is_hire_purchase) {
            return null;
        }

        $downPayment = $this->hire_purchase_down_payment;
        $balance = $this->grand_total - $downPayment;
        $interest = $balance * ($this->hire_purchase_interest_rate / 100);
        $totalWithInterest = $balance + $interest;
        $monthlyInstallment = round($totalWithInterest / $this->hire_purchase_terms, 2);

        return [
            'total_amount' => $this->grand_total,
            'down_payment' => $downPayment,
            'balance' => $balance,
            'interest_rate' => $this->hire_purchase_interest_rate,
            'total_interest' => round($interest, 2),
            'total_with_interest' => $totalWithInterest,
            'terms' => $this->hire_purchase_terms,
            'monthly_installment' => $monthlyInstallment,
            'status' => $this->hire_purchase_status,
            'start_date' => $this->hire_purchase_start_date,
            'end_date' => $this->hire_purchase_end_date,
            'remaining_balance' => $this->hire_purchase_remaining_balance,
        ];
    }
}
