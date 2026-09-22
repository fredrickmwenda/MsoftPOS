<?php

namespace App\Http\Controllers;

use App\Models\Biller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\GeneralSetting;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Product_Sale;
use App\Models\Sale;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
        }
        return view('backend.cart.index', compact('cart', 'subtotal'));
    }

    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $cart = session()->get('cart', []);
        $quantity = max(1, (int) $request->input('quantity', 1));

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
        } else {
            $cart[$id] = [
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => (float) $product->price,
                'image' => $product->image_url ?? $product->image ?? asset('images/placeholder.png'),
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);
        if (isset($cart[$request->id])) {
            $cart[$request->id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Cart updated successfully!');
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Item removed from cart!');
    }

    /**
     * Handle the "Continue to secure payment" form submission.
     *
     * PAYSTACK branch:
     *   1. Resolve or create the customer record
     *   2. Create a PENDING Sale + Product_Sale rows (no stock decrement yet)
     *   3. Initialize Paystack transaction and redirect to authorization URL
     *   4. On callback success → finalize the sale (mark paid + decrement stock)
     *   5. Redirect to order confirmation page showing the reference_no
     *
     * PAY-ON-DELIVERY branch:
     *   1. Guest guard — must be logged in as a customer (no anonymous COD)
     *   2. Resolve or create the customer record
     *   3. Create a PENDING Sale + Product_Sale rows (no stock decrement)
     *   4. Redirect to order confirmation page showing the reference_no
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'delivery_address' => 'required|string',
            'payment_method' => 'required|in:paystack,delivery',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        // ─── Step 1: resolve or create the customer ───────────────────────
        $customer = $this->getCustomerFromRequest($request);
        $subtotal = $this->getCartSubtotal($cart);

        $checkoutData = [
            'customer_id' => $customer->id,
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'delivery_address' => $request->delivery_address,
            'order_notes' => $request->order_notes ?? null,
            'payment_method' => $request->payment_method,
            'cart' => $cart,
            'subtotal' => $subtotal,
        ];

        // ─── Step 2a: PAY ON DELIVERY ─────────────────────────────────────
        if ($request->payment_method === 'delivery') {
            // Guest guard — pay-on-delivery requires a verified customer
            // account so the fulfilment team has a real, contactable buyer.
            if (! auth('customer')->check()) {
                session()->flash('cod_pending_checkout', $checkoutData);

                return redirect()
                    ->route('customer.login')
                    ->withInput()
                    ->with('error',
                        'Please log in or create an account to place a pay-on-delivery order. '
                        . 'This helps our team verify your contact details before arranging delivery.'
                    );
            }

            $sale = DB::transaction(
                fn () => $this->createSaleRecord($checkoutData, paid: false, paymentMethod: 'Pay on Delivery')
            );

            session()->forget('cart');
            session()->forget('pending_checkout');
            session()->forget('cod_pending_checkout');

            // Redirect to the order confirmation page so the customer can
            // see their reference number and track the order later.
            return redirect()
                ->route('customer.orders.confirmation', $sale)
                ->with('success',
                    'Your order has been placed successfully. '
                    . 'Your reference number is ' . $sale->reference_no . '. '
                    . 'Please save it — you can use it to track your order status. '
                    . 'Our team will contact you shortly at ' . $request->phone_number
                    . ' to arrange delivery and collect payment.'
                );
        }

        // ─── Step 2b: PAYSTACK — create pending sale first ────────────────
        $pendingSale = DB::transaction(
            fn () => $this->createSaleRecord($checkoutData, paid: false, paymentMethod: 'Paystack')
        );

        // ─── Step 3: Initialize Paystack transaction ──────────────────────
        $initResult = $this->startPaystackCheckout($checkoutData, $pendingSale);

        if (! $initResult['success']) {
            // Roll back the pending sale so we don't leave dangling rows
            $this->deletePendingSale($pendingSale);

            return redirect()
                ->back()
                ->with('error', $initResult['message'])
                ->withInput();
        }

        // Stash context for the callback
        $checkoutData['sale_id'] = $pendingSale->id;
        $checkoutData['paystack_reference'] = $initResult['reference'];
        session()->put('pending_checkout', $checkoutData);

        return redirect()->away($initResult['authorization_url']);
    }

    /**
     * Paystack redirects the user here after the authorization step.
     * Verify the transaction; on success, finalize the pending sale.
     *
     * Flow:
     *   1. Pull reference + checkout context from the request/session
     *   2. Verify with Paystack (server-to-server call)
     *   3. Finalize the sale (mark paid, decrement stock, create Payment)
     *   4. Redirect to the order confirmation page showing the reference_no
     */
    public function paystackCallback(Request $request)
    {
        // ─── 1. Pull reference + context ─────────────────────────────────
        $reference = $request->input('reference') ?: $request->input('trxref');
        $checkoutData = session()->get('pending_checkout');

        if (empty($reference) || empty($checkoutData)) {
            Log::warning('Paystack callback missing reference or checkout context', [
                'reference' => $reference,
                'has_session' => ! empty($checkoutData),
            ]);

            return redirect()
                ->route('shop.index')
                ->with('error', 'Paystack payment was cancelled or could not be completed. Please try again.');
        }

        // ─── 2. Verify with Paystack ────────────────────────────────────
        $verify = $this->verifyPaystackTransaction($reference);

        if (! $verify['success']) {
            Log::warning('Paystack callback verification failed', [
                'reference' => $reference,
                'reason' => $verify['message'] ?? 'unknown',
            ]);

            session()->forget('pending_checkout');

            return redirect()
                ->route('shop.index')
                ->with('error',
                    'Payment verification failed. If you were charged, please contact support with reference '
                    . $reference
                );
        }

        // ─── 3. Finalize the sale (idempotent — safe if Paystack also fires webhook) ─
        try {
            DB::transaction(function () use ($checkoutData, $reference, $verify) {
                $this->finalizePendingSale(
                    $checkoutData['sale_id'],
                    $reference,
                    $verify['amount'] ?? $checkoutData['subtotal']
                );
            });
        } catch (\Throwable $e) {
            Log::error('Paystack callback finalization failed', [
                'sale_id' => $checkoutData['sale_id'] ?? null,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('shop.index')
                ->with('error',
                    'We received your payment but encountered an error finalizing your order. '
                    . 'Please contact support with reference ' . $reference
                );
        }

        // ─── 4. Clear cart + session ────────────────────────────────────
        session()->forget('cart');
        session()->forget('pending_checkout');

        // ─── 5. Redirect — confirmation page showing reference_no ───────
        $sale = Sale::find($checkoutData['sale_id']);

        if (auth('customer')->check() && $sale) {
            return redirect()
                ->route('customer.orders.confirmation', $sale)
                ->with('success',
                    'Payment successful! Your order has been confirmed. '
                    . 'Your reference number is ' . $sale->reference_no . '. '
                    . 'Save it to track your order. Our team will contact you shortly to arrange delivery.'
                );
        }

        // Guest customer who paid via Paystack — they can track using the
        // reference number on the public track-order page.
        return redirect()
            ->route('track.order', ['reference_no' => $sale?->reference_no])
            ->with('success',
                'Payment successful! Your order ' . ($sale?->reference_no ?? '') . ' has been confirmed. '
                . 'Our team will contact you shortly at '
                . ($checkoutData['phone_number'] ?? 'your phone number')
                . ' to arrange delivery.'
            );
    }

    // ════════════════════════════════════════════════════════════════
    //  ORDER TRACKING — public page, searches by reference_no
    // ════════════════════════════════════════════════════════════════

    /**
     * Show the order tracking page. If a reference_no is provided (via
     * query string or POST), look up the sale and display its status.
     *
     * This page is PUBLIC — no login required. Anyone who has a
     * reference number (including guests who paid via Paystack) can
     * track their order here.
     */
    public function trackOrder(Request $request)
    {
        $reference = trim($request->input('reference_no', ''));
        $sale = null;
        $error = null;

        if ($reference) {
            $sale = Sale::with(['productSales.product', 'delivery', 'customer', 'payments'])
                ->where('reference_no', $reference)
                ->first();

            if (! $sale) {
                $error = 'No order found with reference number "' . $reference . '". Please check and try again.';
            }
        }

        return view('frontend.track-order', compact('sale', 'reference', 'error'));
    }

    // ════════════════════════════════════════════════════════════════
    //  Customer resolution — find by email or create, refresh contact info
    // ════════════════════════════════════════════════════════════════

    protected function getCustomerFromRequest(Request $request): Customer
    {
        $customer = Customer::where('email', $request->email)->first();

        if ($customer) {
            $customer->update([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address' => $request->delivery_address,
            ]);
            return $customer;
        }

        return Customer::create([
            'customer_group_id' => 1,
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'address' => $request->delivery_address,
            'is_active' => true,
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    //  Cart math
    // ════════════════════════════════════════════════════════════════

    protected function getCartSubtotal(array $cart): float
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        }
        return round($subtotal, 2);
    }

    // ════════════════════════════════════════════════════════════════
    //  Sale + Product_Sale + Delivery record creation
    // ════════════════════════════════════════════════════════════════

    protected function createSaleRecord(array $checkoutData, bool $paid, string $paymentMethod = 'Paystack'): Sale
    {
        $cart = $checkoutData['cart'] ?? [];
        $userId = auth()->id() ?? 1;
        $warehouse = Warehouse::where('is_active', true)->first();
        $biller = Biller::where('is_active', true)->first();
        $generalSetting = GeneralSetting::latest()->first();
        $subtotal = $this->getCartSubtotal($cart);

        $referenceNo = 'sr-' . date('Ymd') . '-'
            . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $sale = Sale::create([
            'reference_no' => $referenceNo,
            'user_id' => $userId,
            'customer_id' => $checkoutData['customer_id'],
            'warehouse_id' => $warehouse?->id ?? 1,
            'biller_id' => $biller?->id ?? 1,
            'item' => count($cart),
            'total_qty' => array_sum(array_map(fn ($i) => (int) ($i['quantity'] ?? 0), $cart)),
            'total_discount' => 0,
            'total_tax' => 0,
            'total_price' => $subtotal,
            'order_tax_rate' => 0,
            'order_tax' => 0,
            'order_discount' => 0,
            'shipping_cost' => 0,
            'grand_total' => $subtotal,
            'currency_id' => $generalSetting?->currency ?? 1,
            'exchange_rate' => 1,
            'sale_status' => $paid ? 1 : 2,
            'payment_status' => $paid ? 4 : 1,
            'paid_amount' => $paid ? $subtotal : 0,
            'sale_note' => $checkoutData['order_notes'] ?? null,
            'staff_note' => 'Customer cart checkout via ' . $paymentMethod,
            'sale_source'     => 'ecommerce',   // 👈 NEW — cart checkouts are always ecommerce
            'created_at' => now(),
        ]);

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }

            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            Product_Sale::create([
                'sale_id' => $sale->id,
                'product_id' => $productId,
                'qty' => $qty,
                'sale_unit_id' => 0,
                'net_unit_price' => $price,
                'discount' => 0,
                'tax_rate' => 0,
                'tax' => 0,
                'total' => $price * $qty,
            ]);
        }

        try {
            Delivery::create([
                'sale_id' => $sale->id,
                'customer_id' => $checkoutData['customer_id'],
                'address' => $checkoutData['delivery_address'],
                'status' => 1,
                'delivered_at' => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Delivery record creation failed', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($paid) {
            Payment::create([
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'payment_reference' => $referenceNo,
                'amount' => $subtotal,
                'paying_method' => $paymentMethod,
                'payment_note' => 'Cart checkout via ' . $paymentMethod,
            ]);

            foreach ($cart as $productId => $item) {
                $product = Product::find($productId);
                if ($product) {
                    $product->decrement('qty', (int) ($item['quantity'] ?? 0));
                }
            }
        }

        return $sale;
    }

    protected function finalizePendingSale(int $saleId, string $paystackReference, float $amountPaid): void
    {
        $sale = Sale::with('product_sales')->findOrFail($saleId);

        if ((int) $sale->payment_status === 4) {
            Log::info('finalizePendingSale skipped — sale already paid', [
                'sale_id' => $sale->id,
                'reference' => $paystackReference,
            ]);
            return;
        }

        $sale->update([
            'sale_status' => 1,
            'payment_status' => 4,
            'paid_amount' => $sale->grand_total,
        ]);

        foreach ($sale->product_sales as $productSale) {
            $product = Product::find($productSale->product_id);
            if (! $product) {
                Log::warning('finalizePendingSale: product missing', [
                    'product_id' => $productSale->product_id,
                    'sale_id' => $sale->id,
                ]);
                continue;
            }

            if ($product->qty < $productSale->qty) {
                Log::warning('finalizePendingSale: stock below order qty — clamping to 0', [
                    'product_id' => $product->id,
                    'current_qty' => $product->qty,
                    'order_qty' => $productSale->qty,
                    'sale_id' => $sale->id,
                ]);
                $product->qty = 0;
                $product->save();
            } else {
                $product->decrement('qty', $productSale->qty);
            }
        }

        Payment::create([
            'sale_id' => $sale->id,
            'user_id' => auth()->id() ?? 1,
            'payment_reference' => $paystackReference,
            'amount' => $amountPaid,
            'paying_method' => 'Paystack',
            'payment_note' => 'Paystack callback confirmation',
        ]);

        Delivery::where('sale_id', $sale->id)->update(['status' => 1]);

        Log::info('Sale finalized via Paystack callback', [
            'sale_id' => $sale->id,
            'reference_no' => $sale->reference_no,
            'paystack_reference' => $paystackReference,
            'amount' => $amountPaid,
        ]);
    }

    protected function deletePendingSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            Product_Sale::where('sale_id', $sale->id)->delete();
            Delivery::where('sale_id', $sale->id)->delete();
            $sale->delete();
        });

        Log::info('Pending sale rolled back after Paystack init failure', [
            'sale_id' => $sale->id,
            'reference_no' => $sale->reference_no,
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    //  Paystack integration
    // ════════════════════════════════════════════════════════════════

    protected function startPaystackCheckout(array $checkoutData, Sale $sale): array
    {
        $secretKey = config('services.paystack.secret') ?: env('PAYSTACK_SECRET_KEY');
        $publicKey = config('services.paystack.public') ?: env('PAYSTACK_PUBLIC_KEY');

        if (empty($secretKey) || empty($publicKey)) {
            return [
                'success' => false,
                'message' => 'Paystack is not configured for this platform.',
            ];
        }

        $amountInKobo = (int) round(($checkoutData['subtotal'] ?? 0) * 100);
        if ($amountInKobo <= 0) {
            return [
                'success' => false,
                'message' => 'Cart subtotal is invalid.',
            ];
        }

        $reference = 'cart-' . time() . '-' . random_int(1000, 9999);

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $checkoutData['email'],
                'amount' => $amountInKobo,
                'currency' => 'GHS',
                'reference' => $reference,
                'callback_url' => route('checkout.paystack.callback'),
                'metadata' => [
                    'sale_id' => $sale->id,
                    'customer_id' => $checkoutData['customer_id'],
                    'customer_name' => $checkoutData['name'],
                    'delivery_address' => $checkoutData['delivery_address'],
                    'order_notes' => $checkoutData['order_notes'] ?? '',
                    'payment_method' => 'paystack',
                ],
            ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['data']['authorization_url'] ?? null)) {
            Log::warning('Paystack initialize failed', [
                'sale_id' => $sale->id,
                'reference' => $reference,
                'response' => $body,
            ]);

            return [
                'success' => false,
                'message' => $body['message'] ?? 'Unable to start Paystack payment at the moment. Please try again.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Authorization URL generated.',
            'reference' => $reference,
            'authorization_url' => $body['data']['authorization_url'],
        ];
    }

    protected function verifyPaystackTransaction(string $reference): array
    {
        $secretKey = config('services.paystack.secret') ?: env('PAYSTACK_SECRET_KEY');

        if (empty($secretKey)) {
            return [
                'success' => false,
                'message' => 'Paystack secret key is not configured.',
            ];
        }

        try {
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->get('https://api.paystack.co/transaction/verify/' . $reference);

            $payload = $response->json() ?? [];

            if (! $response->successful()
                || (($payload['status'] ?? false) !== true)
                || (($payload['data']['status'] ?? null) !== 'success')
            ) {
                return [
                    'success' => false,
                    'message' => $payload['message'] ?? 'Paystack verification reported a non-success status.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Verified.',
                'amount' => ($payload['data']['amount'] ?? 0) / 100,
            ];
        } catch (\Throwable $e) {
            Log::error('Paystack verify exception', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Could not reach Paystack to verify the transaction.',
            ];
        }
    }
}