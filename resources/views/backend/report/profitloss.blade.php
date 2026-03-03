@extends('backend.layout.main')

@section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">
                    Profit and Loss Report (<span id="selectedPeriod">{{ $period_label ?? ($selected_year ?? date('Y')) }}</span>)
                </h3>
            </div>

            {{-- Filter Form --}}
            {!! Form::open(['route' => 'report.profitLossData', 'method' => 'post', 'id' => 'yearForm']) !!}
            @csrf
            <div class="row mb-3 pl-report-filter">
                <div class="col-md-3 offset-md-1 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>Choose Year</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select name="year" id="year" class="form-control" required>
                                @php
                                    $currentYear = date('Y');
                                    $startYear = $currentYear - 10;
                                @endphp
                                @for ($year = $currentYear; $year >= $startYear; $year--)
                                    <option value="{{ $year }}"
                                        {{ ($selected_year ?? $currentYear) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>Choose Month</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select name="month" id="month" class="form-control">
                                <option value="">All (Full Year)</option>
                                @foreach(['01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'] as $num => $name)
                                    <option value="{{ $num }}" {{ ($selected_month ?? '') === $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">{{ trans('file.submit') }}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        {{-- Action Buttons --}}
        <div class="mb-3">
            <button class="btn btn-primary" id="printReport"><i class="fa fa-print"></i> Print Report</button>
            <button class="btn btn-success ml-2" id="exportExcel"><i class="fa fa-file-excel"></i> Export Excel</button>
            <button class="btn btn-danger ml-2" id="exportPDF"><i class="fa fa-file-pdf"></i> Export PDF</button>
        </div>

        {{-- P&L Statement Table --}}
        <div class="table-responsive">
            <table class="table table-bordered" style="width: 100%; max-width: 800px;" id="profitLossTable">
                {{-- Report Header --}}
                <thead class="table-dark">
                    <tr>
                        <th colspan="3" class="text-center">
                            <h4 class="mb-0">PROFIT AND LOSS STATEMENT</h4>
                            <p class="mb-0">{{ $period_subtitle ?? 'For the Year Ending ' . $selected_year }}</p>
                        </th>
                    </tr>
                </thead>

                {{-- Revenue Section --}}
                <thead class="table-secondary">
                    <tr>
                        <th colspan="3"><strong>REVENUE</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="70%">Sales Revenue</td>
                        <td width="15%" class="text-end">{{ $currency_code }}</td>
                        <td width="15%" class="text-end">{{ number_format($total_sale, 2) }}</td>
                    </tr>
                    
                    {{-- Optional: Show returns if you want to calculate net revenue --}}
                    @if($sale_return_amount > 0)
                    <tr>
                        <td>&nbsp;&nbsp;Less: Sales Returns</td>
                        <td class="text-end">- {{ $currency_code }}</td>
                        <td class="text-end">{{ number_format($sale_return_amount, 2) }}</td>
                    </tr>
                    @endif
                    
                    {{-- Net Revenue row (optional) --}}
                    @if($sale_return_amount > 0)
                    <tr>
                        <td><strong>Net Sales Revenue</strong></td>
                        <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($total_sale - $sale_return_amount, 2) }}</strong></td>
                    </tr>
                    @endif
                </tbody>

                {{-- Cost of Goods Sold Section --}}
                <thead class="table-secondary">
                    <tr>
                        <th colspan="3"><strong>COST OF GOODS SOLD</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cost of Products Sold (Purchases)</td>
                        <td class="text-end">{{ $currency_code }}</td>
                        <td class="text-end">{{ number_format($product_cost, 2) }}</td>
                    </tr>
                    
                    {{-- Optional: Purchase returns adjustment --}}
                    @if($purchase_return_amount > 0)
                    <tr>
                        <td>&nbsp;&nbsp;Less: Purchase Returns</td>
                        <td class="text-end">- {{ $currency_code }}</td>
                        <td class="text-end">{{ number_format($purchase_return_amount, 2) }}</td>
                    </tr>
                    @endif
                    
                    <tr>
                        <td>Direct Labor (Payroll)</td>
                        <td class="text-end">{{ $currency_code }}</td>
                        <td class="text-end">{{ number_format($payroll, 2) }}</td>
                    </tr>
                    
                    {{-- Total COGS --}}
                    @if($purchase_return_amount > 0)
                        @php $net_purchases = $product_cost - $purchase_return_amount; @endphp
                        <tr class="table-active">
                            <td><strong>Total Cost of Goods Sold</strong></td>
                            <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($net_purchases + $payroll, 2) }}</strong></td>
                        </tr>
                    @else
                        <tr class="table-active">
                            <td><strong>Total Cost of Goods Sold</strong></td>
                            <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($product_cost + $payroll, 2) }}</strong></td>
                        </tr>
                    @endif
                </tbody>

                {{-- Gross Profit Section --}}
                <tbody>
                    @if($sale_return_amount > 0 && $purchase_return_amount > 0)
                        @php 
                            $net_revenue = $total_sale - $sale_return_amount;
                            $net_cogs = ($product_cost - $purchase_return_amount) + $payroll;
                        @endphp
                        <tr class="table-success">
                            <td><strong>GROSS PROFIT</strong></td>
                            <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($net_revenue - $net_cogs, 2) }}</strong></td>
                        </tr>
                    @elseif($sale_return_amount > 0)
                        @php $net_revenue = $total_sale - $sale_return_amount; @endphp
                        <tr class="table-success">
                            <td><strong>GROSS PROFIT</strong></td>
                            <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($net_revenue - ($product_cost + $payroll), 2) }}</strong></td>
                        </tr>
                    @else
                        <tr class="table-success">
                            <td><strong>GROSS PROFIT</strong></td>
                            <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($gross_profit, 2) }}</strong></td>
                        </tr>
                    @endif
                </tbody>

                {{-- Operating Expenses Section --}}
                <thead class="table-secondary">
                    <tr>
                        <th colspan="3"><strong>OPERATING EXPENSES</strong></th>
                    </tr>
                </thead>
                <tbody>
                    @if($filtered_expenses->count() > 0)
                        @foreach($filtered_expenses as $expense)
                            <tr>
                                <td>{{ $expense->name }}</td>
                                <td class="text-end">{{ $currency_code }}</td>
                                <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="text-center">No operating expenses recorded</td>
                        </tr>
                    @endif
                    
                    <tr class="table-active">
                        <td><strong>Total Operating Expenses</strong></td>
                        <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($total_operating_expenses, 2) }}</strong></td>
                    </tr>
                </tbody>

                {{-- Operating Profit Section --}}
                <tbody>
                    <tr class="table-info">
                        <td><strong>OPERATING PROFIT</strong></td>
                        <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($operating_profit, 2) }}</strong></td>
                    </tr>
                </tbody>

                {{-- Taxes Section --}}
                <thead class="table-secondary">
                    <tr>
                        <th colspan="3"><strong>TAXES</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Product Tax Expense</td>
                        <td class="text-end">{{ $currency_code }}</td>
                        <td class="text-end">{{ number_format($product_tax, 2) }}</td>
                    </tr>
                </tbody>

                {{-- Net Profit/Loss Section --}}
                <tbody>
                    <tr class="{{ $net_profit >= 0 ? 'table-success' : 'table-danger' }}">
                        <td>
                            <strong>
                                @if($net_profit >= 0)
                                    NET PROFIT
                                @else
                                    NET LOSS
                                @endif
                            </strong>
                        </td>
                        <td class="text-end"><strong>{{ $currency_code }}</strong></td>
                        <td class="text-end"><strong>{{ number_format(abs($net_profit), 2) }}</strong></td>
                    </tr>
                </tbody>

                {{-- Summary Footer --}}
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="3" class="text-center small">
                            <em>
                                Report generated on {{ date('F d, Y') }} | 
                                Period: {{ date('F d, Y', strtotime($start_date)) }} to {{ date('F d, Y', strtotime($end_date)) }}
                            </em>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
$(document).ready(function() {
    const monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    function updateSelectedPeriod() {
        const year = $('#year').val() || new Date().getFullYear();
        const monthVal = $('#month').val();
        const period = monthVal ? monthNames[parseInt(monthVal, 10)] + ' ' + year : year;
        $('#selectedPeriod').text(period);
    }
    updateSelectedPeriod();
    $('#year, #month').on('change', updateSelectedPeriod);
    $('#yearForm').on('submit', function() {
        updateSelectedPeriod();
    });

    // Print Report
    $('#printReport').on('click', function() {
        const year = $('#selectedPeriod').text() || new Date().getFullYear();
        const printContents = document.getElementById('profitLossTable').outerHTML;
        const printWindow = window.open('', '', 'height=900,width=1000');

        printWindow.document.write('<html><head><title>Profit and Loss Report - ' + (year || '') + '</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style>@media print {body {padding: 20px;}}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    });

    // Export Excel
    $('#exportExcel').on('click', function() {
        const year = $('#selectedPeriod').text() || new Date().getFullYear();
        const table = document.getElementById('profitLossTable');
        const html = table.outerHTML;
        
        // Create a blob and download
        const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
        const fileName = 'Profit_Loss_Report_' + year + '.xls';
        
        if (window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(blob, fileName);
        } else {
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = fileName;
            a.click();
            URL.revokeObjectURL(a.href);
        }
    });

    // Export PDF (using browser print to PDF)
    $('#exportPDF').on('click', function() {
        const year = $('#selectedPeriod').text() || new Date().getFullYear();
        const printWindow = window.open('', '', 'height=900,width=1000');
        const tableHTML = document.getElementById('profitLossTable').outerHTML;

        printWindow.document.write('<html><head><title>Profit and Loss Report - ' + (year || '') + '</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(tableHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        
        // Trigger print dialog (user can choose to save as PDF)
        setTimeout(function() {
            printWindow.print();
        }, 500);
    });
});
</script>
@endpush