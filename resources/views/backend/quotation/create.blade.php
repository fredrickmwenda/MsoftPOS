@extends('backend.layout.main')
@section('content')
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{trans('file.Add Quotation')}}</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        {!! Form::open(['route' => 'quotations.store', 'method' => 'post', 'files' => true, 'id' => 'quotation-form']) !!}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{trans('file.Biller')}} *</label>
                                            <select required name="biller_id" class="selectpicker form-control" data-live-search="true" id="biller-id" title="Select Biller...">
                                                @foreach($lims_biller_list as $biller)
                                                <option value="{{$biller->id}}">{{$biller->name . ' (' . $biller->company_name . ')'}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{trans('file.Supplier')}}</label>
                                            <select name="supplier_id" class="selectpicker form-control" data-live-search="true" id="supplier-id" title="Select Supplier...">
                                                @foreach($lims_supplier_list as $supplier)
                                                <option value="{{$supplier->id}}">{{$supplier->name . ' (' . $supplier->company_name . ')'}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{trans('file.customer')}} *</label>
                                            <select id="customer_id" name="customer_id" required class="selectpicker form-control" data-live-search="true" id="customer-id" title="Select customer...">
                                                @foreach($lims_customer_list as $customer)
                                                <option value="{{$customer->id}}">{{$customer->name . ' (' . $customer->phone_number . ')'}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{trans('file.Warehouse')}} *</label>
                                            <select id="warehouse_id" name="warehouse_id" required class="selectpicker form-control" data-live-search="true" title="Select warehouse...">
                                                @foreach($lims_warehouse_list as $warehouse)
                                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label>{{trans('file.Select Product')}}</label>
                                        <div class="search-box input-group">
                                            <button class="btn btn-secondary"><i class="fa fa-barcode"></i></button>
                                            <input type="text" name="product_code_name" id="lims_productcodeSearch" placeholder="Please type product code and select..." class="form-control" />
                                        </div>
                                    </div>
                                </div>
		                        <div class="row mt-5">
		                            <div class="col-md-12">
		                                <h5>{{trans('file.Order Table')}} *</h5>
		                                <div class="table-responsive mt-3">
		                                    <table id="myTable" class="table table-hover order-list">
		                                        <thead>
		                                            <tr>
		                                                <th>{{trans('file.name')}}</th>
                                                        <th>{{trans('file.Code')}}</th>
                                                        <th>{{trans('file.Batch No')}}</th>
                                                        <th>{{trans('file.Quantity')}}</th>
                                                        <th>Net Unit Price</th>
                                                        <th>{{trans('file.Discount')}}</th>
                                                        <th>{{trans('file.Tax')}}</th>
                                                        <th>{{trans('file.Subtotal')}}</th>
                                                        <th><i class="dripicons-trash"></i></th>
		                                            </tr>
		                                        </thead>
		                                        <tbody>
		                                        </tbody>
		                                        <tfoot class="tfoot active">
		                                            <th colspan="2">{{trans('file.Total')}}</th>
                                                    <th></th>
		                                            <th id="total-qty">0</th>
		                                            <th></th>
		                                            <th id="total-discount">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
		                                            <th id="total-tax">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
		                                            <th id="total">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
		                                            <th><i class="dripicons-trash"></i></th>
		                                        </tfoot>
		                                    </table>
		                                </div>
		                            </div>
		                        </div>
		                        <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_qty" />
                                            <input type="hidden" name="order_tax_names" />
                                            <input type="hidden" name="order_tax_ids" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_discount" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_tax" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_price" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="item" />
                                            <input type="hidden" name="order_tax" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="grand_total" />
                                        </div>
                                    </div>
                                </div>
		                        <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Order Tax')}}</label>
                                            <select class="form-control selectpicker" name="order_tax_rate[]" multiple data-live-search="true" title="Select Order Taxes" data-actions-box="true">
                                                @foreach($lims_tax_list as $tax)
                                                <option value="{{$tax->id}}">{{$tax->name}} ({{$tax->rate}}%)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Order Discount')}}</label>
                                            <input type="number" name="order_discount" class="form-control" step="any">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Shipping Cost')}}</label>
                                            <input type="number" name="shipping_cost" class="form-control" step="any">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                	<div class="col-md-4">
                                		<div class="form-group">
                                			<label>{{trans('file.Status')}}</label>
                                			<select class="form-control" name="quotation_status">
                                				<option value="1">{{trans('file.Pending')}}</option>
                                				<option value="2">{{trans('file.Sent')}}</option>
                                			</select>
                                		</div>
                                	</div>
                                	<div class="col-md-4">
                                		<div class="form-group">
                                			<label>{{trans('file.Attach Document')}}</label>
                                			<i class="dripicons-question" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                            <input type="file" name="document" class="form-control" />
                                            @if($errors->has('extension'))
                                                <span>
                                                   <strong>{{ $errors->first('extension') }}</strong>
                                                </span>
                                            @endif
                                		</div>
                                	</div>
                                </div>
                                <div class="row">
                                	<div class="col-md-12">
                                		<div class="form-group">
                                			<label>{{trans('file.Note')}}</label>
                                			<textarea rows="5" name="note" class="form-control"></textarea>
                                		</div>
                                	</div>
                                </div>
                                <div class="form-group">
                                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary" id="submit-button">
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <table class="table table-bordered table-condensed totals">
            <td><strong>{{trans('file.Items')}}</strong>
                <span class="pull-right" id="item">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{trans('file.Total')}}</strong>
                <span class="pull-right" id="subtotal">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{trans('file.Order Tax')}}</strong>
                <span class="pull-right" id="order_tax">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{trans('file.Order Discount')}}</strong>
                <span class="pull-right" id="order_discount">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{trans('file.Shipping Cost')}}</strong>
                <span class="pull-right" id="shipping_cost">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{trans('file.grand total')}}</strong>
                <span class="pull-right" id="grand_total">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
        </table>
    </div>
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modal_header" class="modal-title"></h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>{{trans('file.Quantity')}}</label>
                            <input type="number" name="edit_qty" class="form-control" step="any">
                        </div>
                        <div class="form-group">
                            <label>{{trans('file.Unit Discount')}}</label>
                            <input type="number" name="edit_discount" class="form-control" step="any">
                        </div>
                        <div class="form-group">
                            <label>Unit Price</label>
                            <input type="number" name="edit_unit_price" class="form-control" step="any">
                        </div>
                        <?php
                            $tax_name_all = [];
                            $tax_rate_all = [];
                            
                            $tax_name_all[0] = 'No Tax';
                            $tax_rate_all[0] = 0;
                            
                            foreach($lims_tax_list as $tax) {
                                $tax_name_all[$tax->id] = $tax->name; // index BY ID
                                $tax_rate_all[$tax->id] = $tax->rate; // index BY ID
                            }
                        ?>
                        <div class="form-group">
                            <label>{{trans('file.Tax Rate')}}</label>
                            <select name="edit_tax_rate[]" class="form-control selectpicker" multiple data-live-search="true" title="Select Taxes" data-actions-box="true">
                                @foreach($lims_tax_list as $tax)
                                    <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="edit_unit" class="form-group">
                            <label>{{trans('file.Product Unit')}}</label>
                            <select name="edit_unit" class="form-control selectpicker">
                            </select>
                        </div>
                        <button type="button" name="update_btn" class="btn btn-primary">{{trans('file.update')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    .bootstrap-select .btn {
        white-space: normal;
    }
    .bootstrap-select .filter-option-inner-inner {
        overflow: hidden;
        text-overflow: ellipsis;
    }
    table.order-list th:nth-child(7),
    table.order-list td:nth-child(7) {
        max-width: 200px;
        word-wrap: break-word;
    }
    .net-unit-price-input {
        width: 100px;
        text-align: right;
    }
</style>
@endpush

@push('scripts')
<script type="text/javascript">

    $("ul#quotation").siblings('a').attr('aria-expanded','true');
    $("ul#quotation").addClass("show");
    $("ul#quotation #quotation-create-menu").addClass("active");

var lims_product_array = [];
var product_code = [];
var product_name = [];
var product_qty = [];
var product_type = [];
var product_id = [];
var product_list = [];
var qty_list = [];

// array data with selection
var product_price = [];
var product_shelf = [];
var product_discount = [];
var tax_rate = []; // Will store comma-separated tax IDs
var tax_name = []; // Will store comma-separated tax names
var tax_method = [];
var unit_name = [];
var unit_operator = [];
var unit_operation_value = [];

// temporary array
var temp_unit_name = [];
var temp_unit_operator = [];
var temp_unit_operation_value = [];

var rowindex;
var customer_group_rate;
var row_product_price;
var pos;
var currency = <?php echo json_encode($currency) ?>;
var without_stock = <?php echo json_encode($general_setting->without_stock) ?>;

// PHP-injected tax list indexed by tax ID
var tax_rate_all = <?php echo json_encode($tax_rate_all) ?>;
var tax_name_all = <?php echo json_encode($tax_name_all) ?>;

	$('.selectpicker').selectpicker({
    	style: 'btn-link',
	});

    $('[data-toggle="tooltip"]').tooltip();

	$('select[name="customer_id"]').on('change', function() {
    	var id = $(this).val();
	    $.get('getcustomergroup/' + id, function(data) {
	        customer_group_rate = (data / 100);
	    });
	});

	$('select[name="warehouse_id"]').on('change', function() {
	    var id = $(this).val();
	    $.get('getproduct/' + id, function(data) {
	        lims_product_array = [];
	        product_code = data[0];
	        product_name = data[1];
	        product_qty = data[2];
            product_type = data[3];
            product_id = data[4];
            product_list = data[5];
           
            product_shelf = data[10];
            qty_list = data[6];
            product_warehouse_price = data[7];
	        $.each(product_code, function(index) {
                lims_product_array.push(
                    'Code:' + product_code[index] +
                    ' | Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index]
                );
	        });
	    });
	});

	$('#lims_productcodeSearch').on('input', function(){
	    var customer_id = $('#customer_id').val();
	    var warehouse_id = $('#warehouse_id').val();
	    temp_data = $('#lims_productcodeSearch').val();
	    if(!customer_id){
	        $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
	        alert('Please select Customer!');
	    }
	    else if(!warehouse_id){
	        $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
	        alert('Please select Warehouse!');
	    }
	});

	var lims_productcodeSearch = $('#lims_productcodeSearch');

	lims_productcodeSearch.autocomplete({
	    source: function(request, response) {
	        var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
	        response($.grep(lims_product_array, function(item) {
	            return matcher.test(item);
	        }));
	    },
        response: function(event, ui) {
            if (ui.content.length == 1) {
                var data = ui.content[0].value;
                $(this).autocomplete( "close" );
                productSearch(data);
            };
        },
	    select: function(event, ui) {
	        var data = ui.item.value;
            productSearch(data);
	    }
	});

	//Change quantity
	$("#myTable").on('input', '.qty', function() {
	    rowindex = $(this).closest('tr').index();
        if($(this).val() < 1 && $(this).val() != '') {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
            alert("Quantity can't be less than 1");
        }
        updateRowValues(rowindex);
	});

    // Handle net unit price change
    $("table.order-list").on('input', '.net-unit-price-input', function() {
        rowindex = $(this).closest('tr').index();
        var new_price = parseFloat($(this).val()) || 0;
        product_price[rowindex] = new_price;
        updateRowValues(rowindex);
    });

    // Handle discount change
    $("table.order-list").on('input', '.discount-input', function() {
        rowindex = $(this).closest('tr').index();
        var new_discount = parseFloat($(this).val()) || 0;
        product_discount[rowindex] = new_discount;
        updateRowValues(rowindex);
    });

    $("#myTable").on("change", ".batch-no", function () {
        rowindex = $(this).closest('tr').index();
        var product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-id').val();
        var warehouse_id = $('#warehouse_id').val();
        $.get('../check-batch-availability/' + product_id + '/' + $(this).val() + '/' + warehouse_id, function(data) {
            if(data['message'] != 'ok') {
                alert(data['message']);
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.batch-no').val('');
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val('');
            }
            else {
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val(data['product_batch_id']);
                code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
                pos = product_code.indexOf(code);
                product_qty[pos] = data['qty'];
            }
        });
    });

	$("table.order-list tbody").on("click", ".ibtnDel", function(event) {
	    rowindex = $(this).closest('tr').index();
	    product_price.splice(rowindex, 1);
	    product_discount.splice(rowindex, 1);
	    tax_rate.splice(rowindex, 1);
	    tax_name.splice(rowindex, 1);
	    tax_method.splice(rowindex, 1);
	    unit_name.splice(rowindex, 1);
	    unit_operator.splice(rowindex, 1);
	    unit_operation_value.splice(rowindex, 1);
	    $(this).closest("tr").remove();
	    calculateTotal();
	});

	$('input[name="order_discount"]').on("input", function() {
	    calculateGrandTotal();
	});

	$('input[name="shipping_cost"]').on("input", function() {
	    calculateGrandTotal();
	});

	$('select[name="order_tax_rate[]"]').on("change", function() {
	    calculateGrandTotal();
	});

//Edit product
$("table.order-list").on("click", ".edit-product", function() {
    rowindex = $(this).closest('tr').index();
    edit();
});

//update product
$('button[name="update_btn"]').on("click", function() {
    var edit_discount = $('input[name="edit_discount"]').val();
    var edit_qty = $('input[name="edit_qty"]').val();
    var edit_unit_price = $('input[name="edit_unit_price"]').val();

    if (parseFloat(edit_discount) > parseFloat(edit_unit_price)) {
        alert('Invalid Discount Input!');
        return;
    }

    if(edit_qty < 1) {
        $('input[name="edit_qty"]').val(1);
        edit_qty = 1;
        alert("Quantity can't be less than 1");
    }

    // Get multiple selected taxes
    var selectedTaxes = $('select[name="edit_tax_rate[]"]').val() || [];
    
    // Store tax IDs as comma-separated string
    tax_rate[rowindex] = selectedTaxes.join(',');
    
    // Store tax names
    var selectedTaxNames = [];
    selectedTaxes.forEach(function(taxId) {
        selectedTaxNames.push(tax_name_all[taxId] || '');
    });
    tax_name[rowindex] = selectedTaxNames.join(',');

    if(product_type[pos] == 'standard'){
        var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
        var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));

        if (row_unit_operator == '*') {
            product_price[rowindex] = $('input[name="edit_unit_price"]').val() / row_unit_operation_value;
        } else {
            product_price[rowindex] = $('input[name="edit_unit_price"]').val() * row_unit_operation_value;
        }

        var position = $('select[name="edit_unit"]').val();
        var temp_operator = temp_unit_operator[position];
        var temp_operation_value = temp_unit_operation_value[position];
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val(temp_unit_name[position]);
        temp_unit_name.splice(position, 1);
        temp_unit_operator.splice(position, 1);
        temp_unit_operation_value.splice(position, 1);

        temp_unit_name.unshift($('select[name="edit_unit"] option:selected').text());
        temp_unit_operator.unshift(temp_operator);
        temp_unit_operation_value.unshift(temp_operation_value);

        unit_name[rowindex] = temp_unit_name.toString() + ',';
        unit_operator[rowindex] = temp_unit_operator.toString() + ',';
        unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';
    }
    else {
        product_price[rowindex] = $('input[name="edit_unit_price"]').val();
    }
    product_discount[rowindex] = $('input[name="edit_discount"]').val();
    
    // Update the row display
    updateRowValues(rowindex);
    
    $('#editModal').modal('hide');
});

$(window).keydown(function(e){
    if (e.which == 13) {
        var $targ = $(e.target);
        if (!$targ.is("textarea") && !$targ.is(":button,:submit")) {
            var focusNext = false;
            $(this).find(":input:visible:not([disabled],[readonly]), a").each(function(){
                if (this === e.target) {
                    focusNext = true;
                }
                else if (focusNext){
                    $(this).focus();
                    return false;
                }
            });
            return false;
        }
    }
});

$('#quotation-form').on('submit',function(e){
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
        e.preventDefault();
    }
    else {
        $("#submit-button").prop('disabled', true);
    }
});

function productSearch(data){
    var code_match = data.match(/Code:\s*([^|]+)/);
    var product_info = data.split(" ");
    var product_code = code_match ? code_match[1].trim() : data.split(" ")[0];
    
    if (product_code.length < 1) {
        alert('Please insert product code!');
        return;
    }
    var pre_qty = 0;
    $(".product-code").each(function(i) {
        if ($(this).val() == product_code) {
            rowindex = i;
            pre_qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
        }
    });
    var ajaxData = product_code + '?' + $('#customer_id').val() + '?' + (parseFloat(pre_qty) + 1);
    
    $.ajax({
        type: 'GET',
        url: 'lims_product_search',
        data: {
            data: ajaxData
        },
        success: function(data) {
            var flag = 1;
            $(".product-code").each(function(i) {
                if ($(this).val() == data[1]) {
                    rowindex = i;
                    var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
                    updateRowValues(rowindex);
                    flag = 0;
                }
            });
            $("input[name='product_code_name']").val('');
            if(flag){
                var newRow = $("<tr>");
                var cols = '';
                temp_unit_name = (data[6]).split(',');
                cols += '<td>' + data[0] + '<button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"> <i class="dripicons-document-edit"></i></button></td>';
                cols += '<td>' + data[1] + '</td>';
                
                if(data[12])
                    cols += '<td><input type="text" class="form-control batch-no" required/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/> </td>';
                else
                    cols += '<td><input type="text" class="form-control batch-no" disabled/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/> </td>';
                
                cols += '<td><input type="number" class="form-control qty" name="qty[]" value="1" step="any" required/></td>';
                cols += '<td><input type="number" class="form-control net-unit-price-input" name="net_unit_price_display[]" value="' + parseFloat(data[2]).toFixed({{$general_setting->decimal}}) + '" step="any" required/></td>';
                cols += '<td><input type="number" class="form-control discount-input" name="discount_display[]" value="0" step="any" style="width: 80px;"/></td>';
                cols += '<td class="tax-names-display"></td>';
                cols += '<td class="sub-total">' + parseFloat(data[2]).toFixed({{$general_setting->decimal}}) + '</td>';
                cols += '<td><button type="button" class="ibtnDel btn btn-md btn-danger">{{trans("file.delete")}}</button></td>';
                
                cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
                cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
                cols += '<input type="hidden" class="sale-unit" name="sale_unit[]" value="' + temp_unit_name[0] + '"/>';
                cols += '<input type="hidden" class="net-unit-price" name="net_unit_price[]" value="' + data[2] + '"/>';
                cols += '<input type="hidden" class="discount-value" name="discount[]" value="0"/>';
                cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + (data[3] || '') + '"/>';
                cols += '<input type="hidden" class="tax-names" name="tax_names[]" value="' + (data[4] || '') + '"/>';
                cols += '<input type="hidden" class="tax-value" name="tax[]" value="0"/>';
                cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" value="' + data[2] + '"/>';

                newRow.append(cols);
                $("table.order-list tbody").prepend(newRow);
                rowindex = newRow.index();
                pos = product_code.indexOf(data[1]);
                
                if(!data[11] && product_warehouse_price[pos]) {
                    product_price.splice(rowindex, 0, parseFloat(product_warehouse_price[pos] * currency['exchange_rate']) + parseFloat(product_warehouse_price[pos] * currency['exchange_rate'] * customer_group_rate));
                }
                else {
                    product_price.splice(rowindex, 0, parseFloat(data[2] * currency['exchange_rate']) + parseFloat(data[2] * currency['exchange_rate'] * customer_group_rate));
                }
                
                product_discount.splice(rowindex, 0, 0);
                tax_rate.splice(rowindex, 0, data[3] || '');
                tax_name.splice(rowindex, 0, data[4] || '');
                tax_method.splice(rowindex, 0, data[5]);
                unit_name.splice(rowindex, 0, data[6]);
                unit_operator.splice(rowindex, 0, data[7]);
                unit_operation_value.splice(rowindex, 0, data[8]);
                
                updateRowValues(rowindex);
            }
        }
    });
}

function edit(){
    var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    $('#modal_header').text(row_product_name + '(' + row_product_code + ')');

    var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
    $('input[name="edit_qty"]').val(qty);

    var current_discount = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val();
    var unit_discount = parseFloat(current_discount) / parseFloat(qty);
    $('input[name="edit_discount"]').val(unit_discount.toFixed({{$general_setting->decimal}}));

    var net_unit_price = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net-unit-price-input').val();
    $('input[name="edit_unit_price"]').val(net_unit_price);

    // Handle multiple taxes in edit modal
    var currentTaxIds = tax_rate[rowindex] ? tax_rate[rowindex].split(',') : [];
    $('select[name="edit_tax_rate[]"]').val(currentTaxIds);

    pos = product_code.indexOf(row_product_code);
    if(product_type[pos] == 'standard'){
        unitConversion();
        temp_unit_name = (unit_name[rowindex]).split(',');
        temp_unit_name.pop();
        temp_unit_operator = (unit_operator[rowindex]).split(',');
        temp_unit_operator.pop();
        temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
        temp_unit_operation_value.pop();
        $('select[name="edit_unit"]').empty();
        $.each(temp_unit_name, function(key, value) {
            $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
        });
        $("#edit_unit").show();
    }
    else{
        row_product_price = product_price[rowindex];
        $("#edit_unit").hide();
    }
    
    $('.selectpicker').selectpicker('refresh');
}

function updateRowValues(rowindex) {
    var row = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')');
    var qty = parseFloat(row.find('.qty').val()) || 0;
    var net_unit_price = parseFloat(row.find('.net-unit-price-input').val()) || 0;
    var discount_per_unit = parseFloat(row.find('.discount-input').val()) || 0;
    var total_discount = discount_per_unit * qty;
    
    // Update hidden discount value
    row.find('.discount-value').val(total_discount.toFixed({{$general_setting->decimal}}));
    
    // Calculate total tax percentage from multiple taxes
    var tax_ids = tax_rate[rowindex] ? tax_rate[rowindex].split(',') : [];
    var total_tax_percentage = 0;
    
    tax_ids.forEach(function(tax_id) {
        total_tax_percentage += parseFloat(tax_rate_all[tax_id] || 0);
    });
    
    // Update tax names display
    row.find('.tax-names-display').text(tax_name[rowindex] || 'No Tax');
    
    // Calculate values based on tax method
    var net_unit_price_after_discount = net_unit_price - discount_per_unit;
    var subtotal_without_tax = net_unit_price_after_discount * qty;
    var tax_amount = 0;
    var subtotal = 0;
    
    if (tax_method[rowindex] == '1') { // Exclusive tax
        tax_amount = subtotal_without_tax * (total_tax_percentage / 100);
        subtotal = subtotal_without_tax + tax_amount;
    } else { // Inclusive tax
        subtotal = net_unit_price_after_discount * qty;
        tax_amount = subtotal - (subtotal / (1 + (total_tax_percentage / 100)));
    }
    
    // Update display values
    row.find('.tax').text(tax_amount.toFixed({{$general_setting->decimal}}));
    row.find('.tax-value').val(tax_amount.toFixed({{$general_setting->decimal}}));
    
    row.find('.sub-total').text(subtotal.toFixed({{$general_setting->decimal}}));
    row.find('.subtotal-value').val(subtotal.toFixed({{$general_setting->decimal}}));
    
    // Update hidden net unit price
    row.find('.net-unit-price').val(net_unit_price.toFixed({{$general_setting->decimal}}));
    
    // Update arrays
    product_price[rowindex] = net_unit_price;
    product_discount[rowindex] = discount_per_unit;
    
    // Calculate totals
    calculateTotal();
}

function checkQuantity(sale_qty, flag) {
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    pos = product_code.indexOf(row_product_code);
    if(without_stock == 'no') {
        if(product_type[pos] == 'standard'){
            var operator = unit_operator[rowindex].split(',');
            var operation_value = unit_operation_value[rowindex].split(',');
            if(operator[0] == '*')
                total_qty = sale_qty * operation_value[0];
            else if(operator[0] == '/')
                total_qty = sale_qty / operation_value[0];
            if (total_qty > parseFloat(product_qty[pos])) {
                alert('Quantity exceeds stock quantity!');
                if (flag) {
                    sale_qty = sale_qty.toString().substring(0, sale_qty.toString().length - 1);
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                }
                else {
                    edit();
                    return;
                }
            }
        }
        else if(product_type[pos] == 'combo'){
            child_id = product_list[pos].split(',');
            child_qty = qty_list[pos].split(',');
            $(child_id).each(function(index) {
                var position = product_id.indexOf(parseInt(child_id[index]));
                if( parseFloat(sale_qty * child_qty[index]) > product_qty[position] ) {
                    alert('Quantity exceeds stock quantity!');
                    if (flag) {
                        sale_qty = sale_qty.substring(0, sale_qty.length - 1);
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                    }
                    else {
                        edit();
                        flag = true;
                        return false;
                    }
                }
            });
        }
    }

    if(!flag){
        $('#editModal').modal('hide');
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
    }
    updateRowValues(rowindex);
}

function unitConversion() {
    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));

    if (row_unit_operator == '*') {
        row_product_price = product_price[rowindex] * row_unit_operation_value;
    } else {
        row_product_price = product_price[rowindex] / row_unit_operation_value;
    }
}

function calculateTotal() {
    //Sum of quantity
    var total_qty = 0;
    $(".qty").each(function() {

        if ($(this).val() == '') {
            total_qty += 0;
        } else {
            total_qty += parseFloat($(this).val());
        }
    });
    $("#total-qty").text(total_qty);
    $('input[name="total_qty"]').val(total_qty);

    //Sum of discount
    var total_discount = 0;
    $(".discount-value").each(function() {
        total_discount += parseFloat($(this).val());
    });
    $("#total-discount").text(total_discount.toFixed({{$general_setting->decimal}}));
    $('input[name="total_discount"]').val(total_discount.toFixed({{$general_setting->decimal}}));

    //Sum of tax
    var total_tax = 0;
    $(".tax-value").each(function() {
        total_tax += parseFloat($(this).val());
    });
    $("#total-tax").text(total_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="total_tax"]').val(total_tax.toFixed({{$general_setting->decimal}}));

    //Sum of subtotal
    var total = 0;
    $(".subtotal-value").each(function() {
        total += parseFloat($(this).val());
    });
    $("#total").text(total.toFixed({{$general_setting->decimal}}));
    $('input[name="total_price"]').val(total.toFixed({{$general_setting->decimal}}));

    calculateGrandTotal();
}

function calculateGrandTotal() {
    var item = $('table.order-list tbody tr:last').index();
    var total_qty = parseFloat($('#total-qty').text());
    var subtotal = parseFloat($('#total').text());
    
    // Handle multiple order taxes
    var selected_order_taxes = $('select[name="order_tax_rate[]"]').val() || [];
    var total_order_tax_percentage = 0;
    var order_tax_names = [];
    var order_tax_ids = [];
    
    selected_order_taxes.forEach(function(tax_id) {
        var tax_option = $('select[name="order_tax_rate[]"] option[value="' + tax_id + '"]');
        var tax_text = tax_option.text();
        var tax_name = tax_text.split('(')[0].trim();
        var tax_rate = parseFloat(tax_text.match(/\(([^)]+)\)/)[1].replace('%', ''));
        
        order_tax_names.push(tax_name);
        order_tax_ids.push(tax_id);
        total_order_tax_percentage += tax_rate;
    });
    
    // Store order tax names and IDs as hidden inputs
    $('input[name="order_tax_names"]').val(order_tax_names.join(','));
    $('input[name="order_tax_ids"]').val(order_tax_ids.join(','));
    
    var order_discount = parseFloat($('input[name="order_discount"]').val()) || 0;
    var shipping_cost = parseFloat($('input[name="shipping_cost"]').val()) || 0;

    if (!order_discount)
        order_discount = {{number_format(0, $general_setting->decimal, '.', '')}};
    if (!shipping_cost)
        shipping_cost = {{number_format(0, $general_setting->decimal, '.', '')}};

    item = ++item + '(' + total_qty + ')';
    var order_tax = ((subtotal - order_discount) * total_order_tax_percentage) / 100;
    var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;

    $('#item').text(item);
    $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
    $('#subtotal').text(subtotal.toFixed({{$general_setting->decimal}}));
    $('#order_tax').text(order_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="order_tax"]').val(order_tax.toFixed({{$general_setting->decimal}}));
    $('#order_discount').text(order_discount.toFixed({{$general_setting->decimal}}));
    $('#shipping_cost').text(shipping_cost.toFixed({{$general_setting->decimal}}));
    $('#grand_total').text(grand_total.toFixed({{$general_setting->decimal}}));
    $('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));
}

</script>
@endpush