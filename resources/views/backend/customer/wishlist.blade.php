<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - arkandstar.com</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: #f7f5f1; color: #17342b; }
        
        nav { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-size: 20px; font-weight: 800; color: #0d5a39; text-decoration: none; }
        .nav-links a { text-decoration: none; color: #5d6d68; font-size: 14px; margin-left: 20px; font-weight: 600; }
        .nav-links a:hover { color: #0d5a39; }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        
        .wishlist-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .wishlist-header h1 { font-size: 28px; color: #0d5a39; }
        .wishlist-header a { color: #0d5a39; text-decoration: none; font-weight: 600; font-size: 14px; }
        
        .alert-success { background: #f0fdf4; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #bbf7d0; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fecaca; font-size: 14px; }

        .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        
        .wishlist-card { background: #fff; border-radius: 10px; border: 1px solid rgba(13, 90, 57, 0.12); overflow: hidden; position: relative; display: flex; flex-direction: column; }
        .wishlist-card img { width: 100%; height: 220px; object-fit: cover; background: #ecf0ed; }
        .card-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .card-body h3 { font-size: 18px; color: #0d5a39; margin-bottom: 8px; }
        .card-body p { font-size: 14px; color: #5d6d68; margin-bottom: 15px; flex-grow: 1; }
        
        .card-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f3f4f6; padding-top: 15px; }
        .price-text { font-size: 18px; font-weight: 800; color: #17342b; }
        
        .btn { display: inline-block; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 700; cursor: pointer; border: none; }
        .btn-cart { background: #0d5a39; color: #fff; }
        .btn-remove { background: #fee2e2; color: #b91c1c; position: absolute; top: 10px; right: 10px; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 18px; }

        .empty-state { text-align: center; background: #fff; padding: 60px 20px; border-radius: 10px; border: 1px solid rgba(13, 90, 57, 0.12); }
        .empty-state svg { width: 60px; height: 60px; color: #d1d5db; margin-bottom: 20px; }
        .empty-state h3 { font-size: 20px; color: #17342b; margin-bottom: 10px; }
        .empty-state p { color: #5d6d68; font-size: 14px; margin-bottom: 25px; }
        .btn-shop { background: #0d5a39; color: #fff; padding: 12px 24px; }
    </style>
</head>
<body>

    <nav>
        <a href="{{ route('shop.index') }}" class="nav-logo">ARK AND STAR ENTERPRISE</a>
        <div class="nav-links">
            <a href="{{ route('shop.index') }}">Store</a>
            <a href="{{ route('customer.dashboard') }}">My Account</a>
            <a href="{{ route('customer.wishlist') }}">Wishlist</a>
            <form action="{{ route('customer.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #5d6d68; font-size: 14px; font-weight: 600; cursor: pointer; padding: 0;">Sign out</button>
            </form>
        </div>
    </nav>

    <div class="container">
        <div class="wishlist-header">
            <h1>Your Wishlist</h1>
            <a href="{{ route('shop.index') }}">← Continue shopping</a>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @if($wishlistItems->count() > 0)
            <div class="wishlist-grid">
                @foreach($wishlistItems as $item)
                    <div class="wishlist-card">
                        <a href="{{ route('wishlist.remove', $item->id) }}" class="btn btn-remove" title="Remove" onclick="return confirm('Remove this item?')">×</a>
                        <!-- Update the link above to: route('wishlist.remove', $item->id) -->
                        
                        <img src="{{ $item->product->image_url ?? asset('images/placeholder.png') }}" alt="{{ $item->product->name }}">
                        
                        <div class="card-body">
                            <h3>{{ $item->product->name }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($item->product->product_details ?? ''), 60) }}</p>
                            
                            <div class="card-footer">
                                <span class="price-text">GH₵ {{ number_format($item->product->price, 2) }}</span>
                                <form action="{{ route('cart.add', $item->product->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-cart">Add to cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h3>Your wishlist is empty</h3>
                <p>Save your favorite items here to view them later.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-shop">Discover Products</a>
            </div>
        @endif
    </div>

</body>
</html>