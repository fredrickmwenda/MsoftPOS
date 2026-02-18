@extends('backend.layout.main')

@section('content')
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
                <div class="col-md-5 offset-md-1 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{trans('file.Choose Your Date')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <div class="input-group">
                                <input type="text" class="daterangepicker-field form-control" value="{{$start_date}} To {{$end_date}}" required />
                                <input type="hidden" name="start_date" value="{{$start_date}}" />
                                <input type="hidden" name="end_date" value="{{$end_date}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{trans('file.Choose Warehouse')}}</strong> &nbsp;</label>
                        <div class="d-tc">
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
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="warehouseStockTable">
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

setTimeout(function() {
    $('.daterangepicker-field').daterangepicker({
        autoUpdateInput: false,
        startDate: "{{ $start_date }}",
        endDate: "{{ $end_date }}",
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        }
    }, function(startDate, endDate, label) {
        var start_date = startDate.format('YYYY-MM-DD');
        var end_date   = endDate.format('YYYY-MM-DD');
        var title = start_date + ' To ' + end_date;

        // set visible field
        $('.daterangepicker-field').val(title);

        // set hidden fields
        $('input[name="start_date"]').val(start_date);
        $('input[name="end_date"]').val(end_date);

        console.log("Callback fired:", start_date, end_date);
    });

    console.log("daterangepicker initialized");
}, 600);



});
$('#warehouseReportForm').on('submit', function(e) {
    e.preventDefault(); // prevent form from reloading the page
    $('#warehouseStockTable').DataTable().ajax.reload(); // reload table with new filters
})


</script>
@endpush
