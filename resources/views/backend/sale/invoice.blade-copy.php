
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{url('logo', $general_setting->site_logo)}}" />
    <title>{{$general_setting->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        * {
            font-size: 14px;multi payment
            line-height: 24px;
            font-family: 'Ubuntu', sans-serif;
            text-transform: capitalize;
        }
        .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor:pointer;
        }

        .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }
        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }
        tr {border-bottom: 1px dotted #ddd;}
        td,th {padding: 4px 2px;}
        .item-col { width: 40%; }
        .unit-col { width: 10%; }
        .qty-col { width: 15%; }
        .price-col { width: 15%; }
        .eprice-col { width: 20%; text-align: right; }
        
        table {width: 100%;}
        tfoot tr th:first-child {text-align: left;}

        .centered {
            text-align: center;
            align-content: center;
        }
        .date-time {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }
        small{font-size:11px;}

        @media print {
            * {
                font-size:12px;
                line-height: 20px;
            }
            td,th {padding: 5px 0;}
            .hidden-print {
                display: none !important;
            }
            @page { margin: 1.5cm 0.5cm 0.5cm; }
            @page:first { margin-top: 0.5cm; }
            /*tbody::after {
                content: ''; display: block;
                page-break-after: always;
                page-break-inside: avoid;
                page-break-before: avoid;
            }*/
        }
    </style>
  </head>
<body>

<div style="max-width:400px;margin:0 auto">
    @if(preg_match('~[0-9]~', url()->previous()))
        @php $url = '../../pos'; @endphp
    @else
        @php $url = url()->previous(); @endphp
    @endif
    <div class="hidden-print d-none">
        <button id="print-and-redirect" class="btn btn-primary"><i class="dripicons-print"></i> {{trans('file.Print')}}</button>
    </div>

    <div id="receipt-data">
        <div class="centered">
            @if($general_setting->site_logo)
                <img src="{{url('logo', $general_setting->site_logo)}}" height="42" width="50" style="margin:10px 0;">
            @endif

            <h2>{{$lims_biller_data->company_name}}</h2>

            <p>{{trans('file.Address')}}: {{$lims_warehouse_data->address}}
                <br>{{trans('file.Phone Number')}}: {{$lims_warehouse_data->phone}}
                @if($general_setting->vat_registration_number)
                <br>{{trans('file.VAT Number')}}: {{$general_setting->vat_registration_number}}
                @endif
            </p>
        </div>
        <div class="date-time">
            <span>{{trans('file.Date')}}: {{ date($general_setting->date_format, strtotime($lims_sale_data->created_at)) }}</span>
            <span>Time: {{ $lims_sale_data->created_at->format('H:i A') }}</span>
        </div>
        <p>{{trans('file.reference')}}: {{$lims_sale_data->reference_no}}<br>
            {{trans('file.customer')}}: {{$lims_customer_data->name}}
            @if($lims_sale_data->table_id)
            <br>{{trans('file.Table')}}: {{$lims_sale_data->table->name}}
            <br>{{trans('file.Queue')}}: {{$lims_sale_data->queue}}
            @endif
            <?php
                foreach($sale_custom_fields as $key => $fieldName) {
                    $field_name = str_replace(" ", "_", strtolower($fieldName));
                    echo '<br>'.$fieldName.': ' . $lims_sale_data->$field_name;
                }
                foreach($customer_custom_fields as $key => $fieldName) {
                    $field_name = str_replace(" ", "_", strtolower($fieldName));
                    echo '<br>'.$fieldName.': ' . $lims_customer_data->$field_name;
                }
            ?>

        </p>
        <table class="table-data">
            <thead>
                <tr>
                    <th class="item-col">Item</th>
                    <th class="unit-col">Unit</th>
                    <th class="qty-col">Qty</th>
                    <th class="price-col">Price</th>
                    <th class="eprice-col">E.Price</th>
                </tr>
            </thead>
            <tbody>
                <?php $total_product_tax = 0; $productTaxSummary=[];?>
                @foreach($lims_product_sale_data as $key => $product_sale_data)
                <?php
                    $lims_product_data = \App\Models\Product::find($product_sale_data->product_id);
                    if($product_sale_data->variant_id) {
                        $variant_data = \App\Models\Variant::find($product_sale_data->variant_id);
                        $product_name = $lims_product_data->name.' ['.$variant_data->name.']';
                    }
                    elseif($product_sale_data->product_batch_id) {
                        $product_batch_data = \App\Models\ProductBatch::select('batch_no')->find($product_sale_data->product_batch_id);
                        $product_name = $lims_product_data->name.' ['.trans("file.Batch No").':'.$product_batch_data->batch_no.']';
                    }
                    else
                        $product_name = $lims_product_data->name ?? '';

                    if($product_sale_data->imei_number) {
                        $product_name .= '<br>'.trans('IMEI or Serial Numbers').': '.$product_sale_data->imei_number;
                    }
                ?>
                <tr>
                    <td class="item-col">{!!$product_name!!}</td>
                    <td class="unit-col">PCS</td>
                    <td class="qty-col">{{$product_sale_data->qty}}</td>
                    <td class="price-col">
                        {{number_format((float)($product_sale_data->total / $product_sale_data->qty), $general_setting->decimal, '.', ',')}}
                    </td>
                    <td class="eprice-col">{{number_format((float)($product_sale_data->total), $general_setting->decimal, '.', ',')}}</td>
                        @foreach($product_custom_fields as $index => $fieldName)
                            <?php $field_name = str_replace(" ", "_", strtolower($fieldName)) ?>
                            @if($lims_product_data->$field_name)
                                @if(!$index)
                                <br>{{$fieldName.': '.$lims_product_data->$field_name}}
                                @else
                                {{'/'.$fieldName.': '.$lims_product_data->$field_name}}
                                @endif
                            @endif
                        @endforeach

                        @if($product_sale_data->tax_rate)
                            <?php $total_product_tax += $product_sale_data->tax ?>
                            [{{trans('file.Tax')}} ({{$product_sale_data->tax_rate}}%): {{$product_sale_data->tax}}]
                        @endif
                    </td>
                </tr>
                @endforeach

            <!-- <tfoot> -->
                <tr>
                    <th colspan="3" style="text-align:right">{{trans('file.Total')}}</th>
                    <th colspan="2" style="text-align:right">{{number_format((float)($lims_sale_data->total_price), $general_setting->decimal, '.', ',')}}</th>
                </tr>
                @if($general_setting->invoice_format == 'gst' && $general_setting->state == 1)
                <tr>
                    <td colspan="2" style="text-align:right">IGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax), $general_setting->decimal, '.', ',')}}</td>
                </tr>
                @elseif($general_setting->invoice_format == 'gst' && $general_setting->state == 2)
                <tr>
                    <td colspan="2" style="text-align:right">SGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax / 2), $general_setting->decimal, '.', ',')}}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right">CGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax / 2), $general_setting->decimal, '.', ',')}}</td>
                </tr>
                @endif
                <tr><td colspan="5"><hr style="border-top: 1px dotted #000;"></td></tr>
            <tr>
                <td colspan="3" style="text-align:right">Total Qty</td>
                <td colspan="2" style="text-align:right">{{$lims_sale_data->total_qty}}</td>
            </tr>
            <tr>
                <td colspan="3"style="text-align:right">{{trans('file.Subtotal')}}</td>
                <td colspan="2" style="text-align:right">{{number_format((float)($lims_sale_data->total_price), $general_setting->decimal, '.', ',')}}</td>
            </tr>
            @if($lims_sale_data->order_tax)
            <tr>
                <td colspan="3"style="text-align:right">VAT Tax</td>
                <td colspan="2" style="text-align:right">{{number_format((float)($lims_sale_data->order_tax), $general_setting->decimal, '.', ',')}}</td>
            </tr>
            @endif

            {{-- ✅ PRODUCT TAXES FROM PIVOT TABLE (AFTER ORDER TAX) --}}
@php
    $taxTotals = [];
@endphp

@foreach($lims_product_sale_data as $product_sale_data)
    @php
        $product = \App\Models\Product::find($product_sale_data->product_id);
    @endphp

    @if($product && $product->product_taxes && $product->product_taxes->count())
        @foreach($product->product_taxes as $tax)
            @php
                $taxKey = $tax->id; // safer than name

                $taxAmount = ($product_sale_data->total * $tax->rate) / 100;

                if (!isset($taxTotals[$taxKey])) {
                    $taxTotals[$taxKey] = [
                        'name'   => $tax->name,
                        'rate'   => $tax->rate,
                        'amount' => 0
                    ];
                }

                $taxTotals[$taxKey]['amount'] += $taxAmount;
            @endphp
        @endforeach
    @endif
@endforeach
@foreach($taxTotals as $tax)
    <tr>
        <td colspan="3" style="text-align:right">
            {{ $tax['name'] }} ({{ $tax['rate'] }}%)
        </td>
        <td colspan="2" style="text-align:right">
            {{ number_format($tax['amount'], $general_setting->decimal, '.', ',') }}
        </td>
    </tr>
@endforeach




                @if($lims_sale_data->order_discount)
                <tr>
                    <th colspan="3" style="text-align:right">{{trans('file.Order Discount')}}</th>
                    <th colspan="2" style="text-align:right">{{number_format((float)($lims_sale_data->order_discount), $general_setting->decimal, '.', ',')}}</th>
                </tr>
                @endif
                @if($lims_sale_data->coupon_discount)
                <tr>
                    <th colspan="3" style="text-align:right">{{trans('file.Coupon Discount')}}</th>
                    <th colspan="2" style="text-align:right">{{number_format((float)($lims_sale_data->coupon_discount), $general_setting->decimal, '.', ',')}}</th>
                </tr>
                @endif
                @if($lims_sale_data->shipping_cost)
                <tr>
                    <th colspan="3" style="text-align:right">{{trans('file.Shipping Cost')}}</th>
                    <th colspan="2" style="text-align:right">{{number_format((float)($lims_sale_data->shipping_cost), $general_setting->decimal, '.', ',')}}</th>
                </tr>
                @endif
                <tr>
                    <th colspan="3" style="text-align:right">{{trans('file.grand total')}}</th>
                    <th colspan="2" style="text-align:right">{{number_format((float)($lims_sale_data->grand_total), $general_setting->decimal, '.', ',')}}</th>
                </tr>
                <!-- <tr>
                    @if($general_setting->currency_position == 'prefix')
                    <th class="centered" colspan="3">{{trans('file.In Words')}}: <span>{{$currency_code}}</span> <span>{{str_replace("-"," ",$numberInWords)}}</span></th>
                    @else
                    <th class="centered" colspan="3">{{trans('file.In Words')}}: <span>{{str_replace("-"," ",$numberInWords)}}</span> <span>{{$currency_code}}</span></th>
                    @endif
                </tr> -->
            </tbody>
            <!-- </tfoot> -->
        </table>
        <table>
            <tbody>
    @php
    $total_paid = 0;
    $total_change = 0;
@endphp

@foreach($lims_payment_data as $payment_data)
    @php
        $total_paid += $payment_data->amount;

        // Change for this payment ONLY if cash
        $change = 0;
        if($payment_data->paying_method == 'Cash') {
            $change = max($total_paid - $lims_sale_data->grand_total, 0);
        }
    @endphp
    <tr style="background-color:#ddd;">
        <td style="padding: 5px;width:30%">
            {{ trans('file.Paid By') }}: {{ $payment_data->paying_method }}
        </td>
        <td style="padding: 5px;width:40%">
            {{ trans('file.Amount') }}: {{ number_format($payment_data->amount, $general_setting->decimal, '.', ',') }}
        </td>
        <td style="padding: 5px;width:30%">
            {{ trans('file.Change') }}: {{ number_format($change , $general_setting->decimal, '.', ',') }}
        </td>
    </tr>
@endforeach


{{-- 🔹 Show summary row --}}
<tr style="background-color:#ccc;">
    <td style="padding: 5px;"><strong>Total Paid</strong></td>
    <td style="padding: 5px;" colspan="2">
        <strong>{{ number_format($total_paid, $general_setting->decimal, '.', ',') }}</strong>
    </td>
</tr>

@if($total_change > 0)
<tr style="background-color:#ccc;">
    <td style="padding: 5px;"><strong>{{ trans('file.Total Change') }}</strong></td>
    <td style="padding: 5px;" colspan="2">
        <strong>{{ number_format($total_change, $general_setting->decimal, '.', ',') }}</strong>
    </td>
</tr>
@endif

                <tr><td class="centered" colspan="3">{{trans('file.Thank you for shopping with us. Please come again')}}</td></tr>
                <!-- <tr>
                    <td class="centered" colspan="3">
                        <img 
                            src="data:image/png;base64,{{ DNS1D::getBarcodePNG($lims_sale_data->reference_no, 'C128') }}" 
                            width="300" alt="Barcode" style="margin-top:10px;"><br>

                        @php
                            $qrData = $lims_sale_data->reference_no . ' | ' . 
                                    $lims_sale_data->grand_total . ' | ' . 
                                    $lims_sale_data->created_at;
                        @endphp
                        <img 
                            src="data:image/png;base64,{{ DNS2D::getBarcodePNG($qrData, 'QRCODE') }}" 
                            width="150" alt="QR Code" style="margin-top:10px;">
                    </td>
                </tr> -->

            </tbody>
        </table>
        <!-- <div class="centered" style="margin:30px 0 50px">
            <small>{{trans('file.Invoice Generated By')}} {{$general_setting->site_title}}.
            {{trans('file.Developed By')}} LionCoders</strong></small>
        </div> -->
    </div>
</div>

<script type="text/javascript">
    localStorage.clear();
    function auto_print() {
        window.print();
        setTimeout(function() {
            window.location.href = '../../pos';
        }, 1000);
    }
    setTimeout(auto_print, 500);
</script>

</body>
</html>
