@extends('backend.layout.main')

@section('content') 

<div class="container-fluid">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to Reports Dashboard
                </a>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-md-2">
                    <label>Date From</label>
                    <input type="date" id="from_date" class="form-control" value="{{ now()->toDateString() }}">
                </div>

                <div class="col-md-2">
                    <label>Date To</label>
                    <input type="date" id="to_date" class="form-control" value="{{ now()->toDateString() }}">
                </div>

                <div class="col-md-2">
                    <label>Status</label>
                    <select id="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="hold">On Hold</option>
                        <option value="denied">Denied</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Category</label>
                    <select id="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary btn-block" type="button" id="searchBtn">
                        {{ trans('file.submit') }}
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered report-table" id="reportTable" width="100%">
                    <thead>
                        <tr>
                            <th rowspan="2" style="vertical-align:middle; background:#fff; color:#333;">PRODUCT NAME</th>
                            <th rowspan="2" style="vertical-align:middle; background:#fff; color:#333;">EXPIRY DATE</th>
                            <th colspan="3" class="text-center" style="background:#4472C4; color:#fff;">SYSTEM</th>
                            <th colspan="3" class="text-center" style="background:#70AD47; color:#fff;">PHYSICAL</th>
                            <th colspan="3" class="text-center" style="background:#FF0000; color:#fff;">VARIANCE</th>
                        </tr>
                        <tr>
                            <th style="background:#4472C4; color:#fff;">COUNT</th>
                            <th style="background:#4472C4; color:#fff;">COST TOTAL</th>
                            <th style="background:#4472C4; color:#fff;">SELLING TOTAL</th>

                            <th style="background:#70AD47; color:#fff;">COUNT</th>
                            <th style="background:#70AD47; color:#fff;">COST TOTAL</th>
                            <th style="background:#70AD47; color:#fff;">SELLING TOTAL</th>

                            <th style="background:#FF0000; color:#fff;">QTY</th>
                            <th style="background:#FF0000; color:#fff;">COST</th>
                            <th style="background:#FF0000; color:#fff;">SELLING</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        {{-- 11 separate <th> elements so .eq() maps 1-to-1 with columns --}}
                        <tr>
                            <th style="background:#fff;"></th>                         {{-- 0  product_name --}}
                            <th style="background:#fff;"></th>                         {{-- 1  expiry_date  --}}
                            <th class="dt-sys-total" style="background:#D9E1F2;"></th> {{-- 2  sys_count    --}}
                            <th class="dt-sys-total" style="background:#D9E1F2;"></th> {{-- 3  sys_cost     --}}
                            <th class="dt-sys-total" style="background:#D9E1F2;"></th> {{-- 4  sys_selling  --}}
                            <th class="dt-phy-total" style="background:#C6E0B4;"></th> {{-- 5  phy_count    --}}
                            <th class="dt-phy-total" style="background:#C6E0B4;"></th> {{-- 6  phy_cost     --}}
                            <th class="dt-phy-total" style="background:#C6E0B4;"></th> {{-- 7  phy_selling  --}}
                            <th class="dt-var-total" style="background:#FFC7CE;"></th> {{-- 8  var_qty      --}}
                            <th class="dt-var-total" style="background:#FFC7CE;"></th> {{-- 9  var_cost     --}}
                            <th class="dt-var-total" style="background:#FFC7CE;"></th> {{-- 10 var_selling  --}}
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
    /* Body cell colours */
    #reportTable tbody tr td.dt-sys { background-color: #D9E1F2 !important; }
    #reportTable tbody tr td.dt-phy { background-color: #C6E0B4 !important; }
    #reportTable tbody tr td.dt-var { background-color: #FFC7CE !important; }

    /* Striped override */
    #reportTable.table-striped tbody tr:nth-of-type(odd) td.dt-sys { background-color: #D9E1F2 !important; }
    #reportTable.table-striped tbody tr:nth-of-type(odd) td.dt-phy { background-color: #C6E0B4 !important; }
    #reportTable.table-striped tbody tr:nth-of-type(odd) td.dt-var { background-color: #FFC7CE !important; }

    /* Hover override */
    #reportTable tbody tr:hover td.dt-sys { background-color: #b4c6e7 !important; }
    #reportTable tbody tr:hover td.dt-phy { background-color: #a9d08e !important; }
    #reportTable tbody tr:hover td.dt-var { background-color: #ff9999 !important; }

    /* Footer borders */
    #reportTable tfoot th { font-weight: bold; border-top: 2px solid #333; }
</style>
@endpush

@push('scripts')
<script>
let table = $('#reportTable').DataTable({
    processing: true,
    serverSide: true,
    pageLength: 10,
    order: [],

    ajax: {
        url: "{{ route('report.stockTakingData') }}",
        data: function (d) {
            d.from_date   = $('#from_date').val();
            d.to_date     = $('#to_date').val();
            d.status      = $('#status').val();
            d.category_id = $('#category_id').val();
        },
        dataSrc: function (json) {
            if (json.totals) {
                let f = $(table.table().footer()).find('tr:first th');

                /* 11 <th> elements = indices 0 … 10  */
                f.eq(0).html('');                               // product
                f.eq(1).html('');                               // expiry
                f.eq(2).html('');                               // sys count
                f.eq(3).html(json.totals.sys_cost_total);
                f.eq(4).html(json.totals.sys_selling_total);
                f.eq(5).html('');                               // phy count
                f.eq(6).html(json.totals.phy_cost_total);
                f.eq(7).html(json.totals.phy_selling_total);
                f.eq(8).html('');                               // var qty (no total)
                f.eq(9).html(json.totals.variance_cost_total);
                f.eq(10).html(json.totals.variance_selling_total);
            }
            return json.data;
        }
    },

    columns: [
        { data: 'product_name',      name: 'product_name' },
        { data: 'expired_date',       name: 'expiry_date' },
        { data: 'system_qty',        name: 'system_qty',        className: 'dt-sys text-right' },
        { data: 'sys_cost_total',    name: 'sys_cost_total',    className: 'dt-sys text-right' },
        { data: 'sys_selling_total', name: 'sys_selling_total', className: 'dt-sys text-right' },
        { data: 'physical_qty',      name: 'physical_qty',      className: 'dt-phy text-right' },
        { data: 'phy_cost_total',    name: 'phy_cost_total',    className: 'dt-phy text-right' },
        { data: 'phy_selling_total', name: 'phy_selling_total', className: 'dt-phy text-right' },
        { data: 'variance_qty',      name: 'variance_qty',      className: 'dt-var text-right' },
        { data: 'variance_cost',     name: 'variance_cost',     className: 'dt-var text-right' },
        { data: 'variance_selling',  name: 'variance_selling',  className: 'dt-var text-right' }
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

    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],

    dom: '<"row"lfB>rtip',

    buttons: [
        {
            extend: 'pdf',
            title: 'Stock Taking Report',
            text: '<i title="Export PDF" class="fa fa-file-pdf-o"></i>',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'excel',
            title: 'Stock Taking Report',
            text: '<i title="Export Excel" class="dripicons-document-new"></i>',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'csv',
            title: 'Stock Taking Report',
            text: '<i title="Export CSV" class="fa fa-file-text-o"></i>',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'print',
            title: 'Stock Taking Report',
            text: '<i title="Print" class="fa fa-print"></i>',
            exportOptions: { columns: ':visible' }
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