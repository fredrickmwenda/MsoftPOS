@extends('backend.layout.main')

@push('css')
<style type="text/css">
    .top-fields { margin-top:10px; position: relative; }
    .top-fields label { font-size:11px; font-weight:600; margin-left:10px; padding:0 3px; position:absolute; top:-8px; z-index:9; background-color: white; }
    .top-fields input { font-size:13px; height:36px; }
    .btn-block { height: 36px; display: flex; align-items: center; justify-content: center; }

    /* =========================================
       PRINT STYLES
       ========================================= */
    @media print {
        /* Hide everything on the page */
        body * {
            visibility: hidden;
        }

        /* Make the report content and its children visible */
        #report-content, #report-content * {
            visibility: visible;
        }

        /* Position the report at the top left of the page */
        #report-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Remove backgrounds so text is readable on paper and doesn't waste ink */
        .bg-light, .bg-primary, .bg-info, .bg-warning, .bg-secondary, .bg-success {
            background-color: transparent !important;
            color: #000 !important;
            border-bottom: 2px solid #000 !important;
        }

        /* Ensure table borders print correctly */
        .table-bordered th, .table-bordered td {
            border: 1px solid #000 !important;
        }

        /* Hide the filter card and print button explicitly just in case */
        #filter-card, .btn-icon, .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<section>
    <div class="container-fluid">
        <form id="filter-form" action="{{ route('accounting.cash-flow') }}" method="GET">
            <div class="card mt-3 mb-2 no-print" id="filter-card">
                <div class="card-body">
                    <h3 class="text-center mt-2">{{__('file.Cash Flow Statement')}}</h3>

                    <!-- Added class 'no-print' to the print button so it hides on paper -->
                    <button class="btn btn-primary btn-icon no-print" onclick="window.print();" type="button" style="position: absolute; top: 15px; right: 15px"><i class="ti ti-printer"></i> Print</button>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="form-group top-fields">
                                <label>{{ __('file.Warehouse') }}</label>
                                <select name="warehouse_id" class="form-control selectpicker" data-live-search="true">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{$warehouse->id}}" {{ isset($warehouse_id) && $warehouse_id == $warehouse->id ? 'selected' : '' }}>{{$warehouse->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group top-fields">
                                <label>{{__('file.start_date')}}</label>
                                <input type="date" class="form-control" name="start_date" value="{{ $startDate ?? date('Y-m-d') }}" required />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group top-fields">
                                <label>{{__('file.end_date')}}</label>
                                <input type="date" class="form-control" name="end_date" value="{{ $endDate ?? date('Y-m-d') }}" required />
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block no-print"><i class="ti ti-filter"></i> Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- This is the only part that will print -->
    <div id="report-content" class="table-responsive px-4 mb-5 pb-5">
        <h3 class="text-center mb-4" style="display: none;" id="print-title">{{__('file.Cash Flow Statement')}}</h3>
        <table class="table table-bordered table-striped dataTable">
            <tbody>
                <tr class="bg-light">
                    <th><strong>{{__('file.Opening Cash')}}</strong></th>
                    <th class="text-right"><strong>{{ number_format($opening_cash, 2) }}</strong></th>
                </tr>

                <tr>
                    <th colspan="2" class="bg-primary text-white"><strong>{{__('file.Operating Activities')}}</strong></th>
                </tr>
                @if(count($operating) > 0)
                    @foreach($operating as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="text-center">{{__('file.No data available')}}</td>
                    </tr>
                @endif
                <tr class="bg-light">
                    <td><strong>{{__('file.Net Cash from Operating Activities')}}</strong></td>
                    <td class="text-right"><strong>{{ number_format($net_operating_cash, 2) }}</strong></td>
                </tr>

                <tr>
                    <th colspan="2" class="bg-info text-white"><strong>{{__('file.Investing Activities')}}</strong></th>
                </tr>
                @if(count($investing) > 0)
                    @foreach($investing as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="text-center">{{__('file.No data available')}}</td>
                    </tr>
                @endif
                <tr class="bg-light">
                    <td><strong>{{__('file.Net Cash from Investing Activities')}}</strong></td>
                    <td class="text-right"><strong>{{ number_format($net_investing_cash, 2) }}</strong></td>
                </tr>

                <tr>
                    <th colspan="2" class="bg-warning text-white"><strong>{{__('file.Financing Activities')}}</strong></th>
                </tr>
                @if(count($financing) > 0)
                    @foreach($financing as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="text-center">{{__('file.No data available')}}</td>
                    </tr>
                @endif
                <tr class="bg-light">
                    <td><strong>{{__('file.Net Cash from Financing Activities')}}</strong></td>
                    <td class="text-right"><strong>{{ number_format($net_financing_cash, 2) }}</strong></td>
                </tr>

                <tr class="bg-secondary text-white">
                    <th><strong>{{__('file.Net Change in Cash')}}</strong></th>
                    <th class="text-right"><strong>{{ number_format($net_change_cash, 2) }}</strong></th>
                </tr>
                <tr class="bg-success text-white">
                    <th><strong>{{__('file.Closing Cash')}}</strong></th>
                    <th class="text-right"><strong>{{ number_format($closing_cash, 2) }}</strong></th>
                </tr>
            </tbody>
        </table>
    </div>
</section>

@push('scripts')
<script type="text/javascript">
$("ul#account").siblings('a').attr('aria-expanded','true');
$("ul#account").addClass("show");
$("ul#account #cash-flow-menu").addClass("active");

    $(document).ready(function() {
        function loadReport() {
            $.ajax({
                url: "{{ route('accounting.cash-flow') }}",
                type: "GET",
                data: $('#filter-form').serialize(),
                beforeSend: function() {
                    $('#report-content').html('<div class="text-center p-5 no-print"><div class="spinner-border text-primary" role="status"></div></div>');
                },
                success: function(response) {
                    var newContent = $(response).find('#report-content').html();
                    $('#report-content').html(newContent);
                    // Re-show the print title if it was removed by the AJAX response
                    $('#print-title').show();
                },
                error: function() {
                    $('#report-content').html('<div class="alert alert-danger m-4 no-print">Error loading data. Please try again.</div>');
                }
            });
        }

        // Submit form via AJAX to prevent page reload
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            loadReport();
        });

        // Auto-trigger when native date inputs change
        $('#filter-form input[type="date"]').on('change', function() {
            loadReport();
        });

        // Auto-trigger when warehouse changes
        $('select[name="warehouse_id"]').on('change', function() {
            loadReport();
        });

        // Add a title just for the print view
        $('#print-title').show();

        // Ensure window.print() triggers the CSS visibility correctly
        window.onbeforeprint = function() {
            $('#print-title').show();
        };
    });
</script>
@endpush
@endsection
