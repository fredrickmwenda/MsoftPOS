<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order {{ $sale->reference_no }} - arkandstar.com</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; color: #333; }
        nav { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-size: 20px; font-weight: bold; color: #D4AF37; }
        .nav-links a { text-decoration: none; color: #4b5563; font-size: 14px; margin-left: 20px; font-weight: 500; }
        .nav-links a:hover { color: #16a34a; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .back-link { margin-bottom: 20px; }
        .back-link a { color: #4b5563; text-decoration: none; font-size: 14px; font-weight: 500; }
        .back-link a:hover { color: #16a34a; }
        .header-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 25px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; }
        .header-card h1 { font-size: 22px; color: #1f2937; margin-bottom: 6px; }
        .header-card .ref { color: #D4AF37; font-weight: 700; }
        .header-card .meta { color: #6b7280; font-size: 14px; line-height: 1.7; }
        .header-card .meta span { font-weight: 600; color: #333; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .badge-pending    { background: #fef3c7; color: #92400e; }
        .badge-processing { background: #dbeafe; color: #1e40af; }
        .badge-paid       { background: #d1fae5; color: #065f46; }
        .badge-failed     { background: #fee2e2; color: #991b1b; }
        .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 25px; margin-bottom: 25px; }
        .card h2 { font-size: 16px; color: #1f2937; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; }
        thead th { text-align: left; font-size: 12px; color: #6b7280; text-transform: uppercase; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb; }
        tbody td { padding: 12px 0; font-size: 14px; color: #4b5563; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        tbody tr:last-child td { border-bottom: none; }
        .product-cell { display: flex; gap: 12px; align-items: center; }
        .product-cell img { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb; }
        .product-name { font-weight: 600; color: #1f2937; }
        .product-variant { font-size: 12px; color: #9ca3af; }
        .totals { margin-top: 20px; }
        .totals .row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #4b5563; }
        .totals .row.grand { font-weight: 700; font-size: 18px; color: #1f2937; border-top: 2px solid #1f2937; margin-top: 10px; padding-top: 12px; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
        .info-row:last-child { border-bottom: none; }
        .info-row .label { color: #6b7280; }
        .info-row .value { color: #1f2937; font-weight: 600; text-align: right; }
        .btn { display: inline-block; padding: 10px 18px; background-color: #16a34a; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; }
        .btn:hover { background-color: #15803d; }
        .btn-outline { background: transparent; color: #4b5563; border: 1px solid #d1d5db; }
        .btn-outline:hover { background: #f3f4f6; color: #333; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; }
    </style>
</head>
<body>
    <nav>
        <div class="nav-logo">Ark & Star Enterprise</div>
        <div class="nav-links">
            <a href="{{ route('shop.index') }}">Store</a>
            <a href="{{ route('customer.dashboard') }}">My account</a>
            <a href="{{ route('customer.orders') }}">Orders</a>
            <a href="#" onclick="document.getElementById('logout-form').submit();">Sign out</a>
            <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </nav>

    <div class="container">
        <div class="back-link">
            <a href="{{ route('customer.orders') }}">&larr; Back to orders</a>
        </div>

        @php
            $paymentLabel = [
                1 => ['Pending', 'badge-pending'],
                2 => ['Processing', 'badge-processing'],
                4 => ['Paid', 'badge-paid'],
                5 => ['Failed', 'badge-failed'],
            ][$sale->payment_status] ?? ['Unknown', 'badge-pending'];
        @endphp

        <div class="header-card">
            <div>
                <h1>Order <span class="ref">{{ $sale->reference_no }}</span></h1>
                <div class="meta">
                    Placed on <span>{{ $sale->created_at->format('F d, Y \a\t g:i A') }}</span><br>
                    Items: <span>{{ $sale->productSales->count() }}</span>
                </div>
            </div>
            <div>
                <div style="margin-bottom: 8px;">
                    Payment: <span class="badge {{ $paymentLabel[1] }}">{{ $paymentLabel[0] }}</span>
                </div>
                @if($sale->delivery)
                    <div>
                        Delivery: <span class="badge {{ $sale->delivery->status == 3 ? 'badge-paid' : 'badge-processing' }}">{{ $sale->delivery->status_label ?? 'Processing' }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid">
            <!-- LEFT: Items + totals -->
            <div>
                <div class="card">
                    <h2>Order Items</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="text-align:center;">Qty</th>
                                <th style="text-align:right;">Price</th>
                                <th style="text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->productSales as $ps)
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            @if($ps->product && $ps->product->image)
                                                <img src="{{ asset('storage/' . $ps->product->image) }}" alt="">
                                            @endif
                                            <div>
                                                <div class="product-name">{{ $ps->product->name ?? 'Product #' . $ps->product_id }}</div>
                                                <div class="product-variant">Unit: ₦{{ number_format($ps->price, 2) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">{{ $ps->quantity }}</td>
                                    <td style="text-align:right;">₦{{ number_format($ps->price, 2) }}</td>
                                    <td style="text-align:right; font-weight:600;">₦{{ number_format($ps->price * $ps->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="totals">
                        <div class="row"><span>Subtotal</span><span>₦{{ number_format($sale->subtotal ?? ($sale->total_amount - ($sale->shipping_fee ?? 0)), 2) }}</span></div>
                        @if(isset($sale->shipping_fee))
                            <div class="row"><span>Shipping</span><span>₦{{ number_format($sale->shipping_fee, 2) }}</span></div>
                        @endif
                        @if(isset($sale->discount) && $sale->discount > 0)
                            <div class="row"><span>Discount</span><span>-₦{{ number_format($sale->discount, 2) }}</span></div>
                        @endif
                        <div class="row grand"><span>Total</span><span>₦{{ number_format($sale->total_amount, 2) }}</span></div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Delivery, Payment, Actions -->
            <div>
                <div class="card">
                    <h2>Delivery Information</h2>
                    @if($sale->delivery)
                        <div class="info-row"><span class="label">Recipient</span><span class="value">{{ $sale->delivery->recipient_name ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">Phone</span><span class="value">{{ $sale->delivery->recipient_phone ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">Address</span><span class="value">{{ $sale->delivery->address ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">Tracking No.</span><span class="value">{{ $sale->delivery->tracking_number ?? 'N/A' }}</span></div>
                        <div class="info-row"><span class="label">Status</span><span class="value">{{ $sale->delivery->status_label ?? 'Processing' }}</span></div>
                        @if($sale->delivery->estimated_delivery)
                            <div class="info-row"><span class="label">ETA</span><span class="value">{{ \Carbon\Carbon::parse($sale->delivery->estimated_delivery)->format('M d, Y') }}</span></div>
                        @endif
                    @else
                        <p style="color:#9ca3af; font-size:14px;">No delivery record yet for this order.</p>
                    @endif
                </div>

                <div class="card">
                    <h2>Payment History</h2>
                    @if($sale->payments->isNotEmpty())
                        @foreach($sale->payments as $payment)
                            <div class="info-row">
                                <span class="label">
                                    {{ $payment->created_at->format('M d, Y') }}<br>
                                    <small style="color:#9ca3af;">{{ $payment->payment_method ?? 'Online' }}</small>
                                </span>
                                <span class="value">
                                    ₦{{ number_format($payment->amount, 2) }}<br>
                                    <small>
                                        @if($payment->status == 1)
                                            <span class="badge badge-paid">Success</span>
                                        @elseif($payment->status == 0)
                                            <span class="badge badge-failed">Failed</span>
                                        @else
                                            <span class="badge badge-pending">Pending</span>
                                        @endif
                                    </small>
                                </span>
                            </div>
                        @endforeach
                    @else
                        <p style="color:#9ca3af; font-size:14px;">No online payments recorded.<br>This order may be paid on delivery.</p>
                    @endif
                </div>

                <div class="card">
                    <h2>Need help?</h2>
                    <p style="font-size:14px; color:#6b7280; margin-bottom:15px;">
                        If you have any questions about this order, our support team is here to help.
                    </p>
                    <div class="actions">
                        <a href="{{ route('shop.index') }}" class="btn btn-outline">Continue shopping</a>
                        <a href="mailto:support@arkandstar.com" class="btn">Contact support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>