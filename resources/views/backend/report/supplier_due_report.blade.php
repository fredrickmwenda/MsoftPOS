@extends('backend.layout.main')

@section('content')
<div class="container-fluid mb-3"><a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a></div>
<section class="forms supplier-due-report">
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ trans('file.Supplier Due Report') }}</h4>
            </div>
            {!! Form::open(['route' => 'report.supplierDueByDate', 'method' => 'post']) !!}
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="control-label"><strong>Start Date</strong></label>
                            <input type="date" class="form-control" name="start_date" value="{{ $start_date ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="control-label"><strong>End Date</strong></label>
                            <input type="date" class="form-control" name="end_date" value="{{ $end_date ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary" type="submit">{{ trans('file.submit') }}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        @if(empty($supplier_summaries))
            <div class="card">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-0">No supplier dues found for the selected date range.</p>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($supplier_summaries as $summary)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card supplier-due-card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="supplier-icon rounded-circle bg-light d-flex align-items-center justify-content-center mr-3" style="width: 48px; height: 48px;">
                                    <i class="fa fa-truck text-primary" style="font-size: 1.4rem;"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 font-weight-bold">{{ $summary['supplier_name'] }}</h5>
                                    @if($summary['supplier_phone'])
                                        <small class="text-muted">{{ $summary['supplier_phone'] }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="border-top pt-3">
                                <div class="row text-center mb-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Purchases</small>
                                        <span class="badge badge-info badge-pill">{{ $summary['purchases_count'] }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ trans('file.grand total') }}</small>
                                        <strong>{{ number_format((float)$summary['total_grand_total'], $general_setting->decimal ?? 2, '.', '') }}</strong>
                                    </div>
                                </div>
                                <div class="row text-center mb-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ trans('file.Returned Amount') }}</small>
                                        <span class="text-secondary">{{ number_format((float)$summary['total_returned'], $general_setting->decimal ?? 2, '.', '') }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ trans('file.Paid') }}</small>
                                        <span class="text-success">{{ number_format((float)$summary['total_paid'], $general_setting->decimal ?? 2, '.', '') }}</span>
                                    </div>
                                </div>
                                <div class="text-center mt-3 pt-3 border-top">
                                    <small class="text-muted d-block mb-1">{{ trans('file.Due') }}</small>
                                    <h4 class="mb-0 font-weight-bold text-danger">{{ number_format((float)$summary['total_due'], $general_setting->decimal ?? 2, '.', '') }}</h4>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-primary btn-block" type="button" data-toggle="collapse" data-target="#purchases-{{ $summary['supplier_id'] }}" aria-expanded="false">
                                    <i class="fa fa-list"></i> View {{ $summary['purchases_count'] }} purchase(s)
                                </button>
                            </div>
                            <div class="collapse mt-2" id="purchases-{{ $summary['supplier_id'] }}">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 small">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{ trans('file.Date') }}</th>
                                                <th>{{ trans('file.reference') }}</th>
                                                <th class="text-right">{{ trans('file.grand total') }}</th>
                                                <th class="text-right">{{ trans('file.Due') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($summary['purchases'] as $p)
                                            <tr>
                                                <td>{{ date($general_setting->date_format ?? 'd/m/Y', strtotime($p->created_at)) }}</td>
                                                <td>{{ $p->reference_no }}</td>
                                                <td class="text-right">{{ number_format((float)$p->grand_total, $general_setting->decimal ?? 2, '.', '') }}</td>
                                                <td class="text-right text-danger">{{ number_format((float)$p->due, $general_setting->decimal ?? 2, '.', '') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<style>
.supplier-due-report .supplier-due-card {
    border-radius: 12px;
    border: 1px solid #e9ecef;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.supplier-due-report .supplier-due-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
}
.supplier-due-report .card-title { font-size: 1.05rem; }
.supplier-due-report .badge-pill { padding: 0.35em 0.65em; }
.supplier-due-report .table-sm td, .supplier-due-report .table-sm th { padding: 0.4rem; font-size: 0.8rem; }
</style>
@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #supplier-due-report-menu").addClass("active");
</script>
@endpush
