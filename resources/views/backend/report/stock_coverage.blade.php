@extends('backend.layout.main')

@section('content')
@push('css')
<style>
    .full-width-card {
        width: 100%;
        margin: 0;
        border-radius: 0;
    }
    .full-width-card .card-body {
        padding: 20px 25px;
    }

    .filter-section {
        background: #f8f9fa;
        padding: 18px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 15px;
    }
    .filter-section .form-group {
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filter-section label {
        font-weight: 600;
        margin-bottom: 0;
        white-space: nowrap;
    }
    .filter-section select,
    .filter-section input[type="date"] {
        min-width: 140px;
    }

    .table-responsive-wrapper {
        overflow-x: auto;
        width: 100%;
    }

    #coverageTable {
        width: 100% !important;
        min-width: 800px;
        border-collapse: collapse;
    }

    #coverageTable th,
    #coverageTable td {
        text-align: left;
        vertical-align: middle;
        padding: 8px 12px;
    }
    #coverageTable th:first-child,
    #coverageTable td:first-child {
        padding-left: 15px;
    }
    /* fixed column widths */
    #coverageTable th:nth-child(1),
    #coverageTable td:nth-child(1) { width: 25%; }
    #coverageTable th:nth-child(2),
    #coverageTable td:nth-child(2) { width: 15%; }
    #coverageTable th:nth-child(3),
    #coverageTable td:nth-child(3) { width: 10%; }
    #coverageTable th:nth-child(4),
    #coverageTable td:nth-child(4) { width: 15%; }
    #coverageTable th:nth-child(5),
    #coverageTable td:nth-child(5) { width: 10%; }
    #coverageTable th:nth-child(6),
    #coverageTable td:nth-child(6) { width: 10%; }
    #coverageTable th:nth-child(7),
    #coverageTable td:nth-child(7) { width: 15%; }

    .badge-success {
        background: #28a745;
        color: #fff;
    }
    .badge-danger {
        background: #dc3545;
        color: #fff;
    }
    .totals-row th {
        background: #f1f3f5;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .filter-section {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-section .form-group {
            flex-wrap: wrap;
        }
        .filter-section .form-group label {
            min-width: 80px;
        }
    }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="card full-width-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-clipboard-check"></i> Stock Coverage Report</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">

                <!-- Filter Bar -->
                <div class="filter-section">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="from_date">From</label>
                        <input type="date" id="from_date" class="form-control" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                    </div>
                    <div class="form-group">
                        <label for="to_date">To</label>
                        <input type="date" id="to_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="status_filter">Status</label>
                        <select id="status_filter" class="form-control">
                            <option value="all">All</option>
                            <option value="counted">Counted</option>
                            <option value="not_counted">Not Counted</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="button" id="filterBtn" class="btn btn-primary">Apply</button>
                        <button type="button" id="resetBtn" class="btn btn-secondary ml-2">Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive-wrapper">
                    <table id="coverageTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>System Qty</th>
                                <th>Last Count Date</th>
                                <th>Physical Qty</th>
                                <th>Variance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="totals-row">
                                <th colspan="2" style="text-align:right">Totals:</th>
                                <th id="totalSystemQty">0.00</th>
                                <th></th>
                                <th id="totalPhysicalQty">0.00</th>
                                <th id="totalVariance">0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#coverageTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: false,
        ajax: {
            url: "{{ route('stock-coverage.data') }}",
            data: function (d) {
                d.category_id = $('#category').val();
                d.from_date = $('#from_date').val();
                d.to_date = $('#to_date').val();
                d.status_filter = $('#status_filter').val();
            }
        },
        columns: [
            { data: 'product_name', name: 'product_name' },
            { data: 'category', name: 'category' },
            { data: 'system_qty', name: 'system_qty' },
            { data: 'last_count_date', name: 'last_count_date' },
            { data: 'physical_qty', name: 'physical_qty' },
            { data: 'variance', name: 'variance' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        columnDefs: [
            // ensure the first column is not wrapped excessively
            { targets: 0, render: function (data) { return data || '-'; } },
            { targets: '_all', className: 'text-left' }
        ],
        footerCallback: function (row, data, start, end, display) {
            let api = this.api();
            let json = api.ajax.json();
            if (json && json.totals) {
                $('#totalSystemQty').text(json.totals.system_qty);
                $('#totalPhysicalQty').text(json.totals.physical_qty);
                $('#totalVariance').text(json.totals.variance);
            }
        }
    });

    // Apply & Reset
    $('#filterBtn').on('click', function() {
        table.ajax.reload();
    });
    $('#resetBtn').on('click', function() {
        $('#category').val('');
        $('#from_date').val('{{ date("Y-m-d", strtotime("-30 days")) }}');
        $('#to_date').val('{{ date("Y-m-d") }}');
        $('#status_filter').val('all');
        table.ajax.reload();
    });
});
</script>
@endpush