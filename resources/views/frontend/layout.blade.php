<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'JoexPOS') }} | Storefront</title>
    <meta name="description" content="Modern ecommerce storefront powered by the JoexPOS inventory.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
</head>
<body class="storefront-page">
    <div class="announcement-bar">
        <div class="container announcement-inner">
            <span>🚚 Free delivery in Accra on orders over GH₵1,000</span>
            <span>Call or WhatsApp: <strong>+233 24 516 8718</strong></span>
        </div>
    </div>

    <header class="store-header">
        <div class="container topbar-row">
            <a href="{{ route('shop.index') }}" class="brand-mark">
                <span class="brand-icon">✦</span>
                <span class="brand-text">
                    <strong>ARK AND STAR</strong>
                    <small>ENTERPRISE</small>
                </span>
            </a>

            <form class="search-box" method="GET" action="{{ route('shop.index') }}">
                <span class="search-icon">⌕</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="What are you looking for?">
                <button type="submit">Search</button>
            </form>

            <div class="header-actions">
                <a href="#" class="help-link">Need help? <strong>+233 24 516 8718</strong></a>
                <a href="#" class="customer-link">Sign in / Register</a>
                <a href="#" class="cart-pill">Cart <span>0</span></a>
            </div>
        </div>

        <div class="container nav-row">
            <a href="#categories" class="shop-category-btn">☰ Shop categories</a>
            <a href="#products">Featured products</a>
            <a href="#">Special offers</a>
            <a href="#">Track order</a>
            <a href="#">Contact us</a>
            <span>📍 Spintex Road, Accra</span>
        </div>
    </header>

    <main class="storefront-main">
        @yield('content')
    </main>

    <footer class="store-footer">
        <div class="container footer-grid">
            <div>
                <h4>ARK AND STAR ENTERPRISE</h4>
                <p>Affordable, reliable and durable tools, building materials and expert support for contractors, tradespeople and home projects.</p>
                <a href="#" class="whatsapp-link">Chat with us on WhatsApp</a>
            </div>
            <div>
                <h4>SHOP</h4>
                <ul>
                    <li><a href="{{ route('shop.index') }}">Power tools</a></li>
                    <li><a href="{{ route('shop.index') }}">Hand tools</a></li>
                    <li><a href="{{ route('shop.index') }}">Electrical</a></li>
                </ul>
            </div>
            <div>
                <h4>CONTACT US</h4>
                <ul>
                    <li>Spintex Road, Accra</li>
                    <li>Monday–Saturday: 8:00 AM–6:00 PM</li>
                    <li>+233 24 516 8718</li>
                </ul>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>© {{ date('Y') }} ARK AND STAR ENTERPRISE. All rights reserved.</span>
            <span>Powered by Msoft Ghana</span>
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
</body>
</html>
