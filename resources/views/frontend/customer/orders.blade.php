<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - arkandstar.com</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; color: #333; }
        nav { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo { font-size: 20px; font-weight: bold; color: #D4AF37; }
        .nav-links a { text-decoration: none; color: #4b5563; font-size: 14px; margin-left: 20px; font-weight: 500; }
        .nav-links a:hover { color: #16a34a; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .page-header { margin-bottom: 25px; }
        .page-header h1 { font-size: 26px; color: #1f2937; margin-bottom: 5px; }
        .page-header p { color: #6b7280; font-size: 14px; }
        .card { background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead th { text-align: left; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; padding: 15px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        tbody td { padding: 16px 20px; font-size: 14px; color: #4b5563; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tbody tr:hover { background: #f9fafb; }
        tbody tr:last-child td { border-bottom: none; }
        .ref { font-weight: 600; color: #1f2937; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-processing{ background: #dbeafe; color: #1e40af; }
        .badge-paid      { background: #d1fae5; color: #065f46; }
        .badge-failed    { background: #fee2e2; color: #991b1b; }
        .btn { display: inline-block; padding: 8px 16px; background-color: #16a34a; color: white; text-decoration: none; border-radius: 5px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; }
        .btn:hover { background-color: #15803d; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-outline { background: transparent; color: #4b5563; border: 1px solid #d1d5db; }
        .btn-outline:hover { background: #f3f4f6; color: #333; }
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state svg { width: 60px; height: 60px; color: #d1d5db; margin-bottom: 15px; }
        .empty-state p { color: #6b7280; font-size: 14px; margin-bottom: 20px; }
        .pagination { margin-top: 25px; display: flex; justify-content: space-between; align-items: center; }
        .pagination a, .pagination span { padding: 8px 14px; border: 1px solid #e5e7eb; border-radius: 5px; text-decoration: none; color: #4b5563; font-size: 13px; }
        .pagination a:hover { background: #f3f4f6; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <nav>
        <div class="nav-logo">Ark & Star Enterprise</div>
        <div class="nav-links">
            <a href="{{ route('shop.index') }}">Store</a>
            <a href="{{ route('customer.dashboard') }}">My account</a>
            <a href="{{ route('customer.orders') }}">Orders</a>
            <a href="#" onclick="document.getElementById('logout-form').submit();">Sign out</a>
            <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>My Orders</h1>
            <p>Track, review and manage all your purchases.</p>
        </div>

        @if($sales->isEmpty())
            <div class="card">
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4m16 0V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7"/></svg>
                    <p>You haven't placed any orders yet.</p>
                    <a href="{{ route('shop.index') }}" class="btn">Start shopping</a>
                </div>
            </div>
        @else
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Order Ref</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Delivery</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            @php
                                $paymentLabel = [
                                    1 => ['Pending', 'badge-pending'],
                                    2 => ['Processing', 'badge-processing'],
                                    4 => ['Paid', 'badge-paid'],
                                    5 => ['Failed', 'badge-failed'],
                                ][$sale->payment_status] ?? ['Unknown', 'badge-pending'];
                            @endphp
                            <tr>
                                <td class="ref">{{ $sale->reference_no }}</td>
                                <td>{{ $sale->created_at->format('M d, Y') }}</td>
                                <td>{{ $sale->productSales->count() }} item(s)</td>
                                <td>₦{{ number_format($sale->total_amount, 2) }}</td>
                                <td><span class="badge {{ $paymentLabel[1] }}">{{ $paymentLabel[0] }}</span></td>
                                <td>
                                    @if($sale->delivery)
                                        <span class="badge {{ $sale->delivery->status == 3 ? 'badge-paid' : 'badge-processing' }}">
                                            {{ $sale->delivery->status_label ?? 'Processing' }}
                                        </span>
                                    @else
                                        <span class="badge badge-pending">N/A</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('customer.orders.show', $sale) }}" class="btn btn-sm">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <div>{{ $sales->firstItem() }} – {{ $sales->lastItem() }} of {{ $sales->total() }}</div>
                <div>
                    {{ $sales->links() }}
                </div>
            </div>
        @endif
    </div>
</body>
</html>