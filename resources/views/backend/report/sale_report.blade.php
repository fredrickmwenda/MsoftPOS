@extends('backend.layout.main') @section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
@if(empty($report_rows))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{'No Data exist between this date range!'}}</div>
@endif

@push('css')
<style>
.report-summary-cards { margin-bottom: 0.5rem; }
.report-summary-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.report-summary-card__label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.report-summary-card__value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #212529;
}
.report-summary-card--amount .report-summary-card__value { color: #0d6efd; }
.report-summary-card--qty .report-summary-card__value { color: #198754; }
.report-summary-card--stock .report-summary-card__value { color: #6f42c1; }
</style>
@endpush

<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Sale Report')}}</h3>
            </div>

            {{-- Exclusive totals (same as footer) in small cards --}}
            <div class="report-summary-cards px-3 pt-3">
                <div class="row no-gutters">
                    <div class="col-md-4 col-6 mb-2 pr-1">
                        <div class="report-summary-card report-summary-card--amount">
                            <span class="report-summary-card__label">{{trans('file.Sold Amount')}}</span>
                            <span class="report-summary-card__value" id="report-total-sold-amount">0</span>
                        </div>
                    </div>
                    <div class="col-md-4 col-6 mb-2 pl-1 pr-1">
                        <div class="report-summary-card report-summary-card--qty">
                            <span class="report-summary-card__label">{{trans('file.Sold Qty')}}</span>
                            <span class="report-summary-card__value" id="report-total-sold-qty">0</span>
                        </div>
                    </div>
                    <div class="col-md-4 col-6 mb-2 pl-1">
                        <div class="report-summary-card report-summary-card--stock">
                            <span class="report-summary-card__label">{{trans('file.In Stock')}}</span>
                            <span class="report-summary-card__value" id="report-total-in-stock">0</span>
                        </div>
                    </div>
                </div>
            </div>
 
            {!! Form::open(['route' => 'report.sale', 'method' => 'post']) !!}
            {{-- Ensures sale_percentage_filter is always submitted when filtering (same as Sale index) --}}
            <input type="hidden" name="sale_percentage_filter" value="{{ isset($sale_percentage_filter) ? $sale_percentage_filter : '' }}" />
            <div class="card-body">
                <div class="row">
                    <!-- Date Range Filter -->
                    <!-- <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>{{trans('file.Choose Your Date')}}</strong></label>
                            <div class="input-group">
                                <input type="text" class="daterangepicker-field form-control" value="{{$start_date}} To {{$end_date}}" />
                                <input type="hidden" name="start_date" value="{{$start_date}}" />
                                <input type="hidden" name="end_date" value="{{$end_date}}" />

                            </div>
                        </div>
                    </div> -->
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

                    <!-- Warehouse Filter -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>{{trans('file.Choose Warehouse')}}</strong></label>
                            <input type="hidden" name="warehouse_id_hidden" value="{{ $warehouse_id ?? 0 }}" />
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="0" {{ (isset($warehouse_id) && $warehouse_id == 0) ? 'selected' : '' }}>{{trans('file.All Warehouse')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                <option value="{{$warehouse->id}}" {{ (isset($warehouse_id) && $warehouse_id == $warehouse->id) ? 'selected' : '' }}>{{$warehouse->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Cashier/Biller Filter -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>Cashier/Biller</strong></label>
                            <select name="biller_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="0" {{ (isset($biller_id) && $biller_id == 0) ? 'selected' : '' }}>All Biller</option>
                                @foreach($lims_biller_list as $biller)
                                <option value="{{$biller->id}}" {{ (isset($biller_id) && $biller_id == $biller->id) ? 'selected' : '' }}>{{$biller->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Sales Person Filter -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>Sales Person</strong></label>
                            <select name="user_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="0" {{ (isset($user_id) && $user_id == 0) ? 'selected' : '' }}>All Sales Person</option>
                                @foreach($lims_user_list as $user)
                                <option value="{{$user->id}}" {{ (isset($user_id) && $user_id == $user->id) ? 'selected' : '' }}>{{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Payment Mode Filter -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>{{trans('file.Payment Mode')}}</strong></label>
                            <select name="payment_mode" class="selectpicker form-control">
                                <option value="0" {{ (isset($payment_mode) && ($payment_mode === '0' || $payment_mode === '')) ? 'selected' : '' }}>All Payment Mode</option>
                                <option value="Cash" {{ (isset($payment_mode) && $payment_mode === 'Cash') ? 'selected' : '' }}>{{trans('file.Cash')}}</option>
                                <option value="Credit Card" {{ (isset($payment_mode) && $payment_mode === 'Credit Card') ? 'selected' : '' }}>Credit Card</option>
                                <option value="Cheque" {{ (isset($payment_mode) && $payment_mode === 'Cheque') ? 'selected' : '' }}>{{trans('file.Cheque')}}</option>
                                <option value="Gift Card" {{ (isset($payment_mode) && $payment_mode === 'Gift Card') ? 'selected' : '' }}>{{trans('file.Gift Card')}}</option>
                                <option value="Deposit" {{ (isset($payment_mode) && $payment_mode === 'Deposit') ? 'selected' : '' }}>{{trans('file.Deposit')}}</option>
                                <option value="PayPal" {{ (isset($payment_mode) && $payment_mode === 'PayPal') ? 'selected' : '' }}>PayPal</option>
                                <option value="Mobile Money" {{ (isset($payment_mode) && $payment_mode === 'Mobile Money') ? 'selected' : '' }}>Mobile Money</option>
                            </select>
                        </div>
                    </div>

                    <!-- Department Filter -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>Department</strong></label>
                            <select name="department_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="0" {{ (isset($department_id) && $department_id == 0) ? 'selected' : '' }}>All Department</option>
                                @foreach($lims_department_list as $department)
                                <option value="{{$department->id}}" {{ (isset($department_id) && $department_id == $department->id) ? 'selected' : '' }}>{{$department->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>Category</strong></label>
                            <select name="category_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="0" {{ (isset($category_id) && $category_id == 0) ? 'selected' : '' }}>All Category</option>
                                @foreach($lims_category_list as $category)
                                <option value="{{$category->id}}" {{ (isset($category_id) && $category_id == $category->id) ? 'selected' : '' }}>{{$category->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                        </div>
                    </div>
                </div>
            </div>
            

            {!! Form::close() !!}
        </div>
    </div>
    <div class="table-responsive">
        <table id="report-table" class="table table-hover">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Product Name')}}</th>
                    <th>Department</th>
                    <th>Category</th>
                    <th>{{trans('file.Sold Amount')}}</th>
                    <th>{{trans('file.Sold Qty')}}</th>
                    <th>{{trans('file.In Stock')}}</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($report_rows))
                @foreach($report_rows as $key => $row)
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $row['product_name'] }}</td>
                    <td>{{ $row['department_name'] }}</td>
                    <td>{{ $row['category_name'] }}</td>
                    <td>{{ number_format((float) $row['sold_amount'], $general_setting->decimal ?? 2, '.', '') }}</td>
                    <td>{{ $row['sold_qty'] }}</td>
                    <td>{{ $row['in_stock'] }}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>

        <th></th>
        <th></th>
        <th></th>
        <th><strong>Total</strong></th>
        <th class="sum"></th>
        <th class="sum"></th>
        <th class="sum"></th>

</tfoot>

            <!-- <tfoot>
                <th></th>
                <th></th>
                <th></th>
                <th>Total</th>
                <th>{{number_format(0, $general_setting->decimal, '.', '')}}</th>
                <th>0</th>
                <th>0</th>
            </tfoot> -->
        </table>
    </div>
</section>


@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #sale-report-menu").addClass("active");

    // Restore all filter selections (server already set selected; refresh so selectpicker shows them)
    $('.selectpicker').selectpicker('refresh');

    $('#report-table').DataTable( {
        "order": [],
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
                    columns: ':visible:not(.not-exported)',
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
                text: '<i title="export to excel" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
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
                    columns: ':visible:not(.not-exported)',
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
                    columns: ':visible:not(.not-exported)',
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
            var decimal = {{ $general_setting->decimal ?? 2 }};
            datatable_sum(api, false);
            var soldAmount = api.column(4, { page: 'all' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            var soldQty = api.column(5, { page: 'all' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            var inStock = api.column(6, { page: 'all' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            $('#report-total-sold-amount').text(soldAmount.toFixed(decimal));
            $('#report-total-sold-qty').text(soldQty.toFixed(0));
            $('#report-total-in-stock').text(inStock.toFixed(0));
        }
    } );

function datatable_sum(dt_selector, is_calling_first) {
    if (dt_selector.rows('.selected').any() && is_calling_first) {
        var rows = dt_selector.rows('.selected').indexes();

        $(dt_selector.column(4).footer()).html(
            dt_selector.cells(rows, 4, { page: 'current' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0).toFixed({{$general_setting->decimal}})
        );
        $(dt_selector.column(5).footer()).html(
            dt_selector.cells(rows, 5, { page: 'current' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0)
        );
        $(dt_selector.column(6).footer()).html(
            dt_selector.cells(rows, 6, { page: 'current' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0)
        );
    } else {
        /* Footer shows grand total (all data), not just current page */
        $(dt_selector.column(4).footer()).html(
            dt_selector.column(4, { page: 'all' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0).toFixed({{$general_setting->decimal}})
        );
        $(dt_selector.column(5).footer()).html(
            dt_selector.column(5, { page: 'all' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0)
        );
        $(dt_selector.column(6).footer()).html(
            dt_selector.column(6, { page: 'all' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0)
        );
    }
}



</script>
<script>
$(document).ready(function() {
    // Get Blade variables or fallback to today's date
    var start = moment("{{ $start_date ?? '' }}");
    var end = moment("{{ $end_date ?? '' }}");

    // If they’re empty or invalid, fallback to today
    if (!start.isValid()) start = moment();
    if (!end.isValid()) end = moment();

    function updateDateFields(start, end) {
       // $('.daterangepicker-field').val(start.format('YYYY-MM-DD') + ' To ' + end.format('YYYY-MM-DD'));
        $('input[name="start_date"]').val(start.format('YYYY-MM-DD'));
        $('input[name="end_date"]').val(end.format('YYYY-MM-DD'));
    }

    // $('.daterangepicker-field').daterangepicker({
    //     startDate: start,
    //     endDate: end,
    //     locale: {
    //         format: 'YYYY-MM-DD',
    //         separator: ' To ',
    //     }
    // }, updateDateFields);

    updateDateFields(start, end);
});
</script>

@endpush
