@extends('backend.layout.main')

@section('content')
<section>
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="d-flex align-items-center mt-3 mb-4">
            <div class="dripicons-card d-flex align-items-center justify-content-center bg-primary text-white rounded mr-3" style="width:44px;height:44px;font-size:20px;">
                <i class="dripicons-calendar"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold">{{ __('file.Salary Template') }}</h4>
                <small class="text-muted">{{ __('file.Create new salary template') }}</small>
            </div>
            <div class="ml-auto">
                <a href="{{ route('salary-template.hourly') }}" class="btn btn-outline-primary">
                    <i class="dripicons-clock"></i> {{ __('file.Hourly Template') }}
                </a>
            </div>
        </div>

        {{-- ================= ADD / EDIT TEMPLATE ================= --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-primary"><i class="dripicons-plus"></i>
                    <span id="formTitle">{{ __('file.Add Salary Template') }}</span>
                </h5>
            </div>
            <div class="card-body">
                <form id="salaryTemplateForm">
                    @csrf
                    <input type="hidden" name="template_id" id="template_id">

                    {{-- Basic Information --}}
                    <h6 class="text-primary mb-3"><i class="dripicons-information"></i> {{ __('file.Basic Information') }}</h6>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label fw-semibold">{{ __('file.Salary Grades') }} <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <input type="text" name="name" id="tpl_name" class="form-control"
                                placeholder="e.g. Biller, Sales Agent, Sales Manager, Executive Level" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label fw-semibold">{{ __('file.Basic Salary') }} <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <input type="number" step="any" min="0" name="basic_salary" id="basic_salary"
                                class="form-control" placeholder="{{ __('file.Basic Salary') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label fw-semibold">{{ __('file.Overtime Rate (Per Hour)') }} <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <input type="number" step="any" min="0" name="overtime_rate" id="overtime_rate"
                                class="form-control" placeholder="{{ __('file.Overtime rate per hour') }}" required>
                        </div>
                    </div>

                    {{-- Allowances --}}
                    <h6 class="text-success mb-2 mt-4"><i class="dripicons-plus"></i> {{ __('file.Allowances') }}
                        <small class="text-muted">(fixed value — no percentage)</small></h6>
                    <div id="allowanceContainer"></div>
                    <button type="button" class="btn btn-success btn-sm" id="addAllowanceBtn">
                        <i class="dripicons-plus"></i>
                    </button>

                    {{-- Deduction --}}
                    <h6 class="text-danger mb-2 mt-4"><i class="dripicons-minus"></i> {{ __('file.Deduction') }}
                        <small class="text-muted">(fixed value — no percentage)</small></h6>
                    <div id="deductionContainer"></div>
                    <button type="button" class="btn btn-danger btn-sm" id="addDeductionBtn">
                        <i class="dripicons-plus"></i>
                    </button>

                    {{-- Total Salary Details --}}
                    <h6 class="text-primary mb-3 mt-4"><i class="dripicons-calculator"></i> {{ __('file.Total Salary Details') }}</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th class="w-50">{{ __('file.Gross Salary') }}</th>
                                    <td><input readonly id="gross_salary" class="form-control text-right" value="0.00"></td>
                                </tr>
                                <tr>
                                    <th>{{ __('file.Total Deduction') }}</th>
                                    <td><input readonly id="total_deduction" class="form-control text-right" value="0.00"></td>
                                </tr>
                                <tr>
                                    <th class="fw-bold">{{ __('file.Net Salary') }}</th>
                                    <td><input readonly id="net_salary" class="form-control text-right font-weight-bold text-success" value="0.00"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4" id="templateSubmitBtn">
                            <i class="dripicons-checkmark"></i> {{ __('file.Save Template') }}
                        </button>
                        <button type="button" class="btn btn-secondary d-none" id="templateCancelEditBtn">
                            {{ __('file.Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= TEMPLATE LIST ================= --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h5 class="mb-0"><i class="dripicons-list"></i> {{ __('file.Salary Templates') }}</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover" id="templateTable">
                    <thead>
                        <tr>
                            <th>{{ __('file.Salary Grades') }}</th>
                            <th>{{ __('file.Basic Salary') }}</th>
                            <th>{{ __('file.Allowances') }}</th>
                            <th>{{ __('file.Deduction') }}</th>
                            <th>OT Rate/hr</th>
                            <th>{{ __('file.Net Salary') }}</th>
                            <th class="not-exported">{{ __('file.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td><strong>{{ $template->name }}</strong>
                                    @if($template->is_default)<span class="badge badge-primary">Default</span>@endif
                                </td>
                                <td>{{ number_format((float)$template->basic_salary, 2) }}</td>
                                <td class="text-success">{{ number_format($template->total_allowance, 2) }}
                                    <small class="text-muted d-block">({{ $template->allowances->count() }} items)</small></td>
                                <td class="text-danger">{{ number_format($template->total_deduction, 2) }}
                                    <small class="text-muted d-block">({{ $template->deductions->count() }} items)</small></td>
                                <td>{{ number_format((float)$template->overtime_rate, 2) }}</td>
                                <td class="font-weight-bold">{{ number_format($template->net_salary, 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm edit-btn"
                                        data-id="{{ $template->id }}"
                                        data-name="{{ $template->name }}"
                                        data-basic="{{ $template->basic_salary }}"
                                        data-ot="{{ $template->overtime_rate }}"
                                        data-items='@json($template->items->map(fn($i) => ["name" => $i->name, "type" => $i->type, "amount" => $i->amount]))'>
                                        <i class="dripicons-document-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm assign-btn"
                                        data-id="{{ $template->id }}" data-name="{{ $template->name }}"
                                        data-toggle="modal" data-target="#assignModal">
                                        <i class="dripicons-user"></i>
                                    </button>
                                    {!! Form::open(['route' => ['salary-template.destroy', $template->id], 'method' => 'DELETE', 'class' => 'd-inline']) !!}
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this template? Employees assigned will be detached.')">
                                            <i class="dripicons-trash"></i>
                                        </button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No salary templates yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

{{-- ================= ASSIGN MODAL ================= --}}
<div id="assignModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Template to Employees</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Template *</label>
                        <select name="salary_template_id" id="assign_template_id" class="form-control" required>
                            @foreach(\App\Models\SalaryTemplate::orderBy('type')->get() as $tpl)
                                <option value="{{ $tpl->id }}">
                                    {{ $tpl->name }} ({{ ucfirst($tpl->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Employees * <small class="text-muted">(Ctrl/Cmd + click for multiple)</small></label>
                        <select name="employee_ids[]" id="assign_employees" class="form-control" multiple size="10" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

<script>
 $(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    /* crash-proof notifier */
    const notify = window.toastr
        ? (toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3000 }, toastr)
        : { success: m => alert('✔ ' + m), error: m => alert('✖ ' + m) };

    const BASE = '{{ url("salary-template") }}';
    let allowIdx = 0, deductIdx = 0, isEditing = false;

    /* ---------- dynamic item rows ---------- */
    function rowHTML(prefix, idx, namePh, amountPh) {
        return `
        <div class="form-row align-items-center ${prefix}-row mb-2">
            <div class="col-md-5"><input type="text" name="${prefix}[${idx}][name]" class="form-control" placeholder="${namePh}" required></div>
            <div class="col-md-4"><input type="number" step="any" min="0" name="${prefix}[${idx}][amount]" class="form-control amount-input" placeholder="${amountPh}" required></div>
            <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></div>
        </div>`;
    }
    $('#addAllowanceBtn').click(() => $('#allowanceContainer').append(rowHTML('allowances', allowIdx++, 'e.g. House Rent', 'Enter Allowances Value')));
    $('#addDeductionBtn').click(() => $('#deductionContainer').append(rowHTML('deductions', deductIdx++, 'e.g. Provident Fund', 'Enter Deduction Value')));
    $(document).on('click', '.remove-row', function () { $(this).closest('.form-row').remove(); calcTotals(); });

    /* ---------- live totals ---------- */
    function calcTotals() {
        const basic = parseFloat($('#basic_salary').val()) || 0;
        let allow = 0, deduct = 0;
        $('#allowanceContainer .amount-input').each(function () { allow += parseFloat($(this).val()) || 0; });
        $('#deductionContainer .amount-input').each(function () { deduct += parseFloat($(this).val()) || 0; });
        $('#gross_salary').val((basic + allow).toFixed(2));
        $('#total_deduction').val(deduct.toFixed(2));
        $('#net_salary').val((basic + allow - deduct).toFixed(2));
    }
    $(document).on('input', '.amount-input, #basic_salary', calcTotals);

    /* ---------- SUBMIT → row appears instantly, NO reload ---------- */
    $('#salaryTemplateForm').on('submit', function (e) {
        e.preventDefault();
        $('#templateSubmitBtn').prop('disabled', true);

        $.post('{{ url("salary-template/store") }}', $(this).serialize())
            .done(res => {
                notify.success(res.message);
                isEditing ? replaceRow(res.template) : prependRow(res.template);
                resetForm();
            })
            .fail(xhr => {
                const msg = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors)[0][0]
                    : (xhr.responseJSON?.message || 'Failed to save template');
                notify.error(msg);
            })
            .always(() => $('#templateSubmitBtn').prop('disabled', false));
    });

    /* ---------- live table helpers ---------- */
    const fmt = n => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function skeletonRow() {
        return `
        <tr>
            <td class="cell-name"></td>
            <td class="cell-basic text-right"></td>
            <td class="cell-allow text-success"></td>
            <td class="cell-deduct text-danger"></td>
            <td class="cell-ot text-right"></td>
            <td class="cell-net font-weight-bold"></td>
            <td class="text-nowrap">
                <button type="button" class="btn btn-info btn-sm edit-btn" title="Edit"><i class="dripicons-document-edit"></i></button>
                <button type="button" class="btn btn-primary btn-sm assign-btn" data-toggle="modal" data-target="#assignModal" title="Assign"><i class="dripicons-user"></i></button>
                <form class="d-inline delete-form" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this template? Employees assigned will be detached.')">
                        <i class="dripicons-trash"></i>
                    </button>
                </form>
            </td>
        </tr>`;
    }

    function fillRow($row, t) {
        $row.attr('id', 'tpl-row-' + t.id);
        $row.find('.cell-name').text(t.name);
        $row.find('.cell-basic').text(fmt(t.basic_salary));
        $row.find('.cell-allow').html(fmt(t.total_allowance) + ' <small class="text-muted d-block">(' + t.allowance_count + ' items)</small>');
        $row.find('.cell-deduct').html(fmt(t.total_deduction) + ' <small class="text-muted d-block">(' + t.deduction_count + ' items)</small>');
        $row.find('.cell-ot').text(fmt(t.overtime_rate));
        $row.find('.cell-net').text(fmt(t.net_salary));

        // attributes via jQuery → no escaping issues with quotes in names
        $row.find('.edit-btn').attr({ 'data-id': t.id, 'data-name': t.name,
                'data-basic': t.basic_salary, 'data-ot': t.overtime_rate })
                             .data('items', t.items);
        $row.find('.assign-btn').attr('data-id', t.id);
        $row.find('.delete-form').attr('action', BASE + '/' + t.id);
        return $row;
    }

    function flash($row) {
        $row.addClass('table-success');
        setTimeout(() => $row.removeClass('table-success'), 2500);
    }

    function prependRow(t) {
        $('#templateTable tbody tr.empty-row').remove();
        flash(fillRow($(skeletonRow()), t).prependTo('#templateTable tbody'));
    }

    function replaceRow(t) {
        const $old = $('#tpl-row-' + t.id);
        const $new = fillRow($(skeletonRow()), t).insertBefore($old);
        $old.remove();
        flash($new);
    }

    /* ---------- Edit (works for server-rendered rows AND new rows) ---------- */
    $(document).on('click', '.edit-btn', function () {
        const d = this.dataset;
        const items = $(this).data('items') || [];   // jQuery auto-parses the JSON attr too

        isEditing = true;
        $('#formTitle').text('Edit Salary Template');
        $('#template_id').val(d.id);
        $('#tpl_name').val(d.name);
        $('#basic_salary').val(d.basic);
        $('#overtime_rate').val(d.ot);

        allowIdx = 0; deductIdx = 0;
        $('#allowanceContainer, #deductionContainer').empty();

        items.forEach(item => {
            const prefix  = item.type === 'allowance' ? 'allowances' : 'deductions';
            const idx     = prefix === 'allowances' ? allowIdx++ : deductIdx++;
            const target  = prefix === 'allowances' ? '#allowanceContainer' : '#deductionContainer';
            $(target).append(rowHTML(prefix, idx, 'Name', 'Value'));
            $(`${target} .${prefix}-row`).last()
                .find('input[type="text"]').val(item.name).end()
                .find('input[type="number"]').val(item.amount);
        });

        $('#templateSubmitBtn').text('Update Template');
        $('#templateCancelEditBtn').removeClass('d-none');
        calcTotals();
        $('html,body').animate({ scrollTop: 0 }, 300);
    });

    function resetForm() {
        $('#salaryTemplateForm')[0].reset();
        $('#template_id').val('');
        $('#allowanceContainer, #deductionContainer').empty();
        allowIdx = 0; deductIdx = 0; isEditing = false;
        $('#formTitle').text('Add Salary Template');
        $('#templateSubmitBtn').text('Save Template');
        $('#templateCancelEditBtn').addClass('d-none');
        calcTotals();
    }
    $('#templateCancelEditBtn').click(resetForm);

    /* ---------- Assign ---------- */
    $(document).on('click', '.assign-btn', function () {
        $('#assign_template_id').val(this.dataset.id);
    });
    $('#assignForm').on('submit', function (e) {
        e.preventDefault();
        $.post('{{ url("salary-template/assign") }}', $(this).serialize())
            .done(res => { notify.success(res.message); $('#assignModal').modal('hide'); })
            .fail(() => notify.error('Failed to assign template'));
    });
});
</script>
@endpush
