@extends('backend.layout.main')

@section('content')

{{-- ============================================================ --}}
{{-- Flash messages                                              --}}
{{-- ============================================================ --}}
@if(session()->has('message'))
    <div class="alert alert-success alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {!! session()->get('message') !!}
    </div>
@endif
@if(session()->has('not_permitted'))
    <div class="alert alert-danger alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session()->get('not_permitted') }}
    </div>
@endif

{{-- ============================================================ --}}
{{-- Header                                                      --}}
{{-- ============================================================ --}}
<section>
    <div class="container-fluid d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">SMS Dashboard</h4>
        <div>
            <button type="button" class="btn btn-info btn-sm" onclick="openCreateModal()">
                <i class="dripicons-plus"></i> Add Gateway
            </button>
            <a href="{{ route('setting.createSms') }}" class="btn btn-primary btn-sm">
                <i class="dripicons-message"></i> Compose SMS
            </a>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Stats cards                                                 --}}
    {{-- ============================================================ --}}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h6 class="text-white">Total Gateways</h6>
                        <h2 class="mb-0">{{ $stats['total_gateways'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h6 class="text-white">Active Gateway</h6>
                        <h5 class="mb-0">{{ $stats['active_gateway'] }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h6 class="text-white">Messages Sent Today</h6>
                        <h2 class="mb-0">{{ $stats['sent_today'] }}</h2>
                        <small class="text-white">{{ $stats['total_messages'] }} all-time</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h6 class="text-white">Failed Today</h6>
                        <h2 class="mb-0">{{ $stats['failed_today'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Gateways table                                              --}}
    {{-- ============================================================ --}}
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="mb-0">Configured SMS Gateways</h5>
                <small class="ml-2 text-muted">
                    Only one gateway can be active at a time.
                </small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 30px">#</th>
                                <th>Name</th>
                                <th>Sender</th>
                                <th>API Key</th>
                                <th>Status</th>
                                <th>Messages Sent</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gateways as $key => $gateway)
                            <tr id="gateway-row-{{ $gateway->id }}">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $gateway->name }}</td>
                                <td>{{ $gateway->sender ?: '—' }}</td>
                                <td>
                                    @if($gateway->key)
                                        <code>{{ substr($gateway->key, 0, 4) }} •••• {{ substr($gateway->key, -4) }}</code>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($gateway->active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-default">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $gateway->messages()->count() }}</td>
                                <td class="text-right">
                                    <div class="btn-group btn-group-sm">
                                        @if(!$gateway->active)
                                            <button type="button"
                                                    class="btn btn-success btn-sm"
                                                    onclick="activateGateway({{ $gateway->id }})">
                                                <i class="dripicons-checkmark"></i> Activate
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn btn-success btn-sm"
                                                    disabled>
                                                <i class="dripicons-checkmark"></i> Active
                                            </button>
                                        @endif
                                        <button type="button"
                                                class="btn btn-default btn-sm"
                                                onclick="openShowModal({{ $gateway->id }})"
                                                title="View">
                                            <i class="dripicons-preview"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-warning btn-sm"
                                                onclick="openEditModal({{ $gateway->id }})"
                                                title="Edit">
                                            <i class="dripicons-document-edit"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-danger btn-sm"
                                                onclick="deleteGateway({{ $gateway->id }}, {{ json_encode($gateway->name) }})"
                                                title="Delete">
                                            <i class="dripicons-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No SMS gateways yet. Click <strong>Add Gateway</strong> to create one.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Recent sent messages                                        --}}
    {{-- ============================================================ --}}
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="mb-0">Recent Sent Messages</h5>
                <small class="ml-2 text-muted">Last 100</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="sms-message-table" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Recipient(s)</th>
                                <th>Message</th>
                                <th>Gateway</th>
                                <th>Sent By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $msg)
                            <tr>
                                <td>{{ $msg->created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span title="{{ $msg->recipients }}">
                                        {{ \Illuminate\Support\Str::limit($msg->recipients, 40) }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($msg->message, 60) }}</td>
                                <td>
                                    @if($msg->gateway)
                                        {{ $msg->gateway->name }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $msg->sender?->name ?? 'System' }}</td>
                                <td>
                                    @if($msg->status === 'sent')
                                        <span class="badge badge-success">Sent</span>
                                    @elseif($msg->status === 'failed')
                                        <span class="badge badge-danger">Failed</span>
                                    @else
                                        <span class="badge badge-warning">{{ ucfirst($msg->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No messages have been sent yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ --}}
{{-- CREATE / EDIT MODAL  (shared form)                          --}}
{{-- Form fields match your SmsGateway fillable:                 --}}
{{--   name, key, sender, active                                 --}}
{{-- ============================================================ --}}
<div class="modal fade" id="gatewayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="gatewayForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="gatewayModalLabel">Add SMS Gateway</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>The field labels marked with * are required input fields.</small>
                    </p>

                    <div class="form-group">
                        <label><strong>Gateway Name *</strong></label>
                        <input type="text"
                               name="name"
                               id="f_name"
                               required
                               class="form-control"
                               placeholder="e.g. Arkesel Production, Arkesel Backup">
                    </div>

                    <div class="form-group">
                        <label><strong>API Key *</strong></label>
                        <input type="text"
                               name="key"
                               id="f_key"
                               required
                               class="form-control"
                               placeholder="Arkesel API key">
                    </div>

                    <div class="form-group">
                        <label><strong>Sender ID</strong></label>
                        <input type="text"
                               name="sender"
                               id="f_sender"
                               class="form-control"
                               placeholder="Sender ID (e.g. MyBrand)">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="active"
                                   value="1"
                                   id="f_active">
                            <label class="form-check-label" for="f_active">
                                <strong>Set as active gateway</strong>
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            If checked, every other gateway will be deactivated.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveGatewayBtn">Save Gateway</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- VIEW MODAL                                                  --}}
{{-- ============================================================ --}}
<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gateway Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody id="showDetails"></tbody>
                </table>

                <h6 class="mt-3 mb-2">Recent Messages (last 20)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Recipients</th>
                                <th>Message</th>
                                <th>Sent By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="showMessages"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">

    // ============================================================
    //  Globals
    // ============================================================
    var BASE_URL  = '{{ route("sms-gateways.index") }}';
    var STORE_URL = '{{ route("sms-gateways.store") }}';
    var editingId = null;

    // ============================================================
    //  Clear all inline validation errors in the form
    // ============================================================
    function clearFormErrors() {
        $('#gatewayForm').find('.text-danger').remove();
    }

    // ============================================================
    //  Open CREATE modal
    // ============================================================
    function openCreateModal() {
        editingId = null;
        $('#gatewayModalLabel').text('Add SMS Gateway');
        $('#saveGatewayBtn').text('Save Gateway');
        $('#gatewayForm')[0].reset();
        clearFormErrors();
        $('#gatewayModal').modal('show');
    }

    // ============================================================
    //  Open EDIT modal  (AJAX fetch, then populate)
    // ============================================================
    function openEditModal(id) {
        editingId = id;
        clearFormErrors();

        $.getJSON(BASE_URL + '/' + id + '/edit', function (data) {
            $('#gatewayModalLabel').text('Update SMS Gateway');
            $('#saveGatewayBtn').text('Update Gateway');
            $('#f_name').val(data.name);
            $('#f_key').val(data.key || '');
            $('#f_sender').val(data.sender || '');
            $('#f_active').prop('checked', data.active == true || data.active == 1);
            $('#gatewayModal').modal('show');
        }).fail(function () {
            alert('Failed to load gateway data.');
        });
    }

    // ============================================================
    //  Open VIEW modal  (AJAX fetch, then populate)
    // ============================================================
    function openShowModal(id) {
        $.getJSON(BASE_URL + '/' + id, function (data) {
            var g = data.gateway;
            var maskedKey = g.key
                ? g.key.substring(0, 4) + ' •••• •••• ' + g.key.substring(g.key.length - 4)
                : '—';

            var html = '';
            html += row('Name',        escapeHtml(g.name));
            html += row('API Key',     '<code>' + maskedKey + '</code>');
            html += row('Sender ID',   g.sender ? escapeHtml(g.sender) : '—');
            html += row('Status',      g.active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-default">Inactive</span>');
            html += row('Created At',  g.created_at || '—');
            html += row('Updated At',  g.updated_at || '—');
            html += row('Messages Sent', (data.messages ? data.messages.length : 0) + ' (last 20)');
            $('#showDetails').html(html);

            var msgHtml = '';
            if (!data.messages || data.messages.length === 0) {
                msgHtml = '<tr><td colspan="5" class="text-center text-muted py-3">' +
                          'No messages sent through this gateway yet.</td></tr>';
            } else {
                data.messages.forEach(function (m) {
                    var badge = m.status === 'sent'
                        ? '<span class="badge badge-success">Sent</span>'
                        : m.status === 'failed'
                        ? '<span class="badge badge-danger">Failed</span>'
                        : '<span class="badge badge-warning">' + escapeHtml(m.status) + '</span>';
                    msgHtml += '<tr>'
                        + '<td>' + (m.created_at || '—') + '</td>'
                        + '<td>' + escapeHtml(m.recipients || '') + '</td>'
                        + '<td>' + escapeHtml(m.message || '') + '</td>'
                        + '<td>' + escapeHtml(m.sender || 'System') + '</td>'
                        + '<td>' + badge + '</td>'
                        + '</tr>';
                });
            }
            $('#showMessages').html(msgHtml);

            $('#showModal').modal('show');
        }).fail(function () {
            alert('Failed to load gateway.');
        });
    }

    // helper: build a <tr> for the show modal
    function row(label, value) {
        return '<tr><th style="width:35%">' + escapeHtml(label) +
               '</th><td>' + value + '</td></tr>';
    }

    // ============================================================
    //  Submit CREATE / EDIT form  (AJAX)
    // ============================================================
    $('#gatewayForm').on('submit', function (e) {
        e.preventDefault();
        clearFormErrors();

        var $btn = $('#saveGatewayBtn');
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('Saving...');

        var url    = editingId ? BASE_URL + '/' + editingId : STORE_URL;
        var data   = $(this).serialize();

        // Laravel expects _method=PUT for the PUT/PATCH via form serialization
        if (editingId) {
            data += '&_method=PUT';
        }

        $.ajax({
            url:  url,
            type: 'POST',                 // always POST; _method field does the trick
            data: data,
            dataType: 'json'
        })
        .done(function (res) {
            $('#gatewayModal').modal('hide');
            showToast(res.message || 'Saved successfully.', 'success');
            setTimeout(function () { location.reload(); }, 800);
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors || {};
                $.each(errors, function (field, messages) {
                    var $input = $('#gatewayForm [name="' + field + '"]');
                    $input.closest('.form-group')
                          .append('<small class="text-danger d-block">' +
                                  escapeHtml(messages[0]) + '</small>');
                });
            } else {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                          ? xhr.responseJSON.message
                          : 'An error occurred.';
                showToast(msg, 'error');
            }
        })
        .always(function () {
            $btn.prop('disabled', false).html(originalText);
        });
    });

    // ============================================================
    //  Activate gateway  (AJAX POST)
    // ============================================================
    function activateGateway(id) {
        if (!confirm('Activate this gateway? The currently active one will be deactivated.')) {
            return;
        }
        $.ajax({
            url:  BASE_URL + '/' + id + '/activate',
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json'
        })
        .done(function (res) {
            showToast(res.message || 'Activated.', 'success');
            setTimeout(function () { location.reload(); }, 800);
        })
        .fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message)
                      ? xhr.responseJSON.message
                      : 'Failed to activate gateway.';
            showToast(msg, 'error');
        });
    }

    // ============================================================
    //  Delete gateway  (AJAX DELETE)
    // ============================================================
    function deleteGateway(id, name) {
        if (!confirm('Delete gateway "' + name + '"? SMS history will be retained.')) {
            return;
        }
        $.ajax({
            url:  BASE_URL + '/' + id,
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'DELETE' },
            dataType: 'json'
        })
        .done(function (res) {
            showToast(res.message || 'Gateway deleted.', 'success');
            setTimeout(function () { location.reload(); }, 800);
        })
        .fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message)
                      ? xhr.responseJSON.message
                      : 'Failed to delete gateway.';
            showToast(msg, 'error');
        });
    }

    // ============================================================
    //  Helpers
    // ============================================================
    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
        });
    }

    function showToast(message, type) {
        var alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        var html = '<div id="smsToast" class="alert ' + alertClass +
                   '" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;">' +
                   message + '</div>';
        $('body').append(html);
        setTimeout(function () {
            $('#smsToast').fadeOut(400, function () { $(this).remove(); });
        }, 2500);
    }

    // ============================================================
    //  DataTable init
    // ============================================================
    $('#sms-message-table').DataTable({
        "order": [[0, 'desc']],
        "pageLength": 25,
        'language': {
            'lengthMenu': '_MENU_ per page',
            'search':     'Search:',
            'info':       '<small>Showing _START_ - _END_ (_TOTAL_)</small>',
            'paginate': {
                'previous': '<i class="dripicons-chevron-left"></i>',
                'next':     '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            { "orderable": false, 'targets': [1, 2, 3, 4, 5] }
        ]
    });

    // Expand settings sidebar (uncomment + adjust to your layout)
    // $("ul#setting").siblings('a').attr('aria-expanded', 'true');
    // $("ul#setting").addClass("show");
    // $("ul#setting #sms-setting-menu").addClass("active");
</script>
@endpush
