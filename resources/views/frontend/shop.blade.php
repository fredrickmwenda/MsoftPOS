@extends('frontend.layout')
@php
    // ── General settings (header/footer identity) ─────────────────
    $setting = \App\Models\GeneralSetting::latest()->first();

    // Helper to safely resolve public path for images (logos, favicons)
    $resolveAssetPath = function ($path, $defaultDir = 'logo') {
        if (empty($path)) return null;
        if (filter_var($path, FILTER_VALIDATE_URL) || str_contains($path, '/')) {
            return $path;
        }
        return $defaultDir . '/' . $path;
    };

    // Safe defaults for all settings
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
        $whatsappNumber = '233' . substr($whatsappNumber, 1);
    }

    // ── Homepage content from ecommerce_setup ─────────────────────
    $hero        = setting('homepage.hero', []);
    $catSection  = setting('homepage.categories', []);
    $deal        = setting('homepage.deal', []);
    $featuredSec = setting('homepage.featured', []);

    // Hero (with safe fallbacks if not yet seeded)
    $heroBadge          = $hero['badge']              ?? $siteTitle;
    $heroTitlePrefix    = $hero['title_prefix']       ?? 'Everything you need to';
    $heroTitleHighlight = $hero['title_highlight']    ?? 'build better.';
    $heroDescription    = $hero['description']        ?? 'Affordable, reliable and durable tools, building materials and expert support for contractors, tradespeople and home projects.';
    $primaryBtnText     = $hero['primary_btn_text']   ?? 'Shop products';
    $primaryBtnLink     = $hero['primary_btn_link']   ?? (\Route::has('all.products') ? route('all.products') : '#');
    $secondaryBtnText   = $hero['secondary_btn_text'] ?? 'Request a quote';
    $secondaryBtnLink   = $hero['secondary_btn_link'] ?? '#categories';
    $warrantyTitle      = $hero['warranty_title']     ?? 'Genuine products';
    $warrantySubtitle   = $hero['warranty_subtitle']  ?? 'Manufacturer warranty';
    $heroStats          = $hero['stats'] ?? [
        ['value' => '5,000+',    'label' => 'Quality products'],
        ['value' => '24–48 hrs', 'label' => 'Accra delivery'],
        ['value' => '100%',      'label' => 'Genuine brands'],
    ];

    // Categories section headings
    $catEyebrow     = $catSection['eyebrow']       ?? 'Find what you need';
    $catHeading     = $catSection['heading']       ?? 'Shop by category';
    $catViewAllText = $catSection['view_all_text'] ?? 'View all products →';

    // Deal banner
    $dealLabel       = $deal['label']        ?? "THIS WEEK'S DEAL";
    $dealHeading     = $deal['heading']      ?? 'Power up your projects.';
    $dealDescription = $deal['description']  ?? 'Save on selected power tools, hand tools and essential site equipment.';
    $dealLinkText    = $deal['link_text']    ?? 'Explore special offers →';
    $dealLinkUrl     = $deal['link_url']     ?? (\Route::has('all.products') ? route('all.products') : '#');
    $dealBadgeTop    = $deal['badge_top']    ?? 'UP TO';
    $dealBadgeValue  = $deal['badge_value']  ?? '25%';
    $dealBadgeBottom = $deal['badge_bottom'] ?? 'OFF';

    // Featured heading
    $featuredEyebrow = $featuredSec['eyebrow'] ?? 'SELECTED FOR YOU';
    $featuredHeading = $featuredSec['heading'] ?? 'Featured products';
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
    {{-- ============================= HERO ============================= --}}
    <section class="store-hero">
        <div class="hero-copy">
            <span class="hero-badge">{{ $heroBadge }}</span>
            <h1>{{ $heroTitlePrefix }} <span>{{ $heroTitleHighlight }}</span></h1>
            <p>{{ $heroDescription }}</p>

            <div class="hero-actions">
                <a href="{{ $primaryBtnLink }}" class="btn btn-primary">{{ $primaryBtnText }}</a>
                @if (!empty($secondaryBtnText))
                    <a href="{{ $secondaryBtnLink }}" class="btn btn-secondary">{{ $secondaryBtnText }}</a>
                @endif
            </div>

            @if (!empty($heroStats))
                <div class="hero-stats">
                    @foreach ($heroStats as $stat)
                        <div>
                            <strong>{{ $stat['value'] ?? '' }}</strong>
                            <span>{{ $stat['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="hero-visual">
            <div class="hero-ring"></div>
            <span class="tool tool-left">🔧</span>
            <span class="tool tool-right">🧰</span>
            <span class="tool tool-bottom">🛠️</span>
            @if (!empty($warrantyTitle))
                <div class="warranty-card">
                    <span>✓</span>
                    <div>
                        <strong>{{ $warrantyTitle }}</strong>
                        <small>{{ $warrantySubtitle }}</small>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ========================== CATEGORIES ========================= --}}
    <section id="categories" class="category-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">{{ $catEyebrow }}</span>
                <h2>{{ $catHeading }}</h2>
            </div>
            <a href="{{ route('all.products') }}">{{ $catViewAllText }}</a>
        </div>

        <div class="category-grid">
            @foreach ($categories as $category)
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

    {{-- =========================== DEAL BANNER ======================== --}}
    <section class="deal-banner">
        <div class="deal-copy">
            <span class="deal-label">{{ $dealLabel }}</span>
            <h2>{{ $dealHeading }}</h2>
            <p>{{ $dealDescription }}</p>
            <a href="{{ $dealLinkUrl }}">{{ $dealLinkText }}</a>
        </div>

        <div class="deal-art">
            <div class="gear-icon">⚙️</div>
            <div class="discount-badge">
                <span>{{ $dealBadgeTop }}</span>
                <strong>{{ $dealBadgeValue }}</strong>
                <small>{{ $dealBadgeBottom }}</small>
            </div>
        </div>
    </section>

    {{-- ============================ FEATURED ========================== --}}
    <section id="products" class="showcase-section">
        <div class="section-heading top-heading">
            <div>
                <span class="eyebrow">{{ $featuredEyebrow }}</span>
                <h2>{{ $featuredHeading }}</h2>
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
                        {{-- IMAGE (separate link, NOT wrapping the body) --}}
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

                        {{-- BODY (flex:1 grows to fill card, pushes footer down) --}}
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

                        {{-- FOOTER (always at the bottom because body has flex:1) --}}
                        <div class="product-footer">
                            <div class="price-box">
                                <strong>{{ $currencyCode }} {{ number_format((float) $product->price, 2) }}</strong>
                                @if ($hasWholesale)
                                    <small>{{ $currencyCode }} {{ number_format((float) $product->wholesale_price, 2) }}</small>
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

        {{-- Toast Notification for Wishlist --}}
        <div id="wishlist-toast" style="position: fixed; bottom: 20px; right: 20px; background: #0d5a39; color: white; padding: 12px 20px; border-radius: 8px; display: none; z-index: 9999; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"></div>

        {{-- CSRF Token for JS --}}
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
                btn.innerHTML = '♥';       // Change to filled heart
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
