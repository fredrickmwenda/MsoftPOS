@extends('backend.layout.main') @section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h4 class="text-center">{{trans('file.Customer Due Report')}}</h4>
            </div>
            {!! Form::open(['route' => 'report.customerDueByDate', 'method' => 'post']) !!}


             <div class="row mb-3">

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
                
                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">{{trans('file.submit')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <div class="table-responsive mb-4">
        <table id="report-table" class="table table-hover" style="width:100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Date')}}</th>
                    <th>{{trans('file.reference')}}</th>
                    <th>{{trans('file.Customer Details')}}</th>
                    <th>{{trans('file.grand total')}}</th>
                    <th>{{trans('file.Returned Amount')}}</th>
                    <th>{{trans('file.Paid')}}</th>
                    <th>{{trans('file.Due')}}</th>
                </tr>
            </thead>

            <tfoot class="tfoot active">
                <th></th>
                <th>{{trans('file.Total')}}:</th>
                <th></th>
                <th></th>
                <th>{{number_format(0, $general_setting->decimal, '.', '')}}</th>
                <th>{{number_format(0, $general_setting->decimal, '.', '')}}</th>
                <th>{{number_format(0, $general_setting->decimal, '.', '')}}</th>
                <th>{{number_format(0, $general_setting->decimal, '.', '')}}</th>
            </tfoot>
        </table>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #due-report-menu").addClass("active");

    var start_date = <?php echo json_encode($start_date); ?>;
    var end_date = <?php echo json_encode($end_date); ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#report-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"customer-due-report-data",
            data:{
                start_date: start_date,
                end_date: end_date,
            },
            dataType: "json",
            type:"post"
        },
        "columns": [
            {"data": "key"},
            {"data": "date"},
            {"data": "reference_no"},
            {"data": "customer"},
            {"data": "grand_total"},
            {"data": "returned_amount"},
            {"data": "paid"},
            {"data": "due"}
        ],
        'language': {

            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        order:[['1', 'desc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 2, 3]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }

                   return data;
                },
                'checkboxes': {
                   'selectRow': true,
                   'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        rowId: 'ObjectID',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-sale)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum_due(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum_due(dt, false);
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-sale)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum_due(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum_due(dt, false);
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-sale)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum_due(dt, true);
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    datatable_sum_due(dt, false);
                },
                footer:true
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
        drawCallback: function () {
            var api = this.api();
            datatable_sum_due(api, false);
        }
    });

    function datatable_sum_due(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();

            $( dt_selector.column( 4 ).footer() ).html(dt_selector.cells( rows, 4, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 5 ).footer() ).html(dt_selector.cells( rows, 5, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 6 ).footer() ).html(dt_selector.cells( rows, 6, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 7 ).footer() ).html(dt_selector.cells( rows, 7, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
        else {
            $( dt_selector.column( 4 ).footer() ).html(dt_selector.column( 4, {page:'current'} ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 5 ).footer() ).html(dt_selector.column( 5, {page:'current'} ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 6 ).footer() ).html(dt_selector.column( 6, {page:'current'} ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 7 ).footer() ).html(dt_selector.column( 7, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
    }



</script>
@endpush
