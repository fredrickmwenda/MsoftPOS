@extends('backend.layout.main')

@section('content')

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">
                    Profit and Loss Report (<span id="selectedYear">{{ $selected_year ?? date('Y') }}</span>)
                </h3>
            </div>

            {{-- ✅ Filter Form --}}
            {!! Form::open(['route' => 'report.profitLossData', 'method' => 'post', 'id' => 'yearForm']) !!}
            @csrf
            <div class="row mb-3 pl-report-filter">
                <div class="col-md-5 offset-md-1 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>Choose Year</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select name="year" id="year" class="form-control" required>
                                @php
                                    $currentYear = date('Y');
                                    $startYear = $currentYear - 10; // last 10 years
                                @endphp
                                @for ($year = $currentYear; $year >= $startYear; $year--)
                                    <option value="{{ $year }}"
                                        {{ old('year', $selected_year ?? $currentYear) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
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


        {{-- ✅ Data Table --}}
        <div class="table-responsive">

            <table class="table table-bordered" style="width: 100%; max-width: 800px;" id="profitLossTable">
                {{-- Table Header --}}
                <thead>
                    <tr>
                        <th colspan="2"><strong>Revenue</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sales Revenue</td>
                        <td>{{ $currency_code }}{{ number_format($product_cost, 2) }}</td>
                    </tr>

                </tbody>

                <thead>
                    <tr>
                        <th colspan="2"><strong>Cost of Goods Sold</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Purchases</td>
                        <td>{{ $currency_code }}{{ number_format($total_purchase, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Employees Payment</td>
                        <td>{{ $currency_code }}{{ number_format($payroll, 2) }}</td>
                    </tr>
                    <!-- <tr>
                        <td>Overhead</td>
                        <td>$0.00</td>
                    </tr> -->
                    <tr>
                        <th>Total Cost of Goods Sold</th>
                        <th>{{ $currency_code }}{{ number_format($total_purchase + $payroll, 2) }}</th>
                    </tr>
                </tbody>

                <thead>
                    <tr>
                        <th colspan="2"><strong>Operating Expenses</strong></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filtered_expenses as $expense)
                        <tr>
                            <td>{{ $expense->name }}</td>
                            <td>{{ $currency_code }}{{ number_format($expense->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>Total Operating Expenses</th>
                        <th>{{ $currency_code }}{{ number_format($total_operating_expenses, 2) }}</th>
                    </tr>
                </tbody>

                <thead>
                    <tr>
                        <th colspan="2"><strong>Earnings Summary</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Net Profit Loss/Profit = Sales Revenue - (Expenses +Purchases) -->
                    @php
                        $total_revenue = $product_cost;
                        $cogs = $total_purchase + $payroll; // Materials replaced by Purchases total

                        // EBIT = total revenue - COGS - expenses
                        $ebit = $total_revenue - $cogs - $total_operating_expenses;

                        // Interest revenue (if any future data, placeholder for now)
                        $interest_income = 0;

                        // EBT = EBIT + Interest
                        $ebt = $ebit + $interest_income;

                        // Tax from products sold
                        $tax_expense = $product_tax;

                        // Net Profit = EBT - Tax
                        $net_profit = $ebt - $tax_expense;
                    @endphp
                    <tr>
                        <td><strong>Earnings Before Interest and Taxes</strong></td>
                        <td>{{ $currency_code }}{{ number_format($ebit, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Interest Income</td>
                        <td>{{ $currency_code }}{{ number_format($interest_income, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Earnings Before Taxes</strong></td>
                        <td>{{ $currency_code }}{{ number_format($ebt, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Tax Expense</td>
                        <td>{{ $currency_code }}{{ number_format($tax_expense, 2) }}</td>
                    </tr>
                    <tr>
                        <th>
                            @if ($net_profit < 0)
                                Net Loss
                            @else
                                Net Profit
                            @endif
                        </th>
                        <th>{{ $currency_code }}{{ number_format($net_profit, 2) }}</th>

                        <!-- <th>${{ number_format(abs($net_profit), 2) }}</th> abs() to show positive value -->
                    </tr>

                </tbody>
            </table>
            
        </div>
    </div>
</section>
@endsection

@push('scripts')
<!-- Optional: FileSaver.js for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
$(document).ready(function() {

    // 🔹 Add buttons before table
    $('<button class="btn btn-primary mb-3" id="printReport"><i class="fa fa-print"></i> Print Report</button>')
        .insertBefore('#profitLossTable');
    $('<button class="btn btn-success mb-3 ml-2" id="exportExcel"><i class="fa fa-file-excel"></i> Export Excel</button>')
        .insertBefore('#profitLossTable');
    $('<button class="btn btn-danger mb-3 ml-2" id="exportPDF"><i class="fa fa-file-pdf"></i> Export PDF</button>')
        .insertBefore('#profitLossTable');

    // ✅ Set and update year
    const currentYear = new Date().getFullYear();
    const selectedYear = $('#year').val() || currentYear;
    $('#selectedYear').text(selectedYear);

    $('#yearForm').on('submit', function() {
        const yearValue = $('#year').val() || currentYear;
        $('#selectedYear').text(yearValue);
    });

    // ✅ Get year helper
    function getSelectedYear() {
        return $('#selectedYear').text() || new Date().getFullYear();
    }

    // ✅ Print handler
    $('#printReport').on('click', function() {
        const year = getSelectedYear();
        const printContents = document.getElementById('profitLossTable').outerHTML;
        const printWindow = window.open('', '', 'height=900,width=1000');

        printWindow.document.write('<html><head><title>Profit and Loss Report ('+year+')</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="text-center mb-3">');
        printWindow.document.write('<h3>Profit and Loss Report ('+year+')</h3>');
        printWindow.document.write('<p><strong>Date:</strong> ' + new Date().toLocaleDateString() + '</p>');
        printWindow.document.write('</div>');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });

    // ✅ Excel export handler
    $('#exportExcel').on('click', function() {
        const year = getSelectedYear();
        const tableHTML = document.getElementById('profitLossTable').outerHTML.replace(/ /g, '%20');
        const filename = 'Profit_and_Loss_Report_'+year+'.xls';
        const dataType = 'application/vnd.ms-excel';

        const downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
        document.body.removeChild(downloadLink);
    });

    // ✅ PDF export handler (print-to-PDF)
    $('#exportPDF').on('click', function() {
        const year = getSelectedYear();
        const printContents = document.getElementById('profitLossTable').outerHTML;
        const pdfWindow = window.open('', '', 'height=900,width=1000');

        pdfWindow.document.write('<html><head><title>Profit and Loss Report ('+year+')</title>');
        pdfWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        pdfWindow.document.write('</head><body>');
        pdfWindow.document.write('<div class="text-center mb-3">');
        pdfWindow.document.write('<h3>Profit and Loss Report ('+year+')</h3>');
        pdfWindow.document.write('<p><strong>Date:</strong> ' + new Date().toLocaleDateString() + '</p>');
        pdfWindow.document.write('</div>');
        pdfWindow.document.write(printContents);
        pdfWindow.document.write('</body></html>');
        pdfWindow.document.close();
        pdfWindow.print();
    });

});

</script>
@endpush




