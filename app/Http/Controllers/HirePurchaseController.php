<?php

namespace App\Http\Controllers;

use App\Models\HirePurchaseInstallment;
use App\Models\Sale;
use App\Models\Customer;
use App\Services\HirePurchaseService;
use Illuminate\Http\Request;
//pagination set 
use  Illuminate\Database\Eloquent\Collection;
use Auth;
use DB;

class HirePurchaseController extends Controller
{
    protected $hirePurchaseService;

    public function __construct(HirePurchaseService $hirePurchaseService)
    {
        $this->hirePurchaseService = $hirePurchaseService;
    }

    /**
     * Display hire purchase list
     */
    public function index(Request $request)
    {
        // if (!Auth::user()->can('read', new Sale())) {
        //     return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        // }

        $status = $request->get('status', 'all');
        $customer_id = $request->get('customer_id');

        $query = Sale::where('is_hire_purchase', true);

        if ($status !== 'all') {
            $query->where('hire_purchase_status', $status);
        }

        if ($customer_id) {
            $query->where('customer_id', $customer_id);
        }

        $sales = $query->with(['customer', 'hire_purchase_installments'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);
   
        $customers = Customer::all();

        return view('backend.hire_purchase.index', compact('sales', 'customers', 'status', 'customer_id'));
    }

    /**
     * Get installments for a sale (AJAX)
     */
    public function getInstallments(Request $request)
    {
        try {
            $saleId = $request->get('sale_id');
            
            if (!$saleId) {
                return response()->json(['error' => 'Sale ID is required'], 400);
            }

            $sale = Sale::with(['customer', 'hire_purchase_installments'])->find($saleId);

            if (!$sale) {
                return response()->json(['error' => 'Sale not found'], 404);
            }

            if (!$sale->is_hire_purchase) {
                return response()->json(['error' => 'This is not a hire purchase sale'], 400);
            }

            $pendingInstallments = $sale->hire_purchase_installments()
                ->where('payment_status', '!=', 'paid')
                ->orderBy('installment_number', 'asc')
                ->get();

            if ($pendingInstallments->isEmpty()) {
                return response()->json([
                    'error' => 'No pending installments. All installments have been paid.',
                    'status' => 'all_paid'
                ], 200);
            }

            return response()->json([
                'has_installments' => true,
                'installments' => $pendingInstallments,
                'customer_name' => $sale->customer->name ?? 'N/A',
                'total_terms' => $sale->hire_purchase_terms,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching installments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process down payment and create installments (AJAX)
     * Called after down payment is confirmed/recorded
     */
    public function processDownPayment(Request $request)
    {
        try {
            $saleId = $request->get('sale_id');
            
            if (!$saleId) {
                return response()->json(['error' => 'Sale ID is required'], 400);
            }

            $sale = Sale::findOrFail($saleId);

            if (!$sale->is_hire_purchase) {
                return response()->json(['error' => 'This is not a hire purchase sale'], 400);
            }

            // Only create installments if status is still pending
            if ($sale->hire_purchase_status !== 'pending') {
                return response()->json([
                    'error' => 'Installments have already been created for this sale',
                    'installments_count' => $sale->hire_purchase_installments()->count()
                ], 409);
            }

            // Create the installment records
            $this->hirePurchaseService->createInstallments($sale, $sale->hire_purchase_start_date);

            // Update hire purchase status to active
            $sale->hire_purchase_status = 'active';
            $sale->save();

            // Invalidate cache
            cache()->forget('hire_purchase_' . $sale->id);

            return response()->json([
                'success' => true,
                'message' => 'Down payment confirmed. Installments created successfully.',
                'installments_count' => $sale->hire_purchase_installments()->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error processing down payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display single hire purchase details with installments
     */
    public function show($id)
    {
        $sale = Sale::with(['customer', 'hire_purchase_installments', 'product_sales.product'])
            ->findOrFail($id);

        if (!$sale->is_hire_purchase) {
            return redirect()->back()->with('error', 'This is not a hire purchase sale');
        }

        // if (!Auth::user()->can('read', $sale)) {
        //     return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        // }

        $installments = $sale->hire_purchase_installments()->orderBy('installment_number', 'asc')->get();
        $summary = $this->hirePurchaseService->calculateDetails(
            $sale->grand_total,
            $sale->hire_purchase_down_payment,
            $sale->hire_purchase_terms
        );
        //dd($summary);

        return view('backend.hire_purchase.show', compact('sale', 'installments', 'summary'));
    }

    /**
     * Show installment payment form
     */
    public function paymentForm($installmentId)
    {
        $installment = HirePurchaseInstallment::findOrFail($installmentId);
        $sale = $installment->sale;

        // if (!Auth::user()->can('update', $sale)) {
        //     return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        // }

        $paymentMethods = [
            'cash' => 'Cash',
            'card' => 'Credit Card',
            'check' => 'Cheque',
            'transfer' => 'Bank Transfer',
            'mobile' => 'Mobile Money',
        ];

        return view('backend.hire_purchase.payment_form', compact('installment', 'sale', 'paymentMethods'));
    }

    /**
     * Record installment payment
     */
    public function recordPayment(Request $request, $installmentId)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,check,transfer,mobile',
            'payment_date' => 'required|date',
        ]);

        $installment = HirePurchaseInstallment::findOrFail($installmentId);
        $sale = $installment->sale;

        try {
            $this->hirePurchaseService->recordPayment(
                $installment,
                $request->amount,
                $request->payment_method,
                $request->payment_note ?? null
            );

            // Check if request is AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully'
                ]);
            }

            return redirect()->back()->with('message', 'Payment recorded successfully');
        } catch(\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error recording payment: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withErrors('Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Create a new installment payment (AJAX)
     * Called when no installments exist yet
     */
    public function createInstallmentPayment(Request $request)
    {
        $this->validate($request, [
            'sale_id' => 'required|exists:sales,id',
            'installment_number' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,check,transfer,mobile',
            'payment_note' => 'nullable|string'
        ]);

        try {
            $sale = Sale::findOrFail($request->sale_id);

            if (!$sale->is_hire_purchase) {
                return response()->json(['error' => 'This is not a hire purchase sale'], 400);
            }

            // Create the installment
            $installment = HirePurchaseInstallment::create([
                'sale_id' => $sale->id,
                'installment_number' => $request->installment_number,
                'due_date' => $request->due_date,
                'amount' => $request->amount,
                'paid_amount' => $request->payment_amount,
                'payment_status' => $request->payment_amount >= $request->amount ? 'paid' : 'partial',
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'notes' => $request->payment_note
            ]);

            // Create payment record if needed
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Installment created successfully',
                    'installment' => $installment
                ]);
            }

            return redirect()->back()->with('message', 'Installment created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Error creating installment: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors('Error creating installment: ' . $e->getMessage());
        }
    }

    /**
     * Display pending installments
     */
    public function pending(Request $request)
    {
        // if (!Auth::user()->can('read', new Sale())) {
        //     return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        // }

        $customer_id = $request->get('customer_id');

        $query = HirePurchaseInstallment::where('payment_status', '!=', 'paid');

        if ($customer_id) {
            $query->whereHas('sale', function ($q) use ($customer_id) {
                $q->where('customer_id', $customer_id);
            });
        }

        $installments = $query->with(['sale.customer'])
            ->orderBy('due_date', 'asc')
            ->paginate(25);

        $customers = Customer::all();

        return view('backend.hire_purchase.pending', compact('installments', 'customers', 'customer_id'));
    }

    /**
     * Display overdue installments
     */
    public function overdue()
    {
        // if (!Auth::user()->can('read', new Sale())) {
        //     return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        // }

        $installments = $this->hirePurchaseService->getOverdueInstallments();

        return view('backend.hire_purchase.overdue', compact('installments'));
    }

    /**
     * Display dashboard summary
     */
    public function dashboard()
    {
        // if (!Auth::user()->can('read', new Sale())) {
        //     return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        // }

        $summary = $this->hirePurchaseService->getDashboardSummary();

        // Get recent transactions
        $recentPayments = HirePurchaseInstallment::where('payment_status', 'paid')
            ->with(['sale.customer'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get upcoming due installments (next 7 days)
        $upcomingDue = HirePurchaseInstallment::where('payment_status', '!=', 'paid')
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->with(['sale.customer'])
            ->orderBy('due_date', 'asc')
            ->get();

        return view('backend.hire_purchase.dashboard', compact('summary', 'recentPayments', 'upcomingDue'));
    }

    /**
     * Generate report
     */
    public function report(Request $request)
    {
        // if (!Auth::user()->can('read', new Sale())) {
        //     return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        // }

        $startDate = $request->get('start_date') ? date('Y-m-d', strtotime($request->get('start_date'))) : now()->startOfMonth()->toDateString();
        $endDate = $request->get('end_date') ? date('Y-m-d', strtotime($request->get('end_date'))) : now()->toDateString();

        $sales = Sale::where('is_hire_purchase', true)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['customer', 'hire_purchase_installments'])
            ->get();

        $reportData = [
            'total_contracts' => $sales->count(),
            'total_amount' => $sales->sum('grand_total'),
            'total_down_payment' => $sales->sum('hire_purchase_down_payment'),
            'total_paid' => HirePurchaseInstallment::whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->where('payment_status', 'paid')
                ->sum('paid_amount'),
            'active_contracts' => $sales->where('hire_purchase_status', 'active')->count(),
            'completed_contracts' => $sales->where('hire_purchase_status', 'completed')->count(),
        ];

        return view('backend.hire_purchase.report', compact('reportData', 'sales', 'startDate', 'endDate'));
    }

    /**
     * Export report to PDF or CSV
     */
    public function exportReport(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $startDate = $request->get('start_date') ? date('Y-m-d', strtotime($request->get('start_date'))) : now()->startOfMonth()->toDateString();
        $endDate = $request->get('end_date') ? date('Y-m-d', strtotime($request->get('end_date'))) : now()->toDateString();

        $sales = Sale::where('is_hire_purchase', true)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['customer', 'hire_purchase_installments'])
            ->get();

        if ($format === 'csv') {
            return $this->exportCSV($sales, $startDate, $endDate);
        } else {
            return $this->exportPDF($sales, $startDate, $endDate);
        }
    }

    private function exportCSV($sales, $startDate, $endDate)
    {
        $filename = 'hire_purchase_report_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Reference No', 'Customer', 'Total Amount', 'Down Payment', 'Terms', 'Interest Rate', 'Status']);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->reference_no,
                    $sale->customer->name ?? '',
                    $sale->grand_total,
                    $sale->hire_purchase_down_payment,
                    $sale->hire_purchase_terms,
                    $sale->hire_purchase_interest_rate . '%',
                    $sale->hire_purchase_status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPDF($sales, $startDate, $endDate)
    {
        // Implementation for PDF export using a library like dompdf or mPDF
        // This is a placeholder - implement based on your PDF library preference
        return response()->json(['error' => 'PDF export not yet implemented'], 501);
    }
}
