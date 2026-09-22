<?php

namespace App\Http\Controllers;

use App\Models\PayrollTemplate;
use App\Models\PayrollTemplateItem;
use Illuminate\Http\Request;

class PayrollTemplateController extends Controller
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
        
        // Create the Template
        $template = PayrollTemplate::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        // Create the Items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                PayrollTemplateItem::create([
                    'payroll_template_id' => $template->id,
                    'name' => $item['name'],
                    'type' => $item['type'] ?? 'allowance',
                    'amount_type' => $item['amount_type'] ?? 'fixed',
                    'amount' => $item['amount'] ?? 0,
                    'taxable' => isset($item['taxable']) ? 1 : 0,
                    'description' => $item['description'] ?? null,
                ]);
            }
        }

        // SYNC MULTI-TAXES <-- ADD THIS BLOCK
        if (isset($data['tax_ids']) && is_array($data['tax_ids'])) {
            $template->taxes()->sync($data['tax_ids']);
        }

        // Load the taxes relationship to return to the frontend
        $template->load('taxes');

        return response()->json([
            'success' => true,
            'id' => $template->id,
            'name' => $template->name
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PayrollTemplate $payrollTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayrollTemplate $payrollTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayrollTemplate $payrollTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollTemplate $payrollTemplate)
    {
        //
    }
}
