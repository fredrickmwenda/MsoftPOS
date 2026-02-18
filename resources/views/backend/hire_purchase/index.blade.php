@extends('backend.layout.main')
@section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4>Hire Purchase Sales</h4>
                        <div>
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
                                        <label for="status">{{trans('file.Status')}}:</label>
                                        <select name="status" id="status" class="form-control ml-2">
                                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="defaulted" {{ $status === 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                                        </select>
                                    </div>
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

                        <table class="table table-striped table-bordered table-hover" id="hire_purchase_table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Reference No</th>
                                    <th>Customer</th>
                                    <th>Total Amount</th>
                                    <th>Down Payment</th>
                                    <th>Terms</th>
                                    <th>Interest Rate</th>
                                    <th>{{trans('file.Status')}}</th>
                                    <th>{{trans('file.Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td><a href="{{ route('hire_purchase.show', $sale->id) }}">{{ $sale->reference_no }}</a></td>
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
                                        <td>
                                            <a href="{{ route('hire_purchase.show', $sale->id) }}" class="btn btn-sm btn-info" title="View Details" style="margin-bottom:2px;">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success payment-btn" data-sale-id="{{ $sale->id }}" data-sale-ref="{{ $sale->reference_no }}" title="Record Payment">
                                                <i class="fa fa-money"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No hire purchase contracts found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="paymentFormModal">
                @csrf
                @method('POST')
                <div class="modal-body">
                    <div class="alert alert-info" id="installmentInfo"></div>
                    
                    <div class="form-group">
                        <label>Customer</label>
                        <input type="text" class="form-control" id="modalCustomerName" disabled>
                    </div>

                    <div class="form-group">
                        <label>Installment Amount</label>
                        <input type="number" class="form-control" id="modalInstallmentAmount" disabled>
                    </div>

                    <div class="form-group">
                        <label>Already Paid</label>
                        <input type="number" class="form-control" id="modalAlreadyPaid" disabled>
                    </div>

                    <div class="form-group">
                        <label>Remaining Amount</label>
                        <input type="number" class="form-control" id="modalRemainingAmount" disabled>
                    </div>

                    <div class="form-group">
                        <label>Amount to Pay *</label>
                        <input type="number" name="amount" class="form-control" id="modalPayAmount" step="0.01" min="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Date *</label>
                        <input type="text" name="payment_date" class="form-control date" id="modalPaymentDate" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Method *</label>
                        <select name="payment_method" class="form-control" id="modalPaymentMethod" required>
                            <option value="">Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Credit Card</option>
                            <option value="check">Cheque</option>
                            <option value="transfer">Bank Transfer</option>
                            <option value="mobile">Mobile Money</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Payment Notes</label>
                        <textarea name="payment_note" class="form-control" rows="3" placeholder="Enter any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

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