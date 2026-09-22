@extends('frontend.layout')

@section('content')
<style>
    .product-page { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .breadcrumb { margin-bottom: 20px; font-size: 0.9rem; color: #666; }
    .breadcrumb a { color: #0d5a39; text-decoration: none; }
    
    .product-layout { display: flex; gap: 40px; margin-bottom: 60px; }
    .product-gallery { flex: 1; }
    .gallery-main { width: 100%; height: 450px; object-fit: contain; border: 1px solid #eee; border-radius: 12px; padding: 20px; }
    .gallery-thumbs { display: flex; gap: 10px; margin-top: 15px; }
    .thumb { width: 80px; height: 80px; border: 2px solid #eee; border-radius: 8px; cursor: pointer; object-fit: contain; }
    .thumb.active { border-color: #0d5a39; }

    .product-info { flex: 1; }
    .product-info h1 { font-size: 2rem; margin-bottom: 10px; }
    .product-meta { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
    .product-meta .stock { color: #0d5a39; font-weight: 600; }
    .price-main { font-size: 2.2rem; color: #0d5a39; font-weight: 700; margin-bottom: 20px; }
    .product-details p { line-height: 1.6; color: #444; margin-bottom: 20px; }
    
    .metrics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
    .metric-item { background: #f8f9fa; padding: 15px; border-radius: 8px; }
    .metric-item span { display: block; font-size: 0.8rem; color: #666; margin-bottom: 5px; }
    .metric-item strong { font-size: 1.1rem; color: #222; }

    .cart-actions { display: flex; gap: 15px; align-items: center; }
    .qty-selector { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; }
    .qty-selector button { padding: 10px 15px; background: #f8f8f8; border: none; cursor: pointer; font-size: 1.2rem; }
    .qty-selector input { width: 50px; text-align: center; border: none; padding: 10px 0; }
    .btn-add-cart { flex: 1; padding: 12px 20px; background: #0d5a39; color: #fff; border: none; border-radius: 6px; font-size: 1.1rem; cursor: pointer; font-weight: 600; }

    /* Carousel */
    .similar-section { margin-top: 50px; border-top: 1px solid #eee; padding-top: 40px; }
    .similar-section h2 { text-align: center; margin-bottom: 30px; font-size: 1.8rem; }
    .carousel-wrapper { position: relative; overflow: hidden; }
    .carousel-track { display: flex; gap: 20px; overflow-x: auto; scroll-behavior: smooth; padding-bottom: 20px; }
    .carousel-track::-webkit-scrollbar { display: none; }
    .carousel-card { min-width: 250px; max-width: 250px; border: 1px solid #eee; border-radius: 12px; overflow: hidden; }
    .carousel-card img { width: 100%; height: 200px; object-fit: contain; background: #f8f8f8; }
    .carousel-body { padding: 15px; text-align: center; }
    .carousel-body h4 { margin: 10px 0; font-size: 1rem; height: 40px; overflow: hidden; }
    .carousel-body strong { color: #0d5a39; font-size: 1.2rem; }
</style>

<div class="product-page">
    <div class="breadcrumb">
        <a href="{{ route('shop.index') }}">Shop</a> / 
        @if($product->category) <a href="{{ route('shop.index', ['category' => \Illuminate\Support\Str::slug($product->category->name)]) }}">{{ $product->category->name }}</a> / @endif
        {{ $product->name }}
    </div>

    <div class="product-layout">
        <!-- Gallery -->
        <div class="product-gallery">
            <img src="{{ $product->image_url }}" id="mainImage" class="gallery-main" alt="{{ $product->name }}">
            @if(!empty($product->gallery))
                <div class="gallery-thumbs">
                    @foreach($product->gallery as $img)
                        <img src="{{ asset('images/product/' . $img) }}" class="thumb" onclick="changeMainImage(this, '{{ asset('images/product/' . $img) }}')" alt="Thumbnail">
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Info -->
        <div class="product-info">
            <span class="product-category" style="text-transform: uppercase; color: #666; font-weight: 600;">{{ $product->category->name ?? 'General' }}</span>
            <h1>{{ $product->name }}</h1>
            
            <div class="product-meta">
                <span class="stars">★★★★★</span>
                <span class="stock">In Stock: {{ $product->qty ?? 0 }} available</span>
            </div>

            <div class="price-main">GH₵ {{ number_format((float) $product->price, 2) }}</div>

            <div class="product-details">
                <h3>Product Details</h3>
                <p>{{ $product->product_details ?? 'No detailed description available for this product at the moment. Please contact us for more information.' }}</p>
            </div>

            <!-- Metrics -->
            <div class="metrics-grid">
                <div class="metric-item">
                    <span>Product Code</span>
                    <strong>{{ $product->code ?? 'N/A' }}</strong>
                </div>
                <div class="metric-item">
                    <span>Wholesale Price</span>
                    <strong>GH₵ {{ number_format((float)($product->wholesale_price ?? 0), 2) }}</strong>
                </div>
                <div class="metric-item">
                    <span>Category</span>
                    <strong>{{ $product->category->name ?? 'N/A' }}</strong>
                </div>
                <div class="metric-item">
                    <span>Availability</span>
                    <strong>{{ ($product->qty ?? 0) > 0 ? 'Ready to Ship' : 'Out of Stock' }}</strong>
                </div>
            </div>

            <!-- Add to Cart -->
            <form action="{{ route('cart.add', $product->id) }}" method="POST" class="cart-actions">
                @csrf
                <div class="qty-selector">
                    <button type="button" onclick="updateQty(-1)">-</button>
                    <input type="number" name="quantity" id="qtyInput" value="1" min="1" readonly>
                    <button type="button" onclick="updateQty(1)">+</button>
                </div>
                <button type="submit" class="btn-add-cart">Add to Cart</button>
            </form>
        </div>
    </div>

    <!-- Similar Products Carousel -->
    @if($relatedProducts->isNotEmpty())
    <section class="similar-section">
        <h2>Similar Products</h2>
        <div class="carousel-wrapper">
            <div class="carousel-track" id="carouselTrack">
                @foreach($relatedProducts as $related)
                    <div class="carousel-card">
                        <a href="{{ route('products.show', $related->id) }}" style="text-decoration:none; color:inherit;">
                            <img src="{{ $related->image_url }}" alt="{{ $related->name }}">
                            <div class="carousel-body">
                                <h4>{{ \Illuminate\Support\Str::limit($related->name, 30) }}</h4>
                                <strong>GH₵ {{ number_format((float) $related->price, 2) }}</strong>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>

<script>
    function changeMainImage(element, newSrc) {
        document.getElementById('mainImage').src = newSrc;
        // Remove active class from all
        document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
        // Add active to clicked
        element.classList.add('active');
    }

    function updateQty(change) {
        const input = document.getElementById('qtyInput');
        let newVal = parseInt(input.value) + change;
        if (newVal < 1) newVal = 1;
        input.value = newVal;
    }
</script>
@endsection

@push('scripts')
<!-- Include standard wishlist script from your original file if needed -->
@endpush