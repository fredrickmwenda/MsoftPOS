<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payroll;
use App\Models\PayrollPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollPaymentController extends Controller
{
    /* MAKE PAYMENT — list of payrolls with outstanding dues */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('payroll')) {
            return redirect()->back()->with('not_permitted', 'Access denied');
        }

        $general_setting = DB::table('general_settings')->latest()->first();

        $payrolls = Payroll::with(['employee', 'salaryTemplate', 'payments'])
            ->when($request->filled('month'), function ($q) use ($request) {
                [$y, $m] = explode('-', $request->month);
                $q->where('year', $y)->where('month', $m);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.hrm.payroll.payment', [
            'lims_payroll_all'  => $payrolls,
            'general_setting'   => $general_setting,
            'lims_account_list' => Account::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payroll_id'    => 'required|exists:payrolls,id',
            'amount'        => 'required|numeric|min:0.01',
            'paying_method' => 'required|in:0,1,2',
            'account_id'    => 'nullable|exists:accounts,id',
            'payment_date'  => 'nullable|date',
            'note'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $payroll = Payroll::lockForUpdate()->findOrFail($validated['payroll_id']);

            $due = (float) $payroll->amount - (float) $payroll->paid_amount;
            if ((float) $validated['amount'] > $due + 0.001) {
                abort(422, 'Payment amount exceeds the outstanding due.');
            }

            PayrollPayment::create([
                'payroll_id'    => $payroll->id,
                'user_id'       => Auth::id(),
                'account_id'    => $validated['account_id'] ?? null,
                'amount'        => $validated['amount'],
                'paying_method' => $validated['paying_method'],
                'payment_date'  => $validated['payment_date'] ?? now()->toDateString(),
                'note'          => $validated['note'] ?? null,
            ]);

            $payroll->refreshPaymentStatus();
        });

        return redirect()->back()->with('message', 'Payment recorded successfully');
    }

    public function destroy($id)
    {
        $payment = PayrollPayment::findOrFail($id);
        $payroll = $payment->payroll;

        DB::transaction(function () use ($payment, $payroll) {
            $payment->delete();
            $payroll->refreshPaymentStatus();
        });

        return redirect()->back()->with('message', 'Payment deleted successfully');
    }
}
