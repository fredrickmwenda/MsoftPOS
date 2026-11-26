@extends('backend.layout.main')

@section('content')

@if(session()->has('message1'))
    <div class="alert alert-success alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {!! session()->get('message1') !!}
    </div>
@endif
@if(session()->has('message2'))
    <div class="alert alert-success alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session()->get('message2') }}
    </div>
@endif
@if(session()->has('message3'))
    <div class="alert alert-danger alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {{ session()->get('message3') }}
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

@if($errors->any())
    <div class="alert alert-danger alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <ul class="mb-0 list-unstyled">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section>
    @if(in_array("variant-types-add", $all_permission ?? []))
        <div class="container-fluid">
            <a href="{{ route('variant-types.create') }}" class="btn btn-info">
                <i class="dripicons-plus"></i> Add Variant Type
            </a>
        </div>
    @else
        <div class="container-fluid">
            <a href="{{ route('variant-types.create') }}" class="btn btn-info">
                <i class="dripicons-plus"></i> Add Variant Type
            </a>
        </div>
    @endif

    <div class="table-responsive">
        <table id="variant-type-table" class="table">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>Order</th>
                    <th>Name</th>
                    <th>Product Variants</th>
                    <th class="not-exported">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($variantTypes as $key => $variantType)
                <tr data-id="{{ $variantType->id }}">
                    <td>{{ $key }}</td>
                    <td>{{ $variantType->order }}</td>
                    <td>{{ $variantType->name }}</td>
                    <td>
                        @if(method_exists($variantType, 'productVariants'))
                            {{ $variantType->productVariants ? $variantType->productVariants->count() : 0 }}
                        @else
                            0
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button"
                                    class="btn btn-default btn-sm dropdown-toggle"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                Action
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" role="menu">
                                <li>
                                    <a href="{{ route('variant-types.show', $variantType->id) }}" class="btn btn-link">
                                        <i class="dripicons-preview"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('variant-types.edit', $variantType->id) }}" class="btn btn-link">
                                        <i class="dripicons-document-edit"></i> Edit
                                    </a>
                                </li>
                                <li class="divider"></li>
                                {{ Form::open(['route' => ['variant-types.destroy', $variantType->id], 'method' => 'DELETE']) }}
                                <li>
                                    <button type="submit" class="btn btn-link" onclick="return confirmDelete()">
                                        <i class="dripicons-trash"></i> Delete
                                    </button>
                                </li>
                                {{ Form::close() }}
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">

    // Expand sidebar menu (if a variant-type menu group exists)
    $("ul#product").siblings('a').attr('aria-expanded', 'true');
    $("ul#product").addClass("show");
    $("ul#product #variation-list-menu").addClass("active");

    var variant_type_id = [];
    var all_permission = <?php echo json_encode($all_permission ?? []) ?>;

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

    $('#variant-type-table').DataTable({
        "order": [[1, 'asc']],
        'language': {
            'lengthMenu': '_MENU_ records per page',
            "info": '<small>Showing _START_ - _END_ (_TOTAL_)</small>',
            'search': 'Search',
            'paginate': {
                'previous': '<i class="dripicons-chevron-left"></i>',
                'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 4]
            },
            {
                'render': function(data, type, row, meta) {
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
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function (e, dt, node, config) {
                    variant_type_id.length = 0;
                    $(':checkbox:checked').each(function(i){
                        if(i){
                            variant_type_id[i-1] = $(this).closest('tr').data('id');
                        }
                    });
                    if(variant_type_id.length && confirm("Are you sure want to delete?")) {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route("variant-types.index") }}/deletebyselection',
                            data: {
                                variantTypeIdArray: variant_type_id
                            },
                            success: function(data) {
                                alert(data);
                            }
                        });
                        dt.rows({ page: 'current', selected: true }).remove().draw(false);
                    } else if(!variant_type_id.length) {
                        alert('No variant type is selected!');
                    }
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    });

    if(all_permission.indexOf("variant-types-delete") == -1) {
        $('.buttons-delete').addClass('d-none');
    }
</script>
@endpush
