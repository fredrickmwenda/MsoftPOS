@extends('backend.layout.main') @section('content')
    @if(session()->has('create_message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('create_message') !!}</div>
    @endif
    @if(session()->has('edit_message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('edit_message') }}</div>
    @endif
    @if(session()->has('import_message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('import_message') !!}</div>
    @endif
    @if(session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif

    <section>
        <div class="container-fluid">
            <a href="{{ url('create-ad') }}" class="btn btn-primary pull-right">Create Ad</a>
        </div>
        <div class="table-responsive">
            <table id="customer-table" class="table">
                <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>Staff</th>
                    <th>Channel</th>
                    <th>Input Amount</th>
                    <th>Time Period</th>
                    <th>Outcome Amount</th>
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($ads as $key => $ad)
                        <?php
                            $staff = App\Models\User::find($ad->staff_id);
                            ?>
                        @if(isset($staff->id))
                            <tr>
                                <td>{{ $key }}</td>
                                <td>{{ $staff->name }}</td>
                                <td>{{ $ad->channel }}</td>
                                <td>{{ \App\Models\GeneralSetting::latest()->first()->curency . ' ' . $ad->input }}</td>
                                <td>{{ $ad->start .' TO '. $ad->end }}</td>
                                <td>
                                    <?php
                                        $check = \App\Models\Sale::where([
                                            ['user_id', $ad->staff_id],
                                            ['created_at', '>=', date('Y-m-d', strtotime($ad->start)) . ' 00:00:00'],
                                            ['created_at', '<=', date('Y-m-d', strtotime($ad->end)) . ' 00:00:00']
                                        ])->sum('total_price');
echo \App\Models\GeneralSetting::latest()->first()->curency . ' ' . $check;
                                        ?>
                                </td>
                                <td>
                                    <a href="{{ url('edit-ad/'.$ad->id) }}" class="btn btn-warning"><i class="fa fa-pencil"></i></a>
                                    <a href="{{ url('delete-ad/'.$ad->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endif

                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="text/javascript">
        $("ul#people").siblings('a').attr('aria-expanded','true');
        $("ul#people").addClass("show");
        $("ul#people #customer-list-menu").addClass("active");

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        var customer_id = [];
        var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table = $('#customer-table').DataTable( {
            "order": [],
            'language': {
                'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
                "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
                "search":  '{{trans("file.Search")}}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
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
                    action: function ( e, dt, node, config ) {
                        if(user_verified == '1') {
                            customer_id.length = 0;
                            $(':checkbox:checked').each(function(i){
                                if(i){
                                    customer_id[i-1] = $(this).closest('tr').data('id');
                                }
                            });
                            if(customer_id.length && confirm("Are you sure want to delete?")) {
                                $.ajax({
                                    type:'POST',
                                    url:'customer/deletebyselection',
                                    data:{
                                        customerIdArray: customer_id
                                    },
                                    success:function(data){
                                        alert(data);
                                    }
                                });
                                dt.rows({ page: 'current', selected: true }).remove().draw(false);
                            }
                            else if(!customer_id.length)
                                alert('No customer is selected!');
                        }
                        else
                            alert('This feature is disable for demo!');
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i title="column visibility" class="fa fa-eye"></i>',
                    columns: ':gt(0)'
                },
            ],
        } );

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        if(all_permission.indexOf("customers-delete") == -1)
            $('.buttons-delete').addClass('d-none');
    </script>
@endpush
