@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush
 
@section('content')

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{__('file.SMS Template List')}}</h3>
            </div>
        </div>
        <button class="btn btn-info" data-toggle="modal" data-target="#smstemplates-modal"><i class="ti ti-plus"></i> {{__('file.Add Template')}}</button>
    </div>

    <div class="table-responsive">
        <table id="template-table" class="table" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{__('file.name')}}</th>
                    <th>{{__('file.Content')}}</th>
                    <th>{{__('file.Default')}}</th>
                    <th>{{__('file.Default Online')}}</th>
                    <th class="not-exported">{{__('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $template)
                    <tr>
                        <td></td>
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->content }}</td>
                        <td>
                            @if ($template->is_default)
                                <span class="badge badge-success">Default</span>
                            @endif
                        </td>
                        <td>
                            @if ($template->is_default_ecommerce)
                                <span class="badge badge-success">Default</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{__('file.action')}}
                                  <span class="caret"></span>
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                    <li>
                                        <button type="button" data-id="{{$template->id}}" data-name="{{$template->name}}" data-content="{{$template->content}}" data-is_default="{{$template->is_default}}" data-is_default_ecommerce="{{$template->is_default_ecommerce}}"class="edit-btn btn btn-link" data-toggle="modal" data-target="#editModal" ><i class="ti ti-edit"></i>  {{__('file.edit')}}</button>
                                    </li>
                                    <li class="divider"></li>
                                    <form action="{{ route('smstemplates.destroy', $template->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <li>
                                            <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="ti ti-trash"></i> {{__('file.delete')}}</button>
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

<!-- create modal -->
<div id="smstemplates-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="exampleModalLabel" class="modal-title">{{__('file.Add Template')}}</h5>
          <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
        </div>
        <div class="modal-body">
          <p class="italic"><small>{{__('file.The field labels marked with are required input fields')}}.</small></p>
          <form action="{{route('smstemplates.store')}}" method="POST">
          @csrf
          <div class="row">
            <div class="col-md-12 form-group">
              <label>{{__('file.name')}}*</label>
              <input type="text" name="name" class="form-control " placeholder="{{ __('file.Template Name') }}" />
            </div>
          </div>
          <div class="form-group">
            <label>{{__('file.Content')}}*</label>
            <textarea type="text" name="content" rows="7" placeholder="You can set following dynamic tags for a template:
[reference], [customer], [sale_status], [payment_status] [sale_total]
Example: 
Hi [customer],
Thanks for the order. Order reference: [reference]. Order status: [sale_status] Sale Total: [sale_total]. Payment status: [payment_status].
" class="form-control"></textarea>
          </div>
          <div class="form-group">
            <input class="mt-2" type="checkbox" name="is_default" value="1">
            <label class="mt-2"><strong>{{__('file.Default SMS Sale')}}</strong></label>
          </div>
          <div class="form-group">
            <input class="mt-2" type="checkbox" name="is_default_ecommerce" value="1">
            <label class="mt-2"><strong>{{__('file.Default SMS E-Commerce')}}</strong></label>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary">{{__('file.submit')}}</button>
          </div>
          </form>
        </div>
      </div>
    </div>
</div>
<!-- edit modal -->
<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{__('file.Update Template')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
            </div>
            <div class="modal-body">
              <p class="italic"><small>{{__('file.The field labels marked with are required input fields')}}.</small></p>
                <form action="{{ route('smstemplates.update', 1) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12 form-group">
	      			        <input type="hidden" name="smstemplate_id">
                            <label>{{__('file.name')}}</label>
                            <input type="text" name="name" value="" class="form-control " placeholder="{{ __('file.Template Name') }}"/>
                        </div>
                    </div>
                  <div class="form-group">
                      <label>{{__('file.Content')}}</label>
                      <textarea name="content" rows="3" class="form-control"></textarea>
                  </div>
                  <div class="form-group">
                    <input class="mt-2" type="checkbox" name="is_default" value="1">
                    <label class="mt-2"><strong>{{__('file.Default SMS Sale')}}</strong></label>
                  </div>
                  <div class="form-group">
                    <input class="mt-2" type="checkbox" class="is_default_ecommerce" name="is_default_ecommerce" value="1">
                    <label class="mt-2"><strong>{{__('file.Default SMS E-Commerce')}}</strong></label>
                  </div>
                  <div class="form-group">
                      <button type="submit" class="btn btn-primary">{{__('file.submit')}}</button>
                  </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        $(document).on('click', '.edit-btn', function(){
            $("#editModal input[name='smstemplate_id']").val($(this).data('id'));
            $("#editModal input[name='name']").val($(this).data('name'));
            $("#editModal textarea[name='content']").val($(this).data('content'));
            $(this).data('is_default') == true ? $("#editModal input[name='is_default']").prop("checked",true) : $("#editModal input[name='is_default']").prop("checked", false);
            $(this).data('is_default_ecommerce') == true ? $("#editModal input[name='is_default_ecommerce']").prop("checked",true) : $("#editModal input[name='is_default_ecommerce']").prop("checked", false);
        });
    });

    $('#template-table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{__("file.records per page")}}',
             "info":      '<small>{{__("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{__("file.Search")}}',
            'paginate': {
                    'previous': '<i class="ti ti-chevron-left"></i>',
                    'next': '<i class="ti ti-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 3]
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
                text: '<i title="export to pdf" class="ti ti-file-type-pdf"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="ti ti-file-type-xls"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="ti ti-file-type-csv"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i title="print" class="ti ti-printer"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="ti ti-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    } );
</script>
@endpush