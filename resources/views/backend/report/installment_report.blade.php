@extends('backend.layout.main')
@section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4>Installment Report</h4>
                        <div>
                            <a href="{{ route('report.dashboard') }}" class="btn btn-sm btn-info">
                                <i class="dripicons-home"></i> Report Dashboard
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="get" class="mb-4">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label for="customer_id">Customer</label>
                                    <select name="customer_id" id="customer_id" class="form-control">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All States</option>
                                        <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="partial" {{ $status == 'partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="start_date">From</label>
                                    <input type="text" name="start_date" id="start_date" class="form-control date" value="{{ $startDate }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="end_date">To</label>
                                    <input type="text" name="end_date" id="end_date" class="form-control date" value="{{ $endDate }}">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-block">Filter Report</button>
                                </div>
                            </div>
                        </form>

                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>Total</strong>
                                        <p class="h5 mb-0">{{ $summary['total_installments'] ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>Paid</strong>
                                        <p class="h5 mb-0 text-success">{{ $summary['paid_installments'] ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>Partial</strong>
                                        <p class="h5 mb-0 text-warning">{{ $summary['partial_installments'] ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>Pending</strong>
                                        <p class="h5 mb-0 text-info">{{ $summary['pending_installments'] ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>Overdue</strong>
                                        <p class="h5 mb-0 text-danger">{{ $summary['overdue_installments'] ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <strong>Amount Due</strong>
                                        <p class="h5 mb-0">{{ number_format($summary['total_remaining_amount'] ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="installment_report_table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Reference No</th>
                                        <th>Installment</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>State</th>
                                        <th>Guideline</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($installments as $installment)
                                        @php
                                            $customer = $installment->sale->customer ?? null;
                                            $remaining = $installment->remaining_amount;
                                            $statusText = ucfirst($installment->payment_status);
                                            $isOverdue = $installment->payment_status !== 'paid' && $installment->due_date->lt(now());
                                            $stateClass = 'secondary';

                                            if ($installment->payment_status === 'paid') {
                                                $stateClass = 'success';
                                            } elseif ($installment->payment_status === 'partial') {
                                                $stateClass = 'warning';
                                            } elseif ($isOverdue) {
                                                $stateClass = 'danger';
                                            } elseif ($installment->payment_status !== 'paid') {
                                                $stateClass = 'info';
                                            }

                                            $guideline = 'On track';
                                            if ($installment->payment_status === 'paid') {
                                                $guideline = 'Completed';
                                            } elseif ($isOverdue) {
                                                $guideline = 'Follow up immediately';
                                            } elseif ($installment->payment_status === 'partial') {
                                                $guideline = 'Collect remaining balance';
                                            } elseif ($installment->due_date->diffInDays(now()) <= 3) {
                                                $guideline = 'Due soon - remind customer';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $customer->name ?? 'N/A' }}</td>
                                            <td>{{ $installment->sale->reference_no ?? 'N/A' }}</td>
                                            <td>#{{ $installment->installment_number }}</td>
                                            <td>{{ $installment->due_date->format('Y-m-d') }}</td>
                                            <td>{{ number_format($installment->amount, 2) }}</td>
                                            <td>{{ number_format($installment->paid_amount, 2) }}</td>
                                            <td>{{ number_format($remaining, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $stateClass }}">{{ $statusText }}</span>
                                            </td>
                                            <td>{{ $guideline }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No installment records found for this filter</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
$(document).ready(function() {
    $('#installment_report_table').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[3, 'desc']],
        language: {
            search: "{{ trans('file.search') }}:",
            lengthMenu: "show _MENU_ entries",
            info: "showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "first",
                last: "last",
                next: "next",
                previous: "previous"
            }
        }
    });
});
</script>
@endpush

@endsection
