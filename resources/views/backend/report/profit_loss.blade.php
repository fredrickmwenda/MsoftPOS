@extends('backend.layout.main')
@section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<section class="summary-report-section">
    <h3 class="text-center">{{trans('file.Summary Report')}}</h3>
    {!! Form::open(['route' => 'report.profitLoss', 'method' => 'post']) !!}
    <div class="container-fluid">
      
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
    </div>
    {{Form::close()}}
    <div class="container-fluid">
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-heart"></i> {{trans('file.Purchase')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)$purchase[0]->grand_total, $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-shopping-cart"></i> {{trans('file.Sale')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)$sale[0]->grand_total, $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-random "></i> {{trans('file.Sale Return')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)$return[0]->grand_total, $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-random "></i> {{trans('file.Purchase Return')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)$purchase_return[0]->grand_total, $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-4 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-money"></i> {{trans('file.profit')}} / {{trans('file.Loss')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)($sale[0]->grand_total - $product_cost - $return[0]->grand_total + $purchase_return[0]->grand_total), $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-money"></i> {{trans('file.Net Profit')}} / {{trans('file.Net Loss')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)(($sale[0]->grand_total-$sale[0]->shipping_cost-$sale[0]->tax) - ($product_cost-$product_tax) - ($return[0]->grand_total-$return[0]->tax) + ($purchase_return[0]->grand_total-$purchase_return[0]->tax) - $expense), $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-dollar"></i> {{trans('file.Payment Recieved')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)$payment_recieved, $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-dollar"></i> {{trans('file.Payment Sent')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)$payment_sent, $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-dollar"></i> {{trans('file.Expense')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)$expense, $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3><i class="fa fa-dollar"></i> {{trans('file.Payroll')}}</h3>
                        <hr>
                        <div class="mt-3 summary-card-value">{{number_format((float)$payroll, $general_setting->decimal, '.', '')}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-4 offset-md-4">
                <div class="card">
                    <div class="card-body">

                        <h3><i class="fa fa-dollar"></i> {{trans('file.Cash in Hand')}}</h3>
                        <hr>
                        <div class="mt-3">
                            <p class="mt-2">{{trans('file.Recieved')}} <span class="float-right"> {{number_format((float)($payment_recieved), $general_setting->decimal, '.', '')}}</span></p>
                            <p class="mt-2">{{trans('file.Sent')}} <span class="float-right">- {{number_format((float)($payment_sent), $general_setting->decimal, '.', '')}}</span></p>
                            <p class="mt-2">{{trans('file.Sale Return')}} <span class="float-right">- {{number_format((float)$return[0]->grand_total, $general_setting->decimal, '.', '')}}</span></p>
                            <p class="mt-2">{{trans('file.Purchase Return')}} <span class="float-right"> {{number_format((float)$purchase_return[0]->grand_total, $general_setting->decimal, '.', '')}}</span></p>
                            <p class="mt-2">{{trans('file.Expense')}} <span class="float-right">- {{number_format((float)$expense, $general_setting->decimal, '.', '')}}</span></p>
                            <p class="mt-2">{{trans('file.Payroll')}} <span class="float-right">- {{number_format((float)$payroll, $general_setting->decimal, '.', '')}}</span></p>
                            <p class="mt-2">{{trans('file.In Hand')}} <span class="float-right">{{number_format((float)($payment_recieved - $payment_sent - $return[0]->grand_total + $purchase_return[0]->grand_total - $expense - $payroll), $general_setting->decimal, '.', '')}}</span></p>
                        </div>
                    </div>
                </div>
            </div>
       
            @foreach($warehouse_name as $key => $name)
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">

                            <h3><i class="fa fa-money"></i> {{$name}}</h3>
                            <h4 class="text-center mt-3">{{number_format((float)($warehouse_sale[$key][0]->grand_total - $warehouse_purchase[$key][0]->grand_total - $warehouse_return[$key][0]->grand_total + $warehouse_purchase_return[$key][0]->grand_total), $general_setting->decimal, '.', '')}}</h4>
                            <p class="text-center">
                                {{trans('file.Sale')}} {{number_format((float)($warehouse_sale[$key][0]->grand_total), $general_setting->decimal, '.', '')}} - {{trans('file.Purchase')}} {{number_format((float)($warehouse_purchase[$key][0]->grand_total), $general_setting->decimal, '.', '')}} - {{trans('file.Sale Return')}} {{number_format((float)($warehouse_return[$key][0]->grand_total), $general_setting->decimal, '.', '')}} + {{trans('file.Purchase Return')}} {{number_format((float)($warehouse_purchase_return[$key][0]->grand_total), $general_setting->decimal, '.', '')}}
                            </p>
                            <hr style="border-color: rgba(0, 0, 0, 0.2);">
                            <h4 class="text-center">{{number_format((float)(($warehouse_sale[$key][0]->grand_total - $warehouse_sale[$key][0]->tax) - ($warehouse_purchase[$key][0]->grand_total - $warehouse_purchase[$key][0]->tax) - ($warehouse_return[$key][0]->grand_total - $warehouse_return[$key][0]->tax) + ($warehouse_purchase_return[$key][0]->grand_total - $warehouse_purchase_return[$key][0]->tax) ), $general_setting->decimal, '.', '')}}</h4>
                            <p class="text-center">
                                {{trans('file.Net Sale')}} {{number_format((float)($warehouse_sale[$key][0]->grand_total - $warehouse_sale[$key][0]->tax), $general_setting->decimal, '.', '')}} -  {{trans('file.Net Purchase')}} {{number_format((float)($warehouse_purchase[$key][0]->grand_total - $warehouse_purchase[$key][0]->tax), $general_setting->decimal, '.', '')}} - {{trans('file.Net Sale Return')}} {{number_format((float)($warehouse_return[$key][0]->grand_total - $warehouse_return[$key][0]->tax), $general_setting->decimal, '.', '')}} + {{trans('file.Net Purchase Return')}} {{number_format((float)($warehouse_purchase_return[$key][0]->grand_total - $warehouse_purchase_return[$key][0]->tax), $general_setting->decimal, '.', '')}}
                            </p>
                            <hr style="border-color: rgba(0, 0, 0, 0.2);">
                            <h4 class="text-center">{{number_format((float)$warehouse_expense[$key], $general_setting->decimal, '.', '')}}</h4>
                            <p class="text-center">{{trans('file.Expense')}}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    .summary-report-section {
        padding-bottom: 2rem;
    }

    .summary-report-section .row.mt-4,
    .summary-report-section .row.mt-2 {
        row-gap: 1.5rem;
    }

    .summary-report-section .card {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }

    .summary-report-section .card-body {
        padding: 16px 18px;
    }

    .summary-report-section h3 {
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        color: #111827;
    }

    .summary-report-section h3 i {
        font-size: 1.1rem;
        color: #13bd60;
    }

    .summary-report-section .card-body hr {
        margin: 0.4rem 0 0.8rem;
        border-color: #e5e7eb;
    }

    .summary-report-section .card-body .mt-3 {
        margin-top: 0.25rem !important;
    }

    .summary-report-section .card-body p {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        margin-bottom: 0.3rem;
        color: #4b5563;
    }

    .summary-report-section .card-body p span.float-right {
        float: none !important;
        font-weight: 600;
        color: #111827;
    }

    .summary-report-section .card-body h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #111827;
    }

    .summary-report-section .summary-card-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #111827;
        text-align: center;
        padding: 0.5rem 0;
    }
</style>

@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #profit-loss-report-menu").addClass("active");


</script>
@endpush
