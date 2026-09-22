<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollPayment;
use Illuminate\Http\Request;
use Illuminate\Facades\Auth;

class PayrollPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payroll_id' => 'required|exists:payrolls,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['paid_by'] = Auth::id();

        // Create the payment record
        $payment = PayrollPayment::create($data);

        // Optional: Update Payroll status if fully paid
        $payroll = Payroll::find($request->payroll_id);
        $totalPaid = $payroll->payments()->sum('amount');
        
        if ($totalPaid >= $payroll->amount) {
            $payroll->update(['payment_status' => 'paid']);
        } else {
            $payroll->update(['payment_status' => 'partial']);
        }

        return redirect()->back()->with('message', 'Payment registered successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PayrollPayment $payrollPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayrollPayment $payrollPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayrollPayment $payrollPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollPayment $payrollPayment)
    {
        //
    }
}
