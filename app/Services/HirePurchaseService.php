<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\HirePurchaseInstallment;
use Carbon\Carbon;

class HirePurchaseService
{
    /**
     * Interest rates by term (months)
     */
    protected $interestRates = [
        3 => 5,      // 5% for 3 months
        6 => 8,      // 8% for 6 months
        12 => 12,    // 12% for 12 months
        24 => 15,    // 15% for 24 months
    ];

    /**
     * Get interest rate for a given term
     */
    public function getInterestRate($terms)
    {
        return $this->interestRates[$terms] ?? 0;
    }

    /**
     * Validate hire purchase data
     */
    public function validateHirePurchase($data)
    {
        $errors = [];

        if (!isset($data['hire_purchase_down_payment']) || $data['hire_purchase_down_payment'] < 0) {
            $errors[] = 'Down payment must be a positive number.';
        }

        $downPaymentPercentage = ($data['hire_purchase_down_payment'] / $data['grand_total']) * 100;
        if ($downPaymentPercentage < 10) {
            $errors[] = 'Down payment must be at least 10% of the total amount.';
        }

        if (!isset($data['hire_purchase_terms']) || !in_array($data['hire_purchase_terms'], [3, 6, 12, 24])) {
            $errors[] = 'Invalid hire purchase terms. Must be 3, 6, 12, or 24 months.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Calculate hire purchase details
     */
    public function calculateDetails($grandTotal, $downPayment, $terms)
    {
        $interestRate = $this->getInterestRate($terms);
        $balance = $grandTotal - $downPayment;
        $interest = $balance * ($interestRate / 100);
        $totalWithInterest = $balance + $interest;
        $monthlyInstallment = round($totalWithInterest / $terms, 2);

        return [
            'down_payment' => $downPayment,
            'balance' => $balance,
            'interest_rate' => $interestRate,
            'total_interest' => round($interest, 2),
            'total_with_interest' => $totalWithInterest,
            'terms' => $terms,
            'monthly_installment' => $monthlyInstallment,
        ];
    }

    /**
     * Generate hire purchase installments
     */
    public function generateInstallments(Sale $sale, $startDate = null)
    {
        if (!$sale->is_hire_purchase || !$sale->hire_purchase_terms) {
            return [];
        }

        $startDate = $startDate ? Carbon::parse($startDate) : now();
        $balance = $sale->grand_total - $sale->hire_purchase_down_payment;
        $interest = $balance * ($sale->hire_purchase_interest_rate / 100);
        $totalWithInterest = $balance + $interest;
        $monthlyInstallment = round($totalWithInterest / $sale->hire_purchase_terms, 2);

        $installments = [];
        $dueDate = $startDate->copy();

        for ($i = 1; $i <= $sale->hire_purchase_terms; $i++) {
            $amount = ($i === $sale->hire_purchase_terms)
                ? $totalWithInterest - (($i - 1) * $monthlyInstallment)
                : $monthlyInstallment;

            $installments[] = [
                'sale_id' => $sale->id,
                'installment_number' => $i,
                'due_date' => $dueDate->toDateString(),
                'amount' => round($amount, 2),
                'paid_amount' => 0,
                'payment_status' => 'pending',
            ];

            $dueDate->addMonth();
        }

        return $installments;
    }

    /**
     * Create installment records in database
     */
    public function createInstallments(Sale $sale, $startDate = null)
    {
        $installments = $this->generateInstallments($sale, $startDate);
        info('installments array: ', array_map(function($i) {
            return (array)$i;
        }, $installments));

        foreach ($installments as $installment) {
            HirePurchaseInstallment::create($installment);
        }

        // Convert start date safely
        if ($startDate) {
            // Try d-m-Y first, fallback to normal parsing
            try {
                $startDate = Carbon::createFromFormat('d-m-Y', $startDate);
            } catch (\Throwable $e) {
                $startDate = Carbon::parse($startDate);
            }
        } else {
            $startDate = now();
        }

        $endDate = $startDate->copy()->addMonths($sale->hire_purchase_terms);

        $sale->update([
            'hire_purchase_start_date' => $startDate->format('Y-m-d'),
            'hire_purchase_end_date'   => $endDate->format('Y-m-d'),
            'payment_status' => 3,
        ]);

        return $installments;
    }


    /**
     * Get pending installments for a sale
     */
    public function getPendingInstallments(Sale $sale)
    {
        return $sale->hire_purchase_installments()
            ->where('payment_status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get overdue installments
     */
    public function getOverdueInstallments($customerId = null)
    {
        $query = HirePurchaseInstallment::query()
            ->where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString());

        if ($customerId) {
            $query->whereHas('sale', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            });
        }

        return $query->get();
    }

    /**
     * Record installment payment
     */
    public function recordPayment(HirePurchaseInstallment $installment, $amount, $method = 'cash', $notes = null)
    {
        $amount = min($amount, $installment->remaining_amount);

        $installment->update([
            'paid_amount' => $installment->paid_amount + $amount,
            'payment_status' => $installment->paid_amount + $amount >= $installment->amount ? 'paid' : 'partial',
            'payment_date' => now(),
            'payment_method' => $method,
            'notes' => $notes,
        ]);

        // Update sale paid_amount
        $sale = $installment->sale;
        $totalPaid = $sale->hire_purchase_installments()->sum('paid_amount');

        $sale->update([
            'paid_amount' => $totalPaid,
            'payment_status' => $totalPaid >= ($sale->grand_total - $sale->hire_purchase_down_payment + 
                (($sale->grand_total - $sale->hire_purchase_down_payment) * $sale->hire_purchase_interest_rate / 100)) ? 4 : 3
        ]);

        return $installment;
    }

    /**
     * Get hire purchase summary for dashboard
     */
    public function getDashboardSummary()
    {
        $totalActive = Sale::where('is_hire_purchase', true)
            ->where('hire_purchase_status', 'active')
            ->count();

        $totalCompleted = Sale::where('is_hire_purchase', true)
            ->where('hire_purchase_status', 'completed')
            ->count();

        $totalOverdue = HirePurchaseInstallment::where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $totalDue = HirePurchaseInstallment::where('payment_status', '!=', 'paid')
            ->where('due_date', '<=', now()->toDateString())
            ->sum('amount');

        $totalPaid = HirePurchaseInstallment::where('payment_status', 'paid')->sum('paid_amount');

        return [
            'active_contracts' => $totalActive,
            'completed_contracts' => $totalCompleted,
            'overdue_installments' => $totalOverdue,
            'total_due' => round($totalDue, 2),
            'total_paid' => round($totalPaid, 2),
        ];
    }
}
