@extends('frontend.layout')
@php
    // Fetch the general settings safely. 
    $setting = \App\Models\GeneralSetting::latest()->first();
    
    // Helper to safely resolve public path for images (logos, favicons)
    // If the DB only stores the filename (e.g., 'logo.png'), it prepends 'logo/'
    $resolveAssetPath = function ($path, $defaultDir = 'logo') {
        if (empty($path)) return null;
        // If it's already a full URL (http://...) or contains a slash (logo/img.png)
        if (filter_var($path, FILTER_VALIDATE_URL) || str_contains($path, '/')) {
            return $path;
        }
        // Otherwise, assume it's just a filename and prepend the directory
        return $defaultDir . '/' . $path;
    };

    // Provide safe defaults for all settings
    $siteTitle      = $setting->site_title      ?? config('app.name', 'JoexPOS');
    $siteLogo       = $resolveAssetPath($setting->site_logo ?? null, 'logo');
    $siteFavicon    = $resolveAssetPath($setting->site_favicon ?? null, 'logo');
    $companyName    = $setting->company_name    ?? 'ARK AND STAR ENTERPRISE';
    $phoneNumber    = $setting->phone           ?? '+233 24 516 8718';
    $whatsappNumber = $setting->whatsapp_number ?? $setting->phone ?? '233245168718';
    $emailAddress   = $setting->email           ?? 'info@arkandstar.com';
    $address        = $setting->address         ?? 'Spintex Road, Accra';
    $developedBy    = $setting->developed_by    ?? 'Msoft Ghana';

    // Normalize WhatsApp number (strip non-digits, ensure country code)
    $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);
    if (substr($whatsappNumber, 0, 1) === '0') {
        // Convert local Ghana format like 0245168718 -> 233245168718
        $whatsappNumber = '233' . substr($whatsappNumber, 1);
    }


@endphp

@push('styles')
<style>
.footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding-top: 18px;
    border-top: 1px solid rgba(255,255,255,0.12);
    font-size: 13px;
}

.footer-policies {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.footer-policies a {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    font-size: 13px;
    transition: color .15s ease;
}

.footer-policies a:hover {
    color: #fff;
    text-decoration: underline;
}

.footer-policies .sep {
    color: rgba(255,255,255,0.35);
    font-size: 12px;
}

@media (max-width: 768px) {
    .footer-bottom {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    .footer-policies {
        justify-content: center;
    }
}
</style>
@endpush
@section('content')
    <section class="store-hero">
        <div class="hero-copy">
            <span class="hero-badge">{{$siteTitle}}</span>
            <h1>Everything you need to <span>build better.</span></h1>
            <p>Affordable, reliable and durable tools, building materials and expert support for contractors, tradespeople and home projects.</p>

            <div class="hero-actions">
                <a href="{{ route('all.products') }}" class="btn btn-primary">Shop products</a>
                <a href="#categories" class="btn btn-secondary">Request a quote</a>
            </div>

            <div class="hero-stats">
                <div><strong>5,000+</strong><span>Quality products</span></div>
                <div><strong>24–48 hrs</strong><span>Accra delivery</span></div>
                <div><strong>100%</strong><span>Genuine brands</span></div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-ring"></div>
            <span class="tool tool-left">🔧</span>
            <span class="tool tool-right">🧰</span>
            <span class="tool tool-bottom">🛠️</span>
            <div class="warranty-card">
                <span>✓</span>
                <div>
                    <strong>Genuine products</strong>
                    <small>Manufacturer warranty</small>
                </div>
            </div>
        </div>
    </section>

    <section id="categories" class="category-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Find what you need</span>
                <h2>Shop by category</h2>
            </div>
            <!-- Update link to point to all products route -->
            <a href="{{ route('all.products') }}">View all products →</a>
        </div>

        <div class="category-grid">
            @foreach ($categories as $category)
                <!-- Update link to point to all products route with category filter -->
                <a href="{{ route('all.products', ['category' => $category->slug]) }}#products" class="category-card">
                    <span class="category-icon">{{ $category->image ?? '🧱' }}</span>
                    <div>
                        <h3>{{ $category->name }}</h3>
                        <small>{{ $category->product_count }} products</small>
                    </div>
                    <strong>→</strong>
                </a>
            @endforeach
        </div>
    </section>

    <section class="deal-banner">
        <div class="deal-copy">
            <span class="deal-label">THIS WEEK'S DEAL</span>
            <h2>Power up your projects.</h2>
            <p>Save on selected power tools, hand tools and essential site equipment.</p>
            <a href="{{ route('all.products') }}">Explore special offers →</a>
        </div>

        <div class="deal-art">
            <div class="gear-icon">⚙️</div>
            <div class="discount-badge">
                <span>UP TO</span>
                <strong>25%</strong>
                <small>OFF</small>
            </div>
        </div>
    </section>

    <section id="products" class="showcase-section">
        <div class="section-heading top-heading">
            <div>
                <span class="eyebrow">SELECTED FOR YOU</span>
                <h2>Featured products</h2>
            </div>
            <span class="results-count">{{ $featuredProducts->count() }} items available</span>
        </div>

        @if ($featuredProducts->isNotEmpty())
            <div class="featured-grid">
                @foreach ($featuredProducts as $product)
                    @php
                        $hasWholesale = !empty($product->wholesale_price) && (float) $product->wholesale_price > 0;
                    @endphp

                    <article class="product-card">
                        <!-- IMAGE (separate link, NOT wrapping the body) -->
                        <div class="product-image-wrap">
                            <a href="{{ route('products.show', $product->id) }}" class="product-image-link">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                            </a>
                            <span class="product-tag">FEATURED</span>
                            <button type="button"
                                    class="wishlist-button"
                                    data-product-id="{{ $product->id }}"
                                    title="Add to Wishlist">♡</button>
                        </div>

                        <!-- BODY (flex:1 grows to fill card, pushes footer down) -->
                        <div class="product-body">
                            <span class="product-category">{{ strtoupper($product->category->name ?? 'General') }}</span>

                            <h3><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></h3>

                            <div class="product-meta">
                                <span class="stars">★★★★★</span>
                                <span class="stock-pill {{ ($product->qty ?? 0) > 0 ? 'in' : 'out' }}">
                                    {{ ($product->qty ?? 0) > 0 ? "In stock: {$product->qty}" : 'Out of stock' }}
                                </span>
                            </div>

                            <p class="product-desc">{{ \Illuminate\Support\Str::limit(strip_tags($product->product_details ?? $product->name), 90) }}</p>
                        </div>

                        <!-- FOOTER (always at the bottom because body has flex:1) -->
                        <div class="product-footer">
                            <div class="price-box">
                                <strong>GH₵ {{ number_format((float) $product->price, 2) }}</strong>
                                @if ($hasWholesale)
                                    <small>GH₵ {{ number_format((float) $product->wholesale_price, 2) }}</small>
                                @endif
                            </div>

                            <form action="{{ route('cart.add', $product->id) }}" method="POST" class="cart-form">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button class="add-to-cart" type="submit">
                                    <span>Add to cart</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.472 8.225-.408L13.38 4H3.102zM5 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <!-- Toast Notification for Wishlist -->
        <div id="wishlist-toast" style="position: fixed; bottom: 20px; right: 20px; background: #0d5a39; color: white; padding: 12px 20px; border-radius: 8px; display: none; z-index: 9999; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"></div>
        
        <!-- CSRF Token for JS -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        const btn = event.target.closest('.wishlist-button');
        if (!btn) return;

        // Prevent the click from bubbling up to the <a> tag and triggering page navigation
        event.preventDefault();
        event.stopPropagation(); 

        const productId = btn.dataset.productId;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const toast = document.getElementById('wishlist-toast');

        btn.disabled = true; // Prevent double clicking

        fetch(`/wishlist/add/${productId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 401) {
                // Unauthorized -> Redirect to login
                return response.json().then(data => {
                    showToast('Please log in to use the wishlist.', true);
                    setTimeout(() => { window.location.href = data.redirect; }, 1500);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                btn.innerHTML = '♥'; // Change to filled heart
                btn.style.color = '#ef4444'; // Make it red
                showToast('Product added to wishlist!');
            }
            btn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
        });

        function showToast(message, isError = false) {
            toast.textContent = message;
            toast.style.backgroundColor = isError ? '#b91c1c' : '#0d5a39';
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }
    });
</script>
@endpush