@extends('frontend.layout')

@push('styles')
<style>
    .track-page { max-width: 800px; margin: 60px auto; padding: 0 20px; }
    .track-card {
        background: #fff; border-radius: 16px; padding: 40px;
        border: 1px solid rgba(13,90,57,0.12); box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .track-title { font-size: 26px; font-weight: 800; color: var(--ark-green); margin-bottom: 8px; }
    .track-subtitle { font-size: 15px; color: var(--ark-muted); margin-bottom: 30px; }
    .track-form { display: flex; gap: 12px; margin-bottom: 30px; }
    .track-form input {
        flex: 1; padding: 14px 18px; border: 2px solid #e5e7eb; border-radius: 10px;
        font-size: 16px; font-weight: 600; letter-spacing: 0.03em;
    }
    .track-form input:focus { border-color: var(--ark-green); outline: none; box-shadow: 0 0 0 4px rgba(13,90,57,0.1); }
    .track-form button {
        padding: 14px 36px; background: var(--ark-green); color: #fff; border: none;
        border-radius: 10px; font-weight: 800; font-size: 15px; cursor: pointer;
        transition: background 0.2s;
    }
    .track-form button:hover { background: var(--ark-green-deep); }

    .order-found {
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px;
        padding: 30px; margin-top: 20px;
    }
    .ref-badge {
        display: inline-block; background: var(--ark-green); color: #fff;
        padding: 8px 20px; border-radius: 8px; font-weight: 800; font-size: 18px;
        letter-spacing: 0.05em; margin-bottom: 20px;
    }
    .status-row { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px; }
    .status-box {
        flex: 1; min-width: 140px; background: #fff; border-radius: 10px;
        padding: 16px; border: 1px solid #e5e7eb; text-align: center;
    }
    .status-box .label { font-size: 12px; color: var(--ark-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .status-box .value { font-size: 16px; font-weight: 700; color: var(--ark-ink); }
    .badge-paid { color: #15803d; }
    .badge-pending { color: #d97706; }

    .product-list { width: 100%; border-collapse: collapse; margin-top: 16px; }
    .product-list th { text-align: left; font-size: 12px; color: var(--ark-muted); padding: 10px; border-bottom: 2px solid #e5e7eb; text-transform: uppercase; letter-spacing: 0.05em; }
    .product-list td { padding: 12px 10px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
    .grand-total { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 2px dashed #e5e7eb; }
    .grand-total span { font-size: 16px; color: var(--ark-muted); font-weight: 600; }
    .grand-total strong { font-size: 24px; color: var(--ark-green); font-weight: 800; }

    .alert-error { background: #fef2f2; color: #b91c1c; padding: 14px 18px; border-radius: 10px; border: 1px solid #fecaca; margin-bottom: 20px; font-weight: 600; }
    .alert-success { background: #f0fdf4; color: #15803d; padding: 14px 18px; border-radius: 10px; border: 1px solid #bbf7d0; margin-bottom: 20px; font-weight: 600; }
</style>
@endpush

@section('content')

<div class="track-page">
    <div class="track-card">
        <h1 class="track-title">Track Your Order</h1>
        <p class="track-subtitle">Enter your order reference number to see its current status.</p>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if($error ?? false)
            <div class="alert-error">{{ $error }}</div>
        @endif

        <form action="{{ route('track.order.search') }}" method="POST" class="track-form">
            @csrf
            <input type="text" name="reference_no" value="{{ $reference ?? '' }}"
                   placeholder="e.g. sr-20250115-0042" required>
            <button type="submit">Track Order</button>
        </form>

        @if($sale)
            <div class="order-found">
                <div class="ref-badge">{{ $sale->reference_no }}</div>

                <div class="status-row">
                    <div class="status-box">
                        <div class="label">Order Date</div>
                        <div class="value">{{ $sale->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="status-box">
                        <div class="label">Payment Status</div>
                        <div class="value {{ $sale->payment_status == 4 ? 'badge-paid' : 'badge-pending' }}">
                            @if($sale->payment_status == 4) Paid @elseif($sale->payment_status == 1) Pending @elseif($sale->payment_status == 2) Due @elseif($sale->payment_status == 3) Partial @endif
                        </div>
                    </div>
                    <div class="status-box">
                        <div class="label">Order Status</div>
                        <div class="value">
                            @if($sale->sale_status == 1) Completed @elseif($sale->sale_status == 2) Pending @elseif($sale->sale_status == 3) Draft @endif
                        </div>
                    </div>
                    <div class="status-box">
                        <div class="label">Delivery</div>
                        <div class="value">
                            @if($sale->delivery)
                                @if($sale->delivery->status == 1) Packing
                                @elseif($sale->delivery->status == 2) Delivering
                                @elseif($sale->delivery->status == 3) Delivered
                                @else Customer Unavailable
                                @endif
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>

                @if($sale->delivery)
                <div class="status-box" style="margin-bottom: 20px;">
                    <div class="label">Delivery Address</div>
                    <div class="value">{{ $sale->delivery->address }}</div>
                </div>
                @endif

                <table class="product-list">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->productSales as $item)
                        <tr>
                            <td>{{ $item->product?->name ?? 'Unknown Product' }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>GH₵ {{ number_format($item->net_unit_price, 2) }}</td>
                            <td>GH₵ {{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="grand-total">
                    <span>Grand Total:</span>
                    <strong>GH₵ {{ number_format($sale->grand_total, 2) }}</strong>
                </div>

                @if($sale->payment_status != 4)
                <div class="alert-error" style="margin-top: 20px;">
                    @if($sale->staff_note && str_contains($sale->staff_note, 'Pay on Delivery'))
                        Your order is pending payment on delivery. Our team will contact you shortly to arrange delivery and collect payment.
                    @else
                        Your payment is being verified. Please check back shortly or contact support.
                    @endif
                </div>
                @else
                <div class="alert-success" style="margin-top: 20px;">
                    @if($sale->delivery && $sale->delivery->status == 3)
                        Your order has been delivered. Thank you for shopping with us!
                    @else
                        Payment confirmed! Our team is preparing your order for delivery.
                    @endif
                </div>
                @endif
            </div>
        @endif
    </div>
</div>

@endsection