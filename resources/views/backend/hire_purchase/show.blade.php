@extends('backend.layout.main')
@section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4>Hire Purchase Details - {{ $sale->reference_no }}</h4>
                        <a href="{{ route('hire_purchase.index') }}" class="btn btn-sm btn-secondary">
                            <i class="dripicons-arrow-left"></i> {{trans('file.Back')}}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Contract Information</h5>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th>Reference No:</th>
                                                <td>{{ $sale->reference_no }}</td>
                                            </tr>
                                            <tr>
                                                <th>Customer:</th>
                                                <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Phone:</th>
                                                <td>{{ $sale->customer->phone_number ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{trans('file.Email')}}:</th>
                                                <td>{{ $sale->customer->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Sale Date:</th>
                                                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Financial Summary</h5>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th>Grand Total:</th>
                                                <td>{{ number_format($sale->grand_total, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Down Payment:</th>
                                                <td>{{ number_format($summary['down_payment'], 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{trans('file.Balance')}}</th>
                                                <td>{{ number_format($summary['balance'], 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Interest Rate:</th>
                                                <td>{{ $summary['interest_rate'] }}%</td>
                                            </tr>
                                            <tr>
                                                <th>Total Interest:</th>
                                                <td>{{ number_format($summary['total_interest'], 2) }}</td>
                                            </tr>
                                            <tr class="font-weight-bold">
                                                <th>Total with Interest:</th>
                                                <td>{{ number_format($summary['total_with_interest'], 2) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Hire Purchase Installment Plan</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
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
                                                    <td>#{{ $installment->installment_number }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($installment->due_date)->format('Y-m-d') }}</td>
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
                                                        @if($installment->payment_status !== 'paid')
                                                            <a href="#" class="btn btn-sm btn-success">
                                                                <i class="dripicons-money"></i> Record Payment
                                                            </a>
                                                        @else
                                                            <span class="text-muted">Paid</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No installments found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>{{trans('file.Products')}}</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{trans('file.Product')}}</th>
                                                <th>{{trans('file.Qty')}}</th>
                                                <th>{{trans('file.Price')}}</th>
                                                <th>{{trans('file.Total')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sale->product_sales as $product)
                                                <tr>
                                                    <td>{{ $product->product->name ?? 'N/A' }}</td>
                                                    <td>{{ $product->qty }}</td>
                                                    <td>{{ number_format($product->net_unit_price, 2) }}</td>
                                                    <td>{{ number_format($product->total, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No products found</td>
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

@endsection
