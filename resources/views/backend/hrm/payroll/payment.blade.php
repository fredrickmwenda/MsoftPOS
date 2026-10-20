@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">

        <div class="mt-3 mb-4">
            <h4 class="mb-0 fw-bold">{{ __('file.Make Payment') }}</h4>
            <small class="text-muted">Pay outstanding payroll dues</small>
        </div>

        <form method="GET" action="{{ route('payroll-payment.index') }}" class="form-inline mb-3">
            <input type="month" name="month" class="form-control mr-2" value="{{ request('month') }}">
            <button class="btn btn-outline-primary btn-sm">{{ __('file.Filter') }}</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('file.reference') }}</th>
                        <th>{{ __('file.Employee') }}</th>
                        <th>{{ __('file.Month') }}</th>
                        <th>{{ __('file.Net Salary') }}</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="not-exported">{{ __('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($lims_payroll_all as $payroll)
                    @php
                        $due = max(0, (float)$payroll->amount - (float)$payroll->paid_amount);
                    @endphp
                    <tr>
                        <td>{{ $payroll->reference_no }}</td>
                        <td>{{ $payroll->employee_name ?? optional($payroll->employee)->name }}</td>
                        <td>{{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F Y') }}</td>
                        <td>{{ number_format((float)$payroll->amount, $general_setting->decimal) }}</td>
                        <td class="text-success">{{ number_format((float)$payroll->paid_amount, $general_setting->decimal) }}</td>
                        <td class="text-danger font-weight-bold">{{ number_format($due, $general_setting->decimal) }}</td>
                        <td>
                            <span class="badge badge-{{ $payroll->payment_status === 'paid' ? 'success' : ($payroll->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                {{ ucfirst($payroll->payment_status) }}
                            </span>
                        </td>
                        <td>
                            @if($due > 0)
                                <button type="button" class="btn btn-primary btn-sm pay-btn"
                                    data-id="{{ $payroll->id }}"
                                    data-employee="{{ $payroll->employee_name }}"
                                    data-due="{{ $due }}"
                                    data-toggle="modal" data-target="#payModal">
                                    <i class="dripicons-dollar"></i> {{ __('file.Pay') }}
                                </button>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No payroll records.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ================= PAY MODAL ================= --}}
<div id="payModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('payroll-payment.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="dripicons-dollar"></i> {{ __('file.Make Payment') }} — <span id="payEmployee"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="payroll_id" id="payPayrollId">
                    <div class="form-group">
                        <label>Outstanding Due</label>
                        <input type="text" class="form-control" id="payDue" readonly>
                    </div>
                    <div class="form-group">
                        <label>{{ __('file.Amount') }} *</label>
                        <input type="number" step="any" min="0.01" name="amount" id="payAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('file.Method') }} *</label>
                        <select name="paying_method" class="form-control" required>
                            <option value="0">{{ __('file.Cash') }}</option>
                            <option value="1">{{ __('file.Cheque') }}</option>
                            <option value="2">{{ __('file.Credit Card') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('file.Account') }}</label>
                        <select name="account_id" class="form-control">
                            @foreach($lims_account_list as $acc)
                                <option value="{{ $acc->id }}" {{ $acc->is_default ? 'selected' : '' }}>
                                    {{ $acc->name }} [{{ $acc->account_no }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('file.date') }}</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="form-group">
                        <label>{{ __('file.Note') }}</label>
                        <textarea name="note" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('file.Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('file.Pay') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
 $(document).on('click', '.pay-btn', function () {
    $('#payPayrollId').val(this.dataset.id);
    $('#payEmployee').text(this.dataset.employee);
    $('#payDue').val(parseFloat(this.dataset.due).toFixed(2));
    $('#payAmount').val(parseFloat(this.dataset.due).toFixed(2)).attr('max', this.dataset.due);
});
</script>
@endpush
