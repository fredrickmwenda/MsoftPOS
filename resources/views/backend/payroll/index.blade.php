@extends('backend.layout.main') @section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
<section>
    <div class="container-fluid">
        <button class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="dripicons-plus"></i> {{trans('file.Add Payroll')}} </button>
    </div>
    <div class="table-responsive">
        <table id="payroll-table" class="table">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.date')}}</th>
                    <th>{{trans('file.reference')}}</th>
                    <th>{{trans('file.Employee')}}</th>
                    <th>{{trans('file.Account')}}</th>
                    <th>{{trans('file.Amount')}}</th>
                    <th>{{trans('file.Method')}}</th>
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lims_payroll_all as $key=>$payroll)
                @php
                    $employee = \App\Models\Employee::find($payroll->employee_id);
                    $account = \App\Models\Account::find($payroll->account_id);
                @endphp
                <tr data-id="{{$payroll->id}}">
                    <td>{{$key}}</td>
                    <td>{{date($general_setting->date_format, strtotime($payroll->created_at->toDateString())) }}</td>
                    <td>{{ $payroll->reference_no }}</td>
                    <td>{{ $employee->name}}</td>
                    <td>{{ $account->name}}</td>
                    <td>{{ number_format((float)$payroll->amount, $general_setting->decimal, '.', '')}}</td>
                    @if($payroll->paying_method == 0)
                        <td>Cash</td>
                    @elseif($payroll->paying_method == 1)
                        <td>Cheque</td>
                    @else
                        <td>Credit Card</td>
                    @endif
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans('file.action')}}
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" data-id="{{$payroll->id}}" data-date="{{date('d-m-Y', strtotime($payroll->created_at->toDateString()))}}" data-reference="{{$payroll->reference_no}}" data-employee="{{$payroll->employee_id}}" data-account="{{$payroll->account_id}}" data-amount="{{$payroll->amount}}" data-note="{{$payroll->note}}" data-paying_method="{{$payroll->paying_method}}" class="edit-btn btn btn-link" data-toggle="modal" data-target="#editModal"><i class="dripicons-document-edit"></i> {{trans('file.edit')}}</button>
                                </li>
                                <li class="divider"></li>
                                {{ Form::open(['route' => ['payroll.destroy', $payroll->id], 'method' => 'DELETE'] ) }}
                                <li>
                                    <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> {{trans('file.delete')}}</button>
                                </li>
                                {{ Form::close() }}
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th>Total:</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

    <!-- Payroll Template Modal -->
    <div id="createTemplateModal" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="templateModalLabel" class="modal-title">{{ __('Create Payroll Template') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <form id="payroll-template-form">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ __('Name') }}</label>
                                <input type="text" name="name" class="form-control" required />
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Work Duration') }}</label>
                                <input type="number" name="work_duration" class="form-control" step="any" />
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Duration Unit') }}</label>
                                <select name="duration_unit" class="form-control">
                                    <option value="day">Day</option>
                                    <option value="week">Week</option>
                                    <option value="month">Month</option>
                                    <option value="year">Year</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Amount per duration') }}</label>
                                <input type="number" name="amount_per_duration" class="form-control" step="any" />
                            </div>
                            <div class="col-md-12 form-group">
                                <label>{{ __('Description') }}</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                        </div>

                        <hr />
                        <h5>Items</h5>
                        <div id="template-items-list"></div>
                        <button type="button" class="btn btn-sm btn-secondary" id="add-template-item">Add Item</button>
                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-primary">Create Template</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Item Modal (Create standalone template item) -->
    <div id="createItemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="itemModalLabel" class="modal-title">{{ __('Create Payroll Item') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <form id="payroll-item-form">
                        <div class="form-group">
                            <label>{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" required />
                        </div>
                        <div class="form-group">
                            <label>{{ __('Type') }}</label>
                            <input type="text" name="type" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>{{ __('Amount Type') }}</label>
                            <select name="amount_type" class="form-control">
                                <option value="fixed">Fixed</option>
                                <option value="percent">Percent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Amount') }}</label>
                            <input type="number" name="amount" class="form-control" step="any" />
                        </div>
                        <div class="form-group">
                            <label>{{ __('Description') }}</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Create Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Payroll')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
              <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                {!! Form::open(['route' => 'payroll.store', 'method' => 'post', 'files' => true]) !!}
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Date')}}</label>
                        <input type="text" name="created_at" class="form-control date" placeholder="Choose date" value="{{date('d-m-Y')}}" />
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Employee')}} *</label>
                        <select class="form-control selectpicker" name="employee_id" required data-live-search="true" data-live-search-style="begins" title="Select Employee...">
                            @foreach($lims_employee_list as $employee)
                            <option value="{{$employee->id}}">{{$employee->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label> {{trans('file.Account')}} *</label>
                        <select class="form-control selectpicker" name="account_id">
                        @foreach($lims_account_list as $account)
                            @if($account->is_default)
                            <option selected value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @else
                            <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @endif
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Payroll Template</label>
                        <select class="form-control selectpicker" id="payroll_template_id" name="payroll_template_id" data-live-search="true">
                            <option value="">-- Select template --</option>
                            @if(isset($payroll_templates))
                                @foreach($payroll_templates as $template)
                                    <option value="{{$template->id}}">{{$template->name ?? 'Template '.$template->id}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Amount')}} *</label>
                        <input type="number" step="any" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Method')}} *</label>
                        <select class="form-control selectpicker" name="paying_method" required>
                            <option value="0">Cash</option>
                            <option value="1">Cheque</option>
                            <option value="2">Credit Card</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>{{trans('file.Note')}}</label>
                        <textarea name="note" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="col-md-12 form-group" id="payroll-items-container">
                        <!-- Dynamic payroll items will be inserted here as inputs named items[index][field] -->
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Update Payroll')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
              <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                {!! Form::open(['route' => ['payroll.update', 1], 'method' => 'put', 'files' => true]) !!}
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Date')}}</label>
                        <input type="text" name="created_at" class="form-control date" placeholder="Choose date" />
                    </div>
                    <div class="col-md-6 form-group">
                        <input type="hidden" name="payroll_id">
                        <label>{{trans('file.Employee')}} *</label>
                        <select class="form-control selectpicker" name="employee_id" required data-live-search="true" data-live-search-style="begins" title="Select Employee...">
                            @foreach($lims_employee_list as $employee)
                            <option value="{{$employee->id}}">{{$employee->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label> {{trans('file.Account')}} *</label>
                        <select class="form-control selectpicker" name="account_id">
                        @foreach($lims_account_list as $account)
                            @if($account->is_default)
                            <option selected value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @else
                            <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @endif
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Amount')}} *</label>
                        <input type="number" step="any" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Method')}} *</label>
                        <select class="form-control selectpicker" name="paying_method" required>
                            <option value="0">Cash</option>
                            <option value="1">Cheque</option>
                            <option value="2">Credit Card</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>{{trans('file.Note')}}</label>
                        <textarea name="note" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="col-md-12 form-group" id="payroll-items-container-edit">
                        <!-- Edit modal: dynamic payroll items will be inserted here -->
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#hrm").siblings('a').attr('aria-expanded','true');
    $("ul#hrm").addClass("show");
    $("ul#hrm #payroll-menu").addClass("active");

    var payroll_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;

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

    $(document).on('click', '.edit-btn', function() {
        $("#editModal input[name='payroll_id']").val( $(this).data('id') );
        $("#editModal input[name='created_at']").val( $(this).data('date') );
        $("#editModal select[name='employee_id']").val( $(this).data('employee') );
        $("#editModal select[name='account_id']").val( $(this).data('account') );
        $("#editModal input[name='amount']").val( $(this).data('amount') );
        $("#editModal select[name='paying_method']").val( $(this).data('paying_method') );
        $("#editModal textarea[name='note']").val( $(this).data('note') );
        $('.selectpicker').selectpicker('refresh');
    });

    $('#payroll-table').DataTable( {
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
                'targets': [0, 1, 6]
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
                    rows: ':visible',
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        payroll_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                payroll_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(payroll_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'payroll/deletebyselection',
                                data:{
                                    payrollIdArray: payroll_id
                                },
                                success:function(data){
                                    alert(data);
                                }
                            });
                            dt.rows({ page: 'current', selected: true }).remove().draw(false);
                        }
                        else if(!payroll_id.length)
                            alert('No payroll is selected!');
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
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    } );

    // Payroll templates data (for create modal dynamic items)
    var payrollTemplates = @json($payroll_templates ?? []);

    function renderTemplateItems(containerSelector, templateId) {
        var container = $(containerSelector);
        container.empty();
        if (!templateId) return;
        var template = payrollTemplates.find(function(t){ return t.id == templateId; });
        if (!template || !template.items) return;
        template.items.forEach(function(item, idx){
            var idxKey = idx;
            var name = item.name || item.label || '';
            var type = item.type || '';
            var amountType = item.amount_type || item.amountType || '';
            var amount = (item.amount !== undefined && item.amount !== null) ? item.amount : 0;
            var description = item.description || '';

            var html = '<div class="row payroll-item-row mb-2">'
                + '<div class="col-md-6">'
                + '<label>'+name+'</label>'
                + '<input type="hidden" name="items['+idxKey+'][payroll_template_item_id]" value="'+(item.id)+'" />'
                + '<input type="hidden" name="items['+idxKey+'][name]" value="'+name+'" />'
                + '<input type="hidden" name="items['+idxKey+'][type]" value="'+type+'" />'
                + '<input type="hidden" name="items['+idxKey+'][amount_type]" value="'+amountType+'" />'
                + '<input class="form-control" name="items['+idxKey+'][amount]" value="'+amount+'" />'
                + '</div>'
                + '<div class="col-md-6">'
                + '<label>Description</label>'
                + '<input class="form-control" name="items['+idxKey+'][description]" value="'+description+'" />'
                + '</div>'
                + '</div>';

            container.append(html);
        });
    }

    $('#payroll_template_id').on('changed.bs.select change', function(){
        renderTemplateItems('#payroll-items-container', $(this).val());
        $('.selectpicker').selectpicker('refresh');
    });

    // Template modal - dynamic items handling
    function templateItemRow(item){
        var html = '<div class="row template-item-row mb-2">'
            + '<div class="col-md-4"><input class="form-control" name="item_name[]" value="'+(item.name||'')+'" placeholder="Name" required /></div>'
            + '<div class="col-md-2"><input class="form-control" name="item_type[]" value="'+(item.type||'')+'" placeholder="Type" /></div>'
            + '<div class="col-md-2"><select class="form-control" name="item_amount_type[]"><option value="fixed">Fixed</option><option value="percent">Percent</option></select></div>'
            + '<div class="col-md-2"><input class="form-control" name="item_amount[]" value="'+(item.amount||0)+'" placeholder="Amount" /></div>'
            + '<div class="col-md-2"><button type="button" class="btn btn-danger remove-template-item">Remove</button></div>'
            + '<div class="col-12 mt-2"><input class="form-control" name="item_description[]" value="'+(item.description||'')+'" placeholder="Description" /></div>'
            + '</div>';
        return html;
    }

    $('#add-template-item').on('click', function(){
        $('#template-items-list').append(templateItemRow({}));
    });

    $(document).on('click', '.remove-template-item', function(){
        $(this).closest('.template-item-row').remove();
    });

    // Submit payroll template via AJAX
    $('#payroll-template-form').on('submit', function(e){
        e.preventDefault();
        var data = $(this).serializeArray();
        // convert items grouped fields into array
        var items = [];
        var names = $(this).find('input[name="item_name[]"]').map(function(){return $(this).val();}).get();
        var types = $(this).find('input[name="item_type[]"]').map(function(){return $(this).val();}).get();
        var amount_types = $(this).find('select[name="item_amount_type[]"]').map(function(){return $(this).val();}).get();
        var amounts = $(this).find('input[name="item_amount[]"]').map(function(){return $(this).val();}).get();
        var descriptions = $(this).find('input[name="item_description[]"]').map(function(){return $(this).val();}).get();
        for(var i=0;i<names.length;i++){
            items.push({
                name: names[i],
                type: types[i]||null,
                amount_type: amount_types[i]||null,
                amount: amounts[i]||0,
                description: descriptions[i]||null
            });
        }
        var payload = {};
        $(this).serializeArray().forEach(function(f){ payload[f.name] = f.value; });
        payload.items = items;

        $.ajax({
            url: '{{ url("payroll-templates/store") }}',
            method: 'POST',
            data: payload,
            success: function(res){
                if(res && res.id){
                    // add new template to select
                    var opt = $('<option>').val(res.id).text(res.name || ('Template '+res.id));
                    $('#payroll_template_id').append(opt).selectpicker('refresh');
                    $('#createTemplateModal').modal('hide');
                    alert('Payroll template created');
                }
            },
            error: function(xhr){
                alert('Error creating template');
            }
        });
    });

    // Submit standalone payroll item via AJAX
    $('#payroll-item-form').on('submit', function(e){
        e.preventDefault();
        var payload = $(this).serialize();
        $.ajax({
            url: '{{ url("payroll-template-items/store") }}',
            method: 'POST',
            data: payload,
            success: function(res){
                if(res && res.id){
                    alert('Payroll item created');
                    $('#createItemModal').modal('hide');
                    // Optionally append to global payroll items list if applicable
                }
            },
            error: function(xhr){ alert('Error creating item'); }
        });
    });

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();

            $( dt_selector.column( 5 ).footer() ).html(dt_selector.cells( rows, 5, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
        else {
            $( dt_selector.column( 5 ).footer() ).html(dt_selector.cells( rows, 5, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
    }
</script>
@endpush
