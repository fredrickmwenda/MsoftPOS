@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">

        {{-- ================= PAGE HEADER ================= --}}
        <div class="d-flex align-items-center mt-3 mb-4">
            <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded mr-3"
                style="width:48px;height:48px;font-size:22px;">
                <i class="dripicons-hourglass"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold">{{ __('file.Overtime') }}</h4>
                <small class="text-muted">{{ __('file.Add overtime work record') }}</small>
            </div>
            <div class="ml-auto text-right">
                <small class="text-muted">
                    <a href="{{ route('payroll.index') }}">Dashboard</a> /
                    Overtime / Add Overtime
                </small>
            </div>
        </div>

        <div class="row">
            {{-- ================= ADD OVERTIME CARD ================= --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary fw-bold">
                            <i class="dripicons-plus"></i> {{ __('file.Add Overtime Record') }}
                        </h5>
                    </div>
                    <div class="card-body py-4">
                        <form method="POST" action="{{ route('overtime.store') }}" id="overtimeForm">
                            @csrf

                            {{-- Employee (no Role — direct employee selection) --}}
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label fw-semibold">
                                    {{ __('file.Employee') }} <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <select name="employee_id" id="employee_id"
                                        class="form-control selectpicker" data-live-search="true"
                                        title="Select Employee" required>
                                        <option value="">{{ __('file.Select Employee') }}</option>
                                        @foreach($employees as $employee)
                                            @php $otRate = (float) ($employee->salaryTemplate?->overtime_rate ?? 0); @endphp
                                            <option value="{{ $employee->id }}"
                                                data-rate="{{ $otRate }}"
                                                {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                                @if($otRate > 0)
                                                    — {{ __('file.Rate') }}: {{ number_format($otRate, 2) }}/hr
                                                @else
                                                    — (no OT rate)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Date (datetime) --}}
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label fw-semibold">
                                    {{ __('file.date') }} <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <input type="text" name="date" id="ot_date"
                                        class="form-control" autocomplete="off"
                                        placeholder="DD-MM-YYYY hh:mm AM/PM"
                                        value="{{ old('date') }}" required>
                                </div>
                            </div>

                            {{-- Hours --}}
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label fw-semibold">
                                    {{ __('file.Hours') }} <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <input type="number" step="any" min="0.25"
                                        name="hours" id="ot_hours" class="form-control"
                                        placeholder="Enter hours (e.g. 2.5)"
                                        value="{{ old('hours') }}" required>
                                </div>
                            </div>

                            {{-- Total Amount (auto-calculated, not submitted) --}}
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label fw-semibold">
                                    {{ __('file.Total Amount') }} <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <input type="text" id="ot_amount" class="form-control"
                                        placeholder="Auto-calculated" readonly>
                                    <small class="text-muted" id="ot_rate_hint"></small>
                                </div>
                            </div>

                            {{-- Note (optional, hidden until needed) --}}
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">{{ __('file.Note') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" name="note" class="form-control"
                                        placeholder="Optional note...">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-8 offset-sm-3">
                                    <button type="submit" class="btn btn-success px-4 py-2">
                                        <i class="dripicons-document"></i> {{ __('file.Add Overtime') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= OVERTIME RECORDS ================= --}}
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="dripicons-list"></i> {{ __('file.Overtime Records') }}</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover" id="overtime-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('file.date') }}</th>
                            <th>{{ __('file.Employee') }}</th>
                            <th>{{ __('file.Month') }}</th>
                            <th>{{ __('file.Hours') }}</th>
                            <th>{{ __('file.Rate') }}/hr</th>
                            <th>{{ __('file.Total Amount') }}</th>
                            <th>{{ __('file.Note') }}</th>
                            <th class="not-exported">{{ __('file.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimes as $ot)
                            <tr>
                                <td>{{ $ot->date ? $ot->date->format('d-m-Y h:i A') : '—' }}</td>
                                <td>{{ optional($ot->employee)->name ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::create($ot->year, $ot->month, 1)->format('F Y') }}</td>
                                <td>{{ $ot->hours }}</td>
                                <td>{{ number_format((float) $ot->rate, 2) }}</td>
                                <td class="font-weight-bold">{{ number_format((float) $ot->amount, 2) }}</td>
                                <td>{{ $ot->note ?? '—' }}</td>
                                <td>
                                    {!! Form::open(['route' => ['overtime.destroy', $ot->id], 'method' => 'DELETE', 'class' => 'd-inline']) !!}
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete this overtime record?')">
                                            <i class="dripicons-trash"></i>
                                        </button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No overtime records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
 $(function () {
    /* ---------- Datetime picker (uses theme's picker if available, else native) ---------- */
    if ($.fn.datetimepicker) {
        $('#ot_date').datetimepicker({
            format: 'd-m-Y h:i A',
            step: 15,
            allowTimes: undefined
        });
    } else {
        // Native fallback — server accepts "Y-m-d\TH:i" too
        $('#ot_date').attr('type', 'datetime-local');
    }

    /* ---------- Auto-calculate Total Amount = Hours × Employee OT Rate ---------- */
    function calcAmount() {
        const rate  = parseFloat($('#employee_id option:selected').data('rate')) || 0;
        const hours = parseFloat($('#ot_hours').val()) || 0;

        $('#ot_amount').val((rate * hours).toFixed(2));

        // Rate hint
        if ($('#employee_id').val()) {
            $('#ot_rate_hint').text(
                rate > 0
                    ? 'Rate: ' + rate.toFixed(2) + ' /hr (from assigned salary template)'
                    : '⚠ No overtime rate — assign a monthly salary template to this employee'
            ).toggleClass('text-danger', rate <= 0);
        } else {
            $('#ot_rate_hint').text('');
        }
    }

    $('#employee_id').on('changed.bs.select change', calcAmount);
    $('#ot_hours').on('input', calcAmount);
});
</script>
@endpush
