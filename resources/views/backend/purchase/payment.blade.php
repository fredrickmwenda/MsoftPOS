@extends('backend.layout.main') @section('content')
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>Purchase Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mt-3 mb-2">
                            <div class="col-md-12">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#add-payment" style="float: right;">
                                    Make Payment
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                            {{-- 🔹 Row 1: Purchase + Supplier + Warehouse Details --}}
                                <div class="row mb-4">
                                    {{-- 🧾 Purchase Details --}}
                                    <div class="col-md-4">
                                        <div class="card shadow-sm p-3 h-100">
                                            <h5 class="mb-3 text-primary border-bottom pb-2">Purchase Details</h5>
                                            <p><strong>Reference No:</strong> {{ $lims_purchase_data->reference_no }}</p>
                                            <p><strong>Date:</strong> {{ $lims_purchase_data->created_at->format('Y-m-d') }}</p>
                                            <!-- Set Product Name from puchase relation -->
                                            <p><strong>Products and Prices:</strong></p>
                                            <ul>
                                                @foreach ($lims_product_purchase_data as $product_purchase)
                                                    @php
                                                        $product_image = $product_purchase->product->image ?? 'default.png';
                                                        $small_image_path = public_path("images/product/small/" . $product_image);
                                                        $large_image_path = public_path("images/product/" . $product_image);

                                                        if (file_exists($small_image_path)) {
                                                            $image_url = url('images/product/small/' . $product_image);
                                                        } elseif (file_exists($large_image_path)) {
                                                            $image_url = url('images/product/' . $product_image);
                                                        } else {
                                                            $image_url = url('images/product/default.png'); // fallback image
                                                        }
                                                    @endphp
                                                    <li>
                                                        <img src="{{ $image_url }}" alt="Product Image" width="30" height="30" style="margin-right: 10px; vertical-align: middle;">
                                                        {{ $product_purchase->product->name }} - 
                                                        {{ number_format($product_purchase->net_unit_cost, 2) }} {{ $lims_purchase_data->currency ?? '' }}
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <p><strong>Total Items:</strong> {{ $lims_purchase_data->total_qty }}</p>
                                            @if($lims_purchase_data->order_tax)
                                                <p><strong>Order Tax:</strong> {{ number_format($lims_purchase_data->order_tax, 2) }}</p>
                                            @endif
                                            @if($lims_purchase_data->order_discount)
                                                <p><strong>Order Discount:</strong> {{ number_format($lims_purchase_data->order_discount, 2) }}</p>
                                            @endif
                                                <p><strong>Total Exclusive  Cost:</strong> {{ number_format($lims_purchase_data->total_cost, 2) }}</p>

                                            <p><strong>Total Inclusive  Cost:</strong> {{ number_format($lims_purchase_data->grand_total, 2) }}</p>
                                            <p><strong>Status:</strong> {{ ucfirst($lims_purchase_data->status) }}</p>
                                            <p><strong>Currency Rate:</strong> {{ $currency_exchange_rate }}</p>
                                        </div>
                                    </div>

                                    {{-- 👤 Supplier Details --}}
                                    <div class="col-md-4">
                                        <div class="card shadow-sm p-3 h-100">
                                            <h5 class="mb-3 text-success border-bottom pb-2">Supplier Details</h5>
                                            @if($lims_purchase_data->supplier)
                                                <p><strong>Company:</strong> {{ $lims_purchase_data->supplier->company_name }}</p>
                                                <p><strong>Supplier Name:</strong> {{ $lims_purchase_data->supplier->name }}</p>
                                                <p><strong>Email:</strong> {{ $lims_purchase_data->supplier->email }}</p>
                                                <p><strong>Phone:</strong> {{ $lims_purchase_data->supplier->phone_number }}</p>
                                                <p><strong>Address:</strong> {{ $lims_purchase_data->supplier->address }}, {{ $lims_purchase_data->supplier->city }}</p>
                                                <p><strong>Country:</strong> {{ $lims_purchase_data->supplier->country }}</p>
                                            @else
                                                <p class="text-muted">No supplier details available.</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- 🏭 Warehouse Details --}}
                                    <div class="col-md-4">
                                        <div class="card shadow-sm p-3 h-100">
                                            <h5 class="mb-3 text-info border-bottom pb-2">Warehouse Details</h5>
                                            @if($lims_purchase_data->warehouse)
                                                <p><strong>Name:</strong> {{ $lims_purchase_data->warehouse->name }}</p>
                                                <p><strong>Email:</strong> {{ $lims_purchase_data->warehouse->email }}</p>
                                                <p><strong>Phone:</strong> {{ $lims_purchase_data->warehouse->phone }}</p>
                                                <p><strong>Address:</strong> {{ $lims_purchase_data->warehouse->address }}</p>
                                            @else
                                                <p class="text-muted">No warehouse details available.</p>
                                            @endif
                                        </div>
                                    </div>
                      
                                 </div>
                        </div>
                        </div>
                        {{-- 🔹 Row 2: Purchased Items Table --}}
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-3">Purchase Payments</h5>
                                <div class="table-responsive">
                                    <table id="payment-table" class="table table-striped"  style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Payment Ref</th>
                                                <th>Purchase Ref</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th colspan="2" style="text-align:right">Total:</th>
                                                <th></th>
                                                <th colspan="4"></th>
                                            </tr>
                                        </tfoot>
                                    </table>

                                </div>
                            </div>
                   
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="add-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Payment')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'purchase.add-payment', 'method' => 'post', 'class' => 'payment-form' ]) !!}
                        <div class="row">
                            <input type="hidden" name="purchase_id" value="{{ $lims_purchase_data->id }}">
                            <input type="hidden" name="balance">
                            <div class="col-md-6">
                                <label>{{trans('file.Recieved Amount')}} *</label>
                                <input type="text" name="paying_amount" class="form-control numkey"  step="any" required value="{{$total}}">
                            </div>
                            <div class="col-md-6">
                                <label>{{trans('file.Paying Amount')}} *</label>
                                <input type="text" id="amount" name="amount" class="form-control"   required value="{{$total}}" readonly>
                            </div>
                            <div class="col-md-6 mt-1">
                                <label>{{trans('file.Change')}} : </label>
                                <p class="change ml-2" id="change-bal">{{number_format(0, $general_setting->decimal, '.', '')}}</p>
                            </div>
                            <div class="col-md-6 mt-1">
                                <label>{{trans('file.Paid By')}}</label>
                                <select name="paid_by_id" class="form-control">
                                    <option value="1">Cash</option>
                                    <option value="2">Gift Card</option>
                                    <option value="3">Credit Card</option>
                                    <option value="4">Cheque</option>
                                    <option value="8">Mobile Money</option>
                                </select>
                                <i class="fa fa-plus text-success ml-2 cursor-pointer" id="multiplePaymentBtn" title="Add Split"></i>

                            </div>
                            <div class="col-md-12 mt-2 ml-2" id="paymentMethodsContainer"></div>



                        </div>
                        <div class="form-group mt-2">
                            <div class="card-element" class="form-control">
                            </div>
                            <div class="card-errors" role="alert"></div>
                        </div>
                        {{-- changes by yogesh --}}
                        <div class="form-group col-md-12 mobile_money_fields">
                            <label>{{trans('Mobile Money')}} *</label>
                            <select id="mobile_money_operator" name="mobile-op" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Mobile money operator">
                                <option value="mtn_mobile_money">MTN Mobile Money</option>
                                <option value="airteltigo_cash">AirtelTigo Cash</option>
                                <option value="telecel_cash">Telecel Cash</option>
                            </select>
                        </div>
                        <input type="hidden" id="selected_mobile_op" name="selected_mobile_op">

                        <div class="form-group col-md-12 mobile_money_fields">
                            <label>{{trans('Mobile Number')}} *</label>
                            <input type="number" name="mobile_number" class="form-control">
                        </div>
                        {{-- changes end by yogesh --}}
                        <div id="cheque-section" style="display: none;" class="cheque-section">
                            <div class="form-group">
                                <label>{{trans('file.Cheque Number')}} *</label>
                                <input type="text" name="cheque_no" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label> {{trans('file.Account')}}</label>
                            <select class="form-control selectpicker" name="account_id">
                            @foreach($lims_account_list as $account)
                                @if($account->is_default)
                                <option selected value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                                @else
                                <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                                @endif
                            @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{trans('file.Payment Note')}}</label>
                            <textarea rows="3" class="form-control" name="payment_note"></textarea>
                        </div>

                        

                        <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div> 

    <div id="edit-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Update Payment')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'purchase.update-payment', 'method' => 'post', 'class' => 'payment-form' ]) !!}
                        <div class="row">
                            <!-- Hidden input with purchase_id -->
                            <input type="hidden" name="purchase_id" value="{{ $lims_purchase_data->id }}">
                            <div class="col-md-6">
                                <label>{{trans('file.Recieved Amount')}} *</label>
                                <input type="text" name="edit_paying_amount" class="form-control numkey"  step="any" required>
                            </div>
                            <div class="col-md-6">
                                <label>{{trans('file.Paying Amount')}} *</label>
                                <input type="text" name="edit_amount" class="form-control"  step="any" required>
                            </div>
                            <div class="col-md-6 mt-1">
                                <label>{{trans('file.Change')}} : </label>
                                <p class="change ml-2">{{number_format(0, $general_setting->decimal, '.', '')}}</p>
                            </div>
                            <div class="col-md-6 mt-1">
                                <label>{{trans('file.Paid By')}}</label>
                                <div class="d-flex align-items-center"> 
                                <select name="edit_paid_by_id" class="form-control selectpicker">
                                    <option value="1">Cash</option>
                                    <option value="3">Credit Card</option>
                                    <option value="4">Cheque</option>
                                    <option value="8">Mobile Money</option>
                                    
                                </select>
                                <i class="fa fa-plus text-success ml-2 cursor-pointer" id="multiplePaymentBtn" title="Add Split"></i>
                            </div>
                            </div>
                             <div class="col-md-12 mt-2 ml-2" id="paymentMethodsContainer"></div>
                        </div>
                        {{-- changes by yogesh --}}
                        <div class="form-group col-md-12 edit_mobile_money_fields">
                            <label>{{trans('Mobile Money')}} *</label>
                            <select id="mobile_money_operator" name="mobile-op" class="form-control" data-live-search="true" data-live-search-style="begins" title="Mobile money operator">
                                <option value="mtn_mobile_money">MTN Mobile Money</option>
                                <option value="airteltigo_cash">AirtelTigo Cash</option>
                                <option value="telecel_cash">Telecel Cash</option>
                            </select>
                        </div>
                        <input type="hidden" id="selected_mobile_op" name="selected_mobile_op">

                        <div class="form-group col-md-12 edit_mobile_money_fields">
                            <label>{{trans('Mobile Number')}} *</label>
                            <input type="number" name="mobile_number" class="form-control">
                        </div>
                        {{-- changes end by yogesh --}}
                        <div class="form-group mt-2">
                            <div class="card-element" class="form-control">
                            </div>
                            <div class="card-errors" role="alert"></div>
                        </div>
                        <div id="edit-cheque"style="display: none;">
                            <div class="form-group">
                                <label>{{trans('file.Cheque Number')}} *</label>
                                <input type="text" name="edit_cheque_no" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label> {{trans('file.Account')}}</label>
                            <select class="form-control selectpicker" name="account_id">
                            @foreach($lims_account_list as $account)
                                <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{trans('file.Payment Note')}}</label>
                            <textarea rows="3" class="form-control" name="edit_payment_note"></textarea>
                        </div>

                        <input type="hidden" name="payment_id">

                        <button type="submit" class="btn btn-primary">{{trans('file.update')}}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Approval Modal -->
    <div id="payment-approval-modal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="approvalModalLabel" class="modal-title">{{trans('Payment Approval')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <form id="payment-approval-form">
                            

                        <div class="form-group">
                            <label>{{trans('Approval Note')}}</label>
                            <textarea name="approval_note" rows="3" class="form-control"></textarea>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <input type="hidden" name="payment_id" id="approval-payment-id">
                        <div class="form-group">
                            <button type="button" class="btn btn-success" id="approve-payment">{{trans('Approve')}}</button>
                            <button type="button" class="btn btn-danger" id="reject-payment">{{trans('Reject')}}</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
@push('scripts')
<script>
    let purchaseId = "{{ $lims_purchase_data->id }}";

    $('#payment-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/purchases/get_payment_data/' + purchaseId,
            type: "GET",
            dataType: "json",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
            }
        },

        columns: [
            { data: 0, title: 'Date' },
            { data: 1, title: 'Payment Reference' },
            { data: 2, title: 'Paid Amount' },
            { data: 7, title: 'Change' },
            { data: 3, title: 'Paying Method' },
            { data: 9, title: 'Account' },
            { data: 5, title: 'Note' },
            {
                data: 4, // Payment ID
                title: 'Action',
                render: function (data, type, row) {
                    const status = row[11] ?? 'draft'; // ✅ status now comes from index 11
                    let buttons = '';

                    buttons += `<button class="btn btn-info btn-sm edit-payment" data-id="${data}">Edit</button> `;
                    buttons += `<button class="btn btn-danger btn-sm delete-payment" data-id="${data}">Delete</button> `;

                    // ✅ Adjust logic to match your backend statuses
                    if (status === 'draft' || status === 'waiting_authorization') {
                        buttons += `<button class="btn btn-warning btn-sm authorize-payment" data-id="${data}">Authorize</button>`;
                    } else if (status === 'waiting_approval') {
                        buttons += `<button class="btn btn-success btn-sm approve-the-payment" data-id="${data}">Approve</button>`;
                    } else if (status === 'pending') {
                        buttons += `<button class="btn btn-secondary btn-sm" disabled>Pending</button>`;
                    }

                    return buttons;
                }
            }
        ],

        language: {
            lengthMenu: '_MENU_ Records per page',
            info: '<small>Showing _START_ - _END_ (_TOTAL_)</small>',
            search: 'Search:',
            paginate: {
                previous: '<i class="dripicons-chevron-left"></i>',
                next: '<i class="dripicons-chevron-right"></i>'
            }
        },
        order: [[6, 'desc']],
        columnDefs: [
            {
                orderable: false,
                targets: [0, 7]
            },
            {
                render: function(data, type, row, meta) {
                    if (type === 'display') {
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }
                    return data;
                },
                checkboxes: {
                    selectRow: true,
                    selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                targets: [0]
            }
        ],
        select: { style: 'multi', selector: 'td:first-child' },
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="Export to PDF" class="fa fa-file-pdf-o"></i>',
                exportOptions: { columns: ':visible:not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'excel',
                text: '<i title="Export to Excel" class="dripicons-document-new"></i>',
                exportOptions: { columns: ':visible:not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'csv',
                text: '<i title="Export to CSV" class="fa fa-file-text-o"></i>',
                exportOptions: { columns: ':visible:not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'print',
                text: '<i title="Print" class="fa fa-print"></i>',
                exportOptions: { columns: ':visible:not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                text: '<i title="Delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function(e, dt, node, config) {
                    var selected = [];
                    $(':checkbox:checked').each(function(i) {
                        if (i) {
                            var payment = $(this).closest('tr').data('payment');
                            selected.push(payment[0]);
                        }
                    });
                    if (selected.length && confirm("Are you sure want to delete selected payments?")) {
                        $.ajax({
                            type: 'POST',
                            url: '#',
                            data: { _token: $('meta[name="csrf-token"]').attr('content'), paymentIdArray: selected },
                            success: function(data) {
                                alert(data);
                                dt.rows({ page: 'current', selected: true }).remove().draw(false);
                            }
                        });
                    } else if (!selected.length) {
                        alert('Nothing selected!');
                    }
                }
            },
            {
                extend: 'colvis',
                text: '<i title="Column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    });

    $(document).on('click', '.approve-the-payment', function () {
        const paymentId = $(this).data('id');

        // Optional: store payment ID in a hidden input if you need it inside the modal
        $('#payment-approval-modal').find('input[name="payment_id"]').val(paymentId);

        // Show the modal
        $('#payment-approval-modal').modal('show');
    });

    //On click of authorize-payment it should get payment id and make an ajax call to authorize the payment
    $(document).on('click', '.authorize-payment', function(e) {

        //dont  allow the button to be clickable again
        e.preventDefault();
        let paymentId = $(this).data('id');
        console.log('Authorizing payment with ID:', paymentId);
        // Make an ajax call to authorize the payment
        $.ajax({
            type: 'POST',
            url: '/purchases/authorize_payment/' + paymentId,
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {
                const toastMagic = new ToastMagic();
                toastMagic.success('Payment authorized successfully.');
                // Reload the DataTable to reflect changes
                $('#payment-table').DataTable().ajax.reload();
            },
            error: function(xhr) {
                const toastMagic = new ToastMagic();
                //get the error message from xhr response

                toastMagic.error('Error authorizing payment.', xhr.responseText);
                //use the Toastr
               
            }
        });
        // You can implement the ajax call here
        

    });

    // $('#add-payment input[name="paying_amount"]').on("input", function() {
    //     let payingInput = $(this);
    //     let payingAmount = parseFloat(payingInput.val()) || 0;
    //     let amount = parseFloat($('input[name="amount"]').val()) || 0;

    //     // Run your change logic — assuming it returns a new value
    //     let newPayingAmount = change(payingAmount, amount);

    //     // If change() returns something, update the field
    //     if (newPayingAmount !== undefined && !isNaN(newPayingAmount)) {
    //         payingInput.val(newPayingAmount);
    //     }
    // });
    $(document).ready(function() {
        // Set paying amount to inclusive total when modal opens
        $('#add-payment').on('show.bs.modal', function () {
            const inclusiveTotal = parseFloat("{{ $lims_purchase_data->grand_total }}");
            const balance = inclusiveTotal - parseFloat("{{ $total_paid }}");
            
            // Set paying amount to the remaining balance (inclusive total)
            $('input[name="paying_amount"]').val(balance.toFixed(2));
            $('input[name="amount"]').val(balance.toFixed(2));
            $('input[name="balance"]').val(balance.toFixed(2));
            
            // Reset change
            change(balance, balance);
        });

        // Optimized change function
        function change(paying_amount, amount) {
            const diff = parseFloat(paying_amount) - parseFloat(amount);
            $("#change-bal").text(diff.toFixed({{ $general_setting->decimal ?? 2 }}));
        }

        // Optimized input event handler
        $('#add-payment input[name="paying_amount"]').on("input", function() {
            let payingAmount = parseFloat($(this).val()) || 0;
            let amount = parseFloat($('input[name="amount"]').val()) || 0;
            change(payingAmount, amount);
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const paidBySelect = document.querySelector('[name="paid_by_id"]');
        const mobileMoneyFields = document.querySelectorAll('.mobile_money_fields');
        function toggleMobileMoneyFields() {
            if (paidBySelect.value === '8') {
                mobileMoneyFields.forEach(field => field.style.display = 'block');
            } else {
                mobileMoneyFields.forEach(field => field.style.display = 'none');
            }
        }
        toggleMobileMoneyFields();
        paidBySelect.addEventListener('change', toggleMobileMoneyFields);
    });

    
    document.addEventListener("DOMContentLoaded", function () {
        const paidBySelect = document.querySelector('[name="edit_paid_by_id"]');
        const mobileMoneyFields = document.querySelectorAll('.edit_mobile_money_fields');
        function toggleMobileMoneyFields() {
            if (paidBySelect.value === '8') {
                mobileMoneyFields.forEach(field => field.style.display = 'block');
            } else {
                mobileMoneyFields.forEach(field => field.style.display = 'none');
            }
        }
        toggleMobileMoneyFields();
        paidBySelect.addEventListener('change', toggleMobileMoneyFields);
    });

    document.addEventListener("DOMContentLoaded", function () {
        const dropdown = document.getElementById("mobile_money_operator");
        const hiddenField = document.getElementById("selected_mobile_op");
        dropdown.addEventListener("change", function () {
            hiddenField.value = dropdown.value;
        });
        hiddenField.value = dropdown.value;
    });

    
    document.addEventListener("DOMContentLoaded", function () {
        const paidBySelect = document.querySelector('[name="paid_by_id"]');
        const chequeFields = document.querySelectorAll('.cheque-section');

        // Function to toggle visibility based on payment method
        function toggleChequeFields() {
            // Assuming '4' is the value for Cheque in your dropdown
            if (paidBySelect.value === '4') {
                chequeFields.forEach(field => field.style.display = 'block');
            } else {
                chequeFields.forEach(field => field.style.display = 'none');
            }
        }

        // Run on page load
        toggleChequeFields();

        // Run when user changes payment method
        paidBySelect.addEventListener('change', toggleChequeFields);
    });


    $(document).on('submit', '.payment-form', function(e) {
        // if( $('input[name="paying_amount"]').val() < parseFloat($('#amount').val()) ) {
        //     alert('Paying amount cannot be bigger than recieved amount');
        //     $('input[name="amount"]').val('');
        //     $(".change").text(parseFloat( $('input[name="paying_amount"]').val() - $('#amount').val() ).toFixed({{$general_setting->decimal}}));
        //     e.preventDefault();
        // }
        // else if( $('input[name="edit_paying_amount"]').val() < parseFloat($('input[name="edit_amount"]').val()) ) {
        //     alert('Paying amount cannot be bigger than recieved amount');
        //     $('input[name="edit_amount"]').val('');
        //     $(".change").text(parseFloat( $('input[name="edit_paying_amount"]').val() - $('input[name="edit_amount"]').val() ).toFixed({{$general_setting->decimal}}));
        //     e.preventDefault();
        // }

        $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', false);
    });


    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }

    function confirmDeletePayment() {
        if (confirm("Are you sure want to delete? If you delete this money will be refunded")) {
            return true;
        }
        return false;
    }

    document.addEventListener("DOMContentLoaded", function () {

        // ✅ Function: show or hide global .mobile_money_fields depending on selections
        function toggleGlobalMobileMoneyFields() {
            // Check if any select currently has value '8'
            const hasMobileMoney = Array.from(document.querySelectorAll('select[name="paid_by_id_select[]"]'))
                .some(select => select.value === '8');

            const fields = document.querySelectorAll('.mobile_money_fields');
            fields.forEach(field => {
                field.style.display = hasMobileMoney ? 'block' : 'none';
            });
        }

        // ✅ Listen to change event on all current & future selects
        $(document).on("change", 'select[name="paid_by_id_select[]"].payment-method-select', function () {
            toggleGlobalMobileMoneyFields();
        });

        // ✅ Initialize state on load
        toggleGlobalMobileMoneyFields();

        // ✅ Add Payment Row Button Logic
        $("#multiplePaymentBtn").on("click", function () {
            let usedMethods = getSelectedPaymentMethods();

            let options = [
                { value: 1, label: 'Cash' },
                { value: 3, label: 'Credit Card' },
                { value: 4, label: 'Cheque' },
                { value: 8, label: 'Mobile Money' }
            ];

            let availableOptions = options.filter(opt => !usedMethods.includes(opt.value));

            if (availableOptions.length === 0) {
                alert("All payment methods have been used.");
                return;
            }

            let selectOptions = availableOptions
                .map(opt => `<option value="${opt.value}">${opt.label}</option>`)
                .join('');

            let paymentRow = `
                <div class="row mt-2 payment-row align-items-center">
                    <div class="col-md-4">
                        <label>Payment Amount *</label>
                        <input type="number" name="split_amount[]" class="form-control numkey" step="any" required>
                    </div>
                    <div class="col-md-4">
                        <label>Payment Method</label>
                        <div class="d-flex align-items-center">
                            <select name="paid_by_id_select[]" class="form-control selectpicker payment-method-select">
                                ${selectOptions}
                            </select>
                            <button type="button" class="btn btn-danger ml-2 remove-payment">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $("#paymentMethodsContainer").append(paymentRow);
            $('.selectpicker').selectpicker('refresh');

            // Re-evaluate Mobile Money visibility after adding new row
            toggleGlobalMobileMoneyFields();
        });
    });


    function getSelectedPaymentMethods() {
        let methods = [];

        let mainMethod = $("select[name='edit_paid_by_id']").val();
        if (mainMethod) methods.push(parseInt(mainMethod));

        $("select[name='paid_by_id[]']").each(function () {
            let val = $(this).val();
            if (val) methods.push(parseInt(val));
        });

        return methods;
    }


    $(document).on("input", 'input[name="split_amount[]"]', function() {
        let payingAmount = parseFloat($('input[name="paying_amount"]').val()) || 0;
        let totalSplit = getTotalSplitAmount();
        let mainPaid = parseFloat($('input[name="amount"]').val()) || 0;
        let totalPaid = totalSplit + payingAmount;

        if (totalPaid > mainPaid) {
            alert('Total paid (main + split) cannot be greater than paying amount');
            $(this).val('');
            totalSplit = getTotalSplitAmount(); // recalculate after clearing this input
            totalPaid = totalSplit + payingAmount;
        }     
        console.log("Total Paid (Main + Split):", totalPaid);  
        // alert('Total paid (main + split) cannot be greater than paying amount');
        //     $(this).val('');
        //     totalSplit = getTotalSplitAmount(); // recalculate after clearing this input
        //     totalPaid = totalSplit + mainPaid;
        // }

        change(totalPaid, mainPaid);
    });

    $(document).ready(function () {
        
        // Remove payment row
        $(document).on("click", ".remove-payment", function () {
            $(this).closest(".payment-row").remove();
            // refreshPaymentMethodOptions();
            
            // Get updated values
            let payingAmount = parseFloat($('input[name="paying_amount"]').val()) || 0;
            let mainPaid = parseFloat($('input[name="amount"]').val()) || 0;
            let totalSplit = getTotalSplitAmount();
            let totalPaid = payingAmount + totalSplit;

            // Update change
            change(totalPaid, mainPaid);
        });
    });

    function change(paying_amount, amount) {
        console.log("Calculating change:", paying_amount, amount);
        $("#change-bal").text((paying_amount - amount).toFixed({{$general_setting->decimal}}));
    }


    function getTotalSplitAmount() {
        let total = 0;
        $('input[name="split_amount[]"]').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        console.log("Total split amount:", total);
        return total;
    }

    // Handle Approve button inside modal
    $('#approve-payment').on('click', function () {
        const paymentId = $('#approval-payment-id').val();
        const note = $('textarea[name="approval_note"]').val();
        const token = $('input[name="_token"]').val();
        const toastMagic = new ToastMagic();

        $.ajax({
            url: `/purchases/approve-payment/${paymentId}`,
            type: 'POST',
            data: {
                _token: token,
                approval_note: note
            },
            success: function (response) {
                $('#payment-approval-modal').modal('hide');
                toastMagic.success('Payment  approved successfully!');
                $('#payment-table').DataTable().ajax.reload(null, false); // reload table without resetting pagination
            },
            error: function (xhr) {
            // convert the error message in xhr to readable text
                const errorReadable = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';

                toastMagic.error('Failed to approve payment: ' + errorReadable);
                console.error(xhr.responseText);
            }
        });
    });

    // Handle Reject button inside modal
    $('#reject-payment').on('click', function () {
        const paymentId = $('#approval-payment-id').val();
        const note = $('textarea[name="approval_note"]').val();
        const token = $('input[name="_token"]').val();
        const toastMagic = new ToastMagic();

        $.ajax({
            url: `/purchases/reject-payment/${paymentId}`,
            type: 'POST',
            data: {
                _token: token,
                approval_note: note
            },
            success: function (response) {
                $('#payment-approval-modal').modal('hide');
                toastMagic.success('Payment rejected successfully!');
                $('#payment-table').DataTable().ajax.reload(null, false);
            },
            error: function (xhr) {
                toastMagic.error('Failed to reject payment: ' + xhr.responseText);
                console.error(xhr.responseText);
            }
        });
    });


    </script>
@endpush