@extends('backend.layout.main')


@section('content')


<section>
    <div class="container-fluid">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="dripicons-plus"></i> {{__('file.Add Designation')}}</button>
    </div>
    <div class="table-responsive">
        <table id="designation-table" class="table">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{__('file.Designation')}}</th>
                    <th class="not-exported">{{__('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lims_designation_all as $key=>$designation)
                <tr data-id="{{$designation->id}}">
                    <td>{{$key}}</td>
                    <td>{{ $designation->name }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{__('file.action')}}
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" data-id="{{$designation->id}}" data-name="{{$designation->name}}" class="edit-btn btn btn-link" data-toggle="modal" data-target="#editModal" ><i class="dripicons-document-edit"></i>  {{__('file.edit')}}</button>
                                </li>
                                <li class="divider"></li>
                                <form action="{{ route('designations.destroy', $designation->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <li>
                                        <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> {{__('file.delete')}}</button>
                                    </li>
                                </form>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- Create Modal -->
<div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('designations.store') }}" method="POST">
            @csrf
        <div class="modal-header">
          <h5 id="exampleModalLabel" class="modal-title">{{__('file.Add Designation')}}</h5>
          <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
        </div>
        <div class="modal-body">
          <p class="italic"><small>{{__('file.The field labels marked with are required input fields')}}.</small></p>
            <div class="form-group">
                <label>{{__('file.name')}} *</label>
                <input type="text" name="name" required="required" class="form-control" placeholder="{{ __('file.Type designation name') }}">
            </div>
            <div class="form-group">
              <input type="submit" value="{{__('file.submit')}}" class="btn btn-primary">
            </div>
        </div>
        </form>
      </div>
    </div>
</div>
<!-- Edit Modal -->
<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
  <div role="document" class="modal-dialog">
    <div class="modal-content">
        <form action="{{ route('designations.update', 1) }}" method="POST">
            @csrf
            @method('PUT')
      <div class="modal-header">
        <h5 id="exampleModalLabel" class="modal-title">{{__('file.Update Designation')}}</h5>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
      </div>
      <div class="modal-body">
        <p class="italic"><small>{{__('file.The field labels marked with are required input fields')}}.</small></p>
            <div class="form-group">
                <label>{{__('file.name')}} *</label>
                <input type="text" name="name" required="required" class="form-control">
            </div>
            <input type="hidden" name="designation_id">
            <div class="form-group">
                <input type="submit" value="{{__('file.submit')}}" class="btn btn-primary">
              </div>
            </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<style>
    /* ── DataTable button group: green wrapper, white buttons ── */
    .dt-buttons {
        background: #13bd60;
        padding: 4px;
        border-radius: 6px;
        display: inline-flex;
        gap: 3px;
        margin-bottom: 5px;
    }
    .dt-buttons .btn,
    .dt-buttons .dt-button {
        background: #fff !important;
        color: #333 !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 4px !important;
        padding: 5px 10px !important;
        font-size: 0.85rem;
        line-height: 1.4;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .dt-buttons .btn:hover,
    .dt-buttons .dt-button:hover {
        background: #f8fff9 !important;
        color: #13bd60 !important;
        border-color: #13bd60 !important;
    }
    .dt-buttons .btn i,
    .dt-buttons .dt-button i {
        color: #333;
        font-size: 16px;
    }
    .dt-buttons .btn:hover i,
    .dt-buttons .dt-button:hover i {
        color: #13bd60;
    }
    .dt-buttons .buttons-delete {
        background: #fff !important;
    }
    .dt-buttons .buttons-delete:hover {
        background: #fff5f5 !important;
        color: #dc3545 !important;
        border-color: #dc3545 !important;
    }
    .dt-buttons .buttons-delete:hover i {
        color: #dc3545;
    }
    .dt-buttons .buttons-columnVisibility.active {
        background: #e8f5e9 !important;
        color: #13bd60 !important;
    }
</style>

<script type="text/javascript">
    $("ul#hrm").siblings('a').attr('aria-expanded','true');
    $("ul#hrm").addClass("show");
    $("ul#hrm #designations-menu").addClass("active");

    var designation_id = [];
    var user_verified = <?php echo json_encode(config('app.user_verified'))?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function confirmDelete() {
      if (confirm("Are you sure want to delete?")) {
          return true;
      }
      return false;
    }
    $(document).ready(function() {
        $('.edit-btn').on('click', function(){
            $("#editModal input[name='designation_id']").val($(this).data('id'));
            $("#editModal input[name='name']").val($(this).data('name'));
        });
    });

    $('#designation-table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{__("file.records per page")}}',
             "info":      '<small>{{__("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{__("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 2]
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
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                className: 'btn btn-default btn-sm',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="fa fa-file-excel-o"></i>',
                className: 'btn btn-default btn-sm',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                className: 'btn btn-default btn-sm',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                className: 'btn btn-default btn-sm',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                text: '<i title="delete" class="fa fa-times"></i>',
                className: 'btn btn-default btn-sm buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        designation_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                designation_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(designation_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'designations/deletebyselection',
                                data:{
                                    designationIdArray: designation_id
                                },
                                success:function(data){
                                    alert(data);
                                }
                            });
                            dt.rows({ page: 'current', selected: true }).remove().draw(false);
                        }
                        else if(!designation_id.length)
                            alert('No designation is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                className: 'btn btn-default btn-sm',
                columns: ':gt(0)'
            },
        ],
    } );
</script>
@endpush