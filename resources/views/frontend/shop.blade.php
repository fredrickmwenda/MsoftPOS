@extends('frontend.layout')

@section('content')
    <section class="store-hero">
        <div class="hero-copy">
            <span class="hero-badge">ARK AND STAR ENTERPRISE</span>
            <h1>Everything you need to <span>build better.</span></h1>
            <p>Affordable, reliable and durable tools, building materials and expert support for contractors, tradespeople and home projects.</p>

            <div class="hero-actions">
                <a href="#products" class="btn btn-primary">Shop products</a>
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
            <a href="{{ route('shop.index') }}">View all products →</a>
        </div>

        <div class="category-grid">
            @foreach ($categories as $category)
                <a href="{{ route('shop.index', ['category' => $category->slug]) }}#products" class="category-card">
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
            <a href="#products">Explore special offers →</a>
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
                    <article class="product-card highlight-card">
                        <div class="product-image-wrap">
                            <span class="product-tag">FEATURED</span>
                            <button class="wishlist-button" type="button">♡</button>
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                            <button class="quick-add" type="button">+</button>
                        </div>

                        <div class="product-body">
                            <span class="product-category">{{ strtoupper($product->category->name ?? 'General') }}</span>
                            <h3>{{ $product->name }}</h3>
                            <div class="product-meta">
                                <span>★★★★★</span>
                                <span>In stock: {{ $product->qty ?? 0 }}</span>
                            </div>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($product->product_details ?? $product->name), 88) }}</p>
                            <div class="product-footer">
                                <div class="price-box">
                                    <strong>GH₵ {{ number_format((float) $product->price, 2) }}</strong>
                                    @if (!empty($product->wholesale_price))
                                        <small>GH₵ {{ number_format((float) $product->wholesale_price, 2) }}</small>
                                    @endif
                                </div>
                                <button class="add-to-cart" type="button">Add to cart</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="catalog-section">
        <div class="section-heading top-heading">
            <div>
                <span class="eyebrow">SHOP ALL</span>
                <h2>Featured products</h2>
            </div>
            <span class="results-count">{{ $products->total() }} items available</span>
        </div>

        @if ($products->isEmpty())
            <div class="empty-state">
                <h3>No products found</h3>
                <p>Try another search or browse another category.</p>
            </div>
        @else
            <div id="store-products-grid" class="product-grid catalog-grid">
                @foreach ($products as $product)
                    <article class="product-card normal-card">
                        <div class="product-image-wrap">
                            <button class="wishlist-button" type="button">♡</button>
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                            <button class="quick-add" type="button">+</button>
                        </div>

                        <div class="product-body">
                            <span class="product-category">{{ strtoupper($product->category->name ?? 'General') }}</span>
                            <h3>{{ $product->name }}</h3>
                            <div class="product-meta">
                                <span>★★★★★</span>
                                <span>In stock: {{ $product->qty ?? 0 }}</span>
                            </div>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($product->product_details ?? $product->name), 88) }}</p>
                            <div class="product-footer">
                                <div class="price-box">
                                    <strong>GH₵ {{ number_format((float) $product->price, 2) }}</strong>
                                    @if (!empty($product->wholesale_price))
                                        <small>GH₵ {{ number_format((float) $product->wholesale_price, 2) }}</small>
                                    @endif
                                </div>
                                <button class="add-to-cart" type="button">Add to cart</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div id="store-pagination" class="pagination-wrap">
                {{ $products->links() }}
            </div>
        @endif
    </section>
@endsection
