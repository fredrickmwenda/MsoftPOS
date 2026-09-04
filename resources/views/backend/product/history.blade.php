@extends('backend.layout.main') 
@section('content')
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Product History')}}</h3>
            </div>
            {!! Form::open(['route' => 'products.history', 'method' => 'get']) !!}
            <div class="row ml-1">
                <input type="hidden" name="product_id" value="{{$product_id}}">
                <div class="col-md-4">
                    <div class="form-group">
                        <h4 class="mt-4">{{$product_data->name.' ['.$product_data->code.']'}}</h4>
                    </div>
                </div>

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
                                name="end_date"
                                value="{{ !empty($ending_date) ? $ending_date : '' }}"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-md-3 @if(!Auth::user()->roles->contains(fn($r) => $r->id <= 2)){{'d-none'}}@endif">
                    <div class="form-group">
                        <label><strong>{{trans('file.Warehouse')}}</strong></label>
                        <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" >
                            <option value="0">{{trans('file.All Warehouse')}}</option>
                            @foreach($lims_warehouse_list as $warehouse)
                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 mt-4">
                    <div class="form-group">
                        <button class="btn btn-primary" id="filter-btn" type="submit">{{trans('file.submit')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <ul class="nav nav-tabs ml-4 mt-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="#product-sale" role="tab" data-toggle="tab">{{trans('file.Sale')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#product-purchase" role="tab" data-toggle="tab">{{trans('file.Purchase')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#product-sale-return" role="tab" data-toggle="tab">{{trans('file.Sale Return')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#product-purchase-return" role="tab" data-toggle="tab">{{trans('file.Purchase Return')}}</a>
        </li>
    </ul>
    <div class="tab-content">
        <!-- sale table -->
        <div role="tabpanel" class="tab-pane fade show active" id="product-sale">
            <div class="table-responsive mb-4">
                <table id="sale-table" class="table table-hover" style="width: 100%">
                    <thead>
                        <tr>
                            <th class="not-exported-purchase"></th>
                            <th>{{trans('file.Date')}}</th>
                            <th>{{trans('file.reference')}}</th>
                            <th>{{trans('file.Warehouse')}}</th>
                            <th>{{trans('file.Supplier')}}</th>
                            <th>{{trans('file.qty')}}</th>
                            <th>{{trans('file.Unit Price')}}</th>
                            <th>{{trans('file.Subtotal')}}</th>
                        </tr>
                    </thead>

                    <tfoot>
                        <tr>
                            <th></th>
                            <th>{{ trans('file.Total') }}</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-right"></th> {{-- TOTAL QTY --}}
                            <th></th>
                            <th class="text-right"></th> {{-- TOTAL AMOUNT --}}
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <!-- purchase table -->
        <div role="tabpanel" class="tab-pane fade" id="product-purchase">
            <div class="table-responsive mb-4">
                <table id="purchase-table" class="table table-hover" style="width: 100%">
                    <thead>
                        <tr>
                            <th class="not-exported-purchase"></th>
                            <th>{{trans('file.Date')}}</th>
                            <th>{{trans('file.reference')}}</th>
                            <th>{{trans('file.Warehouse')}}</th>
                            <th>{{trans('file.Supplier')}}</th>
                            <th>{{trans('file.qty')}}</th>
                            <th>{{trans('file.Unit Price')}}</th>
                            <th>{{trans('file.Subtotal')}}</th>
                        </tr>
                    </thead>

                    <tfoot>
                        <tr>
                            <th></th>
                            <th>{{ trans('file.Total') }}</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-right"></th> {{-- TOTAL QTY --}}
                            <th></th>
                            <th class="text-right"></th> {{-- TOTAL AMOUNT --}}
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <!-- sale return table -->
        <div role="tabpanel" class="tab-pane fade" id="product-sale-return">
            <div class="table-responsive mb-4">
                <table id="sale-return-table" class="table table-hover" style="width: 100%">
                    <thead>
                        <tr>
                            <th class="not-exported-sale-return"></th>
                            <th>{{trans('file.Date')}}</th>
                            <th>{{trans('file.reference')}}</th>
                            <th>{{trans('file.Warehouse')}}</th>
                            <th>{{trans('file.customer')}}</th>
                            <th>{{trans('file.qty')}}</th>
                            <th>{{trans('file.Unit Price')}}</th>
                            <th>{{trans('file.Subtotal')}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!-- purchase return table -->
        <div role="tabpanel" class="tab-pane fade" id="product-purchase-return">
            <div class="table-responsive mb-4">
                <table id="purchase-return-table" class="table table-hover" style="width: 100%">
                    <thead>
                        <tr>
                            <th class="not-exported-purchase-return"></th>
                            <th>{{trans('file.Date')}}</th>
                            <th>{{trans('file.reference')}}</th>
                            <th>{{trans('file.Warehouse')}}</th>
                            <th>{{trans('file.Supplier')}}</th>
                            <th>{{trans('file.qty')}}</th>
                            <th>{{trans('file.Unit Price')}}</th>
                            <th>{{trans('file.Subtotal')}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#product").siblings('a').attr('aria-expanded','true');
    $("ul#product").addClass("show");

    // ✅ Added null-coalescing fallbacks to prevent passing NULL to AJAX
    var starting_date = <?php echo json_encode($starting_date ?? date('Y-m-d', strtotime('-1 year'))); ?>;
    var ending_date = <?php echo json_encode($ending_date ?? date('Y-m-d')); ?>;
    var warehouse_id = <?php echo json_encode($warehouse_id ?? 0); ?>;
    var product_id = <?php echo json_encode($product_id); ?>;

    // ✅ JS safeguard: Ensure they are never empty/null before sending
    if (!starting_date) starting_date = '<?php echo date('Y-m-d', strtotime('-1 year')); ?>';
    if (!ending_date) ending_date = '<?php echo date('Y-m-d'); ?>';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#warehouse_id").val(warehouse_id);


 
 $('#sale-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "sale-history-data",
        type: "POST",
        data: {
            product_id: product_id,
            starting_date: starting_date,
            ending_date: ending_date,
            warehouse_id: warehouse_id
        }
    },
    columns: [
        { data: "key" },
        { data: "date" },
        { data: "reference_no" },
        { data: "warehouse" },
        { data: "customer" },
        { data: "qty" },
        { data: "unit_price" },
        { data: "sub_total" }
    ],
    order: [[1, 'desc']],

    footerCallback: function () {
        var api = this.api();

        // INTEGER for QTY
        var intVal = function (i) {
            return parseInt(i, 10) || 0;
        };

        // FLOAT for money
        var floatVal = function (i) {
            return typeof i === 'string'
                ? i.replace(/[\$,]/g, '') * 1
                : typeof i === 'number'
                ? i
                : 0;
        };

        // TOTAL QTY
        var totalQty = api
            .column(5, { page: 'current' })
            .data()
            .reduce(function (a, b) {
                return intVal(a) + intVal(b);
            }, 0);

        // TOTAL AMOUNT
        var totalAmount = api
            .column(7, { page: 'current' })
            .data()
            .reduce(function (a, b) {
                return floatVal(a) + floatVal(b);
            }, 0);

        // FOOTER OUTPUT
            // GET UNIT NAME FROM FIRST ROW
    var unitName = api.rows({ page: 'current' }).data().length
        ? api.rows({ page: 'current' }).data()[0].unit_name
        : '';

    // FOOTER OUTPUT
    $(api.column(5).footer()).html(totalQty + ' ' + unitName);
        $(api.column(7).footer()).html(
            totalAmount.toFixed({{ $general_setting->decimal }})
        );
    }
});


  $('#purchase-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "purchase-history-data",
        type: "POST",
        data: {
            product_id: product_id,
            starting_date: starting_date,
            ending_date: ending_date,
            warehouse_id: warehouse_id
        }
    },
    columns: [
        { data: "key" },
        { data: "date" },
        { data: "reference_no" },
        { data: "warehouse" },
        { data: "supplier" },
        { data: "qty" },
        { data: "unit_cost" },
        { data: "sub_total" }
    ],
    order: [[1, 'desc']],

    footerCallback: function () {
        var api = this.api();

        // INTEGER for QTY
        var intVal = function (i) {
            return parseInt(i, 10) || 0;
        };

        // FLOAT for money
        var floatVal = function (i) {
            return typeof i === 'string'
                ? i.replace(/[\$,]/g, '') * 1
                : typeof i === 'number'
                ? i
                : 0;
        };

        // TOTAL QTY
        var totalQty = api
            .column(5, { page: 'current' })
            .data()
            .reduce(function (a, b) {
                return intVal(a) + intVal(b);
            }, 0);

        // TOTAL AMOUNT
        var totalAmount = api
            .column(7, { page: 'current' })
            .data()
            .reduce(function (a, b) {
                return floatVal(a) + floatVal(b);
            }, 0);

        
           var unitName = api.rows({ page: 'current' }).data().length
        ? api.rows({ page: 'current' }).data()[0].unit_name
        : '';

        // FOOTER OUTPUT
        $(api.column(5).footer()).html(totalQty + ' ' + unitName);
        $(api.column(7).footer()).html(
            totalAmount.toFixed({{ $general_setting->decimal }})
        );
    }
});

    
    function datatable_sum_purchase(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();

            $( dt_selector.column( 7 ).footer() ).html(dt_selector.cells( rows, 7, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
        else {
            $( dt_selector.column( 7 ).footer() ).html(dt_selector.cells( rows, 7, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
    }

    //retreiving sale return table data
    $('#sale-return-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"sale-return-history-data",
            data:{
                product_id: product_id,
                starting_date: starting_date,
                ending_date: ending_date,
                warehouse_id: warehouse_id
            },
            dataType: "json",
            type:"post"
        },
        "columns": [
            {"data": "key"},
            {"data": "date"},
            {"data": "reference_no"},
            {"data": "warehouse"},
            {"data": "customer"},
            {"data": "qty"},
            {"data": "unit_price"},
            {"data": "sub_total"}
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
                'targets': [0, 3, 4, 5, 6, 7]
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
                    columns: ':visible:Not(.not-exported-sale-return)',
                    rows: ':visible'
                }
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-sale-return)',
                    rows: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-sale-return)',
                    rows: ':visible'
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    
    });
    //retreiving purchase return table data
    $('#purchase-return-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"purchase-return-history-data",
            data:{
                product_id: product_id,
                starting_date: starting_date,
                ending_date: ending_date,
                warehouse_id: warehouse_id
            },
            dataType: "json",
            type:"post"
        },
        "columns": [
            {"data": "key"},
            {"data": "date"},
            {"data": "reference_no"},
            {"data": "warehouse"},
            {"data": "supplier"},
            {"data": "qty"},
            {"data": "unit_cost"},
            {"data": "sub_total"}
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
                'targets': [0, 3, 4, 5, 6, 7]
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
                    columns: ':visible:Not(.not-exported-purchase-return)',
                    rows: ':visible'
                }
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-purchase-return)',
                    rows: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-purchase-return)',
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