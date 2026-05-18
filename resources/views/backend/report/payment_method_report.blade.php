@extends('backend.layout.main') @section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">Payment Method Report</h3>  
            </div>
            {!! Form::open(['route' => 'report.paymentMethod', 'method' => 'post']) !!}
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mt-3 mb-3 ml-2">
                        <div class="form-group">
                            <label class="control-label"><strong>Start Date</strong></label>
                            <input type="date" class="form-control" name="start_date" value="{{ $start_date ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-3 mt-3 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>End Date</strong></label>
                            <input type="date" class="form-control" name="end_date" value="{{ $end_date ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-3 mt-3 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>Payment Method</strong></label>
                            <select name="payment_method" class="form-control selectpicker" data-live-search="true" data-live-search-style="begins">
                                <option value="" {{ (isset($payment_method) && $payment_method === '') ? 'selected' : '' }}>All Payment Methods</option>
                                <option value="Cash" {{ (isset($payment_method) && $payment_method === 'Cash') ? 'selected' : '' }}>Cash</option>
                                <option value="Card" {{ (isset($payment_method) && $payment_method === 'Card') ? 'selected' : '' }}>Card</option>
                                <option value="Cheque" {{ (isset($payment_method) && $payment_method === 'Cheque') ? 'selected' : '' }}>Cheque</option>
                                <option value="Gift Card" {{ (isset($payment_method) && $payment_method === 'Gift Card') ? 'selected' : '' }}>Gift Card</option>
                                <option value="Deposit" {{ (isset($payment_method) && $payment_method === 'Deposit') ? 'selected' : '' }}>Deposit</option>
                                <option value="Paypal" {{ (isset($payment_method) && $payment_method === 'Paypal') ? 'selected' : '' }}>Paypal</option>
                                <option value="MobileMoney" {{ (isset($payment_method) && $payment_method === 'MobileMoney') ? 'selected' : '' }}>Mobile Money</option>
                            </select>
                        </div>
                    </div>
                    <!-- Sales Officer -->
                    <div class="col-md-2 mt-3 mb-3">
                        <div class="form-group">
                            <label class="control-label"><strong>Sales Officer</strong></label>
                            <select name="user_id" class="form-control selectpicker" data-live-search="true" data-live-search-style="begins">
                                <option value="0">All Sales Officers</option>
                                @php 
                                    $list_of_user_id = App\Models\Payment::select('user_id')->distinct()->pluck('user_id');
                                    $lims_user_list = \App\Models\User::whereIn('id', $list_of_user_id)->get();
                                @endphp
                                @foreach($lims_user_list as $user)
                                    <option value="{{ $user->id }}" {{ (isset($user_id) && $user_id === $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 mt-3 mb-3">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Filter</button>    
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        <div class="card mt-3" id="payment-method-report-card" data-start-date="{{ $start_date ?? '' }}" data-end-date="{{ $end_date ?? '' }}">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="report-table" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Payment Method</th>
                                <th>Number of Transactions</th>
                                <th>Total Amount</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report_rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->paying_method ?: 'N/A' }}</td>
                                <td>{{ number_format($row->transaction_count) }}</td>
                                <td class="text-right">{{ number_format((float) $row->total_amount, 2) }}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-info btn-eye-details" title="View sales" data-payment-method="{{ $row->paying_method ?: 'N/A' }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">No payment data for the selected period.</td></tr>
                            @endforelse
                        </tbody>
                        @if($report_rows->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="2">Total</th>
                                <th>{{ number_format($report_rows->sum('transaction_count')) }}</th>
                                <th class="text-right">{{ number_format($report_rows->sum('total_amount'), 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Sales / payment details for selected method -->
        <div class="modal fade" id="paymentMethodDetailsModal" tabindex="-1" role="dialog" aria-labelledby="paymentMethodDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentMethodDetailsModalLabel">Sales &amp; payments</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-2" id="paymentMethodDetailsSubtitle">—</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="paymentMethodDetailsTable">
                                <thead>
                                    <tr>
                                        <th>Date of sale</th>
                                        <th>Products</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentMethodDetailsBody">
                                </tbody>
                            </table>
                        </div>
                        <div id="paymentMethodDetailsEmpty" class="text-center text-muted py-3 d-none">No transactions found.</div>
                        <div id="paymentMethodDetailsLoading" class="text-center text-muted py-3 d-none">Loading…</div>
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

    $(document).on('click', '.btn-eye-details', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var paymentMethod = $btn.data('payment-method');
        var $card = $('#payment-method-report-card');
        var startDate = $card.data('start-date');
        var endDate = $card.data('end-date');
        if (!startDate || !endDate) {
            startDate = $('input[name="start_date"]').val();
            endDate = $('input[name="end_date"]').val();
        }
        $('#paymentMethodDetailsModalLabel').text('Sales & payments: ' + paymentMethod);
        $('#paymentMethodDetailsSubtitle').text('From ' + startDate + ' to ' + endDate);
        $('#paymentMethodDetailsBody').empty();
        $('#paymentMethodDetailsEmpty').addClass('d-none');
        $('#paymentMethodDetailsLoading').removeClass('d-none');
        $('#paymentMethodDetailsTable').closest('.table-responsive').show();
        $('#paymentMethodDetailsModal').modal('show');

        $.ajax({
            url: '{{ url("report/payment-method-details") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                start_date: startDate,
                end_date: endDate,
                payment_method: paymentMethod
            },
            dataType: 'json',
            success: function(res) {
                $('#paymentMethodDetailsLoading').addClass('d-none');
                if (res.rows && res.rows.length) {
                    $.each(res.rows, function(i, row) {
                        $('#paymentMethodDetailsBody').append(
                            '<tr><td>' + (row.date || '—') + '</td><td>' + (row.products || '—') + '</td><td class="text-right">' + parseFloat(row.amount).toFixed(2) + '</td></tr>'
                        );
                    });
                } else {
                    $('#paymentMethodDetailsEmpty').removeClass('d-none');
                    $('#paymentMethodDetailsTable').closest('.table-responsive').hide();
                }
            },
            error: function() {
                $('#paymentMethodDetailsLoading').addClass('d-none');
                $('#paymentMethodDetailsEmpty').removeClass('d-none').text('Error loading details.');
                $('#paymentMethodDetailsTable').closest('.table-responsive').hide();
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
        'columnDefs': [{ "orderable": false, "targets": 4 }]
    });
    @endif
</script>
@endpush
