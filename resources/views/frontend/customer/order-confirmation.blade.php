<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - arkandstar.com</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; color: #333; }
        nav { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-size: 20px; font-weight: bold; color: #D4AF37; }
        .nav-links a { text-decoration: none; color: #4b5563; font-size: 14px; margin-left: 20px; font-weight: 500; }
        .nav-links a:hover { color: #16a34a; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .success-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 50px 30px; text-align: center; margin-bottom: 25px; }
        .success-icon { width: 80px; height: 80px; margin: 0 auto 20px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .success-icon svg { width: 40px; height: 40px; color: #16a34a; }
        .success-card h1 { font-size: 26px; color: #1f2937; margin-bottom: 8px; }
        .success-card .subtitle { color: #6b7280; font-size: 14px; margin-bottom: 30px; }
        .ref-box { background: #fffbeb; border: 2px dashed #D4AF37; border-radius: 8px; padding: 20px; margin: 0 auto 25px; max-width: 400px; }
        .ref-box .label { font-size: 11px; color: #92400e; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .ref-box .ref { font-size: 24px; font-weight: 700; color: #1f2937; letter-spacing: 1px; font-family: monospace; }
        .ref-box .hint { font-size: 12px; color: #9ca3af; margin-top: 8px; }
        .summary-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 25px; margin-bottom: 25px; }
        .summary-card h2 { font-size: 16px; color: #1f2937; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
        .summary-row .label { color: #6b7280; }
        .summary-row .value { color: #1f2937; font-weight: 600; }
        .summary-row.grand { font-weight: 700; font-size: 18px; border-top: 2px solid #1f2937; margin-top: 10px; padding-top: 12px; }
        .items-list { margin-top: 15px; }
        .item-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #4b5563; }
        .item-row .name { color: #1f2937; font-weight: 500; }
        .next-steps { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 25px; margin-bottom: 25px; }
        .next-steps h2 { font-size: 16px; color: #1f2937; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
        .step { display: flex; gap: 12px; margin-bottom: 15px; }
        .step:last-child { margin-bottom: 0; }
        .step-num { flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; background: #16a34a; color: white; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; }
        .step-text { font-size: 14px; color: #4b5563; line-height: 1.5; padding-top: 4px; }
        .step-text strong { color: #1f2937; }
        .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #16a34a; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; }
        .btn:hover { background-color: #15803d; }
        .btn-outline { background: transparent; color: #4b5563; border: 1px solid #d1d5db; }
        .btn-outline:hover { background: #f3f4f6; color: #333; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-paid    { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <nav>
        <div class="nav-logo">Ark & Star Enterprise</div>
        <div class="nav-links">
            <a href="{{ route('shop.index') }}">Store</a>
            <a href="{{ route('customer.dashboard') }}">My account</a>
            <a href="#" onclick="document.getElementById('logout-form').submit();">Sign out</a>
            <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </nav>

    <div class="container">

        <!-- Success Header -->
        <div class="success-card">
            <div class="success-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1>Thank you for your order!</h1>
            <p class="subtitle">Your order has been placed successfully. Save your reference number below.</p>

            <div class="ref-box">
                <div class="label">Order Reference</div>
                <div class="ref">{{ $sale->reference_no }}</div>
                <div class="hint">Use this number to track your order or contact support.</div>
            </div>

            @php
                $isPaid = $sale->payment_status == 4;
            @endphp
            <p style="font-size:14px; color:#6b7280;">
                Payment Status:
                <span class="badge {{ $isPaid ? 'badge-paid' : 'badge-pending' }}">
                    {{ $isPaid ? 'Paid' : 'Pay on Delivery' }}
                </span>
            </p>
        </div>

        <!-- Order Summary -->
        <div class="summary-card">
            <h2>Order Summary</h2>

            @if($sale->productSales->isNotEmpty())
                <div class="items-list">
                    @foreach($sale->productSales as $ps)
                        <div class="item-row">
                            <span class="name">
                                {{ $ps->product->name ?? 'Product #' . $ps->product_id }}
                                <span style="color:#9ca3af; font-weight:400;"> × {{ $ps->quantity }}</span>
                            </span>
                            <span>₦{{ number_format($ps->price * $ps->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="summary-row grand">
                <span class="label">Total</span>
                <span class="value">₦{{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="next-steps">
            <h2>What happens next?</h2>

            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">
                    <strong>Order confirmed.</strong>
                    We've received your order and are getting it ready for dispatch.
                </div>
            </div>

            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">
                    @if($isPaid)
                        <strong>Payment received.</strong>
                        Your payment has been confirmed — no further action needed.
                    @else
                        <strong>Pay on delivery.</strong>
                        Please have ₦{{ number_format($sale->total_amount, 2) }} ready when your order arrives.
                    @endif
                </div>
            </div>

            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">
                    <strong>Shipping.</strong>
                    @if($sale->delivery && $sale->delivery->estimated_delivery)
                        Estimated delivery: {{ \Carbon\Carbon::parse($sale->delivery->estimated_delivery)->format('F d, Y') }}.
                    @else
                        You'll receive an update once your order ships. Track it from your account.
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="{{ route('customer.orders.show', $sale) }}" class="btn">View order details</a>
            <a href="{{ route('shop.index') }}" class="btn btn-outline">Continue shopping</a>
        </div>

    </div>
</body>
</html>