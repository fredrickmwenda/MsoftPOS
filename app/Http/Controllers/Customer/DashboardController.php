<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user();

        // Recent orders shown in the "Your orders" card
        $orders = Sale::where('customer_id', $customer->id)
            ->with(['delivery', 'productSales'])
            ->latest()
            ->take(5)
            ->get();

        // Recent payments shown in the "Your transactions" card
        $transactions = Payment::whereHas('sale', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->with('sale')
            ->latest()
            ->take(5)
            ->get();

        // Stats
        $totalOrders = Sale::where('customer_id', $customer->id)->count();

        // Active = not paid in full (4) and not cancelled (5)
        $activeOrders = Sale::where('customer_id', $customer->id)
            ->whereNotIn('payment_status', [4, 5])
            ->count();

        $successfulPayments = Payment::whereHas('sale', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->where('status', 1) // 1 = success
            ->count();

        $totalPayments = Payment::whereHas('sale', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->count();

        return view('frontend.customer.dashboard', compact(
            'customer',
            'orders',
            'transactions',
            'totalOrders',
            'activeOrders',
            'successfulPayments',
            'totalPayments'
        ));
    }

    /**
     * List all orders belonging to the logged-in customer, newest first.
     */
    public function orders(Request $request)
    {
        $customer = $request->user('customer');

        $sales = Sale::where('customer_id', $customer->id)
            ->with(['productSales.product', 'delivery', 'payments'])
            ->latest()
            ->paginate(10);

        return view('frontend.customer.orders', compact('sales'));
    }

    /**
     * Show a single order's full details.
     */
    public function showOrder(Request $request, Sale $sale)
    {
        if ($sale->customer_id !== $request->user('customer')->id) {
            abort(403, 'You do not have access to this order.');
        }

        $sale->load(['productSales.product', 'delivery', 'payments']);

        return view('frontend.customer.order-details', compact('sale'));
    }

    /**
     * Post-order confirmation page — shown right after a successful checkout.
     */
    public function orderConfirmation(Request $request, Sale $sale)
    {
        if ($sale->customer_id !== $request->user('customer')->id) {
            abort(403, 'You do not have access to this order.');
        }

        $sale->load(['productSales.product', 'delivery']);

        return view('frontend.customer.order-confirmation', compact('sale'));
    }
}