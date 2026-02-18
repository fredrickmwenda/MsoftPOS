@extends('backend.layout.main')
@section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4>Pending Installments</h4>
                        <div>
                            <a href="{{ route('hire_purchase.overdue') }}" class="btn btn-sm btn-danger">
                                <i class="dripicons-alert"></i> Overdue
                            </a>
                            <a href="{{ route('hire_purchase.dashboard') }}" class="btn btn-sm btn-info">
                                <i class="dripicons-home"></i> Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <form method="get" class="form-inline">
                                    <div class="form-group mr-2">
                                        <label for="customer_id">Customer:</label>
                                        <select name="customer_id" id="customer_id" class="form-control ml-2">
                                            <option value="">All Customers</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $customer_id == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                   <div class="form-group mr-2">
                                        <label>&nbsp;</label>
                                        <div class="btn-group bootstrap-select form-control">
                                        <button type="submit" class="btn btn-primary" style="display: block;">Filter</button>
                                        </div>
                                    </div>                               
                                 </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="hire_purchase_table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Reference No</th>
                                        <th>Customer</th>
                                        <th>Installment</th>
                                        <th>Due Date</th>
                                        <th>{{trans('file.Amount')}}</th>
                                        <th>{{trans('file.Paid')}}</th>
                                        <th>Remaining</th>
                                        <th>{{trans('file.Status')}}</th>
                                        <th>{{trans('file.Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($installments as $installment)
                                        <tr>
                                            <td>{{ $installment->sale->reference_no }}</td>
                                            <td>{{ $installment->sale->customer->name ?? 'N/A' }}</td>
                                            <td>#{{ $installment->installment_number }} of {{ $installment->sale->hire_purchase_terms }}</td>
                                            <td>{{ $installment->due_date->format('Y-m-d') }}</td>
                                            <td>{{ number_format($installment->amount, 2) }}</td>
                                            <td>{{ number_format($installment->paid_amount, 2) }}</td>
                                            <td>{{ number_format($installment->remaining_amount, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $installment->status_badge }}">
                                                    {{ ucfirst($installment->payment_status) }}
                                                </span>
                                                @if($installment->days_overdue > 0)
                                                    <span class="badge badge-danger ml-1">
                                                        {{ $installment->days_overdue }} days overdue
                                                    </span>
                                                @endif
                                            </td>
                                            <td>

                                                <a href="{{ route('hire_purchase.show', $installment->sale_id) }}" class="btn btn-sm btn-info">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No pending installments found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($installments instanceof \Illuminate\Pagination\Paginator)
                            <div class="d-flex justify-content-center">
                                {{ $installments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#hire_purchase_table').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] }
        ],
        language: {
            search: "Search:",
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

    // Handle payment button click
    $(document).on('click', '.payment-btn', function(e) {
        e.preventDefault();
        var saleId = $(this).data('sale-id');
        var saleRef = $(this).data('sale-ref');
        
        if (!saleId) {
            alert('Error: Sale ID not found.');
            return;
        }
        
        // Fetch installment data via AJAX
        $.ajax({
            url: '{{ route("hire_purchase.get_installments") }}',
            type: 'GET',
            data: { sale_id: saleId },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(response.has_installments && response.installments && response.installments.length > 0) {
                    var inst = response.installments[0];
                    
                    // Populate modal fields
                    $('#installmentInfo').html(
                        '<strong>Reference:</strong> ' + saleRef + '<br>' +
                        '<strong>Installment #' + inst.installment_number + ' of ' + response.total_terms + '</strong><br>' +
                        '<strong>Due Date:</strong> ' + inst.due_date
                    );
                    
                    $('#modalCustomerName').val(response.customer_name);
                    $('#modalInstallmentAmount').val(inst.amount);
                    $('#modalAlreadyPaid').val(inst.paid_amount);
                    $('#modalRemainingAmount').val(inst.remaining_amount);
                    $('#modalPayAmount').val('').attr('max', inst.remaining_amount);
                    
                    // Update form action
                    $('#paymentFormModal').attr('action', '{{ route("hire_purchase.record_payment", ":id") }}'.replace(':id', inst.id));
                    
                    // Show modal
                    $('#paymentModal').modal('show');
                } else if (response.status === 'all_paid') {
                    alert('All installments have been paid for this sale.');
                } else {
                    alert('Error: ' + (response.error || 'No installments found'));
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Error fetching installment data.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.status === 404) {
                    errorMsg = 'Sale not found or no installments available.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error. Please check console.';
                }
                alert(errorMsg);
                console.log('AJAX Error:', xhr.status, xhr.responseText);
            }
        });
    });

    // Handle form submission
    $('#paymentFormModal').on('submit', function(e) {
        e.preventDefault();
        var formAction = $(this).attr('action');
        
        $.ajax({
            url: formAction,
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#paymentModal').modal('hide');
                alert('Payment recorded successfully!');
                location.reload();
            },
            error: function(xhr) {
                alert('Error recording payment: ' + xhr.responseJSON.message);
            }
        });
    });
});
</script>
@endpush