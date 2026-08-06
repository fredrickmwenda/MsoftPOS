@extends('backend.layout.main')

@section('content')
<div class="container-fluid px-4 py-5">
    <!-- Page Header -->
    <div class="mb-5">
        <div class="row align-items-center"> 
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="dripicons dripicons-document"></i> Reports Dashboard
                </h1>
                <p class="text-muted lead">Access all your business reports in one place</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="card bg-light border-0">
                    <div class="card-body"> 
                        <small class="text-muted">Total Reports</small>
                        <h3 class="mb-0">26</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Tabs (replaces filter & search) --}}
    <div class="category-tabs-container mb-4">
        <div class="category-tabs">
            <div class="category-tab active" data-category="sales">
                <i class="dripicons dripicons-graph-line me-1"></i> Sales
            </div>
            <div class="category-tab" data-category="purchase">
                <i class="dripicons dripicons-box me-1"></i> Purchase
            </div>
            <div class="category-tab" data-category="warehouse">
                <i class="dripicons dripicons-store me-1"></i> Warehouse
            </div>
            <div class="category-tab" data-category="financial">
                <i class="dripicons dripicons-pulse me-1"></i> Financial
            </div>
            <div class="category-tab" data-category="customer">
                <i class="dripicons dripicons-user me-1"></i> Customer
            </div>
            <div class="category-tab" data-category="supplier">
                <i class="dripicons dripicons-briefcase me-1"></i> Supplier
            </div>
            <div class="category-tab" data-category="product">
                <i class="dripicons dripicons-box me-1"></i> Product
            </div>
        </div>
    </div>

    <!-- Reports Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="reports-grid" id="reportsGrid">
                <!-- Sales Reports Section -->
                <div class="report-item" data-category="sales" data-name="Daily Sales">
                    <a href="{{ url('report/daily_sale/'.date('Y').'/'.date('m')) }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-graph-line"></i></div>
                        <h6>Daily Sales</h6>
                        <span class="badge bg-success">Sales</span>
                    </a>
                </div>

                <div class="report-item" data-category="sales" data-name="Monthly Sales">
                    <a href="{{ url('report/monthly_sale/'.date('Y')) }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-document"></i></div>
                        <h6>Monthly Sales</h6>
                        <span class="badge bg-success">Sales</span>
                    </a>
                </div>

                <div class="report-item" data-category="sales" data-name="Best Sellers">
                    <a href="{{ url('report/best_seller') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-star"></i></div>
                        <h6>Best Sellers</h6>
                        <span class="badge bg-success">Sales</span>
                    </a>
                </div>

                <!-- Sale Details remains a simple link -->
                <div class="report-item" data-category="sales" data-name="Sale Details">
                    {!! Form::open(['route' => 'report.sale', 'method' => 'post', 'id' => 'sale-details-form']) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="warehouse_id" value="0">
                    <a href="#" onclick="this.closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-cart"></i></div>
                        <h6>Sale Details</h6>
                        <span class="badge bg-success">Sales</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <div class="report-item" data-category="sales" data-name="Daily Sales Objective">
                    <a href="{{ route('report.dailySaleObjective') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-rocket"></i></div>
                        <h6>Daily Sales Objective</h6>
                        <span class="badge bg-success">Sales</span>
                    </a>
                </div>

                <div class="report-item" data-category="sales" data-name="Sales Person Report">
                    {!! Form::open(['route' => 'report.salesPerson', 'method' => 'post', 'id' => 'sales-person-form-' . uniqid()]) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d')}}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="warehouse_id" value="0">
                    <a href="#" onclick="$(this).closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-user-id"></i></div>
                        <h6>Sales Person Report</h6>
                        <span class="badge bg-success">Sales</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <!-- Purchase Reports Section -->
                <div class="report-item" data-category="purchase" data-name="Daily Purchases">
                    <a href="{{ url('report/daily_purchase/'.date('Y').'/'.date('m')) }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-box"></i></div>
                        <h6>Daily Purchases</h6>
                        <span class="badge bg-info">Purchase</span>
                    </a>
                </div>

                <div class="report-item" data-category="purchase" data-name="Monthly Purchases">
                    <a href="{{ url('report/monthly_purchase/'.date('Y')) }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-document"></i></div>
                        <h6>Monthly Purchases</h6>
                        <span class="badge bg-info">Purchase</span>
                    </a>
                </div>

                <!-- Purchase Details remains a simple link -->
                <div class="report-item" data-category="purchase" data-name="Purchase Details">
                    {!! Form::open(['route' => 'report.purchase', 'method' => 'post', 'id' => 'purchase-details-form']) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="warehouse_id" value="0">
                    <a href="#" onclick="this.closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-list"></i></div>
                        <h6>Purchase Details</h6>
                        <span class="badge bg-info">Purchase</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <!-- Warehouse Reports Section -->
                <div class="report-item" data-category="warehouse" data-name="Warehouse Stock">
                    <a href="{{ route('report.warehouseStock') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-store"></i></div>
                        <h6>Warehouse Stock</h6>
                        <span class="badge bg-warning">Warehouse</span>
                    </a>
                </div>

                <div class="report-item" data-category="warehouse" data-name="Stock Taking">
                    <a href="{{ route('report.stockTaking') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-graph-line"></i></div>
                        <h6>Stock Taking Report</h6>
                        <span class="badge bg-success">Warehouse</span>
                    </a>
                </div>

                {{-- ⭐ NEW: Stock Coverage Report --}}
                <div class="report-item" data-category="warehouse" data-name="Stock Coverage">
                    <a href="{{ route('stock-coverage') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-checklist"></i></div>
                        <h6>Stock Coverage Report</h6>
                        <span class="badge bg-warning">Warehouse</span>
                    </a>
                </div>

                <div class="report-item" data-category="warehouse" data-name="Stock Report">
                    <a href="{{ route('report.warehouseStockReport') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-stack"></i></div>
                        <h6>Stock Report</h6>
                        <span class="badge bg-warning">Warehouse</span>
                    </a>
                </div>

                <div class="report-item" data-category="warehouse" data-name="Warehouse Analysis">
                    <a href="{{ route('report.warehouse') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-briefcase"></i></div>
                        <h6>Warehouse Analysis</h6>
                        <span class="badge bg-warning">Warehouse</span>
                    </a>
                </div>

                <!-- Financial Reports Section -->
                {{-- Summary Report (old Profit & Loss summary) --}}
                <div class="report-item" data-category="financial" data-name="Summary Report">
                    {!! Form::open(['route' => 'report.profitLoss', 'method' => 'post', 'id' => 'profitLoss-form-' . uniqid()]) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <a href="#" onclick="$(this).closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-pulse"></i></div>
                        <h6>Summary Report</h6>
                        <span class="badge bg-danger">Financial</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                {{-- Detailed Profit & Loss (yearly P&L statement) --}}
                <div class="report-item" data-category="financial" data-name="Profit & Loss Report">
                    {!! Form::open(['route' => 'report.profitLossData', 'method' => 'post', 'id' => 'profitLossData-form-' . uniqid()]) !!}
                    <input type="hidden" name="year" value="{{ date('Y') }}">
                    <a href="#" onclick="$(this).closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-graph-line"></i></div>
                        <h6>Profit &amp; Loss Report</h6>
                        <span class="badge bg-danger">Financial</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <div class="report-item" data-category="financial" data-name="Payment Report">
                    {!! Form::open(['route' => 'report.paymentByDate', 'method' => 'post', 'id' => 'payment-form-' . uniqid()]) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d')}}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <a href="#" onclick="$(this).closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-wallet"></i></div>
                        <h6>Payment Report</h6>
                        <span class="badge bg-danger">Financial</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <div class="report-item" data-category="financial" data-name="Payment Method Report">
                    {!! Form::open(['route' => 'report.paymentMethod', 'method' => 'post', 'id' => 'payment-method-form-' . uniqid()]) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <a href="#" onclick="$(this).closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-card"></i></div>
                        <h6>Payment Method Report</h6>
                        <span class="badge bg-danger">Financial</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <!-- Customer Reports Section -->
                <div class="report-item" data-category="customer" data-name="Customer Report">
                    {!! Form::open(['route' => 'report.customer', 'method' => 'post', 'id' => 'customer-report-form']) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <a href="#" onclick="this.closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-user"></i></div>
                        <h6>Customer Report</h6>
                        <span class="badge bg-primary">Customer</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <div class="report-item" data-category="customer" data-name="Customer Due Report">
                    {!! Form::open(['route' => 'report.customerDueByDate', 'method' => 'post', 'id' => 'customer-due-form-' . uniqid()]) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime('-1 year')) }}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <a href="#" onclick="$(this).closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-alarm"></i></div>
                        <h6>Customer Due Report</h6>
                        <span class="badge bg-primary">Customer</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <div class="report-item" data-category="customer" data-name="First Time Customers">
                    <a href="{{ url('report/first-time-customers') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-user-id"></i></div>
                        <h6>First Time Customers</h6>
                        <span class="badge bg-primary">Customer</span>
                    </a>
                </div>

                <div class="report-item" data-category="customer" data-name="Customer Group Report">
                    <a id="customer-group-report-link" href="" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-user-group"></i></div>
                        <h6>Customer Group Report</h6>
                        <span class="badge bg-primary">Customer</span>
                    </a>
                </div>

                <!-- Supplier Reports Section -->
                <div class="report-item" data-category="supplier" data-name="Supplier Report">
                    <a id="supplier-report-link" href="" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-briefcase"></i></div>
                        <h6>Supplier Report</h6>
                        <span class="badge bg-secondary">Supplier</span>
                    </a>
                </div>

                <div class="report-item" data-category="supplier" data-name="Supplier Due Report">
                    {!! Form::open(['route' => 'report.supplierDueByDate', 'method' => 'post', 'id' => 'supplier-due-form-' . uniqid()]) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime('-1 year')) }}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <a href="#" onclick="$(this).closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-wallet"></i></div>
                        <h6>Supplier Due Report</h6>
                        <span class="badge bg-secondary">Supplier</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <!-- Product Reports Section -->
                <div class="report-item" data-category="product" data-name="Product Report">
                    {!! Form::open(['route' => 'report.product', 'method' => 'get', 'id' => 'product-report-form-' . uniqid()]) !!}
                    <input type="hidden" name="start_date" value="{{ date('Y-m-d')}}">
                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="warehouse_id" value="0">
                    <a href="#" onclick="$(this).closest('form').submit(); return false;" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-box"></i></div>
                        <h6>Product Report</h6>
                        <span class="badge bg-secondary">Product</span>
                    </a>
                    {!! Form::close() !!}
                </div>

                <div class="report-item" data-category="product" data-name="Qty Alert">
                    <a href="{{ route('report.qtyAlert') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-alarm"></i></div>
                        <h6>Qty Alert</h6>
                        <span class="badge bg-secondary">Product</span>
                    </a>
                </div>

                <div class="report-item" data-category="product" data-name="Product Expiry">
                    <a href="{{ route('report.productExpiry') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-calendar"></i></div>
                        <h6>Product Expiry</h6>
                        <span class="badge bg-secondary">Product</span>
                    </a>
                </div>

                <!-- User & Department Reports -->
                <div class="report-item" data-category="financial" data-name="User Report">
                    <a id="user-report-link" href="" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-user-group"></i></div>
                        <h6>User Report</h6>
                        <span class="badge bg-danger">Financial</span>
                    </a>
                </div>

                <div class="report-item" data-category="financial" data-name="Department Report">
                    <a href="{{ route('report.department') }}" class="report-link">
                        <div class="report-icon"><i class="dripicons dripicons-photo-group"></i></div>
                        <h6>Department Report</h6>
                        <span class="badge bg-danger">Financial</span>
                    </a>
                </div>
            </div>

            <!-- No Results Message (kept in case of empty tabs) -->
            <div class="alert alert-warning mt-4 d-none text-center" id="noResults">
                <i class="dripicons dripicons-search"></i> No reports found in this category.
            </div>
        </div>
    </div>
</div>

{{-- Additional Styles for Tabs --}}
<style>
    .display-5 {
        font-size: 3rem;
        font-weight: 800;
        background: #1c9f1f;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Category Tabs Container */
    .category-tabs-container {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 4px;
    }

    .category-tabs {
        display: inline-flex;
        gap: 12px;
    }

    .category-tab {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        border-radius: 10px;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        font-weight: 600;
        font-size: 14px;
        color: #444;
        cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
    }

    .category-tab i {
        font-size: 16px;
    }

    .category-tab.active,
    .category-tab:hover {
        background: linear-gradient(135deg, #13bd60, #0f9d4e);
        border-color: #13bd60;
        color: white;
        box-shadow: 0 4px 12px rgba(19, 189, 96, 0.3);
    }

    .category-tab.active i,
    .category-tab:hover i {
        color: white;
    }

    /* Reports Grid */
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
        padding: 0;
    }

    .report-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .report-item.hidden {
        display: none !important;
    }

    .report-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 24px 16px;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        background: #ffffff;
        text-decoration: none;
        color: #1a1a2e;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .report-link:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        border-color: #13bd60;
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
    }

    .report-icon {
        font-size: 2rem;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #13bd60;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .report-link:hover .report-icon {
        transform: scale(1.1);
        color: #764ba2;
    }

    .report-item h6 {
        margin: 12px 0 8px 0;
        font-size: 14px;
        font-weight: 600;
        color: #1a1a2e;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .badge {
        font-weight: 600;
        padding: 4px 8px;
        font-size: 11px;
    }

    .card {
        border-radius: 12px !important;
    }

    /* Responsive Grid */
    @media (max-width: 1400px) {
        .reports-grid {
            grid-template-columns: repeat(5, 1fr);
        }
    }

    @media (max-width: 1200px) {
        .reports-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 992px) {
        .reports-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .category-tab {
            padding: 8px 14px;
            font-size: 13px;
        }
    }

    @media (max-width: 768px) {
        .reports-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .reports-grid {
            grid-template-columns: repeat(1, 1fr);
        }
        .category-tabs {
            gap: 8px;
        }
        .category-tab {
            padding: 6px 12px;
            font-size: 12px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.category-tab');
        const reportItems = document.querySelectorAll('.report-item');
        const noResults = document.getElementById('noResults');

        // Default active tab is Sales (already set via class "active" in HTML)
        let activeCategory = 'sales';
        filterByCategory(activeCategory);

        // Tab click handler
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active to clicked tab
                this.classList.add('active');
                // Update category and filter
                activeCategory = this.getAttribute('data-category');
                filterByCategory(activeCategory);
            });
        });

        function filterByCategory(category) {
            let visibleCount = 0;

            reportItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                if (itemCategory === category) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            // Show/hide no results message
            if (visibleCount === 0) {
                noResults.classList.remove('d-none');
            } else {
                noResults.classList.add('d-none');
            }
        }
    });
</script>
@endsection