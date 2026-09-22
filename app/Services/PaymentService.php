<?php

namespace App\Services;

use Stripe\Stripe;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PosSetting;
use App\Payment\PaystackPayment;

use App\Models\PaymentWithCheque;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentWithCreditCard;
use App\Services\InvoiceService;

class PaymentService
{
 public function __construct(
        private InvoiceService $invoiceService,
        private PaystackPayment $paystack
    ) {}

    /**
     * Resolve the configured gateway. Currently Paystack-only.
     */
    public function initialize(string $payment_type): ?PaystackPayment
    {
        return $payment_type === 'paystack' ? $this->paystack : null;
    }

    /**
     * Resolve Paystack directly — convenient when the controller knows
     * it always wants Paystack and doesn't need the type discriminator.
     */
    public function paystack(): PaystackPayment
    {
        return $this->paystack;
    }

    public function payForPurchase(array $data)
    {
        $lims_purchase_data = Purchase::find($data['purchase_id']);
        $lims_purchase_data->paid_amount += $data['amount'];
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        // dd($data, $balance, $lims_purchase_data);
        if ($balance > 0)
            $lims_purchase_data->payment_status = 1;
        else
            $lims_purchase_data->payment_status = 2;

        $lims_purchase_data->save();

        if ($data['paid_by_id'] == 1)
            $paying_method = 'Cash';
        elseif ($data['paid_by_id'] == 2)
            $paying_method = 'Gift Card';
        elseif ($data['paid_by_id'] == 3)
            $paying_method = 'Credit Card';
        else
            $paying_method = 'Cheque';

        $lims_payment_data = new Payment();
        $lims_payment_data->user_id = Auth::id();
        $lims_payment_data->purchase_id = $lims_purchase_data->id;
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->payment_reference = $this->invoiceService->generateInvoiceName('ppr-'); // 'ppr-' . date("Ymd") . '-'. date("his");
        $lims_payment_data->amount = $data['amount'];
        if (isset($data['currency_id'])) {
            $lims_payment_data->currency_id = $data['currency_id'];
        }
        if (isset($data['exchange_rate'])) {
            $lims_payment_data->exchange_rate = $data['exchange_rate'];
        }
        if (isset($data['document'])) {
            $lims_payment_data->document = $data['document'];
        }
        $lims_payment_data->change = $data['paying_amount'] - $data['amount'];
        $lims_payment_data->paying_method = $paying_method;
        $lims_payment_data->payment_note = $data['payment_note'];
        $lims_payment_data->payment_at = $data['payment_at'];
        $lims_payment_data->save();

        $data['payment_id'] = $lims_payment_data->id;
        $lims_pos_setting_data = PosSetting::latest()->first();
        if ($paying_method == 'Credit Card' && $lims_pos_setting_data->stripe_secret_key) {
            Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['amount'];

            // Charge the Customer
            $charge = \Stripe\Charge::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'source' => $token,
            ]);

            $data['charge_id'] = $charge->id;
            PaymentWithCreditCard::create($data);
        } elseif ($paying_method == 'Cheque') {
            PaymentWithCheque::create($data);
        }

        // === ACCOUNTING ENGINE PHASE 2E: PAYMENT ===
        $accountingService = app(\App\Services\AccountingService::class);
        $result = $accountingService->recordPayment($lims_payment_data);
        if (!$result->success) {
            \Log::error('Accounting failed for Purchase Payment', ['payment_id' => $lims_payment_data->id, 'error' => $result->error]);
            if (\Schema::hasColumn($lims_payment_data->getTable(), 'accounting_status')) {
                $lims_payment_data->accounting_status = 'failed';
                $lims_payment_data->save();
            }
        }
        // ===========================================

        return [
            'status' => true,
            'message' => 'Payment created successfully',
            'data' => '',
        ];
    }
}
