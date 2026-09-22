<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - arkandstar.com</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; color: #333; }
        
        /* Top Navigation */
        nav { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-size: 20px; font-weight: bold; color: #D4AF37; } /* Gold color */
        .nav-links a { text-decoration: none; color: #4b5563; font-size: 14px; margin-left: 20px; font-weight: 500; }
        .nav-links a:hover { color: #16a34a; }

        /* Main Container */
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        
        /* Welcome Section */
        .welcome-box { background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 30px; }
        .welcome-box h1 { font-size: 24px; color: #1f2937; margin-bottom: 10px; }
        .welcome-box p { color: #6b7280; font-size: 14px; line-height: 1.5; }
        .welcome-box p span { font-weight: 600; color: #333; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; text-align: center; }
        .stat-card h3 { font-size: 28px; color: #16a34a; margin-bottom: 5px; }
        .stat-card p { font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }

        /* History Sections */
        .history-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .history-card { background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .history-card h2 { font-size: 18px; color: #1f2937; border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px; }
        .empty-state { text-align: center; padding: 40px 0; }
        .empty-state svg { width: 50px; height: 50px; color: #d1d5db; margin-bottom: 15px; }
        .empty-state p { color: #6b7280; font-size: 14px; margin-bottom: 20px; }
        
        /* Buttons */
        .btn { display: inline-block; padding: 10px 20px; background-color: #16a34a; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; }
        .btn:hover { background-color: #15803d; }
        .btn-outline { background: transparent; color: #4b5563; border: 1px solid #d1d5db; margin-left: 10px; }
        .btn-outline:hover { background: #f3f4f6; color: #333; }

        /* Table Styles for Transactions */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; color: #6b7280; text-transform: uppercase; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb; }
        td { padding: 15px 0; font-size: 14px; color: #4b5563; }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <nav>
        <div class="nav-logo">Ark & Star Enterprise</div>
        <div class="nav-links">
            <a href="{{ route('shop.index') }}">Store</a>
            <a href="{{ route('customer.dashboard') }}">My account</a>
            <a href="{{ route('customer.wishlist') }}">Wishlist</a>
            <a href="#" onclick="document.getElementById('logout-form').submit();">Sign out</a>
            <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </nav>

    <div class="container">
        
        <!-- Welcome Section -->
        <div class="welcome-box">
            <h1>Hello, {{ $customer->name }}</h1>
            <p>
                Email: <span>{{ $customer->email }}</span><br>
                Phone: <span>{{ $customer->phone_number }}</span>
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>{{ $totalOrders }}</h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3>{{ $activeOrders }}</h3>
                <p>Active Orders</p>
            </div>
            <div class="stat-card">
                <h3>{{ $successfulPayments }}</h3>
                <p>Successful Payments</p>
            </div>
            <div class="stat-card">
                <h3>{{ $totalPayments }}</h3>
                <p>Total Payments</p>
            </div>
        </div>

        <!-- Order & Payment History Grid -->
        <div class="history-grid">
            
            <!-- Orders Section -->
            <div class="history-card">
                <h2>Your orders ({{ count($orders) }} records)</h2>
                @if(count($orders) > 0)
                    <!-- Table for orders would go here -->
                @else
                    <div class="empty-state">
                        <!-- Box Icon -->
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4m16 0V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7"/></svg>
                        <p>No orders yet</p>
                        <a href="{{ route('shop.index') }}" class="btn">Start shopping</a>
                    </div>
                @endif
            </div>

            <!-- Transactions Section -->
            <div class="history-card">
                <h2>Your transactions</h2>
                @if(count($transactions) > 0)
                    <!-- Table for transactions would go here -->
                @else
                    <div class="empty-state">
                        <p>No online payment records yet.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

</body>
</html>