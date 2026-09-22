@extends('backend.layout.main')

@push('css')
<style type="text/css">
    .top-fields { margin-top:10px; position: relative; }
    .top-fields label { font-size:11px; font-weight:600; margin-left:10px; padding:0 3px; position:absolute; top:-8px; z-index:9; background-color: white; }
    .top-fields input { font-size:13px; height:45px; }
</style>
@endpush
@section('content')
<section>
    <div class="container-fluid">
        <form id="filter-form" action="{{ route('accounting.balance-sheet') }}" method="GET">
            <div class="card mt-3 mb-2" id="filter-card">
                <div class="card-body">
                    <h3 class="text-center mt-2">{{__('file.Balance Sheet')}}</h3>
                    <button class="btn btn-primary btn-icon" onclick="window.print();" type="button" style="position: absolute; top: 15px; right: 15px"><i class="ti ti-printer"></i></button>
                    <div class="row mt-2">
                        <div class="col-md-4 offset-md-2">
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
                        <div class="col-md-4">
                            <div class="form-group top-fields">
                                <label>As Of Date</label>
                                <!-- Native date input requires Y-m-d format. Defaults to today via Controller -->
                                <input type="date" class="form-control" name="as_of" value="{{ $as_of_date ?? date('Y-m-d') }}" required />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="report-content" class="table-responsive px-4 mb-5 pb-5">
        <div class="dataTables_wrapper">
            <table class="table table-hover dataTable">
                <!-- Assets -->
                <thead>
                    <tr>
                        <th colspan="2"><h4>Assets</h4></th>
                    </tr>
                    <tr>
                        <th>Account Name</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $asset)
                    <tr>
                        <td>{{$asset->account_no}} - {{$asset->name}}</td>
                        <td class="text-right">{{number_format((float)$asset->net_balance, 2, '.', '')}}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="tfoot active">
                    <tr>
                        <th>Total Assets</th>
                        <th class="text-right">{{number_format((float)$total_assets, 2, '.', '')}}</th>
                    </tr>
                </tfoot>

                <!-- Liabilities -->
                <thead>
                    <tr>
                        <th colspan="2" class="pt-4"><h4>Liabilities</h4></th>
                    </tr>
                    <tr>
                        <th>Account Name</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liabilities as $liability)
                    <tr>
                        <td>{{$liability->account_no}} - {{$liability->name}}</td>
                        <td class="text-right">{{number_format((float)$liability->net_balance, 2, '.', '')}}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="tfoot active">
                    <tr>
                        <th>Total Liabilities</th>
                        <th class="text-right">{{number_format((float)$total_liabilities, 2, '.', '')}}</th>
                    </tr>
                </tfoot>

                <!-- Equity -->
                <thead>
                    <tr>
                        <th colspan="2" class="pt-4"><h4>Equity</h4></th>
                    </tr>
                    <tr>
                        <th>Account Name</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equities as $equity)
                    <tr>
                        <td>{{$equity->account_no}} - {{$equity->name}}</td>
                        <td class="text-right">{{number_format((float)$equity->net_balance, 2, '.', '')}}</td>
                    </tr>
                    @endforeach
                    @if($retained_earnings != 0)
                    <tr>
                        <td>3900 - Retained Earnings (Calculated)</td>
                        <td class="text-right">{{number_format((float)$retained_earnings, 2, '.', '')}}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Current Year Earnings</td>
                        <td class="text-right">{{number_format((float)$current_year_earnings, 2, '.', '')}}</td>
                    </tr>
                </tbody>
                <tfoot class="tfoot active">
                    <tr>
                        <th>Total Equity (Including Earnings)</th>
                        <th class="text-right">{{number_format((float)($total_equity + $current_year_earnings), 2, '.', '')}}</th>
                    </tr>
                </tfoot>

                <!-- Validation -->
                <tfoot class="tfoot">
                    <tr>
                        <th colspan="2" class="pt-5"></th>
                    </tr>
                    @php
                        $liab_plus_eq = $total_liabilities + $total_equity + $current_year_earnings;
                        $diff = $total_assets - $liab_plus_eq;
                    @endphp
                    <tr>
                        <th><h4>Total Liabilities + Equity</h4></th>
                        <th class="text-right"><h4>{{number_format((float)$liab_plus_eq, 2, '.', '')}}</h4></th>
                    </tr>
                    <tr>
                        <th class="text-right">Difference:</th>
                        <th class="text-right">
                            @if(abs($diff) > 0.001)
                                <span class="text-danger">WARNING: Balance Sheet Out Of Balance ({{number_format((float)$diff, 2, '.', '')}})</span>
                            @else
                                <span class="text-success">0.00</span>
                            @endif
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</section>

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        function loadReport() {
            $.ajax({
                url: "{{ route('accounting.balance-sheet') }}",
                type: "GET",
                data: $('#filter-form').serialize(),
                beforeSend: function() {
                    // Show a loading spinner while fetching data
                    $('#report-content').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>');
                },
                success: function(response) {
                    // Extract only the report content and inject it
                    var newContent = $(response).find('#report-content').html();
                    $('#report-content').html(newContent);
                },
                error: function() {
                    $('#report-content').html('<div class="alert alert-danger m-4">Error loading data. Please try again.</div>');
                }
            });
        }

        // Listen for native date input change
        $('#filter-form input[name="as_of"]').on('change', function() {
            loadReport();
        });

        // Listen for warehouse select change
        $('#filter-form select[name="warehouse_id"]').on('change', function() {
            loadReport();
        });
    });
</script>
@endpush
@endsection