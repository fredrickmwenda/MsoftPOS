@extends('backend.layout.main')

@section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Department Report</h4>
                </div>
                <div class="card-body">
                    //the filter form
                    <form method="GET" action="{{ route('report.department') }}" class="mb-3">
                        <div class="form-row align-items-end">
                            <div class="col-md-3">
                                <label for="date_from">From Date</label>
                                <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to">To Date</label>
                                <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="department-table">
                            <thead>
                                <tr>
                                    <th>Department Name</th>
                                    <th>Categories</th>
                                    <th>Products</th>
                                    <th>Total Sales Qty</th>
                                    <th>Total Revenue</th>
                                    <th>Cost</th>
                                    <th>Actions</th>
                                </tr> 
                            </thead>
                            <tbody>
                                @forelse($department_data as $dept)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($dept['image'])
                                            <img src="{{ asset('public/' . $dept['image']) }}" alt="{{ $dept['name'] }}" style="width: 40px; height: 40px; margin-right: 10px; border-radius: 4px;">
                                            @endif
                                            <span>{{ $dept['name'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $dept['categories_count'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">{{ $dept['products_count'] }}</span>
                                    </td>
                                    <td>
                                        {{ number_format($dept['total_sales'], 2) }}
                                    </td>
                                    <td>
                                        <span class="text-success font-weight-bold">
                                            {{ config('app.currency_symbol') }} {{ number_format($dept['total_revenue'], 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-danger font-weight-bold">
                                            {{ config('app.currency_symbol') }} {{ number_format($dept['total_cost'] ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#departmentModal{{ $dept['id'] }}">
                                            <i class="fa fa-eye"></i> View Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal for Department Details -->
                                <div class="modal fade" id="departmentModal{{ $dept['id'] }}" tabindex="-1" role="dialog" aria-labelledby="departmentModalLabel{{ $dept['id'] }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="departmentModalLabel{{ $dept['id'] }}">
                                                    {{ $dept['name'] }} - Details
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6><strong>Department Information</strong></h6>
                                                        <p><strong>Name:</strong> {{ $dept['name'] }}</p>
                                                        <p><strong>Total Categories:</strong> {{ $dept['categories_count'] }}</p>
                                                        <p><strong>Total Products:</strong> {{ $dept['products_count'] }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6><strong>Sales Performance</strong></h6>
                                                        <p><strong>Total Sales Qty:</strong> {{ number_format($dept['total_sales'], 2) }}</p>
                                                        <p><strong>Total Revenue:</strong> {{ config('app.currency_symbol') }} {{ number_format($dept['total_revenue'], 2) }}</p>
                                                        <p><strong>Total Cost:</strong> {{ config('app.currency_symbol') }} {{ number_format($dept['total_cost'] ?? 0, 2) }}</p>
                                                        @if($dept['total_sales'] > 0)
                                                        <p><strong>Avg Sale Value:</strong> {{ config('app.currency_symbol') }} {{ number_format($dept['total_revenue'] / $dept['total_sales'], 2) }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <p class="text-muted">No departments found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-responsive {
        overflow-x: auto;
    }

    .badge {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }

    .badge-info {
        background-color: #17a2b8;
        color: white;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #333;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .d-flex {
        display: flex;
    }

    .align-items-center {
        align-items: center;
    }

    .font-weight-bold {
        font-weight: bold;
    }

    .text-success {
        color: #28a745;
    }

    .text-muted {
        color: #6c757d;
    }

    .text-center {
        text-align: center;
    }
</style>

<script>
    $(document).ready(function() {
        // Add date range filter
        var today = new Date().toISOString().slice(0, 10);
        if (!$('#date-from').length && !$('#date-to').length) {
            var filterHtml = "<div class='mb-2' style='display:flex;gap:10px;align-items:center;'><label for='date-from' class='mb-0'>From:</label> <input type='date' id='date-from' class='form-control' value='"+ (getUrlParam('date_from') || today) +"' style='max-width:160px;display:inline-block;'> <label for='date-to' class='mb-0'>To:</label> <input type='date' id='date-to' class='form-control' value='"+ (getUrlParam('date_to') || today) +"' style='max-width:160px;display:inline-block;'><button id='date-range-filter-btn' class='btn btn-primary btn-sm'>Filter</button></div>";
            $(filterHtml).insertBefore('#department-table');
        }
        var table = $('#department-table').DataTable({
            "order": [[4, "desc"]],
            "pageLength": 10
        });
        // Helper to get URL param
        function getUrlParam(name) {
            var results = new RegExp('[?&]' + name + '=([^&#]*)').exec(window.location.href);
            return results ? decodeURIComponent(results[1]) : null;
        }
        // Filter by date range
        $('#date-range-filter-btn').on('click', function() {
            var dateFrom = $('#date-from').val();
            var dateTo = $('#date-to').val();
            var url = new URL(window.location.href);
            url.searchParams.set('date_from', dateFrom);
            url.searchParams.set('date_to', dateTo);
            url.searchParams.delete('date'); // Remove old single date param if present
            window.location.href = url.toString();
        });
    });
</script>
@endsection
