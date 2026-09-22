<?php

namespace App\Http\Controllers;

use App\Models\PayrollItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayrollItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payrollItems = PayrollItem::with(['payroll', 'templateItem'])
            ->latest()
            ->paginate(15);

        return response()->json($payrollItems);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Typically used for returning a view in web applications.
        // Omitted for API usage.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payroll_id' => ['required', 'exists:payrolls,id'],
            'payroll_template_item_id' => ['nullable', 'exists:payroll_template_items,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'amount_type' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'taxable' => ['boolean'],
            'description' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ]);

        $payrollItem = PayrollItem::create($validated);

        return response()->json([
            'message' => 'Payroll item created successfully.',
            'data' => $payrollItem
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PayrollItem $payrollItem)
    {
        // Eager load relationships
        $payrollItem->load(['payroll', 'templateItem']);

        return response()->json($payrollItem);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayrollItem $payrollItem)
    {
        // Typically used for returning a view in web applications.
        // Omitted for API usage.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayrollItem $payrollItem)
    {
        $validated = $request->validate([
            'payroll_id' => ['sometimes', 'required', 'exists:payrolls,id'],
            'payroll_template_item_id' => ['sometimes', 'nullable', 'exists:payroll_template_items,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'max:255'],
            'amount_type' => ['sometimes', 'required', 'string', 'max:255'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'taxable' => ['boolean'],
            'description' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ]);

        $payrollItem->update($validated);

        return response()->json([
            'message' => 'Payroll item updated successfully.',
            'data' => $payrollItem
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollItem $payrollItem)
    {
        $payrollItem->delete();

        return response()->json([
            'message' => 'Payroll item deleted successfully.'
        ], 200);
    }
}