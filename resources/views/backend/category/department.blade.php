@extends('backend.layout.main')

@section('content')

@if($errors->has('name'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('name') }}</div>
@endif
@if(session()->has('message'))
<div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif
@if(session()->has('not_permitted'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#department-modal"><i class="dripicons-plus"></i> Add Department</button>&nbsp;
        <button type="button" class="btn btn-danger" id="bulk-delete-btn"><i class="dripicons-trash"></i> Delete Selected</button>&nbsp;
        <a href="{{ url('category') }}" class="btn btn-secondary">Back to Categories</a>
    </div>

    <div class="table-responsive">
        <table id="department-table" class="table" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>Name</th>
                    <th class="not-exported">Status</th>
                    <th class="not-exported">{{ trans('file.action') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</section>

<!-- Create Department Modal -->
<div id="department-modal" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            {{ Form::open(['route' => 'category-department.store', 'files' => true]) }}
            <div class="modal-header">
                <h5 class="modal-title">Add Department</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ trans('file.name') }} *</label>
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group">
                    <label>{{ trans('file.Image') }}</label>
                    <input type="file" name="image" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            {{ Form::open(['route' => ['category-department.update', 0], 'method' => 'PUT', 'files' => true, 'id' => 'editDepartmentForm']) }}
            <input type="hidden" name="department_id">
            <div class="modal-header">
                <h5 class="modal-title">Update Department</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ trans('file.name') }} *</label>
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group">
                    <label>{{ trans('file.Image') }}</label>
                    <input type="file" name="image" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#product").siblings('a').attr('aria-expanded', 'true');
    $("ul#product").addClass("show");
    $("ul#product #category-menu").addClass("active");

    function confirmHardDelete() {
        return confirm("This department will be PERMANENTLY deleted and cannot be undone!\n\nCategories attached to it will be detached (kept).\n\nContinue?");
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ----- Bulk hard delete -----
    $(document).on('click', '#bulk-delete-btn', function () {
        var ids = [];
        $('#department-table tbody tr').each(function () {
            if ($(this).find('.dt-checkboxes').is(':checked')) {
                ids.push($(this).data('id'));
            }
        });

        if (!ids.length) { alert('Please select at least one department.'); return; }
        if (!confirm(ids.length + ' department(s) will be PERMANENTLY deleted. Continue?')) return;

        $.post("{{ url('category-department/deletebySelection') }}", {
            departmentIdArray: ids,
            _token: "{{ csrf_token() }}"
        }, function (res) {
            alert(res);
            location.reload();
        }).fail(function (xhr) {
            alert('Delete failed: ' + xhr.statusText);
        });
    });

    // ----- Edit dialog (fixed action URL) -----
    $(document).on('click', '.open-EditCategoryDialog', function () {
        var id = $(this).data('id');
        $.get("{{ url('category-department') }}/" + id + "/edit", function (data) {
            $('#editModal input[name="department_id"]').val(data.id);
            $('#editModal input[name="name"]').val(data.name);
            $('#editDepartmentForm').attr('action', "{{ url('category-department/update') }}/" + data.id);
            $('#editModal').modal('show');
        });
    });

    $('#department-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "{{ url('category-department/department-data') }}",
            type: "POST",
            data: function (d) { d._token = "{{ csrf_token() }}"; },
            dataType: "json"
        },
        "createdRow": function (row, data, dataIndex) {
            $(row).attr('data-id', data['id']);
        },
        "columns": [
            { "data": "key" },
            { "data": "name" },
            { "data": "is_active" },
            { "data": "options" }
        ],
        order: [[2, 'asc']],
        'language': {
            'lengthMenu': '_MENU_ {{ trans("file.records per page") }}',
            "info": '<small>{{ trans("file.Showing") }} _START_ - _END_ (_TOTAL_)</small>',
            "search": '{{ trans("file.Search") }}',
            'paginate': {
                'previous': '<i class="dripicons-chevron-left"></i>',
                'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            { "orderable": false, 'targets': [0, 1, 3] },
            {
                'render': function (data, type, row, meta) {
                    if (type === 'display') {
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }
                    return data;
                },
                'checkboxes': {
                    'selectRow': true,
                    'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': { style: 'multi', selector: 'td:first-child' },
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            }
        ]
    });
</script>
@endpush
