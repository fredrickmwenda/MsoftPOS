@extends('backend.layout.main') @section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Payment Report')}}</h3>
            </div>
            {!! Form::open(['route' => 'report.paymentByDate', 'method' => 'post']) !!}
            <div class="row">
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
            <!-- Select filter for suppliers-->
             <div class="col-md-3 mt-3 mb-3">
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

             <div class="col-md-2 mt-3 mb-3" style="display: flex; align-items: flex-end;">
                <div class="form-group w-100">
                    <button class="btn btn-primary w-100" type="submit">{{trans('file.submit')}}</button>
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
        <table id="report-table" class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Date</th>
                    <th>Payment Reference</th>
                    <th>Sale Reference</th>
                    <th>Purchase Reference</th>
                    <th>Paid By</th>
                    <th>Amount</th>
                    <th>Created By</th>
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
                    <td><input type="checkbox" class="row-checkbox"></td>
                    <td>{{date($general_setting->date_format, strtotime($payment->created_at->toDateString()))}}</td>
                    <td>{{$payment->payment_reference}}</td>
                    <td>@if($sale){{$sale->reference_no}}@else N/A @endif</td>
                    <td>@if($purchase){{$purchase->reference_no}}@else N/A @endif</td>
                    <td>{{$payment->paying_method}}</td>
                    <td class="text-right">{{$payment->amount}}</td>
                    <td>{{$user->name}}</td>
                </tr>
                @endforeach  
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>Total:</th>
                    <th></th>
                </tr>
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

    var table = $('#report-table').DataTable( {
        "order": [[0, 'desc']],
        "pageLength": 10,
        'language': {
            'lengthMenu': '_MENU_ records per page',
            "info": 'Showing _START_ to _END_ of _TOTAL_ entries',
            "search": 'Search:',
            'paginate': {
                'previous': '<i class="fa fa-chevron-left"></i>',
                'next': '<i class="fa fa-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': 0
            }
        ],
        'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        'dom': '<"row"<"col-md-6"l><"col-md-6"f>>rtp',
        'drawCallback': function() {
            var api = this.api();
            datatable_sum(api);
        }
    } );

    function datatable_sum(dt_selector) {
        var total = dt_selector.column(6, {page: 'all'}).data().sum();
        dt_selector.column(6).footer().innerHTML = '<strong>' + total.toFixed(2) + '</strong>';
    }

    // Payment method change handler
    document.addEventListener('DOMContentLoaded', function () {
        var paymentMethodsSelect = document.getElementById('payment_methods');
        if (paymentMethodsSelect) {
            paymentMethodsSelect.addEventListener('change', function () {
                document.getElementById('paymentForm').submit();
            });
        }
    });
</script>
@endpush
