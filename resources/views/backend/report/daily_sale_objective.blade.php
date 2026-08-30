@extends('backend.layout.main') @section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Daily Sale Objective Report')}}</h3>
            </div>
            {!! Form::open(['route' => 'report.dailySaleObjective', 'method' => 'get']) !!}
            <div class="row mb-3">
              
                                <div class="col-md-3 mt-3 mb-3 ml-2">
                    <div class="form-group">
                        <label class="control-label"><strong>Start Date</strong> &nbsp;</label>
                        <div class="">
                            <input 
                                type="date" 
                                class="form-control" 
                                name="starting_date"
                                value="{{ !empty($starting_date) ? $starting_date : '' }}"
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
                                name="ending_date"
                                value="{{ !empty($ending_date) ? $ending_date : '' }}"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" id="filter-btn" type="submit">{{trans('file.submit')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <div class="table-responsive">
        <table id="sale-table" class="table sale-list" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Date')}}</th>
                    <th>{{trans('file.Product Info')}}</th>
                    <th>{{trans('file.Number of Products')}}</th>
                </tr>
            </thead>
        </table>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #daily-sale-objective-menu").addClass("active");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });



    var starting_date = $("input[name=starting_date]").val();
    var ending_date = $("input[name=ending_date]").val();

    $('#sale-table').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"daily-sale-objective-data",
            data:{
                starting_date: starting_date,
                ending_date: ending_date
            },
            dataType: "json",
            type:"post"
        },
        "columns": [
            {"data": "key"},
            {"data": "date"},
            {"data": "product_info"},
            {"data": "number_of_products"},
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
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ]
    });

</script>
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endpush
