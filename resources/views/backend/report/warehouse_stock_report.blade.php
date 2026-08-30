@extends('backend.layout.main')

@push('css')
<!--  the btn-group to have a border  radius of the black color -->
<style>
    .btn-group {
        border-radius: 0.25rem;
        border: 1px solid #000000;
    }
    .btn-group .btn {
        border-radius: 0.25rem;
        border: 1px solid #000000;
    }
    .btn-group .btn:hover {
        background-color: #000000;
    }
    .btn-group .btn:active {
        background-color: #000000;
    }
    .btn-group .btn:focus {
        background-color: #000000;
    }
    .btn-group .btn:active:focus {
        background-color: #000000;
    }
    /* Make table use full width of container */
    #warehouseStockTable {
        width: 100% !important;
    }
    .table-responsive .dataTables_wrapper {
        width: 100% !important;
    }
    .table-responsive .dataTables_scroll,
    .table-responsive .dataTables_scrollBody {
        width: 100% !important;
    }
</style>
@endpush
@section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">Warehouse Stock Report</h3>
            </div>

            {{-- ✅ Filter Form --}}
            {!! Form::open(['route' => 'report.warehouseStockData', 'method' => 'post', 'id' => 'warehouseReportForm']) !!}
            @csrf
            <div class="row mb-3 warehouse-report-filter">
                <div class="col-md-3 mt-3 mb-3 ml-2">
                    <div class="form-group">
                        <label class="control-label"><strong>Start Date</strong> &nbsp;</label>
                        <div class="">
                            <input 
                                type="date" 
                                class="form-control" 
                                name="start_date"
                                value="{{ !empty($start_date) ? $start_date : '' }}"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mt-3 mb-3">
                    <div class="form-group">
                        <label class="control-label"><strong>End Date</strong> &nbsp;</label>
                        <div class="">
                            <input 
                                type="date" 
                                class="form-control" 
                                name="end_date"
                                value="{{ !empty($end_date) ? $end_date : '' }}"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3">
                    <div class="form-group row">
                        <label class="control-label"><strong>{{trans('file.Choose Warehouse')}}</strong> &nbsp;</label>
                       
                            <input type="hidden" name="warehouse_id_hidden" value="{{$warehouse_id}}" />
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="">All Warehouses</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{$warehouse->id}}" {{ $warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                        {{$warehouse->name}}
                                    </option>
                                @endforeach
                            </select>
                        
                    </div>
                </div>
                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">{{trans('file.submit')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        {{-- ✅ Data Table --}}
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-bordered table-striped" id="warehouseStockTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Product</th>
                        @foreach($warehouses as $warehouse)
                            <th>{{ $warehouse->name }} Qty</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    // Submit form via AJAX
  $('#warehouseStockTable').DataTable({
    processing: true,
    serverSide: true,
    scrollX: true,
    autoWidth: true,
    ajax: {
        url: "{{ route('report.warehouseStockData') }}", // <-- Laravel route for JSON
        type: "POST",
        data: function (d) {
            d._token      = '{{ csrf_token() }}';
            d.start_date  = $('input[name="start_date"]').val();
            d.end_date    = $('input[name="end_date"]').val();
            d.warehouse_id= $('#warehouse_id').val();
        }
    },
    columns: [
        { data: "checkbox", orderable: false, searchable: false },
        { data: "product" },
        @foreach($warehouses as $warehouse)
            { data: "warehouse_{{ $warehouse->id }}" },
        @endforeach
        { data: "total" }
    ],
    language: {
        lengthMenu: '_MENU_ {{trans("file.records per page")}}',
        info: '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
        search: '{{trans("file.Search")}}',
        paginate: {
            previous: '<i class="dripicons-chevron-left"></i>',
            next: '<i class="dripicons-chevron-right"></i>'
        }
    },
    order: [[1, 'asc']], // order by product
    columnDefs: [
        {
            targets: 0,
            render: function (data, type, row, meta) {
                if (type === 'display') {
                    return '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                }
                return data;
            },
            checkboxes: {
                selectRow: true,
                selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
            }
        }
    ],
    select: { style: 'multi', selector: 'td:first-child' },
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    dom: '<"row"lfB>rtip',
    buttons: [
        {
            extend: 'pdf',
            text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
            footer: true
        },
        {
            extend: 'csv',
            text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
            footer: true
        },
        {
            extend: 'print',
            text: '<i title="print" class="fa fa-print"></i>',
            footer: true
        },
        {
            extend: 'colvis',
            text: '<i title="column visibility" class="fa fa-eye"></i>',
            columns: ':gt(0)'
        }
    ]
});




});
$('#warehouseReportForm').on('submit', function(e) {
    e.preventDefault(); // prevent form from reloading the page
    $('#warehouseStockTable').DataTable().ajax.reload(); // reload table with new filters
})


</script>
@endpush
