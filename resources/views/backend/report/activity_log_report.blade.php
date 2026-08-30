@extends('backend.layout.main')
@section('content')
<div class="container-fluid mb-3">
    <a href="{{ route('report.dashboard') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports Dashboard</a>
</div>
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">Activity Log Report</h3>
            </div>
            {!! Form::open(['route' => 'report.activity-log', 'method' => 'get']) !!}
            <div class="row mb-3">
                <div class="col-md-3 mt-3 mb-3 ml-2">
                    <div class="form-group">
                        <label class="control-label"><strong>Start Date</strong> &nbsp;</label>
                        <input type="date" class="form-control" name="start_date" value="{{ $start_date ?? '' }}" />
                    </div>
                </div>    
                <div class="col-md-3 mt-3 mb-3">
                    <div class="form-group">
                        <label class="control-label"><strong>End Date</strong> &nbsp;</label>
                        <input type="date" class="form-control" name="end_date" value="{{ $end_date ?? '' }}" />
                    </div>
                </div>
                <div class="col-md-2 mt-3 mb-3">
                    <div class="form-group">
                        <label class="control-label"><strong>Log Name</strong></label>
                        <select name="log_name" class="form-control">
                            <option value="">All</option>
                            <option value="default" @if(($log_name ?? '') == 'default') selected @endif>Default</option>
                            <option value="warehouse" @if(($log_name ?? '') == 'warehouse') selected @endif>Warehouse</option>
                            <option value="product" @if(($log_name ?? '') == 'product') selected @endif>Product</option>
                            <option value="supplier" @if(($log_name ?? '') == 'supplier') selected @endif>Supplier</option>
                            <option value="customer" @if(($log_name ?? '') == 'customer') selected @endif>Customer</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mt-3 mb-3">
                    <div class="form-group">
                        <label class="control-label"><strong>Causer (User)</strong></label>
                        <select name="user_id" class="selectpicker form-control" data-live-search="true">
                            <option value="">All Users</option>
                            @foreach($lims_user_list as $user)
                                <option value="{{ $user->id }}" @if(($user_id ?? '') == $user->id) selected @endif>{{ $user->name }} ({{ $user->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary mt-4" type="submit">Filter</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="table-responsive mb-4">
        <table id="activity-log-table" class="table table-hover" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>Date</th>
                    <th>Log Name</th>
                    <th>Description</th>
                    <th>Subject</th>
                    <th>Causer</th>
                    <th>Properties</th>
                </tr>
            </thead>
        </table>
    </div>
</section>

<!-- Modal for viewing properties -->
<div class="modal fade" id="propertiesModal" tabindex="-1" role="dialog" aria-labelledby="propertiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Properties</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Old Attributes</h6>
                        <pre id="old-attributes" style="background:#f4f4f4; padding:10px; max-height:400px; overflow:auto; border-radius:5px;"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6>New Attributes</h6>
                        <pre id="new-attributes" style="background:#f4f4f4; padding:10px; max-height:400px; overflow:auto; border-radius:5px;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #activity-log-report-menu").addClass("active");

    var start_date = <?php echo json_encode($start_date ?? ''); ?>;
    var end_date = <?php echo json_encode($end_date ?? ''); ?>;
    var log_name = <?php echo json_encode($log_name ?? ''); ?>;
    var user_id = <?php echo json_encode($user_id ?? ''); ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.selectpicker').selectpicker('refresh');

    $('#activity-log-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "{{ route('report.activity-log-data') }}",
            data: {
                start_date: start_date,
                end_date: end_date,
                log_name: log_name,
                user_id: user_id
            },
            dataType: "json",
            type: "get"
        },
        "columns": [
            {"data": "key"},
            {"data": "date"},
            {"data": "log_name"},
            {"data": "description"},
            {"data": "subject"},
            {"data": "causer"},
            {"data": "properties"}
        ],
        'language': {
            'lengthMenu': '_MENU_ records per page',
             "info":      '<small>Showing _START_ - _END_ (_TOTAL_)</small>',
            "search":  'Search',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        order: [['1', 'desc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 4, 5, 6]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
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
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        rowId: 'ObjectID',
        buttons: [
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer: true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer: true
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ]
    });

    // Handle viewing properties
    $(document).on('click', '.view-properties', function() {
        var logId = $(this).data('id');
        $.get('{{ route("report.activity-log-details", ":id") }}'.replace(':id', logId), function(res) {
            $('#old-attributes').text(JSON.stringify(res.old, null, 2));
            $('#new-attributes').text(JSON.stringify(res.attributes, null, 2));
            $('#propertiesModal').modal('show');
        });
    });
</script>
@endpush