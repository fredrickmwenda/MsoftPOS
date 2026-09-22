<?php

namespace App\Http\Controllers;

use App\Models\PayrollTemplateItem;
use Illuminate\Http\Request;

class PayrollTemplateItemController extends Controller
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
        $data = $request->all();
        $item = \App\Models\PayrollTemplateItem::create([
            'payroll_template_id' => $data['payroll_template_id'] ?? null,
            'name' => $data['name'] ?? null,
            'type' => $data['type'] ?? null,
            'amount_type' => $data['amount_type'] ?? null,
            'amount' => $data['amount'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return response()->json($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(PayrollTemplateItem $payrollTemplateItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayrollTemplateItem $payrollTemplateItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayrollTemplateItem $payrollTemplateItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollTemplateItem $payrollTemplateItem)
    {
        //
    }
}
