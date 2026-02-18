@extends('backend.layout.main')
@section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>Hire Purchase Report</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <form method="get" class="form-inline">
                                    <div class="form-group mr-2">
                                        <label for="start_date">{{trans('file.From')}}:</label>
                                        <input type="text" name="start_date" id="start_date" class="form-control ml-2 date" value="{{ $startDate }}" />
                                    </div>
                                    <div class="form-group mr-2">
                                        <label for="end_date">{{trans('file.To')}}:</label>
                                        <input type="text" name="end_date" id="end_date" class="form-control ml-2 date" value="{{ $endDate }}" />
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Filter Report</button>
                                    <button type="button" class="btn btn-success" onclick="exportReport('pdf')">
                                        <i class="dripicons-document"></i> PDF
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="exportReport('csv')">
                                        <i class="dripicons-document"></i> CSV
                                    </button>
                                </form>
                            </div>
                        </div>

               

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <strong>Total Contract Value</strong>
                                        <p class="h5">{{ number_format($reportData['total_amount'] ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <strong>Total Down Payments</strong>
                                        <p class="h5">{{ number_format($reportData['total_down_payment'] ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <strong>{{trans('file.Balance')}}</strong>
                                        <p class="h5">{{ number_format(($reportData['total_amount'] ?? 0) - ($reportData['total_down_payment'] ?? 0), 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Contract Details</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="hire_purchase_report_table">
                                        <thead>
                                            <tr>
                                                <th>{{trans('file.Reference No')}}</th>
                                                <th>Customer</th>
                                                <th>Total Amount</th>
                                                <th>Down Payment</th>
                                                <th>Terms</th>
                                                <th>Interest Rate</th>
                                                <th>{{trans('file.Status')}}</th>
                                                <th>{{trans('file.Date')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sales as $sale)
                                                <tr>
                                                    <td>{{ $sale->reference_no }}</td>
                                                    <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                                    <td>{{ number_format($sale->grand_total, 2) }}</td>
                                                    <td>{{ number_format($sale->hire_purchase_down_payment, 2) }}</td>
                                                    <td>{{ $sale->hire_purchase_terms }} months</td>
                                                    <td>{{ $sale->hire_purchase_interest_rate }}%</td>
                                                    <td>
                                                        <span class="badge badge-{{ $sale->hire_purchase_status === 'active' ? 'success' : 'secondary' }}">
                                                            {{ ucfirst($sale->hire_purchase_status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">No contracts found for this period</td>
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
        </div>
    </div>
</section>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#hire_purchase_report_table').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[7, 'desc']],
        columnDefs: [
            { orderable: false, targets: [] }
        ],
        language: {
            search: "{{trans('file.search')}}:",
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

function exportReport(format) {
    var startDate = document.getElementById('start_date').value;
    var endDate = document.getElementById('end_date').value;
    
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("hire_purchase.export_report") }}';
    
    var formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = format;
    
    var startInput = document.createElement('input');
    startInput.type = 'hidden';
    startInput.name = 'start_date';
    startInput.value = startDate;
    
    var endInput = document.createElement('input');
    endInput.type = 'hidden';
    endInput.name = 'end_date';
    endInput.value = endDate;
    
    var tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = '{{ csrf_token() }}';
    
    form.appendChild(formatInput);
    form.appendChild(startInput);
    form.appendChild(endInput);
    form.appendChild(tokenInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endpush

@endsection
