@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
            <div>
                <h4 class="mb-0 fw-bold">{{ __('file.Manage Salary') }}</h4>
                <small class="text-muted">Generated payroll records</small>
            </div>
            <a href="{{ route('payroll.generate') }}" class="btn btn-success">
                <i class="dripicons-plus"></i> {{ __('file.Generate Payroll') }}
            </a>
        </div>

        <form method="GET" action="{{ route('payroll.index') }}" class="form-inline mb-3">
            <input type="month" name="month" class="form-control mr-2" value="{{ request('month') }}">
            <button class="btn btn-outline-primary btn-sm">{{ __('file.Filter') }}</button>
            @if(request('month'))
                <a href="{{ route('payroll.index') }}" class="btn btn-link btn-sm">{{ __('file.Reset') }}</a>
            @endif
        </form>

        <div class="table-responsive">
            <table id="payroll-table" class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('file.date') }}</th>
                        <th>{{ __('file.reference') }}</th>
                        <th>{{ __('file.Employee') }}</th>
                        <th>{{ __('file.Template') }}</th>
                        <th>{{ __('file.Month') }}</th>
                        <th>{{ __('file.Gross Salary') }}</th>
                        <th>{{ __('file.Total Deduction') }}</th>
                        <th>{{ __('file.Net Salary') }}</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="not-exported">{{ __('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($lims_payroll_all as $payroll)
                    @php
                        $period = $payroll->year && $payroll->month
                            ? \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F Y') : '—';
                        $due = max(0, (float)$payroll->amount - (float)$payroll->paid_amount);
                    @endphp
                    <tr>
                        <td>{{ $payroll->date ? date($general_setting->date_format, strtotime($payroll->date)) : '—' }}</td>
                        <td>{{ $payroll->reference_no }}</td>
                        <td>{{ $payroll->employee_name ?? optional($payroll->employee)->name }}</td>
                        <td>{{ optional($payroll->salaryTemplate)->name ?? '—' }}
                            <span class="badge badge-{{ $payroll->template_type === 'hourly' ? 'info' : 'primary' }}">
                                {{ ucfirst($payroll->template_type) }}</span>
                        </td>
                        <td>{{ $period }}</td>
                        <td>{{ number_format((float)$payroll->gross_amount, $general_setting->decimal) }}</td>
                        <td>{{ number_format((float)$payroll->total_deduction, $general_setting->decimal) }}</td>
                        <td class="font-weight-bold">{{ number_format((float)$payroll->amount, $general_setting->decimal) }}</td>
                        <td class="text-success">{{ number_format((float)$payroll->paid_amount, $general_setting->decimal) }}</td>
                        <td class="text-danger">{{ number_format($due, $general_setting->decimal) }}</td>
                        <td>
                            <span class="badge badge-{{ $payroll->payment_status === 'paid' ? 'success' : ($payroll->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                {{ ucfirst($payroll->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                    {{ __('file.action') }} <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><button type="button" class="btn btn-link view-btn" data-id="{{ $payroll->id }}">
                                        <i class="dripicons-preview"></i> {{ __('file.View') }}</button></li>
                                    <li><button type="button" class="btn btn-link edit-btn"
                                        data-id="{{ $payroll->id }}"
                                        data-month="{{ $payroll->year }}-{{ str_pad($payroll->month, 2, '0', STR_PAD_LEFT) }}"
                                        data-method="{{ $payroll->paying_method }}"
                                        data-note="{{ $payroll->note }}"
                                        data-toggle="modal" data-target="#editModal">
                                        <i class="dripicons-document-edit"></i> {{ __('file.Edit') }}</button></li>
                                    <li class="divider"></li>
                                    <!--<li>
                                        {!! Form::open(['route' => ['payroll.destroy', $payroll->id], 'method' => 'DELETE']) !!}
                                            <button type="submit" class="btn btn-link" onclick="return confirm('Delete this payroll?')">
                                                <i class="dripicons-trash"></i> {{ __('file.Delete') }}
                                            </button>
                                        {!! Form::close() !!}
                                    </li>-->
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ================= EDIT MODAL ================= --}}
<div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="payrollEditForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="dripicons-document-edit"></i> {{ __('file.Edit Payroll') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('file.Month') }} *</label>
                        <input type="month" name="month" id="editMonth" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('file.Method') }} *</label>
                        <select name="paying_method" id="editMethod" class="form-control" required>
                            <option value="0">{{ __('file.Cash') }}</option>
                            <option value="1">{{ __('file.Cheque') }}</option>
                            <option value="2">{{ __('file.Credit Card') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('file.Note') }}</label>
                        <textarea name="note" id="editNote" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('file.Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('file.Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= VIEW MODAL ================= --}}
<div id="viewModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="dripicons-preview"></i> {{ __('file.Payroll Details') }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                {{-- ========== EMPLOYEE CARD (with user role) ========== --}}
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="view-avatar mr-3" id="viewAvatar">—</div>
                            <div>
                                <h5 class="mb-1 font-weight-bold" id="viewEmployee">—</h5>
                                <div id="viewRoles"><!-- role badges injected --></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><small class="text-muted">Email</small><br><span id="viewEmail">—</span></div>
                            <div class="col-md-4"><small class="text-muted">Phone</small><br><span id="viewPhone">—</span></div>
                            <div class="col-md-4"><small class="text-muted">Department</small><br><span id="viewDepartment">—</span></div>
                        </div>
                    </div>
                </div>

                {{-- ========== SUMMARY CHIPS ========== --}}
                <div class="row mb-3">
                    <div class="col-md-3"><b>{{ __('file.reference') }}:</b> <span id="viewRef">—</span></div>
                    <div class="col-md-3"><b>{{ __('file.Month') }}:</b> <span id="viewPeriod">—</span></div>
                    <div class="col-md-3"><b>{{ __('file.Template') }}:</b> <span id="viewTemplate">—</span></div>
                    <div class="col-md-3"><b>Status:</b> <span id="viewStatus">—</span></div>
                </div>

                {{-- ========== PAYROLL BREAKDOWN ========== --}}
                <h6 class="text-primary"><i class="dripicons-calculator"></i> Payroll Breakdown</h6>
                <table class="table table-sm table-bordered mb-3">
                    <tbody id="viewBreakdown"></tbody>
                </table>

                {{-- ========== LINE ITEMS ========== --}}
                <h6 class="mt-3"><i class="dripicons-list"></i> Items</h6>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Name</th><th>Type</th><th class="text-right">Amount</th><th>Source</th></tr></thead>
                    <tbody id="viewItems"></tbody>
                </table>

                {{-- ========== PAYMENTS ========== --}}
                <h6 class="mt-3"><i class="dripicons-wallet"></i> Payments</h6>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Date</th><th class="text-right">Amount</th><th>Method</th></tr></thead>
                    <tbody id="viewPayments"></tbody>
                </table>

                <div class="mt-2" id="viewNoteWrap">
                    <b>Note:</b> <span id="viewNote">—</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .view-avatar {
        width: 52px; height: 52px; border-radius: 50%;
        background: #0d5a39; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 18px; flex: 0 0 auto;
    }
    #viewBreakdown .breakdown-net td {
        font-weight: 700; background: #f0faf4; font-size: 1.05rem;
    }
</style>
@endsection

@push('scripts')
<script>
 $(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const money = n => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const METHODS = ['Cash', 'Cheque', 'Credit Card'];

    /* Edit */
    $(document).on('click', '.edit-btn', function () {
        const d = this.dataset;
        $('#payrollEditForm').attr('action', '{{ url("payroll/update") }}/' + d.id);
        $('#editMonth').val(d.month);
        $('#editMethod').val(d.method);
        $('#editNote').val(d.note);
    });

    /* View — AJAX details: employee + role + breakdown */
    $(document).on('click', '.view-btn', function () {
        $.get('{{ url("payroll") }}/' + this.dataset.id + '/details', function (p) {

            /* ---------- Employee card ---------- */
            const emp = p.employee || {};
            const name = emp.name || p.employee_name || '—';
            $('#viewEmployee').text(name);
            $('#viewAvatar').text(
                name.split(' ').filter(Boolean).map(w => w[0]).slice(0, 2).join('').toUpperCase()
            );

            // Role(s) of the employee's linked user account
            const roles = emp.roles || [];
            $('#viewRoles').html(roles.length
                ? roles.map(r => `<span class="badge badge-primary mr-1">${r}</span>`).join('')
                    + `<small class="text-muted ml-1">(user account)</small>`
                : '<span class="badge badge-secondary">No user account</span>');

            $('#viewEmail').text(emp.email || '—');
            $('#viewPhone').text(emp.phone || '—');
            $('#viewDepartment').text(emp.department || '—');

            /* ---------- Summary ---------- */
            $('#viewRef').text(p.reference_no || '—');
            $('#viewPeriod').text(
                new Date(p.year, p.month - 1, 1).toLocaleString('en-US', { month: 'long', year: 'numeric' })
            );
            $('#viewTemplate').text(
                p.salary_template ? p.salary_template.name + ' (' + p.template_type + ')' : '—'
            );
            $('#viewStatus').html(
                `<span class="badge badge-${p.payment_status === 'paid' ? 'success' : (p.payment_status === 'partial' ? 'warning' : 'danger')}">
                    ${p.payment_status.charAt(0).toUpperCase() + p.payment_status.slice(1)}</span>`
            );

            /* ---------- Breakdown (adapts to monthly / hourly) ---------- */
            const due = Math.max(0, p.amount - p.paid_amount);
            const row = (label, value, cls = '') =>
                `<tr class="${cls}"><td>${label}</td><td class="text-right">${value}</td></tr>`;

            let rows = '';

            if (p.template_type === 'hourly') {
                rows += row('Hourly Rate', money(p.hourly_rate));
                rows += row('Hours / Day', p.hours_per_day);
                rows += row('Daily Rate (rate × hours)', money(p.daily_rate));
                rows += row('Days Worked', p.days_worked);
                rows += row('<b>Gross Salary</b>', '<b>' + money(p.gross_amount) + '</b>');
            } else {
                rows += row('Basic Salary', money(p.basic_salary));
                rows += row('Total Allowances', '+ ' + money(p.total_allowance), 'text-success');
                if (p.overtime_amount > 0) {
                    rows += row(
                        `Overtime (${p.overtime_hours} hrs × ${money(p.overtime_rate)}/hr)`,
                        '+ ' + money(p.overtime_amount), 'text-success'
                    );
                }
                rows += row('<b>Gross Salary</b>', '<b>' + money(p.gross_amount) + '</b>');
                rows += row('Total Deduction', '− ' + money(p.total_deduction), 'text-danger');
            }

            rows += `<tr class="breakdown-net"><td>Net Pay</td>
                        <td class="text-right text-success">${money(p.amount)}</td></tr>`;
            rows += row('Paid', money(p.paid_amount), 'text-success');
            rows += row('Due', money(due), 'text-danger');

            $('#viewBreakdown').html(rows);

            /* ---------- Items ---------- */
            let items = '';
            (p.items || []).forEach(i => {
                items += `<tr>
                    <td>${i.name}</td>
                    <td><span class="badge badge-${i.type === 'deduction' ? 'danger' : (i.type === 'overtime' ? 'warning' : 'success')}">${i.type}</span></td>
                    <td class="text-right">${money(i.amount)}</td>
                    <td>${i.source}</td></tr>`;
            });
            $('#viewItems').html(items || '<tr><td colspan="4" class="text-muted text-center">No items</td></tr>');

            /* ---------- Payments ---------- */
            let pays = '';
            (p.payments || []).forEach(pay => {
                pays += `<tr>
                    <td>${pay.payment_date || '—'}</td>
                    <td class="text-right">${money(pay.amount)}</td>
                    <td>${METHODS[pay.paying_method] || '—'}</td></tr>`;
            });
            $('#viewPayments').html(pays || '<tr><td colspan="3" class="text-muted text-center">No payments yet</td></tr>');

            /* ---------- Note ---------- */
            $('#viewNote').text(p.note || '—');
            $('#viewNoteWrap').toggle(!!p.note);
        });
    });
});
</script>
@endpush
