@extends('frontend.layout')

@push('styles')
<style>
    /* Page Layout */
    .cart-page-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    .cart-title {
        font-size: 28px;
        font-weight: 800;
        color: var(--ark-green);
        margin-bottom: 30px;
        letter-spacing: -0.05em;
    }
    .cart-layout {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 40px;
        align-items: start;
    }
    @media (max-width: 900px) {
        .cart-layout { grid-template-columns: 1fr; }
    }

    /* Left Side: Cart Items */
    .cart-items-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid rgba(13, 90, 57, 0.12);
        padding: 30px;
    }
    .cart-items-header {
        font-size: 18px;
        font-weight: 700;
        color: var(--ark-ink);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(13, 90, 57, 0.12);
    }
    .cart-item {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }
    .item-info { flex: 1; }
    .item-info h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--ark-green);
        margin: 0 0 5px 0;
    }
    .item-info p {
        font-size: 14px;
        color: var(--ark-muted);
        margin: 0;
    }
    .item-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .qty-selector {
        display: flex;
        align-items: center;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        overflow: hidden;
    }
    .qty-btn {
        background: #f9fafb;
        border: none;
        width: 36px;
        height: 36px;
        cursor: pointer;
        font-size: 18px;
        font-weight: 600;
        color: #4b5563;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }
    .qty-btn:hover { background: #f3f4f6; }
    .qty-input {
        width: 40px;
        text-align: center;
        border: none;
        border-left: 1px solid #d1d5db;
        border-right: 1px solid #d1d5db;
        height: 36px;
        font-size: 14px;
        font-weight: 600;
        color: var(--ark-ink);
    }
    .btn-remove {
        color: #9ca3af;
        font-size: 24px;
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
        transition: 0.2s;
        line-height: 1;
    }
    .btn-remove:hover { color: #ef4444; }
    .item-total {
        font-weight: 800;
        color: var(--ark-ink);
        font-size: 16px;
        width: 120px;
        text-align: right;
    }

    .empty-cart {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-cart p { font-size: 16px; color: #6b7280; margin-bottom: 20px; }
    .btn-shop-empty {
        display: inline-block;
        margin-top: 15px;
        padding: 12px 30px;
        background: var(--ark-green);
        color: var(--ark-white);
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
    }

    /* Right Side: Checkout Form */
    .checkout-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid rgba(13, 90, 57, 0.12);
        padding: 30px;
    }
    .checkout-card h2 {
        font-size: 18px;
        font-weight: 700;
        color: var(--ark-ink);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(13, 90, 57, 0.12);
    }
    .logged-in-badge {
        background: #f0fdf4;
        color: #15803d;
        padding: 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-group { margin-bottom: 18px; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
        color: #374151;
        letter-spacing: 0.02em;
    }
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border 0.2s;
        background: #fff;
        font-family: 'Inter', sans-serif;
        color: var(--ark-ink);
    }
    .form-group input:focus, .form-group textarea:focus {
        border-color: var(--ark-green);
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 90, 57, 0.1);
    }
    .form-group textarea { resize: vertical; min-height: 80px; }

    .payment-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 12px;
        background: #f9fafb;
        cursor: pointer;
        transition: 0.2s;
    }
    .payment-option:hover { border-color: var(--ark-green); background: #fff; }
    .payment-option input {
        width: 18px;
        height: 18px;
        accent-color: var(--ark-green);
        cursor: pointer;
    }
    .payment-option label {
        margin: 0;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: var(--ark-ink);
        flex: 1;
    }

    .total-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 25px 0 20px;
        padding-top: 20px;
        border-top: 2px dashed #e5e7eb;
    }
    .total-summary span { font-size: 16px; color: #6b7280; font-weight: 600; }
    .total-summary strong { font-size: 24px; color: var(--ark-green); font-weight: 800; }

    .btn-checkout {
        width: 100%;
        background: var(--ark-green);
        color: var(--ark-white);
        padding: 16px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        transition: background 0.2s;
        letter-spacing: 0.02em;
    }
    .btn-checkout:hover { background: var(--ark-green-deep); }
</style>
@endpush

@if(session('success'))
    <div class="alert alert-success" style="background:#f0fdf4;color:#15803d;padding:14px 18px;border-radius:8px;margin-bottom:20px;border:1px solid #bbf7d0;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="background:#fef2f2;color:#b91c1c;padding:14px 18px;border-radius:8px;margin-bottom:20px;border:1px solid #fecaca;">
        {{ session('error') }}
    </div>
@endif

@section('content')
@php
    // Check if customer is logged in to prefill the form
    $customer = auth()->guard('customer')->user();
@endphp

<div class="cart-page-container">
    <h1 class="cart-title">Your Shopping Cart</h1>
    
    <div class="cart-layout">
        
        <!-- Left Side: Cart Items -->
        <div class="cart-items-card">
            @if(count($cart) > 0)
                @foreach($cart as $id => $item)
                    <div class="cart-item">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                        <div class="item-info">
                            <h3>{{ $item['name'] }}</h3>
                            <p>GH₵ {{ number_format($item['price'], 2) }} each</p>
                        </div>
                        
                        <div class="item-actions">
                            <form action="{{ route('cart.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $id }}">
                                <div class="qty-selector">
                                    <button type="button" class="qty-btn" onclick="updateQty(this, -1)">−</button>
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="qty-input" readonly>
                                    <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                                </div>
                            </form>
                            <a href="{{ route('cart.remove', $id) }}" class="btn-remove" title="Remove item">×</a>
                        </div>
                        
                        <div class="item-total">GH₵ {{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                    </div>
                @endforeach
            @else
                <div class="empty-cart">
                    <p>Your cart is currently empty.</p>
                    <a href="{{ route('shop.index') }}" class="btn-shop-empty">Start Shopping</a>
                </div>
            @endif
        </div>

        <!-- Right Side: Checkout Form -->
        @if(count($cart) > 0)
        <div class="checkout-card">
            <h2>Customer and delivery details</h2>
            
            @auth('customer')
                <div class="logged-in-badge">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Signed in as {{ $customer->name }}
                </div>
            @endauth

            <form action="{{ route('checkout.process') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ $customer->name ?? old('name') }}" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone_number" value="{{ $customer->phone_number ?? old('phone_number') }}" placeholder="e.g. +233 24 000 0000" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ $customer->email ?? old('email') }}" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label>Delivery Address</label>
                    <input type="text" name="delivery_address" value="{{ old('delivery_address') }}" placeholder="House number, street name, area" required>
                </div>

                <div class="form-group">
                    <label>Order Notes (Optional)</label>
                    <textarea name="order_notes" placeholder="Any special instructions for delivery?"></textarea>
                </div>

                <div class="form-group" style="margin-top: 25px; margin-bottom: 15px;">
                    <label>Payment Method</label>
                    
                    <div class="payment-option">
                        <input type="radio" id="paystack" name="payment_method" value="paystack" checked required>
                        <label for="paystack">Pay securely with Paystack</label>
                    </div>
                    
                    <div class="payment-option">
                        <input type="radio" id="delivery" name="payment_method" value="delivery" required>
                        <label for="delivery">Pay on delivery</label>
                    </div>
                </div>

                <div class="total-summary">
                    <span>Estimated Total:</span>
                    <strong>GH₵ {{ number_format($subtotal, 2) }}</strong>
                </div>

                <button type="submit" class="btn-checkout">Continue to secure payment</button>
            </form>
        </div>
        @endif
    </div>
</div>

<script>
    // Simple JS to handle + and - buttons
    function updateQty(button, change) {
        const input = button.parentElement.querySelector('.qty-input');
        let newValue = parseInt(input.value) + change;
        if (newValue < 1) newValue = 1;
        input.value = newValue;
        
        // Automatically submit the form when quantity changes
        button.closest('form').submit();
    }
</script>
@endsection