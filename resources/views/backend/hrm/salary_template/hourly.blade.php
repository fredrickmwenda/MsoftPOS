@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">

        <div class="d-flex align-items-center mt-3 mb-4">
            <div class="d-flex align-items-center justify-content-center bg-info text-white rounded mr-3" style="width:44px;height:44px;">
                <i class="dripicons-clock"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold">{{ __('file.Hourly Template') }}</h4>
                <small class="text-muted">Hourly workers — paid on a daily basis (rate × hours/day)</small>
            </div>
            <div class="ml-auto">
                <a href="{{ route('salary-template.index') }}" class="btn btn-outline-primary">
                    <i class="dripicons-calendar"></i> {{ __('file.Salary Template') }}
                </a>
            </div>
        </div>

        {{-- ================= ADD / EDIT HOURLY TEMPLATE ================= --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-primary"><i class="dripicons-plus"></i>
                    <span id="hourlyFormTitle">{{ __('file.Add Hourly Template') }}</span></h5>
            </div>
            <div class="card-body">
                <form id="hourlyTemplateForm">
                    @csrf
                    <input type="hidden" name="template_id" id="hourly_template_id">

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label fw-semibold">{{ __('file.Hourly Name') }} <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <input type="text" name="name" id="hourly_name" class="form-control"
                                placeholder="e.g. General Labour, Technician" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label fw-semibold">{{ __('file.Hourly Rate') }} <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <input type="number" step="any" min="0" name="hourly_rate" id="hourly_rate"
                                class="form-control" placeholder="Rate per hour" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label fw-semibold">{{ __('file.Hours Worked Per Day') }} <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <input type="number" step="any" min="1" max="24" name="hours_per_day" id="hours_per_day"
                                class="form-control" placeholder="e.g. 8" value="8" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th class="w-50">{{ __('file.Daily Rate') }} (Rate × Hours)</th>
                                    <td><input readonly id="daily_rate" class="form-control text-right font-weight-bold text-success" value="0.00"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4" id="hourlySubmitBtn">
                            <i class="dripicons-checkmark"></i> {{ __('file.Save Template') }}
                        </button>
                        <button type="button" class="btn btn-secondary d-none" id="hourlyCancelEditBtn">{{ __('file.Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= HOURLY LIST ================= --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h5 class="mb-0"><i class="dripicons-list"></i> {{ __('file.Hourly Templates') }}</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('file.Hourly Name') }}</th>
                            <th>{{ __('file.Hourly Rate') }}</th>
                            <th>{{ __('file.Hours / Day') }}</th>
                            <th>{{ __('file.Daily Rate') }}</th>
                            <th class="not-exported">{{ __('file.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td><strong>{{ $template->name }}</strong></td>
                                <td>{{ number_format((float)$template->hourly_rate, 2) }}</td>
                                <td>{{ number_format((float)$template->hours_per_day, 2) }}</td>
                                <td class="font-weight-bold text-success">{{ number_format($template->daily_rate, 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm hourly-edit-btn"
                                        data-id="{{ $template->id }}" data-name="{{ $template->name }}"
                                        data-rate="{{ $template->hourly_rate }}" data-hours="{{ $template->hours_per_day }}">
                                        <i class="dripicons-document-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm assign-btn"
                                        data-id="{{ $template->id }}" data-toggle="modal" data-target="#assignModalHourly">
                                        <i class="dripicons-user"></i>
                                    </button>
                                    {!! Form::open(['route' => ['salary-template.destroy', $template->id], 'method' => 'DELETE', 'class' => 'd-inline']) !!}
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this template?')">
                                            <i class="dripicons-trash"></i>
                                        </button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No hourly templates yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

{{-- ================= ASSIGN MODAL ================= --}}
<div id="assignModalHourly" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignFormHourly">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Assign Hourly Template</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Template *</label>
                        <select name="salary_template_id" id="assign_hourly_template_id" class="form-control" required>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Employees *</label>
                        <select name="employee_ids[]" class="form-control" multiple size="10" required>
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

    const notify = window.toastr
        ? (toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3000 }, toastr)
        : { success: m => alert('✔ ' + m), error: m => alert('✖ ' + m) };

    const BASE = '{{ url("salary-template") }}';
    let isEditing = false;

    /* ---------- daily rate preview ---------- */
    function calcDaily() {
        $('#daily_rate').val(((parseFloat($('#hourly_rate').val()) || 0) *
                              (parseFloat($('#hours_per_day').val()) || 0)).toFixed(2));
    }
    $('#hourly_rate, #hours_per_day').on('input', calcDaily);
    calcDaily();

    /* ---------- SUBMIT → row appears instantly, NO reload ---------- */
    $('#hourlyTemplateForm').on('submit', function (e) {
        e.preventDefault();
        $('#hourlySubmitBtn').prop('disabled', true);

        $.post('{{ url("salary-template/hourly/store") }}', $(this).serialize())
            .done(res => {
                notify.success(res.message);
                isEditing ? replaceRow(res.template) : prependRow(res.template);
                resetForm();
            })
            .fail(xhr => {
                const msg = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors)[0][0]
                    : (xhr.responseJSON?.message || 'Failed to save hourly template');
                notify.error(msg);
            })
            .always(() => $('#hourlySubmitBtn').prop('disabled', false));
    });

    /* ---------- live table helpers ---------- */
    const fmt = n => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function skeletonRow() {
        return `
        <tr>
            <td class="cell-name"></td>
            <td class="cell-rate text-right"></td>
            <td class="cell-hours text-right"></td>
            <td class="cell-daily text-right font-weight-bold text-success"></td>
            <td class="text-nowrap">
                <button type="button" class="btn btn-info btn-sm hourly-edit-btn" title="Edit"><i class="dripicons-document-edit"></i></button>
                <button type="button" class="btn btn-primary btn-sm assign-btn" data-toggle="modal" data-target="#assignModalHourly" title="Assign"><i class="dripicons-user"></i></button>
                <form class="d-inline delete-form" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this template?')">
                        <i class="dripicons-trash"></i>
                    </button>
                </form>
            </td>
        </tr>`;
    }

    function fillRow($row, t) {
        $row.attr('id', 'tpl-row-' + t.id);
        $row.find('.cell-name').text(t.name);
        $row.find('.cell-rate').text(fmt(t.hourly_rate));
        $row.find('.cell-hours').text(fmt(t.hours_per_day));
        $row.find('.cell-daily').text(fmt(t.daily_rate));

        $row.find('.hourly-edit-btn').attr({ 'data-id': t.id, 'data-name': t.name,
            'data-rate': t.hourly_rate, 'data-hours': t.hours_per_day });
        $row.find('.assign-btn').attr('data-id', t.id);
        $row.find('.delete-form').attr('action', BASE + '/' + t.id);
        return $row;
    }

    function flash($row) {
        $row.addClass('table-success');
        setTimeout(() => $row.removeClass('table-success'), 2500);
    }

    function prependRow(t) {
        $('table tbody tr.empty-row').remove();
        flash(fillRow($(skeletonRow()), t).prependTo('table tbody'));
    }

    function replaceRow(t) {
        const $old = $('#tpl-row-' + t.id);
        const $new = fillRow($(skeletonRow()), t).insertBefore($old);
        $old.remove();
        flash($new);
    }

    /* ---------- Edit ---------- */
    $(document).on('click', '.hourly-edit-btn', function () {
        isEditing = true;
        $('#hourlyFormTitle').text('Edit Hourly Template');
        $('#hourly_template_id').val(this.dataset.id);
        $('#hourly_name').val(this.dataset.name);
        $('#hourly_rate').val(this.dataset.rate);
        $('#hours_per_day').val(this.dataset.hours);
        $('#hourlySubmitBtn').text('Update Template');
        $('#hourlyCancelEditBtn').removeClass('d-none');
        calcDaily();
        $('html,body').animate({ scrollTop: 0 }, 300);
    });

    function resetForm() {
        $('#hourlyTemplateForm')[0].reset();
        $('#hours_per_day').val(8);
        $('#hourly_template_id').val('');
        $('#hourlyFormTitle').text('Add Hourly Template');
        $('#hourlySubmitBtn').text('Save Template');
        $('#hourlyCancelEditBtn').addClass('d-none');
        isEditing = false;
        calcDaily();
    }
    $('#hourlyCancelEditBtn').click(resetForm);

    /* ---------- Assign ---------- */
    $(document).on('click', '.assign-btn', function () {
        $('#assign_hourly_template_id').val(this.dataset.id);
    });
    $('#assignFormHourly').on('submit', function (e) {
        e.preventDefault();
        $.post('{{ url("salary-template/assign") }}', $(this).serialize())
            .done(res => { notify.success(res.message); $('#assignModalHourly').modal('hide'); })
            .fail(() => notify.error('Failed to assign template'));
    });
});
</script>
@endpush
