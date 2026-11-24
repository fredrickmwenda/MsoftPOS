@extends('backend.layout.main')

@section('content')

@if(session()->has('message'))
    <div class="alert alert-success alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session()->get('message') }}
    </div>
@endif
@if(session()->has('not_permitted'))
    <div class="alert alert-danger alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {!! session()->get('not_permitted') !!}
    </div>
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header d-flex align-items-center">
                        <h4>{{ trans('file.Create SMS') }}</h4>
                        <div class="ml-auto">
                            {{-- Adjust this route to your template management page --}}
                            <a href="{{ route('sms-gateways.index') }}" class="btn btn-default btn-sm">
                                <i class="dripicons-gear"></i> SMS Dashboard
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <p class="italic">
                            <small>
                                {{ trans('file.The field labels marked with * are required input fields') }}.
                                <strong>Add recipients by selecting individuals or groups below.</strong>
                            </small>
                        </p>

                        {!! Form::open(['route' => 'setting.sendSms', 'method' => 'post', 'id' => 'smsForm']) !!}

                            {{-- Hidden field — JS fills this with comma-separated phone numbers --}}
                            <input type="hidden" name="mobile" id="mobile" value="">

                            {{-- ======================================================== --}}
                            {{-- RECIPIENT SELECTION                                       --}}
                            {{-- ======================================================== --}}
                            <div class="card mb-0" style="border: 1px solid #e3e6f0;">
                                <div class="card-header py-2" style="background: #f8f9fc;">
                                    <h6 class="mb-0">Recipients</h6>
                                </div>
                                <div class="card-body p-3">

                                    {{-- Tab buttons --}}
                                    <ul class="nav nav-pills mb-3" id="recipientTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="individuals-tab"
                                               data-toggle="pill" href="#individuals" role="tab">
                                                <i class="dripicons-user"></i> Individuals
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="groups-tab"
                                               data-toggle="pill" href="#groups" role="tab">
                                                <i class="dripicons-user-group"></i> Groups
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">

                                        {{-- ============= INDIVIDUALS TAB ============= --}}
                                        <div class="tab-pane fade show active" id="individuals" role="tabpanel">

                                            {{-- Filter row --}}
                                            <div class="form-row align-items-end mb-3">
                                                <div class="col-md-3">
                                                    <label><small><strong>User Type</strong></small></label>
                                                    <select id="userTypeSelect" class="form-control form-control-sm">
                                                        <option value="customer">Customers</option>
                                                        <option value="staff">Staff (Employees)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label><small><strong>Search</strong></small></label>
                                                    <input type="text"
                                                           id="recipientSearch"
                                                           class="form-control form-control-sm"
                                                           placeholder="Search by name or phone number...">
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="button"
                                                            class="btn btn-primary btn-sm btn-block"
                                                            onclick="addSelectedIndividuals()">
                                                        <i class="dripicons-plus"></i> Add Selected
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Results table --}}
                                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                <table class="table table-sm table-hover mb-0" id="resultsTable">
                                                    <thead style="position: sticky; top: 0; background: #fff;">
                                                        <tr>
                                                            <th style="width: 30px;">
                                                                <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                                                            </th>
                                                            <th>Name</th>
                                                            <th>Phone</th>
                                                            <th>Type</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="resultsBody">
                                                        {{-- populated by JS --}}
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>

                                        {{-- ============= GROUPS TAB ============= --}}
                                        <div class="tab-pane fade" id="groups" role="tabpanel">

                                            <div class="form-row align-items-end mb-3">
                                                <div class="col-md-4">
                                                    <label><small><strong>Group Type</strong></small></label>
                                                    <select id="groupTypeSelect" class="form-control form-control-sm" onchange="loadGroups()">
                                                        <option value="customer_group">Customer Groups</option>
                                                        <option value="department">Staff Departments</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8 text-right">
                                                    <button type="button"
                                                            class="btn btn-primary btn-sm"
                                                            onclick="addSelectedGroups()">
                                                        <i class="dripicons-plus"></i> Add Selected Groups
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                <table class="table table-sm table-hover mb-0" id="groupsTable">
                                                    <thead style="position: sticky; top: 0; background: #fff;">
                                                        <tr>
                                                            <th style="width: 30px;"></th>
                                                            <th>Group Name</th>
                                                            <th>Members</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="groupsBody">
                                                        {{-- populated by JS --}}
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>

                                    </div>{{-- /.tab-content --}}

                                    {{-- Selected recipients display --}}
                                    <div class="mt-3 p-2" style="background: #f8f9fc; border-radius: 4px; min-height: 50px;">
                                        <small class="text-muted d-block mb-1">
                                            <strong>Selected Recipients (<span id="recipientCount">0</span>)</strong>
                                        </small>
                                        <div id="selectedRecipientsList" class="d-flex flex-wrap gap-1">
                                            <span class="text-muted small">No recipients selected. Add individuals or groups above.</span>
                                        </div>
                                    </div>

                                    {{-- Selected groups display --}}
                                    <div id="selectedGroupsContainer" class="mt-2 p-2 d-none"
                                         style="background: #fff3cd; border-radius: 4px;">
                                        <small class="text-muted d-block mb-1">
                                            <strong>Selected Groups</strong>
                                        </small>
                                        <div id="selectedGroupsList" class="d-flex flex-wrap gap-1"></div>
                                    </div>

                                </div>
                            </div>

                            {{-- ======================================================== --}}
                            {{-- MESSAGE DETAILS                                           --}}
                            {{-- ======================================================== --}}
                            <div class="card mt-3" style="border: 1px solid #e3e6f0;">
                                <div class="card-header py-2 d-flex align-items-center" style="background: #f8f9fc;">
                                    <h6 class="mb-0">Message Details</h6>
                                    <div class="ml-auto">
                                        <a href="{{ route('smstemplates.index') }}" class="btn btn-default btn-sm">
                                            <i class="dripicons-document"></i> Manage Templates
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-3">

                                    {{-- Template dropdown --}}
                                    <div class="form-group">
                                        <label><small><strong>Template</strong></small></label>
                                        <select id="templateSelect" class="form-control form-control-sm" onchange="loadTemplate(this.value)">
                                            <option value="">-- No template --</option>
                                            @foreach($lims_sms_template_list as $template)
                                                <option value="{{ $template->id }}"
                                                        data-content="{{ addslashes($template->content) }}">
                                                    {{ $template->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">
                                            Selecting a template fills the message box. Tags like {name}, {phone} will not be auto-replaced in bulk sends.
                                        </small>
                                    </div>

                                    {{-- Message textarea --}}
                                    <div class="form-group">
                                        <label><small><strong>{{ trans('file.Message') }} *</strong></small></label>
                                        <textarea name="message"
                                                  id="message"
                                                  class="form-control"
                                                  rows="4"
                                                  required
                                                  oninput="countChars()"
                                                  placeholder="Type your SMS message here or select a template above..."></textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">
                                                Characters: <span id="charCount">0</span>
                                            </small>
                                            <small class="text-muted">
                                                SMS parts: <span id="smsParts">0</span>
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <div class="form-group mb-0 d-flex align-items-center">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-paper-plane"></i> {{ trans('file.Send SMS') }}
                                        </button>
                                        <small class="text-muted ml-3">
                                            SMS will be sent to <strong><span id="finalRecipientCount">0</span></strong> recipient(s) via the active Arkesel gateway.
                                        </small>
                                    </div>

                                </div>
                            </div>

                        {!! Form::close() !!}

                    </div>{{-- /.card-body --}}
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">

    // ============================================================
    //  Sidebar menu
    // ============================================================
    $("ul#setting").siblings('a').attr('aria-expanded', 'true');
    $("ul#setting").addClass("show");
    $("ul#setting #create-sms-menu").addClass("active");

    // ============================================================
    //  Data from PHP — pre-loaded for client-side filtering
    // ============================================================
    @php
        $customersJson = $lims_customer_list->map(function($c) {
            return [
                'id'       => $c->id,
                'name'     => $c->name,
                'phone'    => $c->phone_number,
                'group_id' => $c->customer_group_id,
            ];
        })->toJson();

        $employeesJson = $lims_employee_list->map(function($e) {
            return [
                'id'            => $e->id,
                'name'          => $e->name,
                'phone'         => $e->phone_number,
                'department_id' => $e->department_id,
            ];
        })->toJson();

        $customerGroupsJson = $lims_customer_group_list->map(function($g) {
            return ['id' => $g->id, 'name' => $g->name];
        })->toJson();

        $departmentsJson = $lims_department_list->map(function($d) {
            return ['id' => $d->id, 'name' => $d->name];
        })->toJson();
    @endphp
    var customers      = {!! $customersJson !!};
    var employees      = {!! $employeesJson !!};
    var customerGroups = {!! $customerGroupsJson !!};
    var departments    = {!! $departmentsJson !!};

    // ============================================================
    //  State
    // ============================================================

    // phone -> { name, phone, source }   source = 'individual' | 'group:<type>:<id>'
    var selectedRecipients = {};
    // key -> { type, groupId, name, memberCount }
    var selectedGroups = {};

    // ============================================================
    //  INDIVIDUALS — render + search
    // ============================================================

    function getCurrentList() {
        var type = $('#userTypeSelect').val();
        if (type === 'staff') return employees;
        return customers;
    }

    function renderResults(query) {
        var list = getCurrentList();
        query = (query || '').toLowerCase().trim();

        var filtered = list.filter(function (item) {
            if (!item.phone) return false;
            var nameMatch  = (item.name || '').toLowerCase().indexOf(query) !== -1;
            var phoneMatch = (item.phone || '').toLowerCase().indexOf(query) !== -1;
            return nameMatch || phoneMatch;
        });

        // Limit to first 100 for performance
        filtered = filtered.slice(0, 100);

        var html = '';
        if (filtered.length === 0) {
            html = '<tr><td colspan="4" class="text-center text-muted py-3">No results found</td></tr>';
        } else {
            var type = $('#userTypeSelect').val();
            filtered.forEach(function (item) {
                var key = item.phone;
                var already = selectedRecipients.hasOwnProperty(key);
                html += '<tr>'
                    + '<td><input type="checkbox" class="result-checkbox" data-phone="' + escapeAttr(item.phone) + '" data-name="' + escapeAttr(item.name) + '" data-type="' + type + '"></td>'
                    + '<td>' + escapeHtml(item.name || '—') + '</td>'
                    + '<td>' + escapeHtml(item.phone || '—') + '</td>'
                    + '<td>' + (type === 'staff' ? 'Staff' : 'Customer') + '</td>'
                    + '</tr>';
            });
        }
        $('#resultsBody').html(html);
        $('#selectAllCheckbox').prop('checked', false);
    }

    function toggleSelectAll(checkbox) {
        $('.result-checkbox').prop('checked', checkbox.checked);
    }

    function addSelectedIndividuals() {
        var checked = $('.result-checkbox:checked');
        if (checked.length === 0) {
            alert('Please check at least one recipient to add.');
            return;
        }
        var added = 0;
        checked.each(function () {
            var phone = $(this).data('phone');
            var name  = $(this).data('name');
            var type  = $(this).data('type');
            if (phone && !selectedRecipients.hasOwnProperty(phone)) {
                selectedRecipients[phone] = {
                    name: name,
                    phone: phone,
                    source: 'individual',
                    type: type
                };
                added++;
            }
        });
        $('.result-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        renderSelectedRecipients();
        if (added > 0) {
            showToast(added + ' recipient(s) added.', 'success');
        }
    }

    // ============================================================
    //  GROUPS — render + add
    // ============================================================

    function loadGroups() {
        var type = $('#groupTypeSelect').val();
        var groups = (type === 'department') ? departments : customerGroups;

        var html = '';
        if (groups.length === 0) {
            html = '<tr><td colspan="3" class="text-center text-muted py-3">No groups available</td></tr>';
        } else {
            groups.forEach(function (g) {
                var count = countGroupMembers(type, g.id);
                html += '<tr>'
                    + '<td><input type="checkbox" class="group-checkbox" data-type="' + type + '" data-id="' + g.id + '" data-name="' + escapeAttr(g.name) + '"></td>'
                    + '<td>' + escapeHtml(g.name) + '</td>'
                    + '<td>' + count + ' member(s)</td>'
                    + '</tr>';
            });
        }
        $('#groupsBody').html(html);
    }

    function countGroupMembers(type, groupId) {
        if (type === 'department') {
            return employees.filter(function (e) {
                return e.department_id == groupId && e.phone;
            }).length;
        }
        return customers.filter(function (c) {
            return c.group_id == groupId && c.phone;
        }).length;
    }

    function addSelectedGroups() {
        var checked = $('.group-checkbox:checked');
        if (checked.length === 0) {
            alert('Please check at least one group to add.');
            return;
        }
        var added = 0;
        checked.each(function () {
            var type = $(this).data('type');
            var id   = $(this).data('id');
            var name = $(this).data('name');
            var key  = type + ':' + id;

            if (selectedGroups.hasOwnProperty(key)) return; // already added

            var members = getGroupMembers(type, id);
            selectedGroups[key] = {
                type: type,
                groupId: id,
                name: name,
                memberCount: members.length
            };

            // Add each member's phone
            members.forEach(function (m) {
                if (m.phone && !selectedRecipients.hasOwnProperty(m.phone)) {
                    selectedRecipients[m.phone] = {
                        name: m.name,
                        phone: m.phone,
                        source: 'group:' + key,
                        type: type === 'department' ? 'staff' : 'customer'
                    };
                }
            });
            added++;
        });
        $('.group-checkbox').prop('checked', false);
        renderSelectedRecipients();
        renderSelectedGroups();
        if (added > 0) {
            showToast(added + ' group(s) added.', 'success');
        }
    }

    function getGroupMembers(type, groupId) {
        if (type === 'department') {
            return employees.filter(function (e) {
                return e.department_id == groupId && e.phone;
            });
        }
        return customers.filter(function (c) {
            return c.group_id == groupId && c.phone;
        });
    }

    function removeGroup(key) {
        if (!selectedGroups.hasOwnProperty(key)) return;
        delete selectedGroups[key];

        // Remove all recipients that came from this group
        Object.keys(selectedRecipients).forEach(function (phone) {
            if (selectedRecipients[phone].source === 'group:' + key) {
                delete selectedRecipients[phone];
            }
        });
        renderSelectedRecipients();
        renderSelectedGroups();
    }

    // ============================================================
    //  Render selected recipients + groups
    // ============================================================

    function renderSelectedRecipients() {
        var phones = Object.keys(selectedRecipients);
        var count = phones.length;
        $('#recipientCount').text(count);
        $('#finalRecipientCount').text(count);

        if (count === 0) {
            $('#selectedRecipientsList').html(
                '<span class="text-muted small">No recipients selected. Add individuals or groups above.</span>'
            );
            return;
        }
        var html = '';
        phones.forEach(function (phone) {
            var r = selectedRecipients[phone];
            var badgeClass = r.type === 'staff' ? 'badge-info' : 'badge-primary';
            html += '<span class="badge ' + badgeClass + ' p-1 mr-1 mb-1" style="font-size: 0.8em;">'
                + escapeHtml(r.name) + ' (' + escapeHtml(phone) + ') '
                + '<a href="javascript:void(0)" onclick="removeRecipient(\'' + escapeAttr(phone) + '\')" '
                + 'style="color:#fff;text-decoration:none;margin-left:4px;">&times;</a>'
                + '</span>';
        });
        $('#selectedRecipientsList').html(html);
    }

    function renderSelectedGroups() {
        var keys = Object.keys(selectedGroups);
        if (keys.length === 0) {
            $('#selectedGroupsContainer').addClass('d-none');
            return;
        }
        $('#selectedGroupsContainer').removeClass('d-none');
        var html = '';
        keys.forEach(function (key) {
            var g = selectedGroups[key];
            html += '<span class="badge badge-warning p-1 mr-1 mb-1" style="font-size: 0.8em;">'
                + escapeHtml(g.name) + ' (' + g.memberCount + ' members) '
                + '<a href="javascript:void(0)" onclick="removeGroup(\'' + escapeAttr(key) + '\')" '
                + 'style="color:#000;text-decoration:none;margin-left:4px;">&times;</a>'
                + '</span>';
        });
        $('#selectedGroupsList').html(html);
    }

    function removeRecipient(phone) {
        // If this recipient came from a group, we can't remove individually
        if (selectedRecipients[phone] && selectedRecipients[phone].source !== 'individual') {
            alert('This recipient was added via a group. Remove the group to remove all its members.');
            return;
        }
        delete selectedRecipients[phone];
        renderSelectedRecipients();
    }

    // ============================================================
    //  Template + character counter
    // ============================================================

    function loadTemplate(templateId) {
        if (!templateId) return;
        var $opt = $('#templateSelect option[value="' + templateId + '"]');
        var content = $opt.data('content') || '';
        $('#message').val(content);
        countChars();
    }

    function countChars() {
        var len = $('#message').val().length;
        $('#charCount').text(len);
        // Standard SMS = 160 chars per part (GSM-7). Long messages split into parts of 153.
        var parts = len === 0 ? 0 : Math.ceil(len / 153);
        $('#smsParts').text(parts);
    }

    // ============================================================
    //  Form submission
    // ============================================================

    $('#smsForm').on('submit', function (e) {
        var phones = Object.keys(selectedRecipients);
        if (phones.length === 0) {
            e.preventDefault();
            alert('Please add at least one recipient before sending.');
            return;
        }
        // Sync phone numbers into the hidden field — sendSms() expects comma-separated
        $('#mobile').val(phones.join(','));
    });

    // ============================================================
    //  Event listeners
    // ============================================================

    $('#userTypeSelect').on('change', function () {
        $('#recipientSearch').val('');
        renderResults('');
    });

    $('#recipientSearch').on('input', function () {
        renderResults($(this).val());
    });

    // ============================================================
    //  Helpers
    // ============================================================

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function escapeAttr(s) {
        if (s == null) return '';
        return String(s).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    function showToast(message, type) {
        var alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        var html = '<div id="smsToast" class="alert ' + alertClass +
                   '" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:250px;">' +
                   message + '</div>';
        $('body').append(html);
        setTimeout(function () {
            $('#smsToast').fadeOut(400, function () { $(this).remove(); });
        }, 2000);
    }

    // ============================================================
    //  Init
    // ============================================================
    renderResults('');
    loadGroups();
    countChars();

</script>
@endpush
