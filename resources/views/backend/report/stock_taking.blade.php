@extends('backend.layout.main')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <!-- <h2>
                    <i class="fa fa-arrow-circle-left"></i>
                    Stock Taking Report
                </h2> -->
                <a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>

                <!-- <a href="{{ url()->previous() }}" class="btn btn-danger">
                    <i class="fa fa-arrow-left"></i> Back
                </a> -->
            </div>

            <hr>

            <div class="row mb-4">

                <div class="col-md-2">
                    <label>Date From</label>
                    <input
                        type="date"
                        id="from_date"
                        class="form-control"
                        value="{{ now()->toDateString() }}">
                </div>

                <div class="col-md-2">
                    <label>Date To</label>
                    <input
                        type="date"
                        id="to_date"
                        class="form-control"
                        value="{{ now()->toDateString() }}">
                </div>

                <div class="col-md-2">
                    <label>Status</label>

                    <select
                        id="status"
                        class="form-control">

                        <option value="">
                            All Status
                        </option>

                        <option value="approved">
                            Approved
                        </option>

                        <option value="hold">
                            On Hold
                        </option>

                        <option value="denied">
                            Denied
                        </option>
                    </select>
                </div>

                <!-- <div class="col-md-2">
                    <label>Stock Type</label>

                    <select
                        id="stock_type"
                        class="form-control">

                        <option value="">
                            All Types
                        </option>

                        <option value="retail">
                            Retail
                        </option>

                        <option value="wholesale">
                            Wholesale
                        </option>

                        <option value="warehouse">
                            Warehouse
                        </option>
                    </select>
                </div> -->

                <div class="col-md-3">
                    <label>Category</label>

                    <select
                        id="category_id"
                        class="form-control">

                        <option value="">
                            All Categories
                        </option>

                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <label>&nbsp;</label>

                    <!-- <button
                        class="btn btn-success btn-block"
                        id="searchBtn">

                        <i class="fa fa-search"></i>
                        Search
                    </button> -->
                    <button class="btn btn-primary" type="submit" id="searchBtn">{{ trans('file.submit') }}</button>

                </div>

            </div>

            <!-- <div class="row mb-4">

                <div class="col-md-3">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3 id="total_records">0</h3>
                            <p>Total Records</p>
                        </div>

                        <div class="icon">
                            <i class="fa fa-list"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="approved_count">0</h3>
                            <p>Approved</p>
                        </div>

                        <div class="icon">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="hold_count">0</h3>
                            <p>On Hold</p>
                        </div>

                        <div class="icon">
                            <i class="fa fa-pause"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="denied_count">0</h3>
                            <p>Denied</p>
                        </div>

                        <div class="icon">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>

            </div> -->

            <div class="table-responsive">

                <table
                    class="table table-bordered table-striped"
                    id="reportTable"
                    width="100%">

                    <thead>

                    <tr>
                        <th>Date & Time</th>
                        <th>Product Name</th>
                        <th>Warehouse</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Actual Stock</th>
                        <th>Difference</th>
                        <th>Taken By</th>
                    </tr>

                    </thead>

                </table>

            </div>

        </div>
    </div>

</div>

@endsection


@push('scripts')

<script>

let table = $('#reportTable').DataTable({
    processing: true,
    serverSide: true,
    pageLength: 10,
    order: [[0, 'desc']],

    ajax: {
        url: "{{ route('report.stockTakingData') }}",
        data: function (d) {
            d.from_date = $('#from_date').val();
            d.to_date = $('#to_date').val();
            d.status = $('#status').val();
            d.category_id = $('#category_id').val();
        }
    },

    columns: [
        { data: 'date', name: 'date' },
        { data: 'product', name: 'product' },
        { data: 'warehouse', name: 'warehouse' },
        { data: 'category', name: 'category' },
        { data: 'system_qty', name: 'system_qty' },
        { data: 'physical_qty', name: 'physical_qty' },
        { data: 'variance', name: 'variance' },
        { data: 'taken_by', name: 'taken_by' }
    ],

    language: {
        lengthMenu: '_MENU_ {{ trans("file.records per page") }}',
        info: '<small>{{ trans("file.Showing") }} _START_ - _END_ (_TOTAL_)</small>',
        search: '{{ trans("file.Search") }}',
        paginate: {
            previous: '<i class="dripicons-chevron-left"></i>',
            next: '<i class="dripicons-chevron-right"></i>'
        }
    },

    lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"]
    ],

    dom: '<"row"lfB>rtip',

    buttons: [
        {
            extend: 'pdf',
            title: 'Stock Taking Report',
            text: '<i title="Export PDF" class="fa fa-file-pdf-o"></i>',
            exportOptions: {
                columns: ':visible'
            }
        },
        {
            extend: 'excel',
            title: 'Stock Taking Report',
            text: '<i title="Export Excel" class="dripicons-document-new"></i>',
            exportOptions: {
                columns: ':visible'
            }
        },
        {
            extend: 'csv',
            title: 'Stock Taking Report',
            text: '<i title="Export CSV" class="fa fa-file-text-o"></i>',
            exportOptions: {
                columns: ':visible'
            }
        },
        {
            extend: 'print',
            title: 'Stock Taking Report',
            text: '<i title="Print" class="fa fa-print"></i>',
            exportOptions: {
                columns: ':visible'
            }
        },
        {
            extend: 'colvis',
            title: 'Stock Taking Report',
            text: '<i title="Column Visibility" class="fa fa-eye"></i>'
        }
    ]
});

$('#searchBtn').click(function () {
    table.ajax.reload();
});

</script>

@endpush