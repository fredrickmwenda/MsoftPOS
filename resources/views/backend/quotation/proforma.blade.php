<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Proforma Invoice - {{ $lims_quotation_data->reference_no }}</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Segoe UI',Arial,sans-serif;font-size:13px;color:#333;background:#f4f6f9;padding:20px}
    .toolbar{max-width:900px;margin:0 auto 15px;text-align:right}
    .btn{display:inline-block;padding:8px 18px;border:none;border-radius:4px;color:#fff;text-decoration:none;cursor:pointer;font-size:13px;margin-left:6px}
    .btn-print{background:#28a745}.btn-back{background:#6c757d}
    .invoice{max-width:900px;margin:0 auto 30px;background:#fff;border:1px solid #ddd;padding:35px;box-shadow:0 1px 4px rgba(0,0,0,.08)}
    .inv-top{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #2c3e50;padding-bottom:18px}
    .inv-top h1{font-size:26px;color:#e67e22;letter-spacing:2px;margin-bottom:8px}
    .inv-top .company h2{font-size:20px;margin-bottom:6px}
    .inv-top p{line-height:1.7}
    .meta{text-align:right}
    .parties{display:flex;justify-content:space-between;margin:22px 0}
    .party{width:48%}
    .party h4{background:#2c3e50;color:#fff;padding:7px 12px;font-size:12px;text-transform:uppercase;letter-spacing:1px}
    .party .box{border:1px solid #ddd;border-top:none;padding:10px 12px;line-height:1.8;min-height:90px;background:#fcfcfc}
    table.items{width:100%;border-collapse:collapse}
    table.items th{background:#2c3e50;color:#fff;padding:10px 8px;font-size:11px;text-transform:uppercase;text-align:left}
    table.items td{border:1px solid #e3e3e3;padding:8px}
    table.items tbody tr:nth-child(even){background:#f8f9fa}
    .right{text-align:right}
    .totals-wrap{display:flex;justify-content:flex-end;margin-top:15px}
    table.totals{width:340px;border-collapse:collapse}
    table.totals td{border:1px solid #e3e3e3;padding:7px 12px}
    table.totals tr.grand td{background:#2c3e50;color:#fff;font-weight:bold;font-size:15px}
    .words{margin-top:15px;padding:10px 14px;background:#fef9e7;border-left:4px solid #e67e22}
    .terms{margin-top:22px}.terms h4{margin-bottom:6px;color:#2c3e50}
    .disclaimer{margin-top:18px;padding:10px 14px;background:#f8f9fa;border:1px dashed #bbb;color:#666;font-size:12px}
    .signatures{display:flex;justify-content:space-between;margin-top:70px}
    .signatures div{width:42%;border-top:1px dashed #555;text-align:center;padding-top:7px;color:#555}
    .footer{margin-top:30px;padding-top:12px;border-top:1px solid #ddd;text-align:center;color:#888;font-size:12px}
    @media print{body{background:#fff;padding:0}.toolbar{display:none}.invoice{border:none;box-shadow:none;padding:10px;max-width:100%}}
</style>
</head>
<body>
@php
    $currency = isset($general_setting) ? $general_setting->currency : '';
    $currency_position = isset($general_setting) ? $general_setting->currency_position : 'prefix';
    $fmt = function ($amount) use ($currency, $currency_position) {
        $value = number_format((float) $amount, config('decimal'));
        return $currency_position == 'suffix' ? $value . ' ' . $currency : $currency . $value;
    };
@endphp

<div class="toolbar">
    <a href="{{ url('quotations') }}" class="btn btn-back">&#8592; Back</a>
    <button onclick="window.print()" class="btn btn-print">&#128424; Print / Save as PDF</button>
</div>

<div class="invoice">
    <div class="inv-top">
        <div class="company">
            <h2>{{ $lims_quotation_data->biller->company_name ?: $lims_quotation_data->biller->name }}</h2>
            <p>
                {{ $lims_quotation_data->biller->address }}<br>
                {{ $lims_quotation_data->biller->city }}<br>
                Phone: {{ $lims_quotation_data->biller->phone_number }}<br>
                {{ $lims_quotation_data->biller->email }}
            </p>
        </div>
        <div class="meta">
            <h1>PROFORMA INVOICE</h1>
            <p><strong>{{ trans('file.reference') }}:</strong> {{ $lims_quotation_data->reference_no }}</p>
            <p><strong>{{ trans('file.Date') }}:</strong> {{ date(config('date_format'), strtotime($lims_quotation_data->created_at)) }}</p>
            <p><strong>Valid Until:</strong> {{ date(config('date_format'), strtotime($valid_until)) }}</p>
        </div>
    </div>

    <div class="parties">
        <div class="party">
            <h4>Quotation For / Bill To</h4>
            <div class="box">
                <strong>{{ $lims_quotation_data->customer->name }}</strong><br>
                {{ $lims_quotation_data->customer->address }}<br>
                {{ $lims_quotation_data->customer->city }}<br>
                Phone: {{ $lims_quotation_data->customer->phone_number }}
            </div>
        </div>
        <div class="party">
            <h4>Fulfilled From</h4>
            <div class="box">
                <strong>{{ $lims_quotation_data->warehouse->name }}</strong>
                @if($lims_quotation_data->supplier_id)
                    <br>Supplier: {{ $lims_quotation_data->supplier->name }}
                @endif
            </div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th>{{ trans('file.product') }}</th>
                <th>{{ trans('file.Batch No') }}</th>
                <th class="right">Qty</th>
                <th class="right">{{ trans('file.Unit Price') }}</th>
                <th class="right">{{ trans('file.Tax') }}</th>
                <th class="right">{{ trans('file.Discount') }}</th>
                <th class="right">{{ trans('file.Subtotal') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($product_data as $key => $product)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $product['name'] }}</td>
                <td>{{ $product['batch_no'] }}</td>
                <td class="right">{{ $product['qty'] }} {{ $product['unit'] }}</td>
                <td class="right">{{ $fmt($product['price']) }}</td>
                <td class="right">{{ $product['tax'] }} ({{ $product['tax_rate'] }}%)</td>
                <td class="right">{{ $fmt($product['discount']) }}</td>
                <td class="right">{{ $fmt($product['total']) }}</td>
            </tr>
            @empty
            <tr><td colspan="8">No products found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-wrap">
        <table class="totals">
            <tr><td><strong>{{ trans('file.Total') }}</strong></td><td class="right">{{ $fmt($lims_quotation_data->total_price) }}</td></tr>
            <tr><td><strong>{{ trans('file.Order Tax') }}</strong> ({{ $lims_quotation_data->order_tax_rate }}%)</td><td class="right">{{ $fmt($lims_quotation_data->order_tax) }}</td></tr>
            <tr><td><strong>{{ trans('file.Order Discount') }}</strong></td><td class="right">{{ $fmt($lims_quotation_data->order_discount) }}</td></tr>
            <tr><td><strong>{{ trans('file.Shipping Cost') }}</strong></td><td class="right">{{ $fmt($lims_quotation_data->shipping_cost) }}</td></tr>
            <tr class="grand"><td>{{ trans('file.grand total') }}</td><td class="right">{{ $fmt($lims_quotation_data->grand_total) }}</td></tr>
        </table>
    </div>

    @if($amount_in_words)
    <div class="words"><strong>Amount in words:</strong> {{ $amount_in_words }} only.</div>
    @endif

    <div class="terms">
        <h4>{{ trans('file.Note') }}</h4>
        <p>{!! nl2br($lims_quotation_data->note ?? '') !!}</p>
    </div>

    <div class="disclaimer">
        <strong>Note:</strong> This is a <strong>Proforma Invoice</strong> issued for reference/approval purposes only.
        It is <strong>not a tax invoice</strong> and not a demand for payment. Prices are valid until
        {{ date(config('date_format'), strtotime($valid_until)) }}. A final invoice will be issued upon order confirmation.
    </div>

    <div class="signatures">
        <div>Customer Signature</div>
        <div>Authorized Signature</div>
    </div>

    <div class="footer">
        {{ $lims_quotation_data->biller->company_name ?: $lims_quotation_data->biller->name }}
        &nbsp;|&nbsp; {{ $lims_quotation_data->biller->phone_number }}
        &nbsp;|&nbsp; {{ $lims_quotation_data->biller->email }}
    </div>
</div>
</body>
</html>
