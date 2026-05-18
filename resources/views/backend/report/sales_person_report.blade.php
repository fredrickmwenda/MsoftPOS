@extends('backend.layout.main') @section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">Sales Person Report</h3>
            </div>
            {!! Form::open(['route' => 'report.salesPerson', 'method' => 'post']) !!}
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mt-3 mb-3 ml-2">
                        <div class="form-group">
                            <label class="control-label"><strong>Start Date</strong></label>
                            <input type="date" class="form-control" name="start_date" value="{{ $start_date ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-2 mt-3 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>End Date</strong></label>
                            <input type="date" class="form-control" name="end_date" value="{{ $end_date ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>Sales Person</strong></label>
                            <select name="user_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="0" {{ (isset($user_id) && $user_id == 0) ? 'selected' : '' }}>All Sales Person</option>
                                @foreach($lims_user_list as $user)
                                <option value="{{ $user->id }}" {{ (isset($user_id) && $user_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>{{ trans('file.Choose Warehouse') }}</strong></label>
                            <select name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="0" {{ (isset($warehouse_id) && $warehouse_id == 0) ? 'selected' : '' }}>{{ trans('file.All Warehouse') }}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ (isset($warehouse_id) && $warehouse_id == $warehouse->id) ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mt-3 mb-3">
                        <div class="form-group">
                            <label class="control-label">&nbsp;</label>
                            <button class="btn btn-primary btn-block" type="submit">{{ trans('file.submit') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        <div class="card mt-3" id="sales-person-report-card" data-start-date="{{ $start_date ?? '' }}" data-end-date="{{ $end_date ?? '' }}" data-warehouse-id="{{ $warehouse_id ?? 0 }}">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="report-table" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sales Person</th>
                                <th>Number of Sales</th>
                                <th>Total Sales Amount</th>
                                <th>Total Paid</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report_rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->sales_person_name }}</td>
                                <td>{{ number_format($row->sale_count) }}</td>
                                <td class="text-right">{{ number_format((float) $row->total_sales, 2) }}</td>
                                <td class="text-right">{{ number_format((float) $row->total_paid, 2) }}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-info btn-eye-sales" title="View sales" data-user-id="{{ $row->user_id }}" data-sales-person-name="{{ $row->sales_person_name }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">No sales data for the selected period.</td></tr>
                            @endforelse
                        </tbody>
                        @if($report_rows->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="2">Total</th>
                                <th>{{ number_format($report_rows->sum('sale_count')) }}</th>
                                <th class="text-right">{{ number_format($report_rows->sum('total_sales'), 2) }}</th>
                                <th class="text-right">{{ number_format($report_rows->sum('total_paid'), 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Sales details for selected sales person -->
        <div class="modal fade" id="salesPersonDetailsModal" tabindex="-1" role="dialog" aria-labelledby="salesPersonDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="salesPersonDetailsModalLabel">Sales</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-2" id="salesPersonDetailsSubtitle">—</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="salesPersonDetailsTable">
                                <thead>
                                    <tr>
                                        <th>Date of sale</th>
                                        <th>Products</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="salesPersonDetailsBody">
                                </tbody>
                            </table>
                        </div>
                        <div id="salesPersonDetailsEmpty" class="text-center text-muted py-3 d-none">No sales found.</div>
                        <div id="salesPersonDetailsLoading" class="text-center text-muted py-3 d-none">Loading…</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $('.selectpicker').selectpicker('refresh');

    $(document).on('click', '.btn-eye-sales', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var userId = $btn.data('user-id');
        var salesPersonName = $btn.data('sales-person-name');
        var $card = $('#sales-person-report-card');
        var startDate = $card.data('start-date');
        var endDate = $card.data('end-date');
        var warehouseId = $card.data('warehouse-id') || 0;
        if (!startDate || !endDate) {
            startDate = $('input[name="start_date"]').val();
            endDate = $('input[name="end_date"]').val();
            warehouseId = $('select[name="warehouse_id"]').val() || 0;
        }
        $('#salesPersonDetailsModalLabel').text('Sales: ' + salesPersonName);
        $('#salesPersonDetailsSubtitle').text('From ' + startDate + ' to ' + endDate);
        $('#salesPersonDetailsBody').empty();
        $('#salesPersonDetailsEmpty').addClass('d-none');
        $('#salesPersonDetailsLoading').removeClass('d-none');
        $('#salesPersonDetailsTable').closest('.table-responsive').show();
        $('#salesPersonDetailsModal').modal('show');

        $.ajax({
            url: '{{ url("report/sales-person-details") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                start_date: startDate,
                end_date: endDate,
                user_id: userId,
                warehouse_id: warehouseId
            },
            dataType: 'json',
            success: function(res) {
                $('#salesPersonDetailsLoading').addClass('d-none');
                if (res.rows && res.rows.length) {
                    $.each(res.rows, function(i, row) {
                        $('#salesPersonDetailsBody').append(
                            '<tr><td>' + (row.date || '—') + '</td><td>' + (row.products || '—') + '</td><td class="text-right">' + parseFloat(row.amount).toFixed(2) + '</td></tr>'
                        );
                    });
                } else {
                    $('#salesPersonDetailsEmpty').removeClass('d-none');
                    $('#salesPersonDetailsTable').closest('.table-responsive').hide();
                }
            },
            error: function() {
                $('#salesPersonDetailsLoading').addClass('d-none');
                $('#salesPersonDetailsEmpty').removeClass('d-none').text('Error loading sales.');
                $('#salesPersonDetailsTable').closest('.table-responsive').hide();
            }
        });
    });

    @if($report_rows->isNotEmpty() && $report_rows->count() > 0)
    $('#report-table').DataTable({
        "order": [[3, 'desc']],
        "pageLength": 25,
        'language': {
            'lengthMenu': '_MENU_ {{ trans("file.records per page") }}',
            "info": '<small>{{ trans("file.Showing") }} _START_ - _END_ (_TOTAL_)</small>',
            "search": '{{ trans("file.Search") }}',
            'paginate': { 'previous': '<i class="dripicons-chevron-left"></i>', 'next': '<i class="dripicons-chevron-right"></i>' }
        },
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
        'columnDefs': [{ "orderable": false, "targets": 5 }]
    });
    @endif
</script>
@endpush
