@extends('backend.layout.main')
@section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4><i class="dripicons dripicons-checkmark"></i> Approvals</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Review and approve purchase payments and expenses in one place.</p>

                        {{-- Statistics --}}
                        <div class="row mb-4">
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card shadow h-100 py-2" style="border-left: 4px solid #4e73df;">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Purchase Payments</div>
                                                <div class="h5 mb-0 font-weight-bold">{{ $stats['pending_purchase_payments'] }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-money fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card shadow h-100 py-2" style="border-left: 4px solid #f6c23e;">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Expenses</div>
                                                <div class="h5 mb-0 font-weight-bold">{{ $stats['pending_expenses'] }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="dripicons dripicons-wallet fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card shadow h-100 py-2" style="border-left: 4px solid #1cc88a;">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Pending</div>
                                                <div class="h5 mb-0 font-weight-bold">{{ $stats['total_pending'] }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-clock-o fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tabs --}}
                        <ul class="nav nav-tabs" id="approvalTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="purchase-payments-tab" data-toggle="tab" href="#purchase-payments" role="tab">Purchase Payments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="expenses-tab" data-toggle="tab" href="#expenses" role="tab">Expenses</a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="approvalTabsContent">
                            {{-- Purchase Payments Tab --}}
                            <div class="tab-pane fade show active" id="purchase-payments" role="tabpanel">
                                @if($pendingPurchasePayments->isEmpty())
                                    <p class="text-muted">No purchase payments pending approval.</p>
                                    @if($canAccessPurchases)
                                        <a href="{{ route('purchases.index') }}" class="btn btn-default">View Purchases</a>
                                    @endif
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Reference</th>
                                                    <th>Purchase</th>
                                                    <th>Supplier</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    @if($canApprovePayments)
                                                    <th class="not-exported">Action</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pendingPurchasePayments as $payment)
                                                @php $purchase = $payment->purchase; @endphp
                                                <tr>
                                                    <td>{{ $payment->created_at ? $payment->created_at->format(config('date_format')) : '—' }}</td>
                                                    <td>{{ $payment->payment_reference ?? '—' }}</td>
                                                    <td>
                                                        @if($purchase && $canAccessPurchases)
                                                            <a href="{{ route('purchases.show', $purchase->id) }}">{{ $purchase->reference_no ?? '—' }}</a>
                                                        @else
                                                            {{ $purchase->reference_no ?? '—' }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $purchase && $purchase->supplier ? $purchase->supplier->name : '—' }}</td>
                                                    <td>{{ number_format($payment->amount ?? 0, config('decimal')) }}</td>
                                                    <td><span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $payment->approval_status ?? 'pending')) }}</span></td>
                                                    @if($canApprovePayments)
                                                    <td>
                                                        <form action="{{ route('purchase.approve-payment', $payment->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                        </form>
                                                        <form action="{{ route('purchase.reject-payment', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Reject this payment?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                        </form>
                                                    </td>
                                                    @endif
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Expenses Tab --}}
                            <div class="tab-pane fade" id="expenses" role="tabpanel">
                                @if($pendingExpenses->isEmpty())
                                    <p class="text-muted">No expenses pending approval.</p>
                                    @if($canAccessExpenses)
                                        <a href="{{ route('expenses.index') }}" class="btn btn-default">View Expenses</a>
                                    @endif
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Reference</th>
                                                    <th>Name</th>
                                                    <th>Category</th>
                                                    <th>Warehouse</th>
                                                    <th>Amount</th>
                                                    @if($canApprovePayments)
                                                    <th class="not-exported">Action</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pendingExpenses as $expense)
                                                <tr>
                                                    <td>{{ $expense->created_at ? $expense->created_at->format(config('date_format')) : '—' }}</td>
                                                    <td>{{ $expense->reference_no ?? '—' }}</td>
                                                    <td>{{ $expense->name ?? '—' }}</td>
                                                    <td>{{ $expense->expenseCategory ? $expense->expenseCategory->name : '—' }}</td>
                                                    <td>{{ $expense->warehouse ? $expense->warehouse->name : '—' }}</td>
                                                    <td>{{ number_format($expense->amount ?? 0, config('decimal')) }}</td>
                                                    @if($canApprovePayments)
                                                    <td>
                                                        <form action="{{ route('approval.expense.approve', $expense->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                        </form>
                                                    </td>
                                                    @endif
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                        </div> {{-- tab-content --}}
                    </div> {{-- card-body --}}
                </div> {{-- card --}}
            </div>
        </div>
    </div>
</section>
@endsection