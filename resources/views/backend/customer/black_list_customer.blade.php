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

        </div>
        <div class="table-responsive">
            <table id="customer-table" class="table">
                <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Customer Details')}}</th>
                    <th>Total Missed Delivery</th>
                    <th>Last Missed Delivery Date</th>
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($black_list_customers as $key => $customer)
                        <?php
                            $customer = \App\Models\Customer::find($customer->customer_id);
                        ?>
                    @if(isset($customer->id))
                        <tr>
                            <td>{{ $key }}</td>
                            <td>
                                <?php
                                    echo $customer->name . '<br>Phone: ' . $customer->phone_number . '<br>Email: ' . $customer->email . '<br>WhatsApp: ' . $customer->whatsapp_number;
                                ?>
                            </td>
                            <td>
                                    <?php
                                        $sum = \App\Models\BlackListCustomer::where('customer_id', $customer->id)->get();
                                        echo count($sum);
                                    ?>
                            </td>
                            <td>
                                    <?php
                                    $last_list = \App\Models\BlackListCustomer::where('customer_id', $customer->id)->orderBy('id', 'desc')->first();
                                    if (isset($last_list->id)) {
                                        echo $last_list->last_delivery_miss_date;
                                    }
                                    ?>
                            </td>
                            <td>
                                <a class="btn btn-danger" href="{{ url('delete-black-list-customer/'.$customer->id) }}"><i class="dripicons-trash"></i> {{trans('file.delete')}}</a>
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
            "order": [[0, 'desc']],
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
