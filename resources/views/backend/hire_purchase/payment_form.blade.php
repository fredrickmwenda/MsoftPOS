@extends('backend.layout.main')
@section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{trans('file.Record Installment Payment')}}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>{{trans('file.Installment Details')}}</strong>
                                    <br>
                                    Reference: {{ $sale->reference_no }}
                                    <br>
                                    Installment #{{ $installment->installment_number }} of {{ $sale->hire_purchase_terms }}
                                    <br>
                                    Due Date: {{ $installment->due_date->format('Y-m-d') }}
                                </div>
                            </div>
                        </div>

                        <form method="post" action="{{ route('hire_purchase.record_payment', $installment->id) }}" class="payment-form">
                            @csrf
                            @method('POST')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{trans('file.Customer')}}</label>
                                        <input type="text" class="form-control" value="{{ $sale->customer->name ?? 'N/A' }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{trans('file.Phone')}}</label>
                                        <input type="text" class="form-control" value="{{ $sale->customer->phone_number ?? 'N/A' }}" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{trans('file.Installment Amount')}}</label>
                                        <input type="number" class="form-control" value="{{ number_format($installment->amount, 2) }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{trans('file.Already Paid')}}</label>
                                        <input type="number" class="form-control" value="{{ number_format($installment->paid_amount, 2) }}" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{trans('file.Remaining Amount')}}</label>
                                        <input type="number" class="form-control" value="{{ number_format($installment->remaining_amount, 2) }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{trans('file.Amount to Pay')}} *</label>
                                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="{{ $installment->remaining_amount }}" required placeholder="Enter amount">
                                        @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{trans('file.Payment Date')}} *</label>
                                        <input type="text" name="payment_date" class="form-control date" required>
                                        @error('payment_date')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{trans('file.Payment Method')}} *</label>
                                        <select name="payment_method" class="form-control" required>
                                            <option value="">Select Payment Method</option>
                                            @foreach($paymentMethods as $key => $method)
                                                <option value="{{ $key }}">{{ $method }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_method')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{trans('file.Payment Notes')}}</label>
                                        <textarea name="payment_note" class="form-control" rows="3" placeholder="Enter any additional notes..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success mr-2">
                                        <i class="dripicons-check"></i> {{trans('file.Record Payment')}}
                                    </button>
                                    <a href="{{ route('hire_purchase.show', $sale->id) }}" class="btn btn-secondary">
                                        {{trans('file.Cancel')}}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
