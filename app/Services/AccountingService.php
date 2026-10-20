<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingConfig;
use App\Models\AccountMapping;
use App\Models\JournalEntry;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleExchange;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AccountingService
{
    use \App\Traits\TenantInfo;
    use \App\Traits\MailInfo;

    /* ============================================================
        CORE ENGINE
    ============================================================ */

    private function executeSafe(...$args): AccountingResult
    {
        $callback = end($args);

        if (!is_callable($callback)) {
            return AccountingResult::failed('Accounting callback is not callable.');
        }

        // ── AUTO-BOOTSTRAP: creates tables, config & chart of accounts on first hit ──
        try {
            AccountingBootstrap::ensure();
        } catch (\Throwable $e) {
            Log::error('Accounting bootstrap failed: ' . $e->getMessage());
            return AccountingResult::failed('Accounting bootstrap failed: ' . $e->getMessage());
        }

        $config = AccountingConfig::first();

        if (!$config || !$config->enabled) {
            Log::info('Accounting skipped: module is disabled.');
            return AccountingResult::success(null);
        }

        // ── Skip documents dated before the configured start date ──
        if (count($args) >= 3) {
            $sourceType = $args[0];
            $sourceId   = $args[1];

            try {
                if (class_exists($sourceType)) {
                    $model = $sourceType::find($sourceId);
                    if ($model && $model->created_at) {
                        $date = $model->created_at->toDateString();
                        if ($config->start_date && $date < $config->start_date) {
                            Log::info("Accounting skipped: {$sourceType}#{$sourceId} dated {$date} is before start_date {$config->start_date}.");
                            return AccountingResult::success(null);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore and proceed if date checking fails
            }
        }

        try {
            $journalEntry = $callback();

            if ($journalEntry && count($args) >= 3) {
                $this->markPosted($args[0], $args[1]);
            }

            return AccountingResult::success($journalEntry);
        } catch (\Throwable $e) {
            Log::error('Journal entry failed: ' . $e->getMessage(), [
                'source_type' => $args[0] ?? null,
                'source_id'   => $args[1] ?? null,
            ]);

            return AccountingResult::failed($e->getMessage());
        }
    }

    private function markPosted(string $sourceType, int $sourceId): void
    {
        try {
            if (class_exists($sourceType)) {
                $model = $sourceType::find($sourceId);
                if ($model && Schema::hasColumn($model->getTable(), 'accounting_status')) {
                    $model->accounting_status = 'posted';
                    $model->saveQuietly();
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Could not mark accounting_status for {$sourceType}#{$sourceId}: " . $e->getMessage());
        }
    }

    /* ============================================================
        ACCOUNT LOOKUPS (self-healing — auto-create if missing)
    ============================================================ */

    public function getAccountId(string $code): int
    {
        return AccountingBootstrap::ensureAccount($code)->id;
    }

    private function getCashAccountId(): int
    {
        $account = AccountingAccount::where('code', '1000')->first()
            ?? AccountingAccount::where('code', '1300')->first()
            ?? AccountingBootstrap::ensureAccount('1000');

        return $account->id;
    }

    public function getMappedAccount(string $type, int $id, int $fallbackAccountId): int
    {
        $mapping = AccountMapping::where('mapped_type', $type)
            ->where('mapped_id', $id)
            ->first();

        if ($mapping) {
            return $mapping->accounting_account_id;
        }

        if ($type === 'App\Models\Account') {
            return $this->createLegacyCashAccountMapping($id, $fallbackAccountId);
        }

        return $fallbackAccountId;
    }

    private function createLegacyCashAccountMapping(int $legacyAccountId, int $fallbackAccountId): int
    {
        $legacyAccount = \App\Models\Account::find($legacyAccountId);
        if (!$legacyAccount) {
            return $fallbackAccountId;
        }

        $baseCode = '10A' . $legacyAccount->id;
        $code = $baseCode;
        $suffix = 1;
        while (AccountingAccount::where('code', $code)->exists()) {
            $code = $baseCode . '-' . $suffix++;
        }

        $attributes = [
            'code' => $code,
            'name' => $legacyAccount->name,
            'account_type' => 'asset',
            'parent_id' => $fallbackAccountId,
            'is_control_account' => false,
            'is_system' => false,
            'is_active' => true,
        ];

        if (Schema::hasColumn('accounting_accounts', 'is_cash_account')) {
            $attributes['is_cash_account'] = true;
        }

        $accountingAccount = AccountingAccount::create($attributes);

        AccountMapping::create([
            'accounting_account_id' => $accountingAccount->id,
            'mapped_type' => 'App\Models\Account',
            'mapped_id' => $legacyAccount->id,
        ]);

        return $accountingAccount->id;
    }

    /* ============================================================
        IDEMPOTENCY HELPERS
    ============================================================ */

    private function existingUnreversedJournal(string $sourceType, int $sourceId, string $eventType): ?JournalEntry
    {
        return JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('event_type', $eventType)
            ->whereNotExists(function ($query) use ($sourceType, $sourceId, $eventType) {
                $query->selectRaw(1)
                    ->from('journal_entries as reversals')
                    ->where('reversals.source_type', $sourceType)
                    ->where('reversals.source_id', $sourceId)
                    ->where('reversals.event_type', $eventType . '_reversed');
            })
            ->first();
    }

    private function journalAlreadyExists(string $sourceType, int $sourceId, string $eventType): ?JournalEntry
    {
        return JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('event_type', $eventType)
            ->first();
    }

    private function resolvePostEventType(string $sourceType, int $sourceId, string $eventType): string
    {
        if (!str_contains($eventType, '_updated')) {
            return $eventType;
        }

        $existing = JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where(function ($query) use ($eventType) {
                $query->where('event_type', $eventType)
                    ->orWhere('event_type', 'like', $eventType . '_%');
            })
            ->pluck('event_type')
            ->all();

        if (!in_array($eventType, $existing, true)) {
            return $eventType;
        }

        $sequence = 2;
        while (in_array($eventType . '_' . $sequence, $existing, true)) {
            $sequence++;
        }

        return $eventType . '_' . $sequence;
    }

    /* ============================================================
        SALES
    ============================================================ */

    public function recordSale($sale, string $eventType = 'sale_created'): AccountingResult
    {
        return $this->executeSafe(get_class($sale), $sale->id, function () use ($sale, $eventType) {
            $sourceType = get_class($sale);
            $eventType = $this->resolvePostEventType($sourceType, $sale->id, $eventType);

            $existing = $this->existingUnreversedJournal($sourceType, $sale->id, $eventType);
            if ($existing) return $existing;

            $arAccountId = $this->getMappedAccount('App\Models\Customer', $sale->customer_id, $this->getAccountId('1100'));
            $revenueAccountId = $this->getAccountId('4100');

            return JournalBuilder::create()
                ->setSource($sourceType, $sale->id)
                ->setEventType($eventType)
                ->setReference(($sale->reference_no ?? 'SALE-' . $sale->id) . '-' . strtoupper($eventType))
                ->setDate(optional($sale->created_at)->toDateString())
                ->setNote($sale->sale_note ?? null)
                ->addDebit($arAccountId, $sale->grand_total, 'Accounts Receivable for Sale ' . $sale->reference_no)
                ->addCredit($revenueAccountId, $sale->grand_total, 'Revenue from Sale ' . $sale->reference_no)
                ->save();
        });
    }

    public function recordSaleReturn($return, string $eventType = 'sale_return_created'): AccountingResult
    {
        return $this->executeSafe(get_class($return), $return->id, function () use ($return, $eventType) {
            $existing = $this->existingUnreversedJournal(get_class($return), $return->id, $eventType);
            if ($existing) return $existing;

            $arAccountId = $this->getMappedAccount('App\Models\Customer', $return->customer_id, $this->getAccountId('1100'));
            $salesReturnAccountId = $this->getAccountId('4150');

            return JournalBuilder::create()
                ->setSource(get_class($return), $return->id)
                ->setEventType($eventType)
                ->setReference(($return->reference_no ?? 'SR') . '-' . $return->id . '-' . strtoupper($eventType))
                ->setDate(optional($return->created_at)->toDateString())
                ->setNote($return->return_note ?? null)
                ->addDebit($salesReturnAccountId, $return->grand_total, 'Sales Return ' . $return->reference_no)
                ->addCredit($arAccountId, $return->grand_total, 'Accounts Receivable reduction for Return ' . $return->reference_no)
                ->save();
        });
    }

    public function recordSaleExchange(SaleExchange $exchange, string $eventType = 'sale_exchange_created'): AccountingResult
    {
        return $this->executeSafe(get_class($exchange), $exchange->id, function () use ($exchange, $eventType) {
            $sourceType = get_class($exchange);
            $existing = $this->existingUnreversedJournal($sourceType, $exchange->id, $eventType);
            if ($existing) return $existing;

            $sale = Sale::findOrFail($exchange->sale_id);
            $arAccountId = $this->getMappedAccount('App\Models\Customer', $exchange->customer_id, $this->getAccountId('1100'));
            $revenueAccountId = $this->getAccountId('4100');
            $salesReturnAccountId = $this->getAccountId('4150');
            $cashAccountId = $this->getCashAccountId();

            $returnedTotal = $exchange->products()->where('type', 'returned')->sum('total');
            $newTotal = $exchange->products()->where('type', 'new')->sum('total');

            $builder = JournalBuilder::create()
                ->setSource($sourceType, $exchange->id)
                ->setSourceSubtype('sale_exchange')
                ->setEventType($eventType)
                ->setReference(($exchange->reference_no ?? 'EXC') . '-' . $exchange->id . '-' . strtoupper($eventType))
                ->setDate(optional($exchange->created_at)->toDateString())
                ->setNote($exchange->exchange_note ?? null);

            if ($returnedTotal > 0) {
                $builder->addDebit($salesReturnAccountId, $returnedTotal, 'Exchange returned items for Sale ' . $sale->reference_no)
                    ->addCredit($arAccountId, $returnedTotal, 'AR reduction for Exchange ' . $exchange->reference_no);
            }

            if ($newTotal > 0) {
                $builder->addDebit($arAccountId, $newTotal, 'AR for Exchange new items ' . $exchange->reference_no)
                    ->addCredit($revenueAccountId, $newTotal, 'Revenue for Exchange new items ' . $exchange->reference_no);
            }

            if ($exchange->payment_type === 'receive' && $exchange->amount > 0) {
                $builder->addDebit($cashAccountId, $exchange->amount, 'Additional payment for Exchange ' . $exchange->reference_no)
                    ->addCredit($arAccountId, $exchange->amount, 'AR clearance for Exchange additional payment');
            } elseif ($exchange->payment_type === 'pay' && $exchange->amount > 0) {
                $builder->addDebit($arAccountId, $exchange->amount, 'Customer refund for Exchange ' . $exchange->reference_no)
                    ->addCredit($cashAccountId, $exchange->amount, 'Cash refund for Exchange ' . $exchange->reference_no);
            }

            return $builder->save();
        });
    }

    /* ============================================================
        PURCHASES
    ============================================================ */

    public function recordPurchase($purchase, string $eventType = 'purchase_created'): AccountingResult
    {
        return $this->executeSafe(get_class($purchase), $purchase->id, function () use ($purchase, $eventType) {
            $sourceType = get_class($purchase);
            $eventType = $this->resolvePostEventType($sourceType, $purchase->id, $eventType);

            $existing = $this->existingUnreversedJournal($sourceType, $purchase->id, $eventType);
            if ($existing) return $existing;

            $apAccountId = $this->getMappedAccount('App\Models\Supplier', $purchase->supplier_id, $this->getAccountId('2100'));
            $inventoryAccountId = $this->getAccountId('1200');
            $inputVatAccountId = $this->getAccountId('1250');
            $freightInAccountId = $this->getAccountId('5200');
            $purchaseDiscountAccountId = $this->getAccountId('5300');

            $builder = JournalBuilder::create()
                ->setSource($sourceType, $purchase->id)
                ->setEventType($eventType)
                ->setReference(($purchase->reference_no ?? 'PURCHASE-' . $purchase->id) . '-' . strtoupper($eventType))
                ->setDate(optional($purchase->created_at)->toDateString())
                ->setNote($purchase->note ?? null);

            if ($purchase->total_cost > 0) {
                $builder->addDebit($inventoryAccountId, $purchase->total_cost, 'Inventory Receipt for Purchase ' . $purchase->reference_no);
            }

            if ($purchase->order_tax > 0) {
                $builder->addDebit($inputVatAccountId, $purchase->order_tax, 'Input VAT for Purchase ' . $purchase->reference_no);
            }

            if ($purchase->shipping_cost > 0) {
                $builder->addDebit($freightInAccountId, $purchase->shipping_cost, 'Shipping Cost for Purchase ' . $purchase->reference_no);
            }

            if ($purchase->order_discount > 0) {
                $builder->addCredit($purchaseDiscountAccountId, $purchase->order_discount, 'Purchase Discount for ' . $purchase->reference_no);
            }

            $paidAmount = $purchase->paid_amount ?? 0;
            $unpaidAmount = $purchase->grand_total - $paidAmount;

            if ($unpaidAmount > 0) {
                $builder->addCredit($apAccountId, $unpaidAmount, 'Accounts Payable for Purchase ' . $purchase->reference_no);
            }

            if ($paidAmount > 0) {
                $cashAccountId = $this->getCashAccountId();
                $builder->addCredit($cashAccountId, $paidAmount, 'Cash out for Purchase Payment ' . $purchase->reference_no);
            }

            return $builder->save();
        });
    }

    public function recordPurchaseReturn($returnPurchase, string $eventType = 'purchase_return_created'): AccountingResult
    {
        return $this->executeSafe(get_class($returnPurchase), $returnPurchase->id, function () use ($returnPurchase, $eventType) {
            $existing = $this->existingUnreversedJournal(get_class($returnPurchase), $returnPurchase->id, $eventType);
            if ($existing) return $existing;

            $apAccountId = $this->getMappedAccount('App\Models\Supplier', $returnPurchase->supplier_id, $this->getAccountId('2100'));
            $purchaseReturnAccountId = $this->getAccountId('5150');

            return JournalBuilder::create()
                ->setSource(get_class($returnPurchase), $returnPurchase->id)
                ->setEventType($eventType)
                ->setReference(($returnPurchase->reference_no ?? 'PR') . '-' . $returnPurchase->id . '-' . strtoupper($eventType))
                ->setDate(optional($returnPurchase->created_at)->toDateString())
                ->setNote($returnPurchase->return_note ?? null)
                ->addDebit($apAccountId, $returnPurchase->grand_total, 'Accounts Payable reduction for Return ' . $returnPurchase->reference_no)
                ->addCredit($purchaseReturnAccountId, $returnPurchase->grand_total, 'Purchase Return ' . $returnPurchase->reference_no)
                ->save();
        });
    }

    /* ============================================================
        EXPENSE / INCOME / PAYROLL
    ============================================================ */

    public function recordExpense($expense): AccountingResult
    {
        return $this->executeSafe(get_class($expense), $expense->id, function () use ($expense) {
            $cashAccountId = $this->getMappedAccount('App\Models\Account', $expense->account_id, $this->getCashAccountId());
            $expenseAccountId = $this->getMappedAccount('App\Models\ExpenseCategory', $expense->expense_category_id, $this->getAccountId('6100'));

            return JournalBuilder::create()
                ->setSource(get_class($expense), $expense->id)
                ->setEventType('expense_created')
                ->setReference($expense->reference_no ?? 'EXP-' . $expense->id)
                ->setDate(optional($expense->created_at)->toDateString())
                ->setNote($expense->note ?? null)
                ->addDebit($expenseAccountId, $expense->amount, 'Operating Expense')
                ->addCredit($cashAccountId, $expense->amount, 'Cash Out for Expense')
                ->save();
        });
    }

    public function recordIncome($income): AccountingResult
    {
        return $this->executeSafe(get_class($income), $income->id, function () use ($income) {
            $cashAccountId = $this->getMappedAccount('App\Models\Account', $income->account_id, $this->getCashAccountId());
            $incomeAccountId = $this->getMappedAccount('App\Models\IncomeCategory', $income->income_category_id, $this->getAccountId('4300'));

            return JournalBuilder::create()
                ->setSource(get_class($income), $income->id)
                ->setEventType('income_created')
                ->setReference($income->reference_no ?? 'INC-' . $income->id)
                ->setDate(optional($income->created_at)->toDateString())
                ->setNote($income->note ?? null)
                ->addDebit($cashAccountId, $income->amount, 'Cash In for Income')
                ->addCredit($incomeAccountId, $income->amount, 'Other Income')
                ->save();
        });
    }

    public function recordPayroll($payroll): AccountingResult
    {
        return $this->executeSafe(get_class($payroll), $payroll->id, function () use ($payroll) {
            $cashAccountId = $this->getMappedAccount('App\Models\Account', $payroll->account_id, $this->getCashAccountId());
            $payrollAccountId = $this->getAccountId('5100');

            return JournalBuilder::create()
                ->setSource(get_class($payroll), $payroll->id)
                ->setEventType('payroll_paid')
                ->setReference($payroll->reference_no ?? 'PAYROLL-' . $payroll->id)
                ->setDate(optional($payroll->created_at)->toDateString())
                ->setNote($payroll->note ?? null)
                ->addDebit($payrollAccountId, $payroll->amount, 'Payroll Expense')
                ->addCredit($cashAccountId, $payroll->amount, 'Cash Out for Payroll')
                ->save();
        });
    }

    /* ============================================================
        OPENING BALANCES
    ============================================================ */

    public function recordCustomerOpeningBalance($customer, string $eventType = 'customer_opening_balance_created'): AccountingResult
    {
        return $this->executeSafe(get_class($customer), $customer->id, function () use ($customer, $eventType) {
            if ((float) $customer->opening_balance <= 0) return null;

            $existing = $this->journalAlreadyExists(get_class($customer), $customer->id, $eventType);
            if ($existing) return $existing;

            $arAccountId = $this->getMappedAccount('App\Models\Customer', $customer->id, $this->getAccountId('1100'));
            $openingBalanceEquityId = $this->getAccountId('3900');

            return JournalBuilder::create()
                ->setSource(get_class($customer), $customer->id)
                ->setEventType($eventType)
                ->setReference('CUST-OB-' . $customer->id)
                ->setDate(optional($customer->created_at)->toDateString())
                ->setNote('Opening Balance for Customer ' . $customer->name)
                ->addDebit($arAccountId, $customer->opening_balance, 'A/R Initial Balance')
                ->addCredit($openingBalanceEquityId, $customer->opening_balance, 'Opening Equity')
                ->save();
        });
    }

    public function recordSupplierOpeningBalance($supplier, string $eventType = 'supplier_opening_balance_created'): AccountingResult
    {
        return $this->executeSafe(get_class($supplier), $supplier->id, function () use ($supplier, $eventType) {
            if ((float) $supplier->opening_balance <= 0) return null;

            $existing = $this->journalAlreadyExists(get_class($supplier), $supplier->id, $eventType);
            if ($existing) return $existing;

            $apAccountId = $this->getMappedAccount('App\Models\Supplier', $supplier->id, $this->getAccountId('2100'));
            $openingBalanceEquityId = $this->getAccountId('3900');

            return JournalBuilder::create()
                ->setSource(get_class($supplier), $supplier->id)
                ->setEventType($eventType)
                ->setReference('SUPP-OB-' . $supplier->id)
                ->setDate(optional($supplier->created_at)->toDateString())
                ->setNote('Opening Balance for Supplier ' . $supplier->name)
                ->addDebit($openingBalanceEquityId, $supplier->opening_balance, 'Opening Equity')
                ->addCredit($apAccountId, $supplier->opening_balance, 'A/P Initial Balance')
                ->save();
        });
    }

    public function recordAccountOpeningBalance($account, string $eventType = 'account_opening_balance_created'): AccountingResult
    {
        return $this->executeSafe(get_class($account), $account->id, function () use ($account, $eventType) {
            if ((float) $account->initial_balance <= 0) return null;

            $existing = $this->journalAlreadyExists(get_class($account), $account->id, $eventType);
            if ($existing) return $existing;

            $cashAccountId = $this->getMappedAccount('App\Models\Account', $account->id, $this->getCashAccountId());
            $openingBalanceEquityId = $this->getAccountId('3900');

            return JournalBuilder::create()
                ->setSource(get_class($account), $account->id)
                ->setEventType($eventType)
                ->setReference('ACC-OB-' . $account->id)
                ->setDate(optional($account->created_at)->toDateString())
                ->setNote('Opening Balance for Account ' . $account->name)
                ->addDebit($cashAccountId, $account->initial_balance, 'Cash/Bank Initial Balance')
                ->addCredit($openingBalanceEquityId, $account->initial_balance, 'Opening Equity')
                ->save();
        });
    }

    /* ============================================================
        REVERSALS
    ============================================================ */

    public function reverseTransaction(string $sourceType, int $sourceId, string $eventSuffix = '_reversed'): AccountingResult
    {
        return $this->executeSafe($sourceType, $sourceId, function () use ($sourceType, $sourceId, $eventSuffix) {
            $entries = JournalEntry::where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->whereNot('event_type', 'like', '%_reversed')
                ->whereNot('event_type', 'like', '%_reversal')
                ->whereNot('event_type', 'like', '%_deleted')
                ->when(
                    Schema::hasColumn('journal_entries', 'related_journal_entry_id'),
                    fn ($query) => $query->whereNull('related_journal_entry_id')
                )
                ->with('lines')
                ->get();

            $lastReversed = null;
            foreach ($entries as $entry) {
                $alreadyReversedQuery = JournalEntry::where('source_type', $sourceType)
                    ->where('source_id', $sourceId)
                    ->where(function ($query) use ($entry) {
                        $query->whereIn('event_type', [
                            $entry->event_type . '_reversed',
                            $entry->event_type . '_deleted',
                        ]);

                        if (Schema::hasColumn('journal_entries', 'related_journal_entry_id')) {
                            $query->orWhere('related_journal_entry_id', $entry->id);
                        }
                    });

                if ($alreadyReversedQuery->exists()) {
                    continue;
                }

                $lastReversed = JournalBuilder::reverse($entry, $eventSuffix);
            }

            return $lastReversed;
        });
    }

    /* ============================================================
        PAYMENTS
    ============================================================ */

    public function recordPayment($payment, string $eventType = 'payment_received'): AccountingResult
    {
        if (!is_numeric($payment->amount ?? null) || (float) $payment->amount <= 0) {
            return AccountingResult::failed('Payment amount must be greater than zero.');
        }

        // Bootstrap BEFORE touching JournalEntry directly
        try {
            AccountingBootstrap::ensure();
        } catch (\Throwable $e) {
            Log::error('Accounting bootstrap failed: ' . $e->getMessage());
            return AccountingResult::failed('Accounting bootstrap failed: ' . $e->getMessage());
        }

        if ($payment->sale_id) {
            if ($eventType === 'payment_received') {
                $eventType = $payment->return_id ? 'sale_refund_created' : 'sale_payment_created';
            }
            $existing = $this->journalAlreadyExists(get_class($payment), $payment->id, $eventType);
            if ($existing) {
                return AccountingResult::success($existing);
            }
            if ($payment->return_id) {
                return $this->recordCustomerRefund($payment, $eventType);
            }
            return $this->recordCustomerPayment($payment, $eventType);
        } elseif ($payment->purchase_id) {
            $isPurchaseRefund = !empty($payment->purchase_return_id) || !empty($payment->return_id);
            if ($eventType === 'payment_received') {
                $eventType = $isPurchaseRefund ? 'purchase_refund_created' : 'purchase_payment_created';
            }
            $existing = $this->journalAlreadyExists(get_class($payment), $payment->id, $eventType);
            if ($existing) {
                return AccountingResult::success($existing);
            }
            if ($isPurchaseRefund) {
                return $this->recordSupplierRefund($payment, $eventType);
            }
            return $this->recordSupplierPayment($payment, $eventType);
        }

        return AccountingResult::failed('Unknown payment type.');
    }

    private function recordCustomerPayment($payment, string $eventType): AccountingResult
    {
        return $this->executeSafe(get_class($payment), $payment->id, function () use ($payment, $eventType) {
            $sale = Sale::findOrFail($payment->sale_id);
            $arAccountId = $this->getMappedAccount('App\Models\Customer', $sale->customer_id, $this->getAccountId('1100'));

            $builder = JournalBuilder::create()
                ->setSource(get_class($payment), $payment->id)
                ->setSourceSubtype('customer_payment')
                ->setEventType($eventType)
                ->setReference(($payment->payment_reference ?? 'PAY') . '-' . $payment->id . '-' . strtoupper($eventType))
                ->setDate(optional($payment->payment_at)->toDateString() ?? optional($payment->created_at)->toDateString())
                ->setNote($payment->payment_note ?? null);

            $amount = $payment->amount;

            if ($payment->paying_method === 'Deposit') {
                $depositLiabilityAccountId = $this->getAccountId('2210');
                $builder->addDebit($depositLiabilityAccountId, $amount, 'Deposit Applied to Sale ' . $sale->reference_no)
                        ->setSourceSubtype('deposit_redemption');
            } elseif ($payment->paying_method === 'Gift Card') {
                $giftCardLiabilityAccountId = $this->getAccountId('2250');
                $builder->addDebit($giftCardLiabilityAccountId, $amount, 'Gift Card Applied to Sale ' . $sale->reference_no)
                        ->setSourceSubtype('gift_card_redemption');
            } elseif ($payment->paying_method === 'Points') {
                $rewardsLiabilityAccountId = $this->getAccountId('2220');
                $builder->addDebit($rewardsLiabilityAccountId, $amount, 'Reward Points Applied to Sale ' . $sale->reference_no)
                        ->setSourceSubtype('reward_point_redemption');
            } else {
                $cashAccountId = $payment->account_id
                    ? $this->getMappedAccount('App\Models\Account', $payment->account_id, $this->getCashAccountId())
                    : $this->getCashAccountId();
                $builder->addDebit($cashAccountId, $amount, 'Payment Received for Sale ' . $sale->reference_no);
            }

            $builder->addCredit($arAccountId, $amount, 'AR Clearance for Sale ' . $sale->reference_no);

            return $builder->save();
        });
    }

    private function recordSupplierPayment($payment, string $eventType): AccountingResult
    {
        return $this->executeSafe(get_class($payment), $payment->id, function () use ($payment, $eventType) {
            $purchase = Purchase::findOrFail($payment->purchase_id);
            $apAccountId = $this->getMappedAccount('App\Models\Supplier', $purchase->supplier_id, $this->getAccountId('2100'));
            $cashAccountId = $payment->account_id
                ? $this->getMappedAccount('App\Models\Account', $payment->account_id, $this->getCashAccountId())
                : $this->getCashAccountId();

            return JournalBuilder::create()
                ->setSource(get_class($payment), $payment->id)
                ->setSourceSubtype('supplier_payment')
                ->setEventType($eventType)
                ->setReference(($payment->payment_reference ?? 'PAY') . '-' . $payment->id . '-' . strtoupper($eventType))
                ->setDate(optional($payment->payment_at)->toDateString() ?? optional($payment->created_at)->toDateString())
                ->setNote($payment->payment_note ?? null)
                ->addDebit($apAccountId, $payment->amount, 'AP Clearance for Purchase ' . $purchase->reference_no)
                ->addCredit($cashAccountId, $payment->amount, 'Payment Sent for Purchase ' . $purchase->reference_no)
                ->save();
        });
    }

    private function recordCustomerRefund($payment, string $eventType): AccountingResult
    {
        return $this->executeSafe(get_class($payment), $payment->id, function () use ($payment, $eventType) {
            $sale = Sale::findOrFail($payment->sale_id);
            $arAccountId = $this->getMappedAccount('App\Models\Customer', $sale->customer_id, $this->getAccountId('1100'));
            $cashAccountId = $payment->account_id
                ? $this->getMappedAccount('App\Models\Account', $payment->account_id, $this->getCashAccountId())
                : $this->getCashAccountId();

            return JournalBuilder::create()
                ->setSource(get_class($payment), $payment->id)
                ->setSourceSubtype('customer_refund')
                ->setEventType($eventType)
                ->setReference(($payment->payment_reference ?? 'REF') . '-' . $payment->id . '-' . strtoupper($eventType))
                ->setDate(optional($payment->payment_at)->toDateString() ?? optional($payment->created_at)->toDateString())
                ->setNote($payment->payment_note ?? null)
                ->addDebit($arAccountId, $payment->amount, 'Customer refund for Sale ' . $sale->reference_no)
                ->addCredit($cashAccountId, $payment->amount, 'Cash refund for Sale ' . $sale->reference_no)
                ->save();
        });
    }

    private function recordSupplierRefund($payment, string $eventType): AccountingResult
    {
        return $this->executeSafe(get_class($payment), $payment->id, function () use ($payment, $eventType) {
            $purchase = Purchase::findOrFail($payment->purchase_id);
            $apAccountId = $this->getMappedAccount('App\Models\Supplier', $purchase->supplier_id, $this->getAccountId('2100'));
            $cashAccountId = $payment->account_id
                ? $this->getMappedAccount('App\Models\Account', $payment->account_id, $this->getCashAccountId())
                : $this->getCashAccountId();

            return JournalBuilder::create()
                ->setSource(get_class($payment), $payment->id)
                ->setSourceSubtype('supplier_refund')
                ->setEventType($eventType)
                ->setReference(($payment->payment_reference ?? 'REF') . '-' . $payment->id . '-' . strtoupper($eventType))
                ->setDate(optional($payment->payment_at)->toDateString() ?? optional($payment->created_at)->toDateString())
                ->setNote($payment->payment_note ?? null)
                ->addDebit($cashAccountId, $payment->amount, 'Supplier refund for Purchase ' . $purchase->reference_no)
                ->addCredit($apAccountId, $payment->amount, 'AP reinstatement for Purchase refund ' . $purchase->reference_no)
                ->save();
        });
    }

    /* ============================================================
        DEPOSITS / TRANSFERS / GIFT CARDS / POINTS
    ============================================================ */

    public function recordDeposit($deposit, string $eventType = 'customer_deposit'): AccountingResult
    {
        return $this->executeSafe(get_class($deposit), $deposit->id, function () use ($deposit, $eventType) {
            $existing = $this->journalAlreadyExists(get_class($deposit), $deposit->id, $eventType);
            if ($existing) return $existing;

            // FIX: map legacy account ID → accounting account (auto-creates mapping)
            $cashAccountId = !empty($deposit->account_id)
                ? $this->getMappedAccount('App\Models\Account', $deposit->account_id, $this->getCashAccountId())
                : $this->getCashAccountId();
            $depositLiabilityAccountId = $this->getAccountId('2210');

            return JournalBuilder::create()
                ->setSource(get_class($deposit), $deposit->id)
                ->setSourceSubtype('deposit')
                ->setEventType($eventType)
                ->setReference('DEP-' . $deposit->id . '-' . strtoupper($eventType))
                ->setDate(optional($deposit->created_at)->toDateString())
                ->setNote($deposit->note ?? null)
                ->addDebit($cashAccountId, $deposit->amount, 'Customer Deposit Received')
                ->addCredit($depositLiabilityAccountId, $deposit->amount, 'Customer Deposit Liability')
                ->save();
        });
    }

    public function recordMoneyTransfer($transfer, string $eventType = 'money_transfer'): AccountingResult
    {
        return $this->executeSafe(get_class($transfer), $transfer->id, function () use ($transfer, $eventType) {
            $existing = $this->journalAlreadyExists(get_class($transfer), $transfer->id, $eventType);
            if ($existing) return $existing;

            // FIX: legacy account IDs are now mapped to real accounting accounts
            $fromAccountId = !empty($transfer->from_account_id)
                ? $this->getMappedAccount('App\Models\Account', $transfer->from_account_id, $this->getCashAccountId())
                : $this->getCashAccountId();
            $toAccountId = !empty($transfer->to_account_id)
                ? $this->getMappedAccount('App\Models\Account', $transfer->to_account_id, $this->getCashAccountId())
                : $this->getCashAccountId();

            if ($fromAccountId === $toAccountId) {
                throw new Exception('Money transfer requires two different accounts (from and to resolve to the same account).');
            }

            return JournalBuilder::create()
                ->setSource(get_class($transfer), $transfer->id)
                ->setSourceSubtype('money_transfer')
                ->setEventType($eventType)
                ->setReference(($transfer->reference_no ?? 'TRF-' . $transfer->id) . '-' . strtoupper($eventType))
                ->setDate(optional($transfer->created_at)->toDateString())
                ->setNote($transfer->note ?: 'Money Transfer')
                ->addDebit($toAccountId, $transfer->amount, 'Transfer In')
                ->addCredit($fromAccountId, $transfer->amount, 'Transfer Out')
                ->save();
        });
    }

    public function recordGiftCardSale($giftCard, string $eventType = 'gift_card_sale'): AccountingResult
    {
        return $this->executeSafe(get_class($giftCard), $giftCard->id, function () use ($giftCard, $eventType) {
            $existing = $this->journalAlreadyExists(get_class($giftCard), $giftCard->id, $eventType);
            if ($existing) return $existing;

            $cashAccountId = $this->getCashAccountId();
            $giftCardLiabilityAccountId = $this->getAccountId('2250');

            return JournalBuilder::create()
                ->setSource(get_class($giftCard), $giftCard->id)
                ->setSourceSubtype('gift_card')
                ->setEventType($eventType)
                ->setReference('GC-' . $giftCard->card_no . '-' . strtoupper($eventType))
                ->setDate(optional($giftCard->created_at)->toDateString())
                ->setNote('Gift Card Sold')
                ->addDebit($cashAccountId, $giftCard->amount, 'Gift Card Sale Receipt')
                ->addCredit($giftCardLiabilityAccountId, $giftCard->amount, 'Gift Card Liability')
                ->save();
        });
    }

    public function recordPointsEarned($sale, $pointsEarned, $monetaryValue, string $eventType = 'points_earned'): AccountingResult
    {
        if ($monetaryValue <= 0) {
            return AccountingResult::success(null);
        }

        return $this->executeSafe(get_class($sale), $sale->id, function () use ($sale, $pointsEarned, $monetaryValue, $eventType) {
            $existing = $this->journalAlreadyExists(get_class($sale), $sale->id, $eventType);
            if ($existing) return $existing;

            $rewardsExpenseAccountId = $this->getAccountId('5210');
            $rewardsLiabilityAccountId = $this->getAccountId('2220');

            return JournalBuilder::create()
                ->setSource(get_class($sale), $sale->id)
                ->setSourceSubtype('points_earned')
                ->setEventType($eventType)
                ->setReference('PTS-' . $sale->id . '-' . strtoupper($eventType))
                ->setDate(optional($sale->created_at)->toDateString())
                ->setNote("Earned $pointsEarned points for Sale " . $sale->reference_no)
                ->addDebit($rewardsExpenseAccountId, $monetaryValue, 'Loyalty Rewards Expense')
                ->addCredit($rewardsLiabilityAccountId, $monetaryValue, 'Customer Rewards Liability')
                ->save();
        });
    }
}
