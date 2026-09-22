<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\Purchase;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Flip a pending Payment to paid and reconcile the Purchase balance.
     * Wrapped in a transaction so Payment + Purchase + accounting entry
     * commit together (or not at all). Idempotent — safe to call from
     * both the webhook and the redirect callback.
     */
    protected function finalizePayment(Payment $payment): void
    {
        if ((int) $payment->payment_status === 2) {
            return; // already settled
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['payment_status' => 2]);

            $purchase = Purchase::find($payment->purchase_id);
            if ($purchase) {
                $purchase->paid_amount += $payment->amount;
                $balance = $purchase->grand_total - $purchase->paid_amount;
                $purchase->payment_status = $balance > 0 ? 1 : 2;
                $purchase->save();
            }

            try {
                app(AccountingService::class)->recordPayment($payment);
            } catch (\Throwable $e) {
                Log::error('Accounting post-payment failure', [
                    'payment_id' => $payment->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        });
    }
}