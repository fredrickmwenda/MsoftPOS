@extends('backend.layout.main')
@section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4>Hire Purchase Dashboard</h4>
                        <div>
                            <a href="{{ route('hire_purchase.index') }}" class="btn btn-sm btn-primary">
                                <i class="dripicons-list"></i> All Contracts
                            </a>
                            <a href="{{ route('hire_purchase.report') }}" class="btn btn-sm btn-info">
                                <i class="dripicons-document"></i> Reports
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card text-center bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Active Contracts</h5>
                                        <h3>{{ $summary['active_contracts'] ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Completed Contracts</h5>
                                        <h3>{{ $summary['completed_contracts'] ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Overdue Installments</h5>
                                        <h3 class="text-danger">{{ $summary['overdue_installments'] ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Paid</h5>
                                        <h3>{{ number_format($summary['total_paid'] ?? 0, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5>Recent Payments</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>{{trans('file.Amount')}}</th>
                                                <th>{{trans('file.Date')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentPayments as $payment)
                                                <tr>
                                                    <td>{{ $payment->sale->customer->name ?? 'N/A' }}</td>
                                                    <td>{{ number_format($payment->paid_amount, 2) }}</td>
                                                    <td>{{ $payment->updated_at->format('Y-m-d') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No payments yet</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5>Upcoming Due (7 days)</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Due Date</th>
                                                <th>{{trans('file.Amount')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($upcomingDue as $installment)
                                                <tr>
                                                    <td>{{ $installment->sale->customer->name ?? 'N/A' }}</td>
                                                    <td>{{ $installment->due_date->format('Y-m-d') }}</td>
                                                    <td>{{ number_format($installment->amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No upcoming installments</td>
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
