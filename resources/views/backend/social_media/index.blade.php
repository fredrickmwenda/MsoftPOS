@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">

        {{-- ============================ --}}
        {{-- PAGE HEADER                  --}}
        {{-- ============================ --}}
        <div class="d-flex align-items-center mt-3 mb-4">
            <div class="d-flex align-items-center justify-content-center text-white rounded mr-3"
                style="width:44px;height:44px;font-size:20px;background:#0d5a39;">
                <i class="dripicons-share"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold">Social Media</h4>
                <small class="text-muted">Active links show in the announcement bar &amp; footer</small>
            </div>
        </div>

        {{-- ============================ --}}
        {{-- ADD / EDIT FORM              --}}
        {{-- ============================ --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-primary">
                    <i class="dripicons-plus"></i> <span id="formTitle">Add Social Media</span>
                </h5>
            </div>
            <div class="card-body">
                <form id="socialMediaForm">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Platform Name * <small class="text-muted">(e.g. Facebook)</small></label>
                            <input type="text" name="name" id="sm_name" class="form-control" required>
                        </div>
                        <div class="form-group col-md-5">
                            <label>URL * <small class="text-muted">(https://facebook.com/yourpage)</small></label>
                            <input type="text" name="url" id="sm_url" class="form-control" placeholder="https://..." required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" id="sm_sort" class="form-control" value="0" min="0">
                        </div>
                        <div class="form-group col-md-1 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="is_active" id="sm_active" value="1" checked>
                                <label class="form-check-label" for="sm_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <label class="fw-semibold">Icon * <small class="text-muted">(click to select)</small></label>
                    <div class="icon-picker d-flex flex-wrap" id="iconPicker">
                        @foreach($icons as $key => $meta)
                            <label class="icon-tile" data-key="{{ $key }}" title="{{ $meta['label'] }}"
                                style="--brand: {{ $meta['color'] }}">
                                <input type="radio" name="icon" value="{{ $key }}" class="d-none" required>
                                <span class="icon-preview">@include('frontend.partials.social-icon', ['icon' => $key])</span>
                                <small>{{ $meta['label'] }}</small>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary px-4" id="smSubmitBtn">
                            <i class="dripicons-checkmark"></i> <span id="smSubmitText">Add Link</span>
                        </button>
                        <button type="button" class="btn btn-secondary d-none" id="smCancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================ --}}
        {{-- SOCIAL LINKS TABLE           --}}
        {{-- ============================ --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="dripicons-list"></i> Social Links</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover" id="socialTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:70px;">Icon</th>
                            <th>Platform</th>
                            <th>URL</th>
                            <th style="width:80px;">Sort</th>
                            <th style="width:110px;">Status</th>
                            <th class="not-exported" style="width:130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($socialMedias as $s)
                            <tr id="sm-row-{{ $s->id }}">
                                <td>
                                    <span class="icon-preview" style="color: {{ $s->brand_color }}">
                                        @include('frontend.partials.social-icon', ['icon' => $s->icon])
                                    </span>
                                </td>
                                <td><strong>{{ $s->name }}</strong></td>
                                <td><a href="{{ $s->url }}" target="_blank" rel="noopener">{{ $s->url }}</a></td>
                                <td>{{ $s->sort_order }}</td>
                                <td class="text-center">
                                    <button type="button"
                                        class="btn btn-sm toggle-btn {{ $s->is_active ? 'btn-success' : 'btn-secondary' }}"
                                        data-id="{{ $s->id }}">
                                        {{ $s->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-info btn-sm edit-btn"
                                        data-id="{{ $s->id }}" data-name="{{ $s->name }}"
                                        data-url="{{ $s->url }}" data-icon="{{ $s->icon }}"
                                        data-sort="{{ $s->sort_order }}" data-active="{{ $s->is_active ? 1 : 0 }}">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true">
                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $s->id }}">
                                        <i class="dripicons-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="6" class="text-center text-muted py-4">No social links yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<style>
    .icon-tile {
        width: 86px; margin: 4px; padding: 8px 4px;
        border: 2px solid #e3e6ea; border-radius: 8px;
        text-align: center; cursor: pointer; transition: .2s; background: #fff;
    }
    .icon-tile small { display: block; margin-top: 4px; font-size: 11px; color: #555; }
    .icon-tile:hover { border-color: var(--brand); }
    .icon-tile.selected { border-color: var(--brand); background: var(--brand); }
    .icon-tile.selected small, .icon-tile.selected .icon-preview { color: #fff !important; }
    .icon-preview { display: inline-flex; }
    .icon-preview svg { width: 22px; height: 22px; }
    .flash-row { animation: rowFlash 2s ease-out; }
    @keyframes rowFlash {
        0%   { background-color: #d4edda; }
        100% { background-color: transparent; }
    }
</style>
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

    /* ==========================================================
       ICON MAP — every icon pre-rendered server-side into JS.
       No img tags, no extra route, no 404 fallback.
    ========================================================== */
    const ICON_MAP = @json(collect(\App\Models\SocialMedia::ICONS)
        ->mapWithKeys(fn ($meta, $key) => [$key => view('frontend.partials.social-icon', ['icon' => $key])->render()]));

    function iconSVG(key) {
        if (!key)                            return ICON_MAP['website'];
        if (String(key).startsWith('<svg'))  return key;      // custom raw SVG
        return ICON_MAP[key] || ICON_MAP['website'];
    }

    /* inline pencil — immune to missing dripicons glyphs */
    const PENCIL = '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true">'
                 + '<path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>'
                 + '</svg>';

    let isEditing = false;

    /* ========================= ICON PICKER ========================= */
    $('#iconPicker .icon-tile').on('click', function () {
        $('#iconPicker .icon-tile').removeClass('selected');
        $(this).addClass('selected').find('input[type=radio]').prop('checked', true);
    });

    /* ========================= SUBMIT (create / update) ========================= */
    $('#socialMediaForm').on('submit', function (e) {
        e.preventDefault();
        $('#smSubmitBtn').prop('disabled', true);

        const url = isEditing
            ? '{{ url("social-media/update") }}/' + $('#socialMediaForm').data('id')
            : '{{ url("social-media/store") }}';

        $.post(url, $(this).serialize())
            .done(res => {
                notify.success(res.message);
                isEditing ? replaceRow(res.social) : prependRow(res.social);
                resetForm();
            })
            .fail(xhr => {
                const msg = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors)[0][0]
                    : (xhr.responseJSON?.message || 'Failed to save');
                notify.error(msg);
            })
            .always(() => $('#smSubmitBtn').prop('disabled', false));
    });

    /* ========================= LIVE TABLE HELPERS ========================= */
    function skeletonRow() {
        return `<tr>
            <td class="cell-icon"><span class="icon-preview"></span></td>
            <td class="cell-name"><strong></strong></td>
            <td class="cell-url"></td>
            <td class="cell-sort"></td>
            <td class="text-center"><button type="button" class="btn btn-sm toggle-btn"></button></td>
            <td class="text-nowrap">
                <button type="button" class="btn btn-info btn-sm edit-btn">${PENCIL}</button>
                <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="dripicons-trash"></i></button>
            </td>
        </tr>`;
    }

    function fillRow($row, s) {
        $row.attr('id', 'sm-row-' + s.id);

        $row.find('.cell-icon .icon-preview').css('color', s.color).html(iconSVG(s.icon));
        $row.find('.cell-name').text(s.name);
        $row.find('.cell-url').html(`<a href="${s.url}" target="_blank" rel="noopener">${s.url}</a>`);
        $row.find('.cell-sort').text(s.sort_order);

        $row.find('.toggle-btn').attr('data-id', s.id)
            .removeClass('btn-success btn-secondary')
            .addClass(s.is_active ? 'btn-success' : 'btn-secondary')
            .text(s.is_active ? 'Active' : 'Inactive');

        $row.find('.edit-btn').attr({
            'data-id': s.id, 'data-name': s.name, 'data-url': s.url,
            'data-icon': s.icon, 'data-sort': s.sort_order, 'data-active': s.is_active ? 1 : 0
        });
        $row.find('.delete-btn').attr('data-id', s.id);

        return $row;
    }

    function flash($row) {
        $row.addClass('flash-row');
        setTimeout(() => $row.removeClass('flash-row'), 2000);
    }

    function prependRow(s) {
        $('#socialTable tbody tr.empty-row').remove();
        flash(fillRow($(skeletonRow()), s).prependTo('#socialTable tbody'));
    }

    function replaceRow(s) {
        const $old = $('#sm-row-' + s.id);
        const $new = fillRow($(skeletonRow()), s).insertBefore($old);
        $old.remove();
        flash($new);
    }

    /* ========================= TOGGLE ACTIVE ========================= */
    $(document).on('click', '.toggle-btn', function () {
        $.post('{{ url("social-media/toggle") }}/' + this.dataset.id)
            .done(res => {
                notify.success(res.message);
                replaceRow(res.social);
            })
            .fail(() => notify.error('Failed to toggle'));
    });

    /* ========================= EDIT ========================= */
    $(document).on('click', '.edit-btn', function () {
        const d = this.dataset;
        isEditing = true;

        $('#socialMediaForm').data('id', d.id);
        $('#formTitle').text('Edit Social Media');
        $('#sm_name').val(d.name);
        $('#sm_url').val(d.url);
        $('#sm_sort').val(d.sort);
        $('#sm_active').prop('checked', d.active === '1');

        $('#iconPicker .icon-tile').removeClass('selected');
        if (ICON_MAP[d.icon]) {   // only match a tile for preset keys
            $(`#iconPicker .icon-tile[data-key="${d.icon}"]`)
                .addClass('selected').find('input').prop('checked', true);
        }

        $('#smSubmitText').text('Update Link');
        $('#smCancelBtn').removeClass('d-none');
        $('html,body').animate({ scrollTop: 0 }, 300);
    });

    function resetForm() {
        $('#socialMediaForm')[0].reset();
        $('#socialMediaForm').removeData('id');
        $('#sm_active').prop('checked', true);
        $('#iconPicker .icon-tile').removeClass('selected');
        $('#formTitle').text('Add Social Media');
        $('#smSubmitText').text('Add Link');
        $('#smCancelBtn').addClass('d-none');
        isEditing = false;
    }
    $('#smCancelBtn').click(resetForm);

    /* ========================= DELETE ========================= */
    $(document).on('click', '.delete-btn', function () {
        if (!confirm('Delete this social media link?')) return;
        const id = this.dataset.id;
        $.ajax({
            url: '{{ url("social-media") }}/' + id,
            type: 'DELETE',
            success: res => { notify.success(res.message); $('#sm-row-' + id).remove(); },
            error: () => notify.error('Failed to delete')
        });
    });
});
</script>
@endpush
