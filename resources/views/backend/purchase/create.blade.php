@extends('backend.layout.main')

@section('content')
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{trans('file.Add Purchase')}}</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        {!! Form::open(['route' => 'purchases.store', 'method' => 'post', 'files' => true, 'id' => 'purchase-form']) !!}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{trans('file.Date')}}</label>
                                            <input type="text" name="created_at" class="form-control date" placeholder="Choose date"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Bill Number</label>
                                            <div class="input-group">
                                                <input class="form-control" type="text" id="bill_no" name="bill_no" placeholder="Auto-generated or enter manually">
                                                <div class="input-group-append">
                                                    <button type="button" id="generateBillNo" class="btn btn-sm btn-default" title="Generate Bill Number">
                                                       <i class="fa fa-refresh"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{trans('file.Warehouse')}} *</label>
                                            <select required name="warehouse_id" id="warehouse_id" class="selectpicker form-control" data-live-search="true" title="Select warehouse...">
                                                @foreach($lims_warehouse_list as $warehouse)
                                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{trans('file.Supplier')}} *</label>
                                            <div class="input-group">
                                                <select name="supplier_id" id="supplier_id" class="selectpicker form-control" data-live-search="true" title="Select supplier..." required>
                                                    @foreach($lims_supplier_list as $supplier)
                                                    <option value="{{$supplier->id}}">{{$supplier->name .' ('. $supplier->company_name .')'}}</option>
                                                    @endforeach
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="button" id="addSupplierBtn" class="btn btn-sm btn-primary" title="Add new supplier" data-toggle="modal" data-target="#addSupplierModal">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{ __('file.payment_term') }}</label>
                                            <div class="d-flex">
                                                <input type="number" id="pay_term_no" name="pay_term_no" class="form-control" placeholder="e.g. 30">
                                                <select name="pay_term_period" id="pay_term_period" class="form-control ml-2">
                                                    <option value="days">{{ __('file.Days') }}</option>
                                                    <option value="months">{{ __('file.Months') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{ __('file.due_date') }}</label>
                                            <input type="hidden" name="due_date" id="due_date_hidden">
                                            <input type="text" id="due_date" class="form-control date" placeholder="{{ __('file.Choose Date') }}" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Purchase Status')}}</label>
                                            <select name="status" class="form-control">
                                                <option value="1">{{trans('file.Recieved')}}</option>
                                                <option value="2">{{trans('file.Partial')}}</option>
                                                <option value="3">{{trans('file.Pending')}}</option>
                                                <option value="4">{{trans('file.Ordered')}}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Attach Document')}}</label> <i class="dripicons-question" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                            <input type="file" name="document" class="form-control">
                                            @if($errors->has('extension'))
                                                <span><strong>{{ $errors->first('extension') }}</strong></span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{trans('file.Currency')}} *</label>
                                            <select name="currency_id" id="currency-id" class="form-control selectpicker" data-toggle="tooltip" title="">
                                                @foreach($currency_list as $currency_data)
                                                <option value="{{$currency_data->id}}" data-rate="{{$currency_data->exchange_rate}}">{{$currency_data->code}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label>{{trans('file.Exchange Rate')}} *</label>
                                        </div>
                                        <div class="form-group d-flex">
                                            <input class="form-control" type="text" id="exchange_rate" name="exchange_rate" value="{{$currency->exchange_rate}}">
                                            <div class="input-group-append">
                                                <span class="input-group-text" data-toggle="tooltip" title="" data-original-title="currency exchange rate">i</span>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach($custom_fields as $field)
                                        @if(!$field->is_admin || $isAdmin)
                                            <div class="{{'col-md-'.$field->grid_value}}">
                                                <div class="form-group">
                                                    <label>{{$field->name}}</label>
                                                    @if($field->type == 'text')
                                                        <input type="text" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control" @if($field->is_required){{'required'}}@endif>
                                                    @elseif($field->type == 'number')
                                                        <input type="number" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control" @if($field->is_required){{'required'}}@endif>
                                                    @elseif($field->type == 'textarea')
                                                        <textarea rows="5" name="{{str_replace(' ', '_', strtolower($field->name))}}" class="form-control" @if($field->is_required){{'required'}}@endif>{{ $field->default_value }}</textarea>
                                                    @elseif($field->type == 'checkbox')
                                                        <br>
                                                        <?php $option_values = explode(",", $field->option_value); ?>
                                                        @foreach($option_values as $value)
                                                            <label>
                                                                <input type="checkbox" name="{{str_replace(' ', '_', strtolower($field->name))}}[]" value="{{$value}}" @if($value == $field->default_value){{'checked'}}@endif @if($field->is_required){{'required'}}@endif> {{$value}}
                                                            </label>
                                                            &nbsp;
                                                        @endforeach
                                                    @elseif($field->type == 'radio_button')
                                                        <br>
                                                        <?php $option_values = explode(",", $field->option_value); ?>
                                                        @foreach($option_values as $value)
                                                            <label class="radio-inline">
                                                                <input type="radio" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$value}}" @if($value == $field->default_value){{'checked'}}@endif @if($field->is_required){{'required'}}@endif> {{$value}}
                                                            </label>
                                                            &nbsp;
                                                        @endforeach
                                                    @elseif($field->type == 'select')
                                                        <?php $option_values = explode(",", $field->option_value); ?>
                                                        <select class="form-control" name="{{str_replace(' ', '_', strtolower($field->name))}}" @if($field->is_required){{'required'}}@endif>
                                                            @foreach($option_values as $value)
                                                                <option value="{{$value}}" @if($value == $field->default_value){{'selected'}}@endif>{{$value}}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($field->type == 'multi_select')
                                                        <?php $option_values = explode(",", $field->option_value); ?>
                                                        <select class="form-control" name="{{str_replace(' ', '_', strtolower($field->name))}}[]" @if($field->is_required){{'required'}}@endif multiple>
                                                            @foreach($option_values as $value)
                                                                <option value="{{$value}}" @if($value == $field->default_value){{'selected'}}@endif>{{$value}}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($field->type == 'date_picker')
                                                        <input type="text" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control date" @if($field->is_required){{'required'}}@endif>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    <div class="col-md-12 mt-3">
                                        <label>{{trans('file.Select Product')}}</label>
                                        <div id="product-search-warning" class="alert alert-warning py-2">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            <span id="warning-message">Please select a <strong>Warehouse</strong> and <strong>Supplier</strong> first to enable product search.</span>
                                        </div>
                                        <div class="search-box input-group" id="product-search-box" style="display:none;">
                                            <button class="btn btn-secondary"><i class="fa fa-barcode"></i></button>
                                            <input type="text" name="product_code_name" id="lims_productcodeSearch" placeholder="Please type product code and select..." class="form-control" disabled />
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h5>{{trans('file.Order Table')}} *</h5>
                                        <div class="table-responsive mt-3">
                                            <table id="myTable" class="table table-hover order-list">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('file.name')}}</th>
                                                        <th>{{trans('file.Code')}}</th>
                                                        <th>{{trans('file.Quantity')}}</th>
                                                        <th class="recieved-product-qty d-none">{{trans('file.Recieved')}}</th>
                                                        <th>{{trans('file.Batch No')}}</th>
                                                        <th>{{trans('file.Expired Date')}}</th>
                                                        <th>Net Unit Cost</th>
                                                        <th>{{trans('file.Product Price')}}</th>
                                                        <th>{{trans('file.Discount')}}</th>
                                                        <th>{{trans('file.Tax')}}</th>
                                                        <th>Tax Amount</th>
                                                        <th>{{trans('file.Subtotal')}}</th>
                                                        <th><i class="dripicons-trash"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot class="tfoot active">
                                                    <th colspan="2">{{trans('file.Total')}}</th>
                                                    <th id="total-qty">0</th>
                                                    <th class="recieved-product-qty d-none"></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th id="total-discount">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
                                                    <th></th>
                                                    <th id="total-tax">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
                                                    <th id="total">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
                                                    <th><i class="dripicons-trash"></i></th>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2"><div class="form-group"><input type="hidden" name="total_qty" /></div></div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_discount" />
                                            <input type="hidden" name="order_tax_names" />
                                            <input type="hidden" name="order_tax_ids" />
                                        </div>
                                    </div>
                                    <div class="col-md-2"><div class="form-group"><input type="hidden" name="total_tax" /></div></div>
                                    <div class="col-md-2"><div class="form-group"><input type="hidden" name="total_cost" /></div></div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="item" />
                                            <input type="hidden" name="order_tax" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="grand_total" />
                                            <input type="hidden" name="paid_amount" value="{{number_format(0, $general_setting->decimal, '.', '')}}" />
                                            <input type="hidden" name="payment_status" value="1" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
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
                                            <label><strong>{{trans('file.Discount')}}</strong></label>
                                            <input type="number" name="order_discount" class="form-control" step="any" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>{{trans('file.Shipping Cost')}}</strong></label>
                                            <input type="number" name="shipping_cost" class="form-control" step="any" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{trans('file.Note')}}</label>
                                            <textarea rows="5" class="form-control" name="note"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="submit-btn">{{trans('file.submit')}}</button>
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
            <td><strong>{{trans('file.Items')}}</strong><span class="pull-right" id="item">{{number_format(0, $general_setting->decimal, '.', '')}}</span></td>
            <td><strong>{{trans('file.Total')}}</strong><span class="pull-right" id="subtotal">{{number_format(0, $general_setting->decimal, '.', '')}}</span></td>
            <td><strong>{{trans('file.Order Tax')}}</strong><span class="pull-right" id="order_tax">{{number_format(0, $general_setting->decimal, '.', '')}}</span></td>
            <td><strong>{{trans('file.Order Discount')}}</strong><span class="pull-right" id="order_discount">{{number_format(0, $general_setting->decimal, '.', '')}}</span></td>
            <td><strong>{{trans('file.Shipping Cost')}}</strong><span class="pull-right" id="shipping_cost">{{number_format(0, $general_setting->decimal, '.', '')}}</span></td>
            <td><strong>{{trans('file.grand total')}}</strong><span class="pull-right" id="grand_total">{{number_format(0, $general_setting->decimal, '.', '')}}</span></td>
        </table>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modal-header" class="modal-title"></h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row modal-element">
                            <div class="col-md-4 form-group">
                                <label>{{trans('file.Quantity')}}</label>
                                <input type="number" name="edit_qty" class="form-control" step="any">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{trans('file.Unit Discount')}}</label>
                                <input type="number" name="edit_discount" class="form-control" step="any">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Unit Cost</label>
                                <input type="number" name="edit_unit_cost" class="form-control" step="any">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{trans('file.Tax Rate')}}</label>
                                <select name="edit_tax_rate[]" class="form-control selectpicker" multiple data-live-search="true" title="Select Taxes" data-actions-box="true">
                                    @foreach($lims_tax_list as $tax)
                                        <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{trans('file.Product Unit')}}</label>
                                <select name="edit_unit" class="form-control selectpicker"></select>
                            </div>
                        </div>
                        <button type="button" name="update_btn" class="btn btn-primary">{{trans('file.update')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-user-plus"></i> Add New Supplier</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <form id="addSupplierForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Company Name</label>
                                <input type="text" name="company_name" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" class="form-control">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>City</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>State</label>
                                <input type="text" name="state" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Country</label>
                                <input type="text" name="country" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>VAT Number</label>
                                <input type="text" name="vat_number" class="form-control">
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .bootstrap-select .btn { white-space: normal; }
    .bootstrap-select .filter-option-inner-inner { overflow: hidden; text-overflow: ellipsis; }
    table.order-list th:nth-child(10),
    table.order-list td:nth-child(10) { max-width: 200px; word-wrap: break-word; }
    .net-unit-cost-input { width: 100px; text-align: right; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();

        $('#generateBillNo').on('click', function () {
            var randomNum = Math.floor(1000000000 + Math.random() * 9000000000);
            $('#bill_no').val('BNO-' + randomNum);
        });
    });
</script>

<script type="text/javascript">
    $("ul#purchase").siblings('a').attr('aria-expanded', 'true');
    $("ul#purchase").addClass("show");
    $("ul#purchase #purchase-create-menu").addClass("active");

    /* =====================================================
       GLOBAL STATE
    ===================================================== */
    var decimal = {{ $general_setting->decimal }};

    var product_code = [];
    var product_name = [];
    var product_qty = [];

    var product_cost = [];
    var product_discount = [];
    var tax_rate = [];        // comma-separated tax IDs per row
    var tax_name = [];        // comma-separated tax names per row
    var tax_method = [];
    var unit_name = [];
    var unit_operator = [];
    var unit_operation_value = [];
    var is_imei = [];

    var temp_unit_name = [];
    var temp_unit_operator = [];
    var temp_unit_operation_value = [];

    var rowindex;
    var customer_group_rate;
    var row_product_cost;

    var currency = <?php echo json_encode($currency) ?>;
    var exchangeRate = 1;
    var currencyChange = false;

    /* Tax lookup maps built straight from the tax list (id-keyed) */
    var tax_rate_all = { 0: 0 };
    var tax_name_all = { 0: 'No Tax' };
    @foreach($lims_tax_list as $t)
    tax_rate_all[{{ $t->id }}] = {{ $t->rate }};
    tax_name_all[{{ $t->id }}] = "{{ addslashes($t->name) }}";
    @endforeach

    /* =====================================================
       INIT
    ===================================================== */
    $('#currency-id').val(currency['id']);
    $('.selectpicker').selectpicker({ style: 'btn-link' });
    $('.selectpicker').selectpicker('refresh');

    $('#currency-id').change(function () {
        var rate = $(this).find(':selected').data('rate');
        $('#exchange_rate').val(rate);
        exchangeRate = rate;
        currencyChange = true;
        $("table.order-list tbody .qty").each(function (index) {
            rowindex = index;
            checkQuantity($(this).val(), true);
        });
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('select[name="status"]').on('change', function () {
        var status = $('select[name="status"]').val();
        if (status == 2) {
            $(".recieved-product-qty").removeClass("d-none");
            $(".qty").each(function () {
                rowindex = $(this).closest('tr').index();
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
            });
        }
        else if (status == 3 || status == 4) {
            $(".recieved-product-qty").addClass("d-none");
            $(".recieved").each(function () { $(this).val(0); });
        }
        else {
            $(".recieved-product-qty").addClass("d-none");
            $(".qty").each(function () {
                rowindex = $(this).closest('tr').index();
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
            });
        }
    });

    /* =====================================================
       WAREHOUSE + SUPPLIER DEPENDENT PRODUCT SEARCH
    ===================================================== */
    var lims_product_code = [];

    function checkWarehouseSupplier() {
        var warehouse_id = $('select[name="warehouse_id"]').val();
        var supplier_id = $('select[name="supplier_id"]').val();
        var $warning = $('#product-search-warning');
        var $searchBox = $('#product-search-box');
        var $searchInput = $('#lims_productcodeSearch');
        var $warningMsg = $('#warning-message');

        if (!warehouse_id && !supplier_id) {
            $warningMsg.html('Please select a <strong>Warehouse</strong> and <strong>Supplier</strong> first to enable product search.');
            $warning.show();
            $searchBox.hide();
            $searchInput.prop('disabled', true).val('');
        } else if (!warehouse_id) {
            $warningMsg.html('Please select a <strong>Warehouse</strong> first to enable product search.');
            $warning.show();
            $searchBox.hide();
            $searchInput.prop('disabled', true).val('');
        } else if (!supplier_id) {
            $warningMsg.html('Please select a <strong>Supplier</strong> first (or click the <i class="fa fa-plus"></i> button to add a new one) to enable product search.');
            $warning.show();
            $searchBox.hide();
            $searchInput.prop('disabled', true).val('');
        } else {
            $warning.hide();
            $searchBox.show();
            $searchInput.prop('disabled', false);
            loadWarehouseProducts();
        }
    }

    function loadWarehouseProducts() {
        var warehouse_id = $('select[name="warehouse_id"]').val();
        if (!warehouse_id) { lims_product_code = []; return; }

        $.ajax({
            type: 'GET',
            url: '{{ route("warehouse.products") }}',
            data: { warehouse_id: warehouse_id },
            async: false,
            success: function (data) { lims_product_code = data; },
            error: function () { lims_product_code = []; }
        });
    }

    $(document).ready(function () { checkWarehouseSupplier(); });

    $('select[name="warehouse_id"]').on('changed.bs.select', function () {
        checkWarehouseSupplier();
        if ($('table.order-list tbody tr').length > 0) {
            if (!confirm('Changing the warehouse will clear the current order table. Continue?')) {
                return;
            }
            $('table.order-list tbody').empty();
            product_cost = []; product_discount = []; tax_rate = []; tax_name = [];
            tax_method = []; unit_name = []; unit_operator = []; unit_operation_value = [];
            is_imei = [];
            calculateTotal();
        }
    });

    $('select[name="supplier_id"]').on('changed.bs.select', function () {
        checkWarehouseSupplier();
    });

    /* =====================================================
       ADD SUPPLIER VIA AJAX
    ===================================================== */
    $('#addSupplierForm').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: '{{ url("suppliers/quick-store") }}',
            data: formData,
            success: function (response) {
                var newOption = '<option value="' + response.id + '">' + response.name + ' (' + (response.company_name || '') + ')</option>';
                $('select[name="supplier_id"]').append(newOption);
                $('select[name="supplier_id"]').val(response.id);
                $('select[name="supplier_id"]').selectpicker('refresh');

                $('#addSupplierModal').modal('hide');
                $('#addSupplierForm')[0].reset();
                checkWarehouseSupplier();

                alert('Supplier "' + response.name + '" added successfully!');
            },
            error: function (xhr) {
                var errors = xhr.responseJSON;
                if (errors && errors.errors) {
                    alert('Validation Error:\n' + Object.values(errors.errors).join('\n'));
                } else {
                    alert('Error creating supplier. Please try again.');
                }
            }
        });
    });

    /* =====================================================
       AUTOCOMPLETE
    ===================================================== */
    var lims_productcodeSearch = $('#lims_productcodeSearch');

    lims_productcodeSearch.autocomplete({
        source: function (request, response) {
            var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
            response($.grep(lims_product_code, function (item) {
                return matcher.test(item);
            }));
        },
        response: function (event, ui) {
            if (ui.content.length == 1) {
                var data = ui.content[0].value;
                $(this).autocomplete("close");
                productSearch(data);
            }
        },
        select: function (event, ui) {
            var data = ui.item.value;
            productSearch(data);
        }
    });

    $('body').on('focus', ".expired-date", function () {
        $(this).datepicker({
            format: "yyyy-mm-dd",
            startDate: "<?php echo date('Y-m-d', strtotime('+ 1 days')) ?>",
            autoclose: true,
            todayHighlight: true
        });
    });

    /* =====================================================
       QUANTITY CHANGE
    ===================================================== */
    $("#myTable").on('input', '.qty', function () {
        rowindex = $(this).closest('tr').index();
        if ($(this).val() < 1 && $(this).val() != '') {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
            alert("Quantity can't be less than 1");
        }
        updateRowValues(rowindex);
    });

    /* Keep recieved synced for status 1 & 2 */
    $("#myTable").on('input', '.recieved', function () {
        rowindex = $(this).closest('tr').index();
        updateRowValues(rowindex);
    });

    /* =====================================================
       DELETE PRODUCT ROW
    ===================================================== */
    $("table.order-list tbody").on("click", ".ibtnDel", function (event) {
        rowindex = $(this).closest('tr').index();
        product_cost.splice(rowindex, 1);
        product_discount.splice(rowindex, 1);
        tax_rate.splice(rowindex, 1);
        tax_name.splice(rowindex, 1);
        tax_method.splice(rowindex, 1);
        unit_name.splice(rowindex, 1);
        unit_operator.splice(rowindex, 1);
        unit_operation_value.splice(rowindex, 1);
        is_imei.splice(rowindex, 1);
        $(this).closest("tr").remove();
        calculateTotal();
    });

    /* =====================================================
       EDIT PRODUCT (MODAL)
    ===================================================== */
    $("table.order-list").on("click", ".edit-product", function () {
        rowindex = $(this).closest('tr').index();
        $(".imei-section").remove();
        if (is_imei[rowindex]) {
            var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
            htmlText = '<div class="col-md-12 form-group imei-section"><label>IMEI or Serial Numbers</label><input type="text" name="imei_numbers" value="' + (imeiNumbers || '') + '" class="form-control imei_number" placeholder="Type imei or serial numbers and separate them by comma. Example:1001,2001" step="any"></div>';
            $("#editModal .modal-element").append(htmlText);
        }

        var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
        var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
        $('#modal-header').text(row_product_name + ' (' + row_product_code + ')');

        var qty = $(this).closest('tr').find('.qty').val();
        $('input[name="edit_qty"]').val(qty);

        var current_discount = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val();
        var unit_discount = parseFloat(current_discount) / (parseFloat(qty) || 1);
        $('input[name="edit_discount"]').val(unit_discount.toFixed(decimal));

        var net_unit_cost = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net-unit-cost-input').val();
        $('input[name="edit_unit_cost"]').val(net_unit_cost);

        var currentTaxIds = tax_rate[rowindex] ? tax_rate[rowindex].split(',') : [];
        $('select[name="edit_tax_rate[]"]').val(currentTaxIds);

        temp_unit_name = (unit_name[rowindex] || '').split(',');
        temp_unit_name.pop();
        temp_unit_operator = (unit_operator[rowindex] || '').split(',');
        temp_unit_operator.pop();
        temp_unit_operation_value = (unit_operation_value[rowindex] || '').split(',');
        temp_unit_operation_value.pop();

        $('select[name="edit_unit"]').empty();
        $.each(temp_unit_name, function (key, value) {
            $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
        });
        $('.selectpicker').selectpicker('refresh');
    });

    /* =====================================================
       UPDATE PRODUCT (MODAL CONFIRM)
    ===================================================== */
    $('button[name="update_btn"]').on("click", function () {
        if (is_imei[rowindex]) {
            var imeiNumbers = $("#editModal input[name=imei_numbers]").val();
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')')
                .find('.imei-number').val(imeiNumbers);
        }

        var edit_discount = parseFloat($('input[name="edit_discount"]').val()) || 0;
        var edit_qty = parseFloat($('input[name="edit_qty"]').val()) || 0;
        var edit_unit_cost = parseFloat($('input[name="edit_unit_cost"]').val()) || 0;

        if (edit_discount > edit_unit_cost) {
            alert('Invalid Discount Input!');
            return;
        }

        if (edit_qty < 1) {
            $('input[name="edit_qty"]').val(1);
            edit_qty = 1;
            alert("Quantity can't be less than 1");
        }

        // Selected unit from the modal (position within the row's unit list)
        var position = $('select[name="edit_unit"]').val();
        var temp_operator = temp_unit_operator[position];
        var temp_operation_value = temp_unit_operation_value[position];

        // Move the selected unit to the front of the row's unit list
        temp_unit_name.splice(position, 1);
        temp_unit_operator.splice(position, 1);
        temp_unit_operation_value.splice(position, 1);

        temp_unit_name.unshift($('select[name="edit_unit"] option:selected').text());
        temp_unit_operator.unshift(temp_operator);
        temp_unit_operation_value.unshift(temp_operation_value);

        unit_name[rowindex] = temp_unit_name.toString() + ',';
        unit_operator[rowindex] = temp_unit_operator.toString() + ',';
        unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';

        // Multiple taxes from the modal
        var selectedTaxes = $('select[name="edit_tax_rate[]"]').val() || [];
        tax_rate[rowindex] = selectedTaxes.join(',');
        var selectedTaxNames = [];
        selectedTaxes.forEach(function (taxId) {
            selectedTaxNames.push(tax_name_all[taxId] || '');
        });
        tax_name[rowindex] = selectedTaxNames.join(',');

        product_cost[rowindex] = edit_unit_cost;
        product_discount[rowindex] = edit_discount;

        // Set the row's unit select to the chosen unit NAME (values ARE names)
        var $unitSelect = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit');
        if ($unitSelect.length) $unitSelect.val(temp_unit_name[0]);

        // Sync qty + recieved so the server stocks the correct quantity
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(edit_qty);
        if ($('select[name="status"]').val() == '1' || $('select[name="status"]').val() == '2') {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(edit_qty);
        }

        updateRowValues(rowindex);
        $('#editModal').modal('hide');
    });

    /* =====================================================
       INLINE COST / DISCOUNT EDITS
    ===================================================== */
    $("table.order-list").on('input', '.net-unit-cost-input', function () {
        rowindex = $(this).closest('tr').index();
        product_cost[rowindex] = parseFloat($(this).val()) || 0;
        updateRowValues(rowindex);
    });

    $("table.order-list").on('input', '.discount-input', function () {
        rowindex = $(this).closest('tr').index();
        product_discount[rowindex] = parseFloat($(this).val()) || 0;
        updateRowValues(rowindex);
    });

    /* =====================================================
       PRODUCT SEARCH → BUILD ROW
       Controller indices: 0 name, 1 code, 2 price, 3 tax rate,
       4 tax name, 5 tax method, 6 unit names, 7 operators,
       8 op values, 9 PRODUCT ID, 10 variant id, 13 is_imei
    ===================================================== */
    function productSearch(data) {
        var code_match = data.match(/Code:\s*([^\]]+)\]/);
        var searchedCode = code_match ? code_match[1].trim() : String(data).split(" ")[0];

        if (!searchedCode) {
            alert('Please insert product code!');
            return;
        }

        // Duplicate check: if the code is already in the table, bump qty instead of adding a row
        var duplicateIndex = -1;
        var pre_qty = 0;
        $(".product-code").each(function (i) {
            if ($(this).val() == searchedCode) {
                duplicateIndex = i;
                pre_qty = $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .qty').val();
            }
        });

        // Purchase-style payload: code?qty
        var ajaxData = searchedCode + '?' + (parseFloat(pre_qty) + 1);

        $.ajax({
            type: 'GET',
            url: 'lims_product_search',
            data: {
                data: ajaxData,
                warehouse_id: $('select[name="warehouse_id"]').val()
            },
            success: function (response) {
                if (!response || response.error) {
                    alert(response && response.error ? response.error : 'Product could not be loaded.');
                    return;
                }
                var d = response;

                if (!d[9]) {
                    alert('Product data is incomplete (missing product id). Please re-add the product.');
                    return;
                }

                // Existing row → just increase its quantity
                if (duplicateIndex > -1) {
                    var $q = $('table.order-list tbody tr:nth-child(' + (duplicateIndex + 1) + ') .qty');
                    $q.val(parseFloat($q.val()) + 1);
                    updateRowValues(duplicateIndex);
                    return;
                }

                var i = $('table.order-list tbody tr').length; // new row index

                // Parallel arrays
                product_cost.push(d[2]);
                product_discount.push(0);
                tax_method.push(d[5]);
                unit_name.push(d[6]);
                unit_operator.push(d[7]);
                unit_operation_value.push(d[8]);
                is_imei.push(d[13] == 1 ? 1 : 0);

                // Resolve tax IDs for this row (uses exact tax id if the controller sends it,
                // otherwise matches by rate against the tax list)
                var taxIds = [];
                if (d[17] !== undefined && d[17] !== null && d[17] !== '' && d[17] != 0) {
                    taxIds.push(String(d[17]));
                } else if (parseFloat(d[3]) > 0) {
                    $.each(tax_rate_all, function (id, rate) {
                        if (id != 0 && parseFloat(rate) == parseFloat(d[3])) taxIds.push(String(id));
                    });
                }
                tax_rate.push(taxIds.join(','));

                var taxNames = [];
                $.each(taxIds, function (k, id) { taxNames.push(tax_name_all[id] || ''); });
                if (!taxNames.length) taxNames.push(d[4] || 'No Tax');
                tax_name.push(taxNames.join(','));

                // Unit select — VALUE IS THE UNIT NAME (what Unit::where('unit_name',...) expects)
                var uNames = d[6].split(',');  uNames.pop();
                var uOps   = d[7].split(',');  uOps.pop();
                var uVals  = d[8].split(',');  uVals.pop();

                var unitOptions = '';
                for (var u = 0; u < uNames.length; u++) {
                    unitOptions += '<option value="' + uNames[u] + '"'
                                 + ' data-operator="' + uOps[u] + '"'
                                 + ' data-value="' + uVals[u] + '">'
                                 + uNames[u] + '</option>';
                }

                var imeiField = is_imei[i]
                    ? '<input type="text" name="imei_number[]" class="form-control imei-number mt-1" placeholder="IMEI / Serial">'
                    : '';

                var status = $('select[name="status"]').val();
                var defaultRecieved = (status == 3 || status == 4) ? 0 : 1;

                var row =
                '<tr>' +
                    '<td>' +
                        '<input type="hidden" name="product_id[]" class="product-id" value="' + d[9] + '">' +
                        '<input type="hidden" class="product-variant-id" value="' + (d[10] == null ? '' : d[10]) + '">' +
                        '<input type="hidden" name="tax_rate[]" class="tax-rate-input" value="' + taxIds.join(',') + '">' +
                        '<input type="hidden" name="tax_names[]" class="tax-names-input" value="' + taxNames.join(',') + '">' +
                        '<select name="purchase_unit[]" class="purchase-unit d-none">' + unitOptions + '</select>' +
                        d[0] + imeiField +
                        ' <button type="button" class="btn btn-link edit-product"><i class="dripicons-document-edit"></i></button>' +
                    '</td>' +
                    '<td><input type="hidden" name="product_code[]" class="product-code" value="' + d[1] + '">' + d[1] + '</td>' +
                    '<td><input type="number" name="qty[]" class="form-control qty" step="any" min="1" value="1"></td>' +
                    '<td class="recieved-product-qty d-none"><input type="number" name="recieved[]" class="form-control recieved" step="any" value="' + defaultRecieved + '"></td>' +
                    '<td><input type="text" name="batch_no[]" class="form-control batch-no"></td>' +
                    '<td><input type="text" name="expired_date[]" class="form-control expired-date" readonly></td>' +
                    '<td><input type="number" name="net_unit_cost[]" class="form-control net-unit-cost-input" step="any" value="' + d[2] + '"></td>' +
                    '<td><span class="product-price-text">' + parseFloat(d[2]).toFixed(decimal) + '</span></td>' +
                    '<td><input type="number" name="discount[]" class="form-control discount-input" step="any" value="0">' +
                         '<input type="hidden" class="discount-value" value="0"></td>' +
                    '<td class="tax-name-display">No Tax</td>' +
                    '<td><span class="tax-amount-text">0.00</span><input type="hidden" name="tax[]" class="tax-amount-input" value="0"></td>' +
                    '<td><span class="subtotal-text">0.00</span><input type="hidden" name="subtotal[]" class="subtotal-input" value="0"></td>' +
                    '<td><button type="button" class="btn btn-danger btn-sm ibtnDel">Delete</button></td>' +
                '</tr>';

                $('table.order-list tbody').append(row);
                updateRowValues(i);
            },
            error: function (xhr) {
                var msg = 'Product not found in the selected warehouse.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                alert(msg);
            }
        });
    }

    /* =====================================================
       ROW CALCULATIONS
    ===================================================== */
    function checkQuantity(qty, recalc) {
        if (recalc) updateRowValues(rowindex);
    }

    function updateRowValues(rowindex) {
        var $row = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')');
        if (!$row.length) return;

        var qty          = parseFloat($row.find('.qty').val()) || 0;
        var unitCost     = parseFloat(product_cost[rowindex]) || 0;
        var unitDiscount = parseFloat(product_discount[rowindex]) || 0;

        $row.find('.net-unit-cost-input').val(unitCost);
        $row.find('.discount-input').val(unitDiscount);
        $row.find('.discount-value').val((unitDiscount * qty).toFixed(2));

        // Taxes: tax_rate[rowindex] holds comma-separated tax IDs
        var taxIds  = (tax_rate[rowindex] || '') ? String(tax_rate[rowindex]).split(',') : [];
        var taxRate = 0, taxNames = [];
        $.each(taxIds, function (k, id) {
            taxRate += parseFloat(tax_rate_all[id]) || 0;
            taxNames.push(tax_name_all[id] || '');
        });
        if (!taxNames.length) taxNames.push('No Tax');

        var netAmount = (unitCost - unitDiscount) * qty;
        var taxAmount = 0, lineTotal = netAmount;

        if (taxRate > 0) {
            if (tax_method[rowindex] == 2) {
                taxAmount = netAmount - (netAmount * 100 / (100 + taxRate)); // tax inclusive
            } else {
                taxAmount = netAmount * (taxRate / 100);                     // tax exclusive
                lineTotal = netAmount + taxAmount;
            }
        }

        $row.find('.tax-name-display').text(taxNames.join(', '));
        $row.find('.tax-amount-input').val(taxAmount.toFixed(2));
        $row.find('.tax-amount-text').text(taxAmount.toFixed(decimal));
        $row.find('.subtotal-input').val(lineTotal.toFixed(2));
        $row.find('.subtotal-text').text(lineTotal.toFixed(decimal));

        // Keep recieved consistent with the purchase status
        var status = $('select[name="status"]').val();
        if (status == 1)                       $row.find('.recieved').val(qty);
        else if (status == 3 || status == 4)   $row.find('.recieved').val(0);

        calculateTotal();
    }

    function calculateTotal() {
        var totalQty = 0, totalDiscount = 0, totalTax = 0, totalCost = 0, items = 0;

        $('table.order-list tbody tr').each(function (i) {
            var qty = parseFloat($(this).find('.qty').val()) || 0;
            totalQty      += qty;
            items++;
            totalDiscount += (parseFloat(product_discount[i]) || 0) * qty;
            totalTax      += parseFloat($(this).find('.tax-amount-input').val()) || 0;
            totalCost     += parseFloat($(this).find('.subtotal-input').val()) || 0;
        });

        var selectedOrderTaxes = $('select[name="order_tax_rate[]"]').val() || [];
        var orderTaxRate = 0;
        $.each(selectedOrderTaxes, function (k, id) {
            orderTaxRate += parseFloat(tax_rate_all[id]) || 0;
        });
        var orderDiscount = parseFloat($('input[name="order_discount"]').val()) || 0;
        var shipping      = parseFloat($('input[name="shipping_cost"]').val()) || 0;
        var orderTax      = ((totalCost - totalDiscount) * orderTaxRate / 100);
        var grandTotal    = totalCost - totalDiscount + orderTax + shipping;

        $('#total-qty').text(totalQty);
        $('#total-discount').text(totalDiscount.toFixed(decimal));
        $('#total-tax').text(totalTax.toFixed(decimal));
        $('#total').text(totalCost.toFixed(decimal));

        $('input[name="total_qty"]').val(totalQty);
        $('input[name="total_discount"]').val(totalDiscount.toFixed(2));
        $('input[name="total_tax"]').val(totalTax.toFixed(2));
        $('input[name="total_cost"]').val(totalCost.toFixed(2));
        $('input[name="item"]').val(items);
        $('input[name="order_tax"]').val(orderTax.toFixed(2));
        $('input[name="grand_total"]').val(grandTotal.toFixed(2));

        $('#item').text(items + ' (' + totalQty + ')');
        $('#subtotal').text(totalCost.toFixed(decimal));
        $('#order_tax').text(orderTax.toFixed(decimal));
        $('#order_discount').text(orderDiscount.toFixed(decimal));
        $('#shipping_cost').text(shipping.toFixed(decimal));
        $('#grand_total').text(grandTotal.toFixed(decimal));
    }

    // Live recalculation for order-level fields
    $('input[name="order_discount"], input[name="shipping_cost"]').on('input', calculateTotal);
    $(document).on('changed.bs.select', 'select[name="order_tax_rate[]"]', calculateTotal);

    /* =====================================================
       SUBMIT — SAFETY NETS
    ===================================================== */
    $('#purchase-form').on('submit', function (e) {
        var $submitBtn = $('#submit-btn');
        var bad = function (v) { return !v || v === '' || v === 'undefined' || v === 'null'; };

        /* NET 1: remove only TRUE ghosts — no product id AND no product code */
        $('table.order-list tbody tr').each(function () {
            var $tr = $(this);
            var pid = ($tr.find('input[name="product_id[]"]').val() || '').trim();
            var pcode = ($tr.find('.product-code').val() || '').trim();
            if (bad(pid) && bad(pcode)) $tr.remove();
        });

        /* NET 1b: row has a code but no id → block with a clear message (a real product!) */
        var broken = null;
        $('table.order-list tbody tr').each(function () {
            if (broken) return false;
            var $tr = $(this);
            var pid = ($tr.find('input[name="product_id[]"]').val() || '').trim();
            var pcode = ($tr.find('.product-code').val() || '').trim();
            if (!bad(pcode) && bad(pid)) {
                broken = 'Row with product "' + pcode + '" is missing its product ID. Delete that row and add the product again.';
            }
        });
        if (broken) { alert(broken); e.preventDefault(); return; }

        /* Empty-table check (after ghost cleanup) */
        if ($('table.order-list tbody tr').length === 0) {
            alert("Please insert product to order table!");
            e.preventDefault();
            return;
        }

        /* NET 2: repair unit option values still carrying operators */
        $('select[name="purchase_unit[]"]').each(function () {
            var $sel = $(this);
            if (!$sel.find('option').length) return;
            $sel.find('option').each(function () {
                var v = $(this).val();
                if (v === '*' || v === '/' || v === '') $(this).val($(this).text().trim());
            });
            var current = $sel.val();
            if (current === '*' || current === '/' || current === null || current === '') {
                var firstName = $sel.find('option:first').text().trim();
                if (firstName) $sel.val(firstName);
            }
        });

        /* NET 3: header fields */
        if (!$('select[name="warehouse_id"]').val()) { alert('Please select a Warehouse!'); e.preventDefault(); return; }
        if (!$('select[name="supplier_id"]').val())  { alert('Please select a Supplier!');  e.preventDefault(); return; }

        /* NET 4: sanitize quantities */
        $(".qty").each(function () {
            var v = parseFloat($(this).val());
            if ($(this).val() === '' || isNaN(v) || v < 1) $(this).val(1);
        });

        /* Original status/recieved validation */
        if ($('select[name="status"]').val() != 1) {
            var flag = 0;
            $(".qty").each(function () {
                var ri = $(this).closest('tr').index();
                var quantity = $(this).val();
                var recieved = $('table.order-list tbody tr:nth-child(' + (ri + 1) + ')').find('.recieved').val();
                if (quantity != recieved) { flag = 1; return false; }
            });
            if (!flag) {
                alert('Quantity and Recieved value is same! Please Change Purchase Status or Recieved value');
                e.preventDefault();
                return;
            }
            $(".batch-no, .expired-date").prop('disabled', false);
        }
        else {
            $(".batch-no, .expired-date").prop('disabled', false);
        }

        /* NET 5: double-submit lock (last — never locks on a blocked submit) */
        $submitBtn.prop('disabled', true);
    });
</script>
@endpush
