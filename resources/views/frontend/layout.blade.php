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
    $address        = $setting->address         ?? '';
    $developedBy    = $setting->developed_by    ?? 'Msoft Ghana';

    // Normalize WhatsApp number (strip non-digits, ensure country code)
    $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);
    if (substr($whatsappNumber, 0, 1) === '0') {
        // Convert local Ghana format like 0245168718 -> 233245168718
        $whatsappNumber = '233' . substr($whatsappNumber, 1);
    }

    // Calculate real cart count from session
    $cartItems = session()->get('cart', []);
    $cartCount = 0;
    foreach ($cartItems as $item) {
        $cartCount += $item['quantity'] ?? 0;
    }

    // Calculate real wishlist count from database
    $wishlistCount = 0;
    if (auth()->guard('customer')->check()) {
        $wishlistCount = \App\Models\Wishlist::where('customer_id', auth()->guard('customer')->id())->count();
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteTitle }} | Storefront</title>
    
    <!-- Dynamic Favicon -->
    @if($siteFavicon)
        <link rel="icon" href="{{ asset($siteFavicon) }}" type="image/x-icon">
    @endif
    
    <meta name="description" content="Modern ecommerce storefront powered by the JoexPOS inventory.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
    @stack('styles')
</head>
<body class="storefront-page">
    <div class="announcement-bar">
        <div class="container announcement-inner">
            <span>🚚 Free delivery in Accra on orders over GH₵1,000</span>
            <span>Call or WhatsApp: <strong>{{ $phoneNumber }}</strong></span>
        </div>
    </div>

    <header class="store-header">
        <div class="container topbar-row">
            <a href="{{ route('shop.index') }}" class="brand-mark">
                @if($siteLogo)
                    <img src="{{ asset($siteLogo) }}" alt="{{ $siteTitle }}" style="max-height: 40px; max-width: 150px; object-fit: contain;">
                @else
                    <span class="brand-icon">✦</span>
                    <span class="brand-text">
                        <strong>{{ $siteTitle }}</strong>
                        <small>{{ $companyName }}</small>
                    </span>
                @endif
            </a>

            <form class="search-box" method="GET" action="{{ route('shop.index') }}">
                <span class="search-icon">⌕</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="What are you looking for?">
                <button type="submit">Search</button>
            </form>

            <div class="header-actions">
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phoneNumber) }}" class="help-link">
                    Need help? <strong>{{ $phoneNumber }}</strong>
                </a>
                
                <!-- Authentication Logic for Customer -->
                @auth('customer')
                    <a href="{{ route('customer.dashboard') }}" class="customer-link">My Account</a>
                @else
                    <a href="{{ route('customer.login') }}" class="customer-link">Sign in / Register</a>
                @endauth

                <!-- Wishlist Icon -->
                <a href="{{ route('customer.wishlist') }}" class="customer-link" title="My Wishlist" style="display: flex; align-items: center; gap: 6px;">
                    ♡ <span id="wishlist-count" style="background: #0d5a39; color: #fff; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700;">{{ $wishlistCount }}</span>
                </a>

                <!-- Cart Icon with real count -->
                <a href="{{ route('cart.index') }}" class="cart-pill">Cart <span>{{ $cartCount }}</span></a>
            </div>
        </div>

        <div class="container nav-row">
            <a href="#categories" class="shop-category-btn">☰ Shop categories</a>
            <a href="#products">Featured products</a>
            <a href="#">Special offers</a>
            <a href="{{ route('track.order') }}">Track order</a>
            <a href="{{ route('contact.us') }}">Contact us</a>
            <span> {{ $address }}</span>
        </div>
    </header>

    <main class="storefront-main">
        @yield('content')
    </main>

    <footer class="store-footer">
        <div class="container footer-grid">
            <div>
                <h4>{{ $siteTitle }}</h4>
                <p>Affordable, reliable and durable tools, building materials and expert support for contractors, tradespeople and home projects.</p>

                @php
                    $whatsappMessage = urlencode("Hello " . $siteTitle . ", I would like to make an inquiry.");
                @endphp

                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" class="whatsapp-link">
                    Chat with us on WhatsApp
                </a>
            </div>

            <div>
                <h4>SHOP</h4>
                <ul>
                    @php
                        $footerCategories = \App\Models\Category::where('is_active', true)
                            ->orderBy('name')
                            ->take(5)
                            ->get();
                    @endphp
                    
                    @foreach($footerCategories as $category)
                        <li>
                            <a href="{{ route('shop.index') }}?category={{ \Illuminate\Support\Str::slug($category->name) }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4>Policies</h4>
                <ul>
                    @forelse($footerPolicies as $policy)
                    <li><a href="{{ route('policy.show', $policy) }}">{{ $policy->title }}</a></li>

                    
                @empty
                    {{-- No policies yet --}}
                @endforelse
                </ul>
            </div>

            <div>
                <h4>CONTACT US</h4>
                <ul>
                    <li>{{ $address }}</li>
                    <li>Monday–Saturday: 8:00 AM–6:00 PM</li>
                    <li>Phone: {{ $phoneNumber }}</li>
                    <li>Email: {{ $emailAddress }}</li>
                </ul>
            </div>


        </div>
        <div class="container footer-bottom">
            <span>© {{ date('Y') }} {{ $siteTitle }}. All rights reserved.</span>
            <span>Powered by <a href="https://msoftghana.com/en/" target="_blank" rel="noopener noreferrer" class="developer-link">{{ $developedBy }}</a></span>
        </div>
    </footer>

    <script>
        document.addEventListener('click', function (event) {
            const link = event.target.closest('.pagination a');
            if (!link) return;

            event.preventDefault();
            const url = link.getAttribute('href');

            fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const grid = doc.querySelector('#store-products-grid');
                const pager = doc.querySelector('#store-pagination');
                const targetGrid = document.querySelector('#store-products-grid');
                const targetPager = document.querySelector('#store-pagination');

                if (grid && targetGrid) targetGrid.innerHTML = grid.innerHTML;
                if (pager && targetPager) targetPager.innerHTML = pager.innerHTML;

                window.history.pushState({}, '', url);
                document.getElementById('products')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            })
            .catch(() => {
                window.location.href = url;
            });
        });
    </script>
    <!-- Allow scripts -->
    @stack('scripts')
</body>
</html>