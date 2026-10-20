@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
            <div>
                <h4 class="mb-0 fw-bold">{{ __('file.Generate Payroll') }}</h4>
                <small class="text-muted">Payrolls are built from each employee's assigned template</small>
            </div>
            <a href="{{ route('payroll.index') }}" class="btn btn-secondary"><i class="dripicons-arrow-left"></i> {{ __('file.Back') }}</a>
        </div>

        {{-- Filters --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('payroll.generate') }}">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-4">
                            <label>{{ __('file.Warehouse') }}</label>
                            <select name="warehouse_id" class="form-control">
                                <option value="0">{{ __('file.All Warehouse') }}</option>
                                @foreach($lims_warehouse_list as $wh)
                                    <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>{{ __('file.Month') }} *</label>
                            <input type="month" name="month" class="form-control" value="{{ $month }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <button class="btn btn-primary"><i class="dripicons-loop"></i> {{ __('file.Load Employees') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Generation form --}}
        <form method="POST" action="{{ route('payroll.generate-store') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ \Carbon\Carbon::create($year, $mon, 1)->format('F Y') }}</h5>
                <div class="d-flex align-items-center">
                    <label class="mr-2 mb-0 fw-semibold">{{ __('file.Account') }}</label>
                    <select name="account_id" class="form-control" style="width:230px;">
                        @foreach($lims_account_list as $acc)
                            <option value="{{ $acc->id }}" {{ $acc->is_default ? 'selected' : '' }}>
                                {{ $acc->name }} [{{ $acc->account_no }}]
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th><input type="checkbox" id="checkAll" checked></th>
                            <th>{{ __('file.Employee') }}</th>
                            <th>{{ __('file.Template') }}</th>
                            <th>Breakdown</th>
                            <th style="width:120px;">OT Hrs / Days</th>
                            <th style="width:150px;">{{ __('file.Net Salary') }}</th>
                            <th style="width:120px;">{{ __('file.Method') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($employees as $e)
                        @php
                            $tpl = $e->salaryTemplate;
                            $ex  = $existing->get($e->id);
                            $otH = (float) ($overtimes->get($e->id)?->sum('hours') ?? 0);
                            $all = $tpl && $tpl->type === 'monthly' ? $tpl->total_allowance : 0;
                            $ded = $tpl && $tpl->type === 'monthly' ? $tpl->total_deduction : 0;
                        @endphp
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="include-check"
                                    name="payrolls[{{ $e->id }}][include]" value="1" checked>
                            </td>
                            <td><strong>{{ $e->name }}</strong></td>
                            <td>
                                @if($tpl)
                                    {{ $tpl->name }}
                                    <span class="badge badge-{{ $tpl->type === 'hourly' ? 'info' : 'primary' }}">{{ ucfirst($tpl->type) }}</span>
                                @else
                                    <span class="badge badge-warning">No Template</span>
                                @endif
                            </td>
                            <td>
                                @if($tpl && $tpl->type === 'monthly')
                                    <small>
                                        Basic: <b>{{ number_format((float)$tpl->basic_salary, 2) }}</b> |
                                        Allow: <b class="text-success">{{ number_format($all, 2) }}</b> |
                                        Deduct: <b class="text-danger">{{ number_format($ded, 2) }}</b> |
                                        OT: <b>{{ number_format((float)$tpl->overtime_rate, 2) }}</b>/hr
                                    </small>
                                @elseif($tpl)
                                    <small>
                                        <b>{{ number_format((float)$tpl->hourly_rate, 2) }}</b>/hr ×
                                        {{ number_format((float)$tpl->hours_per_day, 2) }}h =
                                        <b>{{ number_format($tpl->daily_rate, 2) }}</b>/day
                                    </small>
                                @else
                                    <small class="text-muted">Manual salary entry</small>
                                @endif
                            </td>
                            <td>
                                @if($tpl && $tpl->type === 'hourly')
                                    <input type="number" min="0" step="1" class="form-control form-control-sm days-input"
                                        name="payrolls[{{ $e->id }}][days_worked]"
                                        value="{{ $ex->days_worked ?? 0 }}">
                                @elseif($tpl)
                                    <input type="number" min="0" step="any" class="form-control form-control-sm ot-input"
                                        name="payrolls[{{ $e->id }}][overtime_hours]"
                                        value="{{ $ex->overtime_hours ?? $otH }}">
                                @else
                                    <input type="number" min="0" step="any" class="form-control form-control-sm manual-salary"
                                        name="payrolls[{{ $e->id }}][basic_salary]"
                                        value="{{ $ex->basic_salary ?? $e->salary ?? 0 }}">
                                @endif
                            </td>
                            <td>
                                <input type="number" step="any" readonly
                                    class="form-control form-control-sm text-right font-weight-bold row-net"
                                    data-type="{{ $tpl->type ?? 'none' }}"
                                    data-basic="{{ $tpl->basic_salary ?? 0 }}"
                                    data-allow="{{ $all }}"
                                    data-deduct="{{ $ded }}"
                                    data-otrate="{{ $tpl->overtime_rate ?? 0 }}"
                                    data-daily="{{ $tpl->daily_rate ?? 0 }}"
                                    value="0">
                            </td>
                            <td>
                                <select class="form-control form-control-sm" name="payrolls[{{ $e->id }}][paying_method]">
                                    <option value="0">{{ __('file.Cash') }}</option>
                                    <option value="1">{{ __('file.Cheque') }}</option>
                                    <option value="2">{{ __('file.Credit Card') }}</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No active employees found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-right">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="dripicons-checkmark"></i> {{ __('file.Generate Payroll') }}
                </button>
            </div>
        </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
 $(function () {
    function calcRow($net) {
        const type = $net.data('type');
        const $row = $net.closest('tr');
        let net = 0;

        if (type === 'hourly') {
            net = (parseFloat($row.find('.days-input').val()) || 0) * parseFloat($net.data('daily'));
        } else if (type === 'monthly') {
            const ot = parseFloat($row.find('.ot-input').val()) || 0;
            net = parseFloat($net.data('basic')) + parseFloat($net.data('allow'))
                + ot * parseFloat($net.data('otrate')) - parseFloat($net.data('deduct'));
        } else {
            net = parseFloat($row.find('.manual-salary').val()) || 0;
        }
        $net.val(Math.max(0, net).toFixed(2));
    }

    $(document).on('input', '.ot-input, .days-input, .manual-salary', function () {
        calcRow($(this).closest('tr').find('.row-net'));
    });
    $('.row-net').each(function () { calcRow($(this)); });

    $('#checkAll').on('change', function () {
        $('.include-check').prop('checked', this.checked);
    });
});
</script>
@endpush
