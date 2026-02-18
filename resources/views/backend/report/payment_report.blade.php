@extends('backend.layout.main') @section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Payment Report')}}</h3>
            </div>
            {!! Form::open(['route' => 'report.paymentByDate', 'method' => 'post']) !!}
            <div class="row">
            <div class="col-md-5 mt-3 mb-3">
                <div class="form-group">
                    <label class="control-label"><strong>{{trans('file.Choose Your Date')}}</strong> &nbsp;</label>
                    <div class="">
                        <div class="input-group">
                            <input 
                                type="text" 
                                class="daterangepicker-field form-control" 
                                value="{{ !empty($start_date) ? $start_date : '' }}{{ !empty($end_date) ? ' To ' . $end_date : '' }}" 
                                
                            />

                            <input type="hidden" name="start_date" />
                            <input type="hidden" name="end_date" />
                         
                        </div>
                    </div>
                </div>
            </div>
            <!-- Select filter for suppliers-->
             <div class="col-md-5 mt-3 mb-3">
                <div class="form-group">
                    <label class="control-label"><strong>Filter By Supplier</strong> &nbsp;</label>
                    <?php
                        $suppliers = \DB::table('suppliers')->where('is_active', true)->get();
                    ?>
                    <select name="supplier_id" class="form-control selectpicker" data-live-search="true" data-live-search-style="begins">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @if(isset($supplier_id) && $supplier_id == $supplier->id) selected @endif>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
             </div>

             <div class="col-md-2 mt-4 mb-3">
                <div class="form-group mt-5">
                    <button class="btn btn-primary" type="submit">{{trans('file.submit')}}</button>
                </div>
                </div>
            </div>

            {!! Form::close() !!}
           
            {!! Form::open(['route' => 'report.paymentByDate', 'method' => 'post', 'id' => 'paymentForm']) !!}

           
            <div class="form-group col-md-12 edit_mobile_money_fields">
                <label>Payment Methods</label>
                <select id="payment_methods" name="payment_methods" class="form-control" data-live-search="true" data-live-search-style="begins">
                    <option value="Cash" @if($method == 'Cash') @selected(true) @endif>Cash</option>
                    <option value="Card" @if($method == 'Card') @selected(true) @endif>Card</option>
                    <option value="Cheque" @if($method == 'Cheque') @selected(true) @endif>Cheque</option>
                    <option value="Gift Card" @if($method == 'Gift Card') @selected(true) @endif>Gift Card</option>
                    <option value="Deposit" @if($method == 'Deposit') @selected(true) @endif>Deposit</option>
                    <option value="Paypal" @if($method == 'Paypal') @selected(true) @endif>Paypal</option>
                    <option value="MobileMoney" @if($method == 'MobileMoney') @selected(true) @endif>Mobile Money</option>
                </select>
            </div>
            
            {!! Form::close() !!}
            {{-- changes end by yogesh --}}
        </div>
    </div>
    <div class="table-responsive mb-4">
        <table id="report-table" class="table table-hover">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Date')}}</th>
                    <th>{{trans('file.Payment Reference')}} </th>
                    <th>{{trans('file.Sale Reference')}}</th>
                    <th>{{trans('file.Purchase Reference')}}</th>
                    <th>{{trans('file.Paid By')}}</th>
                    <th>{{trans('file.Amount')}}</th>
                    <th>{{trans('file.Created By')}}</th>
                </tr>
            </thead>
            <tbody>
                {{-- {{$lims_payment_data}} --}}
                @foreach($lims_payment_data as $payment)
                <?php
                    $sale = DB::table('sales')->find($payment->sale_id);
                    $purchase = DB::table('purchases')->find($payment->purchase_id);
                    $user = DB::table('users')->find($payment->user_id);
                ?>
                <tr>
                    <td></td>
                    <td>{{date($general_setting->date_format, strtotime($payment->created_at->toDateString())) . ' '. $payment->created_at->toTimeString()}}</td>
                    <td>{{$payment->payment_reference}}</td>
                    <td>@if($sale){{$sale->reference_no}}@endif</td>
                    <td>@if($purchase){{$purchase->reference_no}}@endif</td>
                    <td>{{$payment->paying_method}}</td>
                    <td>{{$payment->amount}}</td>
                    <td>{{$user->name}}<br>{{$user->email}}</td>
                </tr>
                @endforeach  
            </tbody>
            <tfoot class="tfoot active">
                <th></th>
                <th>{{trans('file.Total')}}:</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>{{number_format(0, $general_setting->decimal, '.', '')}}<</th>
                <th></th>
            </tfoot>
        </table>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report li#payment-report-menu").addClass("active");

    $('#report-table').DataTable( {
        "order": [[1, 'desc']],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': 0
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
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            }
        ],
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    } );

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();

            $( dt_selector.column( 6 ).footer() ).html(dt_selector.cells( rows, 6, { page: 'all' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
        else {
            $( dt_selector.column( 6 ).footer() ).html(dt_selector.column( 6, {page:'all'} ).data().sum().toFixed({{$general_setting->decimal}}));
        }
    }

$(".daterangepicker-field").daterangepicker({
  callback: function(startDate, endDate, period){
    var start_date = startDate.format('YYYY-MM-DD');
    var end_date = endDate.format('YYYY-MM-DD');
    var title = start_date + ' to ' + end_date;
    $(this).val(title);
    $('input[name="start_date"]').val(start_date);
    $('input[name="end_date"]').val(end_date);
  }
});

// changes by yogesh
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('payment_methods').addEventListener('change', function () {
            document.getElementById('paymentForm').submit();
        });
    });
// changes end by yogesh
</script>
@endpush
