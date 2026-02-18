@extends('backend.layout.main')
@section('content')
<style>
    /* Modern Color Scheme */
    :root {
        --primary: #2ecc71;
        --primary-dark: #27ae60;
        --primary-light: #a3e4b5;
        --secondary: #62cff4;
        --dark: #2c3e50;
        --light: #f8f9fa;
        --gray: #6c757d;
        --white: #ffffff;
        --shadow: 0 4px 20px rgba(0,0,0,0.08);
        --shadow-hover: 0 8px 25px rgba(46,204,113,0.15);
        --border-radius: 16px;
        --border-radius-sm: 12px;
    }

    /* Global Styles */
    .dashboard-counts {
        padding: 20px 0;
    }

    /* Welcome Section */
    .welcome-section {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: var(--border-radius);
        padding: 30px;
        margin-bottom: 30px;
        color: var(--white);
        box-shadow: var(--shadow);
    }

    .welcome-title {
        font-size: 2.2rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .welcome-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    /* Quick Action Cards */
    .action-card {
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        position: relative;
        background: var(--white);
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .action-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .action-card:hover img {
        transform: scale(1.05);
    }

    .action-content {
        padding: 20px;
        text-align: center;
        background: var(--white);
    }

    .action-btn {
        background: var(--primary);
        color: var(--white);
        border: none;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(46,204,113,0.3);
        display: inline-block;
        text-decoration: none;
    }

    .action-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(46,204,113,0.4);
        color: var(--white);
        text-decoration: none;
    }

    /* Date Filter Buttons */
    .filter-group {
        background: var(--white);
        padding: 10px;
        border-radius: 50px;
        box-shadow: var(--shadow);
        display: inline-flex;
        margin-bottom: 20px;
    }

    .filter-btn {
        border: none;
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: transparent;
        color: var(--gray);
    }

    .filter-btn.active {
        background: var(--primary);
        color: var(--white);
        box-shadow: 0 4px 10px rgba(46,204,113,0.3);
    }

    .filter-btn:hover:not(.active) {
        background: var(--light);
        color: var(--dark);
    }

    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--white);
        border-radius: var(--border-radius);
        padding: 25px;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border-left: 5px solid var(--primary);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .stat-icon {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 3rem;
        color: rgba(46,204,113,0.1);
        transition: all 0.3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.2);
        color: rgba(46,204,113,0.2);
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0;
    }

    .stat-trend {
        font-size: 0.8rem;
        color: var(--primary);
        margin-top: 5px;
    }

    /* Chart Cards - Fixed Height */
    .chart-card {
        background: var(--white);
        border-radius: var(--border-radius);
        padding: 20px;
        box-shadow: var(--shadow);
        margin-bottom: 30px;
        height: 100%;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--dark);
        margin: 0;
    }

    .chart-subtitle {
        font-size: 0.85rem;
        color: var(--gray);
        margin: 0;
    }

    /* Chart Container - Fixed Height */
    .chart-container {
        position: relative;
        height: 200px !important; /* Fixed height for all charts */
        width: 100%;
        margin: 0 auto;
    }

    .chart-container canvas {
        display: block;
        max-width: 100%;
        max-height: 100%;
        width: auto !important;
        height: auto !important;
    }

    /* Reports Card */
    .reports-card {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-top: 30px;
    }

    .reports-header {
        padding: 20px 25px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: var(--white);
    }

    .reports-header h4 {
        margin: 0;
        font-weight: 600;
    }

    /* Tabs */
    .reports-tabs {
        padding: 0 25px;
        background: var(--white);
        border-bottom: 2px solid var(--light);
    }

    .reports-tabs .nav-tabs {
        border: none;
    }

    .reports-tabs .nav-link {
        border: none;
        padding: 12px 20px;
        color: var(--gray);
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
        font-size: 0.95rem;
    }

    .reports-tabs .nav-link:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .reports-tabs .nav-link.active {
        color: var(--primary);
        background: transparent;
    }

    .reports-tabs .nav-link.active:after {
        transform: scaleX(1);
    }

    .reports-tabs .nav-link:hover {
        color: var(--primary);
        border: none;
    }

    /* Tables */
    .table-responsive {
        padding: 20px;
    }

    .table {
        margin: 0;
        font-size: 0.95rem;
    }

    .table thead th {
        border-top: none;
        border-bottom: 2px solid var(--primary);
        color: var(--dark);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 8px;
    }

    .table tbody td {
        padding: 12px 8px;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: background 0.3s ease;
    }

    .table tbody tr:hover {
        background: var(--light);
    }

    .badge-custom {
        padding: 5px 10px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.75rem;
        display: inline-block;
    }

    .badge-success {
        background: rgba(46,204,113,0.1);
        color: var(--primary);
    }

    .badge-danger {
        background: rgba(231,76,60,0.1);
        color: #e74c3c;
    }

    .badge-warning {
        background: rgba(241,196,15,0.1);
        color: #f1c40f;
    }

    /* Product Image in Tables */
    .product-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-image {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        object-fit: cover;
    }

    /* Legend Badges */
    .legend-badge {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 3px;
        margin-right: 5px;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        font-size: 0.8rem;
        color: var(--gray);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-group {
            flex-wrap: wrap;
            border-radius: var(--border-radius-sm);
        }

        .filter-btn {
            width: 100%;
        }

        .chart-container {
            height: 180px !important;
        }
    }

    @media (max-width: 576px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .chart-container {
            height: 160px !important;
        }
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate {
        animation: fadeInUp 0.5s ease forwards;
    }
</style>

@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif

@php
    $color = '#2ecc71';
    $color_rgba = 'rgba(46, 204, 113, 0.8)';
    $revenue_profit_summary = $role_has_permissions_list->where('name', 'revenue_profit_summary')->first();
@endphp

<!-- Welcome Section -->
<div class="welcome-section animate">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="welcome-title"><span id="greeting">Welcome</span>, {{Auth::user()->name}}!</h1>
            <p class="welcome-subtitle">Ready to pick it up from where you left? Here's what's happening with your business today.</p>
        </div>
        <div class="col-md-4 text-right">
            <i class="dripicons-calendar" style="font-size: 2.5rem; opacity: 0.5;"></i>
            <div class="mt-1">{{date('l, F d, Y')}}</div>
        </div>
    </div>
</div>

<!-- Quick Actions Row -->
<div class="row mb-4">
    <div class="col-md-4 animate" style="animation-delay: 0.1s">
        <div class="action-card">
            <img src="{{asset('/images/action.jpg')}}" alt="Quick Actions">
            <div class="action-content">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="dropdown">
                    <button class="action-btn dropdown-toggle" type="button" data-toggle="dropdown">
                        Select Action <i class="dripicons-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu">
                        @if($role_has_permissions_list->where('name', 'category')->first())
                            <li class="dropdown-item"><a data-toggle="modal" data-target="#category-modal">{{__('file.Add Category')}}</a></li>
                        @endif
                        @if($role_has_permissions_list->where('name', 'products-add')->first())
                            <li class="dropdown-item"><a href="{{route('products.create')}}">{{__('file.add_product')}}</a></li>
                        @endif
                        @if($role_has_permissions_list->where('name', 'purchases-add')->first())
                            <li class="dropdown-item"><a href="{{route('purchases.create')}}">{{trans('file.Add Purchase')}}</a></li>
                        @endif
                        @if($role_has_permissions_list->where('name', 'sales-add')->first())
                            <li class="dropdown-item"><a href="{{route('sales.create')}}">{{trans('file.Add Sale')}}</a></li>
                        @endif
                        @if($role_has_permissions_list->where('name', 'expenses-add')->first())
                            <li class="dropdown-item"><a data-toggle="modal" data-target="#expense-modal">{{trans('file.Add Expense')}}</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 animate" style="animation-delay: 0.2s">
        <div class="action-card">
            <img src="{{asset('/images/POSs.jpg')}}" alt="POS System">
            <div class="action-content">
                <h5 class="mb-3">Point of Sale</h5>
                <a @if($role_has_permissions_list->where('name', 'sales-add')->first()) href="{{url('/pos')}}" @else onclick="alert('You do not have permission')" @endif class="action-btn">
                    Open POS <i class="dripicons-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 animate" style="animation-delay: 0.3s">
        <div class="action-card">
            <img src="{{asset('/images/registers.jpg')}}" alt="Cash Register">
            <div class="action-content">
                <h5 class="mb-3">Cash Register</h5>
                <a href="{{url('/cash-register')}}" class="action-btn">
                    Manage Register <i class="dripicons-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@if($revenue_profit_summary)
<!-- Date Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="filter-group">
            <button class="filter-btn date-btn" data-start_date="{{date('Y-m-d')}}" data-end_date="{{date('Y-m-d')}}">Today</button>
            <button class="filter-btn date-btn" data-start_date="{{date('Y-m-d', strtotime(' -7 day'))}}" data-end_date="{{date('Y-m-d')}}">Last 7 Days</button>
            <button class="filter-btn date-btn active" data-start_date="{{date('Y').'-'.date('m').'-'.'01'}}" data-end_date="{{date('Y-m-d')}}">This Month</button>
            <button class="filter-btn date-btn" data-start_date="{{date('Y').'-01'.'-01'}}" data-end_date="{{date('Y').'-12'.'-31'}}">This Year</button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-row">
    <div class="stat-card animate" style="animation-delay: 0.4s">
        <i class="stat-icon dripicons-graph-bar"></i>
        <div class="stat-label">Revenue Earned</div>
        <div class="stat-value revenue-data">{{number_format((float)$revenue,$general_setting->decimal, '.', '')}}</div>
        <div class="stat-trend"><i class="dripicons-arrow-up"></i> +12% from last month</div>
    </div>

    <div class="stat-card animate" style="animation-delay: 0.5s">
        <i class="stat-icon dripicons-return"></i>
        <div class="stat-label">Sale Returns</div>
        <div class="stat-value return-data">{{number_format((float)$return,$general_setting->decimal, '.', '')}}</div>
        <div class="stat-trend"><i class="dripicons-arrow-down"></i> -3% from last month</div>
    </div>

    <div class="stat-card animate" style="animation-delay: 0.6s">
        <i class="stat-icon dripicons-media-loop"></i>
        <div class="stat-label">Purchase Returns</div>
        <div class="stat-value purchase_return-data">{{number_format((float)$purchase_return,$general_setting->decimal, '.', '')}}</div>
        <div class="stat-trend"><i class="dripicons-arrow-up"></i> +5% from last month</div>
    </div>

    <div class="stat-card animate" style="animation-delay: 0.7s">
        <i class="stat-icon dripicons-trophy"></i>
        <div class="stat-label">Net Profit</div>
        <div class="stat-value profit-data">{{number_format((float)$profit,$general_setting->decimal, '.', '')}}</div>
        <div class="stat-trend"><i class="dripicons-arrow-up"></i> +18% from last month</div>
    </div>
</div>

<!-- Charts Row - With Fixed Height -->
<div class="row mb-4">
    @if($role_has_permissions_list->where('name', 'cash_flow')->first())
    <div class="col-md-6 animate" style="animation-delay: 0.8s">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h5 class="chart-title">Cash Flow Analysis</h5>
                    <p class="chart-subtitle">Monthly payment comparison</p>
                </div>
                <div>
                    <span class="legend-item">
                        <span class="legend-badge" style="background: #2ecc71;"></span> Received
                    </span>
                    <span class="legend-item">
                        <span class="legend-badge" style="background: #3498db;"></span> Sent
                    </span>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="cashFlow" 
                    data-color="#00cc66" 
                    data-color_rgba="{{$color_rgba}}" 
                    data-recieved="{{json_encode($payment_recieved)}}" 
                    data-sent="{{json_encode($payment_sent)}}" 
                    data-month="{{json_encode($month)}}" 
                    data-label1="Payment Recieved" 
                    data-label2="Payment Sent">
                </canvas>
            </div>
        </div>
    </div>
    @endif

    @if($role_has_permissions_list->where('name', 'yearly_report')->first())
    <div class="col-md-6 animate" style="animation-delay: 0.9s">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h5 class="chart-title">Yearly Performance</h5>
                    <p class="chart-subtitle">Sales vs Purchases overview</p>
                </div>
                <div>
                    <span class="legend-item">
                        <span class="legend-badge" style="background: #2ecc71;"></span> Sales
                    </span>
                    <span class="legend-item">
                        <span class="legend-badge" style="background: #3498db;"></span> Purchases
                    </span>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="saleChart" 
                    data-color="#2ecc71" 
                    data-color_rgba="{{$color_rgba}}" 
                    data-sale_chart_value="{{json_encode($yearly_sale_amount)}}" 
                    data-purchase_chart_value="{{json_encode($yearly_purchase_amount)}}" 
                    data-label1="Purchased Amount" 
                    data-label2="Sold Amount">
                </canvas>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Monthly Summary - With Fixed Height -->
@if($role_has_permissions_list->where('name', 'monthly_summary')->first())
<div class="row mb-4">
    <div class="col-12 animate" style="animation-delay: 1s">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h5 class="chart-title">{{date('F')}} {{date('Y')}} - Transaction Summary</h5>
                    <p class="chart-subtitle">Revenue, purchases and expenses breakdown</p>
                </div>
                <div>
                    <span class="legend-item">
                        <span class="legend-badge" style="background: #f1c40f;"></span> Purchases
                    </span>
                    <span class="legend-item">
                        <span class="legend-badge" style="background: #2ecc71;"></span> Revenue
                    </span>
                    <span class="legend-item">
                        <span class="legend-badge" style="background: #e74c3c;"></span> Expenses
                    </span>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="transactionChart" 
                    data-color="{{$color}}" 
                    data-color_rgba="{{$color_rgba}}" 
                    data-revenue="{{$revenue}}" 
                    data-purchase="{{$purchase}}" 
                    data-expense="{{$expense}}" 
                    data-label1="Purchase" 
                    data-label2="Revenue" 
                    data-label3="Expense">
                </canvas>
            </div>
        </div>
    </div>
</div>
@endif

@endif <!-- Missing endif fixed -->

<!-- Reports Center -->
<div class="reports-card animate" style="animation-delay: 1.1s">
    <div class="reports-header">
        <h4><i class="dripicons-document"></i> Reports Center</h4>
    </div>

    <div class="reports-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#sale-latest" role="tab" data-toggle="tab">Recent Sales</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#purchase-latest" role="tab" data-toggle="tab">Recent Purchases</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#quotation-latest" role="tab" data-toggle="tab">Recent Quotations</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#payment-latest" role="tab" data-toggle="tab">Recent Payments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#seller-latest" role="tab" data-toggle="tab">Best Sellers</a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- Recent Sales Tab -->
        <div role="tabpanel" class="tab-pane fade show active" id="sale-latest">
            <div class="table-responsive">
                <table id="recent-sale" class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th class="text-right">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Recent Purchases Tab -->
        <div role="tabpanel" class="tab-pane fade" id="purchase-latest">
            <div class="table-responsive">
                <table id="recent-purchase" class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th class="text-right">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Recent Quotations Tab -->
        <div role="tabpanel" class="tab-pane fade" id="quotation-latest">
            <div class="table-responsive">
                <table id="recent-quotation" class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th class="text-right">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments Tab -->
        <div role="tabpanel" class="tab-pane fade" id="payment-latest">
            <div class="table-responsive">
                <table id="recent-payment" class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th class="text-right">Amount</th>
                            <th>Paid By</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Best Sellers Tab -->
        <div role="tabpanel" class="tab-pane fade" id="seller-latest">
            <div class="reports-tabs">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="#purchase-latest3" role="tab" data-toggle="tab">Best Seller (This Month)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#quotation-latest3" role="tab" data-toggle="tab">Best Seller (Year - Qty)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#payment-latest3" role="tab" data-toggle="tab">Best Seller (Year - Value)</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade show active" id="purchase-latest3">
                    <div class="table-responsive">
                        <table id="monthly-best-selling-qty" class="table">
                            <thead>
                                <tr>
                                    <th>Product Details</th>
                                    <th class="text-right">Quantity Sold</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane fade" id="quotation-latest3">
                    <div class="table-responsive">
                        <table id="yearly-best-selling-qty" class="table">
                            <thead>
                                <tr>
                                    <th>Product Details</th>
                                    <th class="text-right">Quantity Sold</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane fade" id="payment-latest3">
                    <div class="table-responsive">
                        <table id="yearly-best-selling-price" class="table">
                            <thead>
                                <tr>
                                    <th>Product Details</th>
                                    <th class="text-right">Total Value</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // Dynamic Greeting
    var today = new Date();
    var curHr = today.getHours();
    
    if (curHr >= 0 && curHr < 4) {
        document.getElementById("greeting").innerHTML = 'Good Night';
    } else if (curHr >= 4 && curHr < 12) {
        document.getElementById("greeting").innerHTML = 'Good Morning';
    } else if (curHr >= 12 && curHr < 16) {
        document.getElementById("greeting").innerHTML = 'Good Afternoon';
    } else {
        document.getElementById("greeting").innerHTML = 'Good Evening';
    }

    // Load all tables
    loadYearlyBestSellingPrice();
    loadYearlyBestSellingQty();
    loadMonthlyBestSellingQty();
    loadRecentSales();
    loadRecentPurchases();
    loadRecentQuotations();
    loadRecentPayments();

    // Date filter functionality
    $(".filter-btn").on("click", function() {
        $(".filter-btn").removeClass("active");
        $(this).addClass("active");
        
        var start_date = $(this).data('start_date');
        var end_date = $(this).data('end_date');
        
        $.get('dashboard-filter/' + start_date + '/' + end_date, function(data) {
            dashboardFilter(data);
        });
    });
});

// Table loading functions
function loadYearlyBestSellingPrice() {
    $.ajax({
        url: '{{url("/yearly-best-selling-price")}}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var url = '{{url("/images/product")}}';
            var tbody = $('#yearly-best-selling-price').find('tbody');
            tbody.empty();
            
            data.forEach(function(item) {
                var images = item.product_images ? item.product_images.split('|') : ['zummXD2dvAtI.png'];
                tbody.append(`
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="${url}/${images[0]}" class="product-image">
                                <span>${item.product_name} [${item.product_code}]</span>
                            </div>
                        </td>
                        <td class="text-right font-weight-bold">${parseFloat(item.total_price).toFixed(2)}</td>
                    </tr>
                `);
            });
        }
    });
}

function loadYearlyBestSellingQty() {
    $.ajax({
        url: '{{url("/yearly-best-selling-qty")}}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var url = '{{url("/images/product")}}';
            var tbody = $('#yearly-best-selling-qty').find('tbody');
            tbody.empty();
            
            data.forEach(function(item) {
                var images = item.product_images ? item.product_images.split('|') : ['zummXD2dvAtI.png'];
                tbody.append(`
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="${url}/${images[0]}" class="product-image">
                                <span>${item.product_name} [${item.product_code}]</span>
                            </div>
                        </td>
                        <td class="text-right font-weight-bold">${item.sold_qty}</td>
                    </tr>
                `);
            });
        }
    });
}

function loadMonthlyBestSellingQty() {
    $.ajax({
        url: '{{url("/monthly-best-selling-qty")}}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var url = '{{url("/images/product")}}';
            var tbody = $('#monthly-best-selling-qty').find('tbody');
            tbody.empty();
            
            data.forEach(function(item) {
                var images = item.product_images ? item.product_images.split('|') : ['zummXD2dvAtI.png'];
                tbody.append(`
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="${url}/${images[0]}" class="product-image">
                                <span>${item.product_name} [${item.product_code}]</span>
                            </div>
                        </td>
                        <td class="text-right font-weight-bold">${item.sold_qty}</td>
                    </tr>
                `);
            });
        }
    });
}

function loadRecentSales() {
    $.ajax({
        url: '{{url("/recent-sale")}}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var tbody = $('#recent-sale').find('tbody');
            tbody.empty();
            
            data.forEach(function(item) {
                var sale_date = dateFormat(item.created_at.split('T')[0], '{{$general_setting->date_format}}');
                var statusClass = '';
                var statusText = '';
                
                if(item.sale_status == 1) {
                    statusClass = 'badge-success';
                    statusText = 'Completed';
                } else if(item.sale_status == 2) {
                    statusClass = 'badge-danger';
                    statusText = 'Pending';
                } else {
                    statusClass = 'badge-warning';
                    statusText = 'Draft';
                }
                
                tbody.append(`
                    <tr>
                        <td>${sale_date}</td>
                        <td><strong>${item.reference_no}</strong></td>
                        <td>${item.name}</td>
                        <td><span class="badge-custom ${statusClass}">${statusText}</span></td>
                        <td class="text-right font-weight-bold">${parseFloat(item.grand_total).toFixed(2)}</td>
                    </tr>
                `);
            });
        }
    });
}

function loadRecentPurchases() {
    $.ajax({
        url: '{{url("/recent-purchase")}}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var tbody = $('#recent-purchase').find('tbody');
            tbody.empty();
            
            data.forEach(function(item) {
                var payment_date = dateFormat(item.created_at.split('T')[0], '{{$general_setting->date_format}}');
                var statusClass = '';
                var statusText = '';
                
                if(item.payment_status == 1) {
                    statusClass = 'badge-success';
                    statusText = 'Completed';
                } else if(item.payment_status == 2) {
                    statusClass = 'badge-danger';
                    statusText = 'Pending';
                } else {
                    statusClass = 'badge-warning';
                    statusText = 'Draft';
                }
                
                tbody.append(`
                    <tr>
                        <td>${payment_date}</td>
                        <td><strong>${item.reference_no}</strong></td>
                        <td>${item.name}</td>
                        <td><span class="badge-custom ${statusClass}">${statusText}</span></td>
                        <td class="text-right font-weight-bold">${parseFloat(item.grand_total).toFixed(2)}</td>
                    </tr>
                `);
            });
        }
    });
}

function loadRecentQuotations() {
    $.ajax({
        url: '{{url("/recent-quotation")}}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var tbody = $('#recent-quotation').find('tbody');
            tbody.empty();
            
            data.forEach(function(item) {
                var quotation_date = dateFormat(item.created_at.split('T')[0], '{{$general_setting->date_format}}');
                var statusClass = '';
                var statusText = '';
                
                if(item.quotation_status == 1) {
                    statusClass = 'badge-success';
                    statusText = 'Completed';
                } else if(item.quotation_status == 2) {
                    statusClass = 'badge-danger';
                    statusText = 'Pending';
                } else {
                    statusClass = 'badge-warning';
                    statusText = 'Draft';
                }
                
                tbody.append(`
                    <tr>
                        <td>${quotation_date}</td>
                        <td><strong>${item.reference_no}</strong></td>
                        <td>${item.name}</td>
                        <td><span class="badge-custom ${statusClass}">${statusText}</span></td>
                        <td class="text-right font-weight-bold">${parseFloat(item.grand_total).toFixed(2)}</td>
                    </tr>
                `);
            });
        }
    });
}

function loadRecentPayments() {
    $.ajax({
        url: '{{url("/recent-payment")}}',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var tbody = $('#recent-payment').find('tbody');
            tbody.empty();
            
            data.forEach(function(item) {
                var payment_date = dateFormat(item.created_at.split('T')[0], '{{$general_setting->date_format}}');
                tbody.append(`
                    <tr>
                        <td>${payment_date}</td>
                        <td><strong>${item.payment_reference}</strong></td>
                        <td class="text-right font-weight-bold">${parseFloat(item.amount).toFixed(2)}</td>
                        <td>${item.paying_method}</td>
                    </tr>
                `);
            });
        }
    });
}

function dateFormat(inputDate, format) {
    const date = new Date(inputDate);
    const day = date.getDate();
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    
    format = format.replace("m", month.toString().padStart(2,"0"));
    format = format.replace("Y", year.toString());
    format = format.replace("d", day.toString().padStart(2,"0"));
    
    return format;
}

function dashboardFilter(data) {
    $('.revenue-data').fadeOut(300, function() {
        $(this).html(parseFloat(data[0]).toFixed({{$general_setting->decimal}})).fadeIn(300);
    });
    
    $('.return-data').fadeOut(300, function() {
        $(this).html(parseFloat(data[1]).toFixed({{$general_setting->decimal}})).fadeIn(300);
    });
    
    $('.profit-data').fadeOut(300, function() {
        $(this).html(parseFloat(data[2]).toFixed({{$general_setting->decimal}})).fadeIn(300);
    });
    
    $('.purchase_return-data').fadeOut(300, function() {
        $(this).html(parseFloat(data[3]).toFixed({{$general_setting->decimal}})).fadeIn(300);
    });
}

// Chart initialization with fixed height
$(document).ready(function() {
    // Cash Flow Chart
    var cashFlowCanvas = document.getElementById("cashFlow");
    if (cashFlowCanvas) {
        var ctx = cashFlowCanvas.getContext('2d');
        var dataRecieved = JSON.parse(cashFlowCanvas.dataset.recieved);
        var dataSent = JSON.parse(cashFlowCanvas.dataset.sent);
        var months = JSON.parse(cashFlowCanvas.dataset.month);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: cashFlowCanvas.dataset.label1,
                        data: dataRecieved,
                        backgroundColor: 'rgba(46, 204, 113, 0.8)',
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8,
                    },
                    {
                        label: cashFlowCanvas.dataset.label2,
                        data: dataSent,
                        backgroundColor: 'rgba(52, 152, 219, 0.8)',
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 12 },
                        bodyFont: { size: 11 },
                        padding: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [3, 3],
                            drawBorder: false,
                        },
                        ticks: {
                            font: { size: 10 }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 10 },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 5,
                        bottom: 5
                    }
                }
            }
        });
    }

    // Sales Chart
    var saleCanvas = document.getElementById("saleChart");
    if (saleCanvas) {
        var ctx = saleCanvas.getContext('2d');
        var saleData = JSON.parse(saleCanvas.dataset.sale_chart_value);
        var purchaseData = JSON.parse(saleCanvas.dataset.purchase_chart_value);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: saleCanvas.dataset.label2,
                        data: saleData,
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.05)',
                        tension: 0.3,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: saleCanvas.dataset.label1,
                        data: purchaseData,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.05)',
                        tension: 0.3,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 12 },
                        bodyFont: { size: 11 },
                        padding: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [3, 3],
                            drawBorder: false,
                        },
                        ticks: {
                            font: { size: 10 }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 10 }
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 5,
                        bottom: 5
                    }
                }
            }
        });
    }

    // Transaction Chart
    var transactionCanvas = document.getElementById("transactionChart");
    if (transactionCanvas) {
        var ctx = transactionCanvas.getContext('2d');
        var revenue = parseFloat(transactionCanvas.dataset.revenue) || 0;
        var purchase = parseFloat(transactionCanvas.dataset.purchase) || 0;
        var expense = parseFloat(transactionCanvas.dataset.expense) || 0;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [transactionCanvas.dataset.label1, transactionCanvas.dataset.label2, transactionCanvas.dataset.label3],
                datasets: [{
                    data: [purchase, revenue, expense],
                    backgroundColor: ['#f1c40f', '#2ecc71', '#e74c3c'],
                    borderWidth: 0,
                    hoverOffset: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 12 },
                        bodyFont: { size: 11 },
                        padding: 8,
                    }
                },
                cutout: '65%',
                layout: {
                    padding: {
                        top: 5,
                        bottom: 5
                    }
                }
            }
        });
    }
});
</script>
@endpush