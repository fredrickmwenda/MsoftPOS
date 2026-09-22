<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollItemMeta;
use App\Models\PayrollPayment;
use Carbon\Carbon;
use DB;

class ProcessRecurringPayrolls extends Command
{
    protected $signature = 'payroll:process-recurring';
    protected $description = 'Process recurring payrolls, regenerate them, and register automatic payments.';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Find payrolls that are recurring and due today or earlier
        $recurringPayrolls = Payroll::where('is_recurring', true)
            ->where('next_date', '<=', $today)
            ->get();

        foreach ($recurringPayrolls as $templatePayroll) {
            DB::transaction(function () use ($templatePayroll, $today) {
                
                // 1. Duplicate the Payroll
                $newPayroll = $templatePayroll->replicate();
                $newPayroll->created_at = $today;
                $newPayroll->reference_no = 'payroll-' . date("Ymd") . '-' . date("his") . '-' . $templatePayroll->id;
                $newPayroll->is_recurring = false; // The new instance is a snapshot, not the template
                $newPayroll->next_date = null;
                $newPayroll->payment_status = 'unpaid';
                $newPayroll->save();

                // 2. Duplicate Items & Meta
                foreach ($templatePayroll->items as $item) {
                    $newItem = $item->replicate();
                    $newItem->payroll_id = $newPayroll->id;
                    $newItem->save();

                    // Duplicate Item Meta
                    $metas = PayrollItemMeta::where('payroll_item_id', $item->id)->get();
                    foreach ($metas as $meta) {
                        $newMeta = $meta->replicate();
                        $newMeta->payroll_id = $newPayroll->id;
                        $newMeta->payroll_item_id = $newItem->id;
                        $newMeta->save();
                    }
                }

                // 3. Register Automatic Payment
                PayrollPayment::create([
                    'payroll_id' => $newPayroll->id,
                    'paid_by' => $templatePayroll->user_id,
                    'user_id' => $templatePayroll->user_id,
                    'amount' => $newPayroll->amount,
                    'payment_method' => 'Bank Transfer', // Default automatic method
                    'payment_date' => $today,
                ]);

                $newPayroll->update(['payment_status' => 'paid']);

                // 4. Update the original template's next_date
                $templatePayroll->next_date = Carbon::parse($templatePayroll->next_date)->addMonth();
                $templatePayroll->save();
            });
            
            $this->info("Processed and regenerated payroll ID: {$templatePayroll->id}");
        }

        $this->info('Recurring payrolls processed successfully.');
    }
}