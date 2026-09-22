@extends('frontend.layout')

@section('content')
<style>
    .shop-container { display: flex; gap: 30px; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .shop-sidebar { width: 260px; flex-shrink: 0; }
    .shop-main { flex: 1; }
    .sidebar-block { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #f0f0f0; }
    .sidebar-block h3 { font-size: 1.1rem; margin-bottom: 15px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; color: #333; }
    .cat-list { list-style: none; padding: 0; margin: 0; }
    .cat-list li { margin-bottom: 8px; }
    .cat-list a { text-decoration: none; color: #555; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; padding: 8px 5px; border-radius: 6px; }
    .cat-list a:hover, .cat-list a.active { background: #f0f7f4; color: #0d5a39; font-weight: 600; }
    .cat-list a small { background: #eee; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; color: #666; }
    .price-inputs { display: flex; gap: 10px; align-items: center; }
    .price-inputs input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
    .btn-filter { width: 100%; margin-top: 15px; padding: 12px; background: #0d5a39; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; }
    .btn-filter:hover { background: #0a4a30; }
    .shop-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .shop-header h2 { margin: 0; font-size: 1.8rem; color: #222; }
    .shop-header span { color: #666; font-size: 0.9rem; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
    .product-card { background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #f0f0f0; transition: 0.3s; display: flex; flex-direction: column; }
    .product-card:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.08); transform: translateY(-5px); }
    .card-img-box { height: 220px; background: #f8f8f8; display: flex; align-items: center; justify-content: center; padding: 15px; position: relative; }
    .card-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; }

    /* ===== ADD TO CART BUTTON ===== */
    .card-body { padding: 15px; flex: 1; display: flex; flex-direction: column; }
    .card-cat { font-size: 0.75rem; color: #888; text-transform: uppercase; font-weight: 600; }
    .card-title { font-size: 1rem; color: #222; margin: 5px 0; height: 40px; overflow: hidden; font-weight: 500; }
    .card-title a { text-decoration: none; color: inherit; }
    .card-footer { margin-top: auto; display: flex; flex-direction: column; gap: 12px; }
    .card-price-row { display: flex; justify-content: space-between; align-items: center; }
    .card-price { font-size: 1.25rem; font-weight: 800; color: #0d5a39; }

    .btn-add-cart {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px 16px;
        background: #0d5a39;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 700;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
        letter-spacing: 0.02em;
    }
    .btn-add-cart svg {
        width: 18px;
        height: 18px;
        transition: transform 0.25s ease;
    }
    .btn-add-cart:hover {
        background: #0a4a30;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(13, 90, 57, 0.3);
    }
    .btn-add-cart:hover svg {
        transform: scale(1.2);
    }
    .btn-add-cart:active {
        transform: translateY(0);
    }
    .btn-add-cart .btn-label {
        transition: opacity 0.2s ease;
    }
    .btn-add-cart.adding .btn-label {
        opacity: 0;
    }
    .btn-add-cart.adding::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 18px;
        height: 18px;
        margin: -9px 0 0 -9px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.5s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== FLOATING CART BUTTON ===== */
    .float-cart-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 64px;
        height: 64px;
        background: #0d5a39;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 24px rgba(13, 90, 57, 0.35);
        text-decoration: none;
        z-index: 999;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .float-cart-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 30px rgba(13, 90, 57, 0.45);
    }
    .float-cart-btn svg {
        width: 28px;
        height: 28px;
        color: #fff;
    }
    .float-cart-btn .cart-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 24px;
        height: 24px;
        padding: 0 6px;
        background: #ef4444;
        color: #fff;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        line-height: 1;
    }
    .float-cart-btn .cart-badge:empty {
        display: none;
    }
    .float-cart-btn.pulse {
        animation: cartPulse 0.5s ease;
    }
    @keyframes cartPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); }
    }

    /* ===== TOAST NOTIFICATION ===== */
    .cart-toast {
        position: fixed;
        bottom: 110px;
        right: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1000;
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.3s ease, transform 0.3s ease;
        pointer-events: none;
        max-width: 300px;
    }
    .cart-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
    .cart-toast .toast-icon {
        width: 40px;
        height: 40px;
        background: #f0fdf4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .cart-toast .toast-icon svg {
        width: 22px;
        height: 22px;
        color: #0d5a39;
    }
    .cart-toast .toast-text {
        font-size: 0.85rem;
        color: #333;
        line-height: 1.4;
    }
    .cart-toast .toast-text strong {
        color: #0d5a39;
        font-weight: 800;
    }
    .cart-toast .toast-link {
        display: block;
        margin-top: 4px;
        color: #0d5a39;
        font-weight: 700;
        text-decoration: none;
        font-size: 0.8rem;
    }
    .cart-toast .toast-link:hover { text-decoration: underline; }

    /* ===== PAGINATION ===== */
    .shop-pagination {
        margin-top: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .shop-pagination a,
    .shop-pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        text-decoration: none;
        color: #333;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.2s;
        background: #fff;
    }
    .shop-pagination a:hover {
        background: #0d5a39;
        color: #fff;
        border-color: #0d5a39;
    }
    .shop-pagination .page-active {
        background: #0d5a39;
        color: #fff;
        border-color: #0d5a39;
    }
    .shop-pagination .page-disabled {
        color: #ccc;
        border-color: #eee;
        background: #f9f9f9;
        cursor: not-allowed;
    }
    .shop-pagination .page-ellipsis {
        border: none;
        background: transparent;
        color: #999;
        font-weight: 700;
    }
</style>

<div class="shop-container">
    <!-- Sidebar -->
    <aside class="shop-sidebar">
        <div class="sidebar-block">
            <h3>Categories</h3>
            <ul class="cat-list">
                <li>
                    <a href="{{ route('shop.index') }}" class="{{ !$selectedCategory ? 'active' : '' }}">
                        All Products <small>{{ $categories->sum('product_count') }}</small>
                    </a>
                </li>
                @foreach ($categories as $category)
                    <li>
                        <a href="{{ route('all.products', ['category' => $category->slug]) }}" class="{{ $selectedCategory && $selectedCategory->slug == $category->slug ? 'active' : '' }}">
                            {{ $category->name }} <small>{{ $category->product_count }}</small>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="sidebar-block">
            <h3>Filter by Price</h3>
            <form action="{{ route('shop.index') }}" method="GET">
                @if($selectedCategory) <input type="hidden" name="category" value="{{ $selectedCategory->slug }}"> @endif
                @if($query) <input type="hidden" name="q" value="{{ $query }}"> @endif
                <div class="price-inputs">
                    <input type="number" name="min_price" placeholder="Min GH" value="{{ $minPrice > 0 ? $minPrice : '' }}" min="0">
                    <span>-</span>
                    <input type="number" name="max_price" placeholder="Max GH" value="{{ $maxPrice > 0 ? $maxPrice : '' }}" min="0">
                </div>
                <button type="submit" class="btn-filter">Apply Filter</button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="shop-main">
        <div class="shop-header">
            <h2>{{ $selectedCategory ? $selectedCategory->name : 'All Products' }}</h2>
            <span>Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items</span>
        </div>

        @if($products->isNotEmpty())
            <div class="product-grid">
                @foreach ($products as $product)
                    <div class="product-card">
                        <a href="{{ route('products.show', $product->id) }}">
                            <div class="card-img-box">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                            </div>
                        </a>
                        <div class="card-body">
                            <span class="card-cat">{{ strtoupper($product->category->name ?? 'General') }}</span>
                            <h3 class="card-title">
                                <a href="{{ route('products.show', $product->id) }}">{{ \Illuminate\Support\Str::limit($product->name, 40) }}</a>
                            </h3>
                            <div class="card-footer">
                                <div class="card-price-row">
                                    <span class="card-price">GH {{ number_format((float) $product->price, 2) }}</span>
                                </div>
                                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="add-cart-form">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-add-cart" data-product-name="{{ $product->name }}">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span class="btn-label">Add to Cart</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $products->withQueryString()->links('partials.shop-pagination') }}
        @else
            <div style="text-align: center; padding: 60px 0; color: #777;">
                <h3>No products found</h3>
                <p>Try adjusting your filters or search query.</p>
            </div>
        @endif
    </div>
</div>

<!-- FLOATING CART BUTTON -->
<?php $cartCount = count(session('cart', [])); ?>
<a href="{{ route('cart.index') }}" class="float-cart-btn" id="floatCartBtn">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
    </svg>
    <span class="cart-badge" id="cartBadge">{{ $cartCount > 0 ? $cartCount : '' }}</span>
</a>

<!-- TOAST NOTIFICATION -->
<div class="cart-toast" id="cartToast">
    <div class="toast-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>
    <div class="toast-text">
        <strong id="toastProductName">Product</strong> added to cart
        <a href="{{ route('cart.index') }}" class="toast-link">View Cart</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ---- Add to Cart form submission ----
    document.querySelectorAll('.add-cart-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var btn = form.querySelector('.btn-add-cart');
            var productName = btn.getAttribute('data-product-name') || 'Product';

            // Show loading spinner on the button
            btn.classList.add('adding');
            btn.disabled = true;

            // Show toast (will stay visible until page redirects)
            window._cartToastProductName = productName;
        });
    });

    // ---- After page load: if session has 'success' from CartController redirect,
    //       show the toast with the product name. ----
    @if(session('success'))
        var toast = document.getElementById('cartToast');
        var toastName = document.getElementById('toastProductName');
        var badge = document.getElementById('cartBadge');
        var floatBtn = document.getElementById('floatCartBtn');

        // Update badge count
        var newCount = '{{ $cartCount }}';
        badge.textContent = newCount > 0 ? newCount : '';

        // Pulse animation
        floatBtn.classList.add('pulse');
        setTimeout(function() { floatBtn.classList.remove('pulse'); }, 500);

        // Show toast
        toast.classList.add('show');

        // Auto-hide after 3 seconds
        setTimeout(function() { toast.classList.remove('show'); }, 3000);
    @endif
});
</script>
@endsection