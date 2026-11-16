@extends('backend.layout.main')

@section('content')

    {{-- ════════════════════════════════════════════════════════════════════════════
        ⚡ BOOTSTRAP CRITICAL VARIABLES
        $general_setting  — fetched from GeneralSetting if not injected by the controller
        $currency          — fetched from Currency (is_active = true)
        $authUser          — role_id is NOT on users table, it's on the role_user pivot,
                             so we get the minimum role ID from the relationship
    ════════════════════════════════════════════════════════════════════════════ --}}
    @php
        // ── General Setting ──────────────────────────────────────────────
        if (!isset($general_setting) || !$general_setting) {
            if (class_exists(\App\Models\GeneralSetting::class)) {
                $general_setting = \App\Models\GeneralSetting::latest()->first();
            } elseif (class_exists(\App\GeneralSetting::class)) {
                $general_setting = \App\GeneralSetting::latest()->first();
            } else {
                // Last-resort fallback so the view doesn't crash
                $general_setting = new \stdClass();
                $general_setting->date_format    = 'd-m-Y';
                $general_setting->without_stock  = 'no';
                $general_setting->decimal         = 2;
                $general_setting->package_id      = 0;
                $general_setting->modules         = '';
            }
        }

        // ── Currency ────────────────────────────────────────────────────
        if (!isset($currency) || !$currency) {
            if (class_exists(\App\Models\Currency::class)) {
                $currency = \App\Models\Currency::where('is_active', true)->first();
            } elseif (class_exists(\App\Currency::class)) {
                $currency = \App\Currency::where('is_active', true)->first();
            }
            if (!$currency) {
                $currency = new \stdClass();
                $currency->id            = 1;
                $currency->exchange_rate = 1;
            }
        }

        // ── Auth User Role ID ───────────────────────────────────────────
        // role_id is NOT a column on users — it's on the role_user pivot.
        // Get the minimum role ID (lowest = highest privilege).
        if (!isset($authUser) || !$authUser) {
            $authUser = Auth::user()->roles->min('id') ?? 999;
        }

        // Tax lists shared by the edit modal and the page script
        $tax_name_all = ['No Tax'];
        $tax_rate_all = [0];
        foreach ($lims_tax_list as $tax) {
            $tax_name_all[] = $tax->name;
            $tax_rate_all[] = (float) $tax->rate;
        }
    @endphp

    @push('css')
        {{-- jQuery UI for the autocomplete dropdown (same as discount pages) --}}
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <style>
            @media print {
                .hidden-print {
                    display: none !important;
                }
            }

            .ui-autocomplete {
                max-height: 300px;
                overflow-y: auto;
                overflow-x: hidden;
                z-index: 9999 !important;
                font-size: 13px;
            }

            .ui-menu-item .ui-menu-item-wrapper {
                padding: 7px 10px;
            }

            .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
                background: #7c5cc4 !important;
                border-color: #7c5cc4 !important;
                color: #fff !important;
            }

            /* ===== RETURN PRODUCT ROW STYLING ===== */
            .return-product-row {
                background-color: #fff3cd !important;
                border-left: 3px solid #ff9800;
            }

            .exchange-checkbox {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
        </style>
    @endpush



    <section id="pos-layout" class="forms hidden-print">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ __('file.Sale Exchange') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ __('file.The field labels marked with are required input fields') }}.</small>
                            </p>
                            <form action="{{ route('exchange.store', @$lims_sale_data->id ?? null) }}" method="POST"
                                enctype="multipart/form-data" id="payment-form">
                                @csrf

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('file.date') }}</label>
                                                    @php
                                                        // If editing an existing sale, use its date.
                                                        // Otherwise (new exchange), default to today.
                                                        $dateValue = (!empty($lims_sale_data) && $lims_sale_data->created_at)
                                                            ? date($general_setting->date_format, strtotime($lims_sale_data->created_at->toDateString()))
                                                            : date($general_setting->date_format);
                                                    @endphp

                                                    @can('change_sale_date')
                                                        <input type="text" name="created_at" class="form-control date"
                                                            value="{{ $dateValue }}" />
                                                    @else
                                                        <input type="text" name="created_at" class="form-control date"
                                                            value="{{ $dateValue }}" readonly />
                                                    @endcan
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('file.reference') }}</label>
                                                    <p><strong>{{ @$lims_sale_data->reference_no ?? 'N/A' }}</strong></p>
                                                    <input type="hidden" name="reference_no"
                                                        value="{{ @$lims_sale_data->reference_no ?? 'N/A' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('file.customer') }} *</label>
                                                    <input type="hidden" name="customer_id_hidden"
                                                        value="{{ @$lims_sale_data->customer_id }}" />
                                                    <select required name="customer_id" class="selectpicker form-control"
                                                        data-live-search="true" id="customer_id" title="Select customer...">
                                                        @foreach ($lims_customer_list as $customer)
                                                            <option value="{{ $customer->id }}">
                                                                {{ $customer->name . ' (' . $customer->phone_number . ')' }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('file.Warehouse') }} *</label>
                                                    <input type="hidden" name="warehouse_id_hidden"
                                                        value="{{ @$lims_sale_data->warehouse_id }}" />
                                                    <select required id="warehouse_id" name="warehouse_id"
                                                        class="selectpicker form-control" data-live-search="true"
                                                        data-live-search-style="begins" title="Select warehouse...">
                                                        @foreach ($lims_warehouse_list as $warehouse)
                                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('file.Biller') }} *</label>
                                                    <input type="hidden" name="biller_id_hidden"
                                                        value="{{ @$lims_sale_data->biller_id }}" />
                                                    <select required name="biller_id" class="selectpicker form-control"
                                                        data-live-search="true" data-live-search-style="begins"
                                                        title="Select Biller...">
                                                        @foreach ($lims_biller_list as $biller)
                                                            <option value="{{ $biller->id }}">
                                                                {{ $biller->name . ' (' . $biller->company_name . ')' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">{{ __('file.Account') }} *</label>
                                                <select class="form-control selectpicker" name="account_id">
                                                    @foreach ($lims_account_list as $account)
                                                        <option value="{{ $account->id }}"
                                                            {{ $account->is_default ? 'selected' : '' }}>
                                                            {{ $account->name }} [{{ $account->account_no }}]
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <h3>{{ __('file.search_by_reference_no_or_Product') }}</h3>
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <label>Search by Reference Number</label>

                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="sale_product_search"
                                                        name="reference_no"
                                                        value="{{ request('reference_no', $lims_sale_data->reference_no ?? '') }}"
                                                        placeholder="Enter reference number">

                                                    <a href="#" class="btn btn-primary" id="saleSearchBtn">
                                                        <i class="ti ti-search"></i> Search
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="col-md-8">

                                                <label>{{ __('file.Return Select Product') }}</label>

                                                <div class="search-box form-group mb-2">
                                                    <div class="input-group pos">

                                                        <input style="border: 1px solid #7c5cc4;" type="text"
                                                            id="sale-product-search-input"
                                                            placeholder="Scan/Search product by name/code/IMEI"
                                                            class="form-control" autocomplete="off" />

                                                        <button type="button" class="btn btn-primary"
                                                            onclick="barcodeSale()">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor" class="bi bi-upc"
                                                                viewBox="0 0 16 16">
                                                                <path
                                                                    d="M3 4.5a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0z" />
                                                            </svg>
                                                        </button>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <input type="hidden" name="sale_id"
                                                            value="{{ @$lims_sale_data->id }}">
                                                        <h5>{{ __('file.Order Product') }} *</h5>
                                                        <div class="table-responsive mt-3">
                                                            <table id="sale-product-table"
                                                                class="table table-hover return-order-list">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('file.name') }}</th>
                                                                        <th>{{ __('file.Quantity') }}</th>
                                                                        <th>{{ __('file.Net Unit Price') }}</th>
                                                                        <th>{{ __('file.Discount') }}</th>
                                                                        <th>{{ __('file.Tax') }}</th>
                                                                        <th>{{ __('file.Subtotal') }}</th>
                                                                        <th><i class="ti ti-trash"></i></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @include('backend.sale-exchange.partials.sale-products')
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-12">

                                                <label>{{ __('file.New Select Product') }}</label>

                                                <div class="search-box form-group mb-2">
                                                    <div class="input-group pos">
                                                        <input style="border: 1px solid #7c5cc4;" type="text"
                                                            id="product-search-input"
                                                            placeholder="Scan/Search product by name/code/IMEI"
                                                            class="form-control" autocomplete="off" />
                                                        <button type="button" class="btn btn-primary"
                                                            onclick="barcodeNew()">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor" class="bi bi-upc"
                                                                viewBox="0 0 16 16">
                                                                <path
                                                                    d="M3 4.5a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive mt-3">
                                                    <table id="myTable" class="table table-hover order-list">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('file.name') }}</th>
                                                                <th>{{ __('file.Quantity') }}</th>
                                                                <th>{{ __('file.Net Unit Price') }}</th>
                                                                <th>{{ __('file.Discount') }}</th>
                                                                <th>{{ __('file.Tax') }}</th>
                                                                <th>{{ __('file.Subtotal') }}</th>
                                                                <th><i class="ti ti-trash"></i></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-group"><input type="hidden" name="total_qty" /></div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group"><input type="hidden" name="total_discount" /></div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group"><input type="hidden" name="total_tax" /></div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group"><input type="hidden" name="total_price" /></div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <input type="hidden" name="item" />
                                                    <input type="hidden" name="order_tax" />
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                @if (@$lims_sale_data->coupon_id)
                                                    @php
                                                        $coupon_data = DB::table('coupons')->find(
                                                            @$lims_sale_data->coupon_id,
                                                        );
                                                    @endphp
                                                    <input type="hidden" name="coupon_active" value="1" />
                                                    <input type="hidden" name="coupon_type" value="{{ $coupon_data->type }}" />
                                                    <input type="hidden" name="coupon_amount" value="{{ $coupon_data->amount }}" />
                                                    <input type="hidden" name="coupon_minimum_amount" value="{{ $coupon_data->minimum_amount }}" />
                                                    <input type="hidden" name="coupon_discount" value="{{ @$lims_sale_data->coupon_discount }}">
                                                @else
                                                    <input type="hidden" name="coupon_active" value="0" />
                                                @endif
                                                <div class="form-group">
                                                    <input type="hidden" name="grand_total" class="exchage_grand_total" />
                                                    <input type="hidden" name="change_sale_status" value="0">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('file.Order Tax') }}</label>
                                                    <select class="form-control" name="order_tax_rate">
                                                        <option value="0">No Tax</option>
                                                        @foreach ($lims_tax_list as $tax)
                                                            <option value="{{ $tax->rate }}">{{ $tax->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('file.Attach Document') }}</label>
                                                    <i class="ti ti-info-circle" data-toggle="tooltip"
                                                        title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                                    <input type="file" name="document" class="form-control" />
                                                    @if ($errors->has('extension'))
                                                        <span><strong>{{ $errors->first('extension') }}</strong></span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('file.Return Discount') }}</label>
                                                    <input type="number" name="total_sale_discount" id="discount_value"
                                                        class="form-control"
                                                        value="{{ @$lims_sale_data->order_discount ?? 0 }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('file.Return Note') }}</label>
                                                    <textarea rows="5" class="form-control" name="return_note"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('file.Staff Note') }}</label>
                                                    <textarea rows="5" class="form-control" name="staff_note"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Exchange Calculation Section -->
                                        <table class="table table-bordered table-condensed exchange-totals">
                                            <tbody>
                                                <tr style="background-color: #e3f2fd;">
                                                    <td colspan="6"
                                                        style="text-align: center; font-weight: bold; padding: 10px; color: #1976d2;">
                                                        EXCHANGE CALCULATION
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Exchange Value</strong>
                                                        <span class="pull-right" id="exchange-value"
                                                            style="color: #d32f2f; font-weight: bold;">0.00</span>
                                                    </td>
                                                    <td><strong>New Products Total</strong>
                                                        <span class="pull-right" id="new-products-total"
                                                            style="color: #388e3c; font-weight: bold;">0.00</span>
                                                    </td>
                                                    <td><strong>New Shipping</strong>
                                                        <span class="pull-right" id="exchange-shipping"
                                                            style="color: #f57c00; font-weight: bold;">0.00</span>
                                                    </td>
                                                    <td><strong>New Grand Total</strong>
                                                        <span class="pull-right" id="new-grand-total"
                                                            style="color: #1565c0; font-weight: bold;">0.00</span>
                                                    </td>
                                                </tr>
                                                <tr style="background-color: #fff3e0;">
                                                    <td colspan="2"
                                                        style="text-align: right; font-size: 16px; font-weight: bold;">
                                                        BALANCE (Customer Payment):
                                                    </td>
                                                    <td colspan="4">
                                                        <input id="amount" type="hidden" value="0"
                                                            name="amount" />
                                                        <input id="payment-type" type="hidden" value="0"
                                                            name="payment_type" />
                                                        <span id="balance-display"
                                                            style="font-size: 18px; font-weight: bold; color: #1565c0;">0.00</span>
                                                        <span id="balance-status"
                                                            style="margin-left: 20px; font-weight: bold; font-size: 14px;"></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <!-- QR Scanner -->
                                        <div
                                            style="width:100%;max-width:350px;position:fixed;top:5%;left:50%;transform:translateX(-50%);z-index:999">
                                            <button type="button" class="btn btn-danger" id="closeScannerBtn"
                                                style="display:none">X</button>
                                            <div id="reader" style="width:100%;"></div>
                                        </div>

                                        <!-- Hidden Inputs for Return/Exchange Data -->
                                        <input type="hidden" id="return-products-data" name="return_products_data"
                                            value="">
                                        <input type="hidden" id="exchange-products-data" name="exchange_products_data"
                                            value="">

                                        <div class="form-group">
                                            <input type="hidden" name="draft" value="0" />
                                            <button id="submit-button" type="submit"
                                                class="btn btn-primary">{{ __('file.submit') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modal_header" class="modal-title"></h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true"><i class="ti ti-x"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="row modal-element">
                                <div class="col-md-4 form-group">
                                    <label>{{ __('file.Quantity') }}</label>
                                    <input type="number" step="any" name="edit_qty" class="form-control numkey">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>{{ __('file.Unit Discount') }}</label>
                                    <input type="number" name="edit_discount" class="form-control numkey">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('file.Price Option') }}</label>
                                        <div class="input-group">
                                            <select class="form-control" name="price_option" id="price_option">
                                                <option value="default">Default Price</option>
                                                <option value="custom">Custom Price</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>{{ __('file.Unit Price') }}</label>
                                    <input type="number" name="edit_unit_price" class="form-control numkey"
                                        step="any">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>{{ __('file.Tax Rate') }}</label>
                                    <select name="edit_tax_rate" class="form-control">
                                        @foreach (($tax_name_all ?? []) as $key => $name)
                                            <option value="{{ $key }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="edit_unit" class="col-md-4 form-group">
                                    <label>{{ __('file.Product Unit') }}</label>
                                    <select name="edit_unit" class="form-control"></select>
                                </div>
                            </div>
                            <button type="button" name="update_btn"
                                class="btn btn-primary">{{ __('file.update') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="print-layout"></section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    {{-- jQuery UI for autocomplete (same dropdown widget as the discount pages) --}}
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        /* =====================================================================
           Sale Exchange — page logic (rewritten, row-driven)
           - Every table row owns its own hidden inputs & data (no parallel
             arrays), so server-rendered "Order Product" rows work correctly.
           - "Return Select Product" & "New Select Product" use jQuery UI
             autocomplete (dropdown from the first character, narrows as
             you type) hitting the same exchange/product-search endpoint.
           - Edit modal (Order Product click) fully functional, including the
             Price Option selector (Default / Wholesale / Custom).
           ===================================================================== */

        var decimal = {{ $general_setting->decimal ?? 2 }};
        var without_stock = '{{ $general_setting->without_stock ?? 'no' }}';
        var authUser = {{ $authUser ?? 999 }};
        var currency = @json($currency ?? null);
        if (!currency || typeof currency !== 'object') { currency = { id: 1, exchange_rate: 1 }; }
        if (!currency.exchange_rate) { currency.exchange_rate = 1; }
        var customer_group_rate = 0;
        var currentEditRow = null;
        var rowindex = 0; /* kept for compatibility */

        var tax_name_all = @json($tax_name_all ?? []) || [];
        var tax_rate_all = @json($tax_rate_all ?? []) || [];

        var MSG_OUT_OF_STOCK = @json(__('file.Out of Stock!'));
        var MSG_IN_STOCK = @json(__('file.In Stock'));
        var MSG_EMBEDDED = @json(__('file.This product has been added using the weight scale machine.'));

        /* ---------------- helpers ---------------- */
        function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }
        function fmt(v) { return num(v).toFixed(decimal); }
        function parseFormattedNumber(str) { if (!str) return 0; return parseFloat(String(str).replace(/,/g, '').trim()) || 0; }
        function parseCsv(list) {
            return String(list === null || list === undefined ? '' : list)
                .split(',').map(function (s) { return s.trim(); }).filter(function (s) { return s !== ''; });
        }
        function escapeHtml(s) { return $('<div/>').text(s === null || s === undefined ? '' : String(s)).html(); }
        function escapeAttr(s) { return escapeHtml(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

        /* ---------------- unit helpers ---------------- */
        function rowUnitData($row) {
            var names = parseCsv($row.find('.sale-unit-names').val());
            var operators = parseCsv($row.find('.sale-unit-operator').val());
            var values = parseCsv($row.find('.sale-unit-operation-value').val());
            var current = $.trim($row.find('.sale-unit').val() || '');
            var idx = names.indexOf(current);
            if (idx < 0) idx = 0;
            return {
                names: names, operators: operators, values: values, index: idx,
                operator: operators[idx] || '*', value: num(values[idx]) || 1
            };
        }
        function unitDisplayPrice(basePrice, unit) {
            return unit.operator === '/' ? basePrice / unit.value : basePrice * unit.value;
        }
        function unitToBasePrice(unitPrice, unit) {
            return unit.operator === '/' ? unitPrice * unit.value : unitPrice / unit.value;
        }

        /* ---------------- row calculations ---------------- */
        function updateRowCalc($row) {
            if (!$row || !$row.length) return;
            var qty = num($row.find('.qty').val());
            var basePrice = num($row.data('base-price'));
            var unit = rowUnitData($row);
            var unitPrice = unitDisplayPrice(basePrice, unit);
            var discountPerUnit = num($row.data('discount-per-unit'));
            var rate = num($row.find('.tax-rate').val());
            var method = String($row.find('.tax-method').val() || '1');
            var netUnitPrice, tax, subTotal;

            if (method === '2') { /* tax inclusive */
                var grossUnit = unitPrice - discountPerUnit;
                netUnitPrice = (100 / (100 + rate)) * grossUnit;
                tax = (grossUnit - netUnitPrice) * qty;
                subTotal = grossUnit * qty;
            } else { /* tax exclusive */
                netUnitPrice = unitPrice - discountPerUnit;
                tax = netUnitPrice * qty * (rate / 100);
                subTotal = netUnitPrice * qty + tax;
            }

            $row.find('.net_unit_price').val(fmt(netUnitPrice));
            $row.find('.discount-value').val(fmt(discountPerUnit * qty));
            $row.find('.tax-value').val(fmt(tax));
            $row.find('.tax-rate').val(fmt(rate));
            $row.find('.product-price').text(fmt(unitPrice));
            $row.find('.discount').text(fmt(discountPerUnit));
            $row.find('.tax').text(fmt(tax));
            $row.find('.sub-total').text(fmt(subTotal));
            $row.find('.subtotal-value').val(fmt(subTotal));
        }

        function isReturnRow($row) {
            return $row.closest('table').hasClass('return-order-list');
        }

        function pendingReturnBaseQty($row) {
            var pid = String($row.find('.product-id').val() || '');
            var pcode = String($row.find('.product-code').val() || '');
            var total = 0;
            $('table.return-order-list tbody tr').each(function () {
                var $r = $(this);
                if (!$r.find('.exchange-checkbox').is(':checked')) return;
                if (String($r.find('.product-id').val() || '') === pid ||
                    String($r.find('.product-code').val() || '') === pcode) {
                    var q = num($r.find('.qty').val());
                    var unit = rowUnitData($r);
                    total += unit.operator === '/' ? q / unit.value : q * unit.value;
                }
            });
            return total;
        }

        /* quantity clamping / stock guard for one row */
        function enforceQuantity($row) {
            if (!$row || !$row.length) return;
            var qty = num($row.find('.qty').val());
            if (qty <= 0) qty = 1;
            var maxAttr = $row.find('.qty').attr('max');
            var imeiVal = $.trim($row.find('.imei-number').val() || '');

            /* IMEI rows: qty always equals the number of serials on the row */
            if (imeiVal) {
                $row.find('.qty').val(imeiVal.split(',').filter(Boolean).length);
                return;
            }

            if (isReturnRow($row)) {
                if (maxAttr !== undefined && maxAttr !== '' && qty > num(maxAttr)) {
                    alert('Quantity exceeds the returnable quantity (' + maxAttr + ')!');
                    qty = num(maxAttr) || 1;
                }
            } else if (without_stock === 'no') {
                var ptype = $.trim($row.find('.product_type').val() || 'standard');
                if (ptype === 'standard' || ptype === 'combo') {
                    var unit = rowUnitData($row);
                    var baseQty = unit.operator === '/' ? qty / unit.value : qty * unit.value;
                    var available = num(maxAttr) + pendingReturnBaseQty($row);
                    if (baseQty > available + 0.00001) {
                        alert('Quantity exceeds stock quantity!');
                        var maxUnitQty = unit.operator === '/' ? available * unit.value : available / unit.value;
                        qty = Math.max(Math.floor(maxUnitQty * 10000) / 10000, 1);
                    }
                }
            }
            $row.find('.qty').val(qty);
        }

        /* kept for backward compatibility (e.g. other inline handlers) */
        function checkQuantity(value, flag, tableSelector) {
            var $row = currentEditRow && currentEditRow.length
                ? currentEditRow
                : $(tableSelector || 'table.order-list').find('tbody tr').first();
            if ($row.length) {
                $row.find('.qty').val(num(value));
                enforceQuantity($row);
                updateRowCalc($row);
                recalculateAll();
            }
            return true;
        }

        /* Server-rendered rows (Order Product table) carry their data in hidden
           inputs — mirror it into the row's jQuery data once so the edit modal
           and recalculation work on them exactly like on search-added rows. */
        function initRowData($row) {
            if (!$row || !$row.length || $row.data('base-price') !== undefined) return;
            var unit = rowUnitData($row);
            var netUnit = parseFormattedNumber($row.find('.net_unit_price').val());
            var qty = num($row.find('.qty').val()) || 1;
            var lineDiscount = parseFormattedNumber($row.find('.discount-value').val());
            $row.data('base-price', unitToBasePrice(netUnit, unit));
            $row.data('wholesale-price', 0);
            $row.data('discount-per-unit', lineDiscount / qty);
        }

        /* ---------------- exchange totals ---------------- */
        var exchangeValue = 0, newProductsTotal = 0;

        function recalculateAll() {
            var returnedTotal = 0, newTotal = 0;

            $('table.return-order-list tbody tr').each(function () {
                var $row = $(this);
                if ($row.find('.exchange-checkbox').is(':checked')) {
                    returnedTotal += parseFormattedNumber($row.find('.sub-total').text());
                }
            });
            $('table.order-list tbody tr').each(function () {
                newTotal += parseFormattedNumber($(this).find('.sub-total').text());
            });

            var shippingCost = parseFormattedNumber($('input[name="shipping_cost"]').val());
            var returnDiscount = parseFormattedNumber($('input[name="total_sale_discount"]').val());

            exchangeValue = returnedTotal;
            newProductsTotal = newTotal;

            $('#exchange-value').text(fmt(returnedTotal));
            $('#new-products-total').text(fmt(newTotal));
            $('#exchange-shipping').text(fmt(shippingCost));

            var grandTotal = newTotal + shippingCost;
            $('#new-grand-total').text(fmt(grandTotal));
            $('.exchage_grand_total').val(fmt(grandTotal));

            var balance = grandTotal - (returnedTotal - returnDiscount);
            var $balanceDisplay = $('#balance-display'),
                $balanceStatus = $('#balance-status'),
                $paymentType = $('#payment-type'),
                $balanceAmount = $('#amount');

            $balanceDisplay.text(fmt(Math.abs(balance)));
            $balanceAmount.val(fmt(Math.abs(balance)));

            if (balance > 0.009) {
                $balanceDisplay.css('color', '#d32f2f');
                $balanceStatus.text('(Customer Pays)').css('color', '#d32f2f');
                $paymentType.val('receive');
            } else if (balance < -0.009) {
                $balanceDisplay.css('color', '#388e3c');
                $balanceStatus.text('(Refund to Customer)').css('color', '#388e3c');
                $paymentType.val('pay');
            } else {
                $balanceDisplay.css('color', '#1565c0');
                $balanceStatus.text('(No Balance)').css('color', '#1565c0');
                $paymentType.val('');
                $balanceAmount.val('0');
            }

            /* hidden totals (single set of inputs) */
            var totalQty = 0;
            $("table.order-list tbody .qty, table.return-order-list tbody .qty").each(function () {
                totalQty += num($(this).val());
            });
            var totalDiscount = 0, totalTax = 0;
            $(".discount-value").each(function () { totalDiscount += num($(this).val()); });
            $(".tax-value").each(function () { totalTax += num($(this).val()); });

            $('input[name="total_qty"]').val(totalQty);
            $('input[name="total_discount"]').val(fmt(totalDiscount));
            $('input[name="total_tax"]').val(fmt(totalTax));
            $('input[name="total_price"]').val(fmt(returnedTotal + newTotal));
            $('input[name="item"]').val($('table.order-list tbody tr').length + ' (' + totalQty + ')');

            var orderTaxRate = num($('select[name="order_tax_rate"]').val());
            $('input[name="order_tax"]').val(fmt(newTotal * (orderTaxRate / 100)));
        }

        /* ---------------- product search (autocomplete dropdowns) ---------------- */
        function isStockManaged(p) {
            return p && (p.type === 'standard' || p.type === 'combo') && !num(p.is_imei);
        }

        /* dropdown row text: name (code) | price | stock | IMEI | batch */
        function suggestionLabel(p) {
            var label = p.name + ' (' + p.code + ')  |  ' + num(p.price);
            if (authUser <= 2) label += '  |  ' + p.qty + ' ' + MSG_IN_STOCK;
            if (p.imei_number) label += '  |  IMEI: ' + p.imei_number;
            if (p.batch_no) label += '  |  Batch: ' + p.batch_no;
            if (p.expired_date) label += '  |  ' + p.expired_date;
            return label;
        }

        /* resolve the warehouse (auto-select first if the operator hasn't) */
        function resolveWarehouse() {
            var $wh = $('#warehouse_id');
            var warehouse = $wh.val();
            if (!warehouse) {
                $wh.find('option').each(function () {
                    if (!warehouse && $(this).attr('value')) {
                        warehouse = $(this).attr('value');
                    }
                });
                if (warehouse) {
                    $wh.val(warehouse);
                    if ($.fn.selectpicker) { $wh.selectpicker('refresh'); }
                }
            }
            return warehouse;
        }

        /* Fetch suggestions. `response` is jQuery UI's callback; when
           autoAddSingle is true (barcode) a single hit is added immediately. */
        function fetchSuggestions(term, kind, autoAddSingle, response) {
            term = String(term || '').trim();
            if (!term) { if (response) response([]); return; }

            var warehouse = resolveWarehouse();
            if (!warehouse) {
                alert('Please add a warehouse first — product search needs it.');
                if (response) response([]);
                return;
            }

            $.ajax({
                url: '{{ url('exchange/product-search') }}',
                type: 'GET',
                data: { warehouse_id: warehouse, search: term }
            }).done(function (list) {
                var items = $.map(list || [], function (p) {
                    return $.extend({}, p, {
                        label: suggestionLabel(p),
                        value: p.name + ' (' + p.code + ')'
                    });
                });
                if (autoAddSingle && items.length === 1) {
                    handleSuggestionSelect(items[0], kind);
                    if (response) response([]);
                } else if (response) {
                    response(items);
                }
            }).fail(function () {
                if (response) response([]);
            });
        }

        /* picking an item from the dropdown */
        function handleSuggestionSelect(item, kind) {
            if (kind === 'new' && without_stock === 'no' && isStockManaged(item) && num(item.qty) <= 0) {
                alert(MSG_OUT_OF_STOCK);
                return;
            }
            addRowFromProduct(item, kind);
        }

        function wireAutocomplete($input, kind) {
            $input.autocomplete({
                minLength: 1,
                delay: 200,
                source: function (request, response) {
                    fetchSuggestions(request.term, kind, false, response);
                },
                select: function (event, ui) {
                    handleSuggestionSelect(ui.item, kind);
                    $(this).val(''); /* ready for the next search */
                    return false;
                }
            });
            /* clicking back into a non-empty box re-opens the dropdown */
            $input.on('focus', function () {
                var value = $(this).val().trim();
                if (value) $(this).autocomplete('search', value);
            });
        }

        /* ---------------- add product rows ---------------- */
        function addRowFromProduct(p, kind) {
            if (!p) return;
            if (num(p.is_embeded) === 1) {
                alert(MSG_EMBEDDED);
                return;
            }

            var code = String(p.code || '').trim();
            var $table = kind === 'return' ? $('table.return-order-list') : $('table.order-list');
            var isImeiRow = num(p.is_imei) === 1 && p.imei_number && String(p.imei_number) !== 'null' &&
                String(p.imei_number).trim() !== '';

            /* duplicate handling */
            var $existing = null, duplicateImei = false;
            $table.find('tbody tr').each(function () {
                var $r = $(this);
                if ($.trim($r.find('.product-code').val()) !== code) return;
                var rowImei = $.trim($r.find('.imei-number').val() || '');
                if (isImeiRow) {
                    if (rowImei.split(',').indexOf(String(p.imei_number).trim()) >= 0) duplicateImei = true;
                } else if (!rowImei && !$existing) {
                    $existing = $r;
                }
            });
            if (duplicateImei) {
                alert('Same imei or serial number is not allowed!');
                return;
            }
            if ($existing) {
                if (kind === 'return') {
                    alert('This product is already in the return list.');
                    return;
                }
                $existing.find('.qty').val(num($existing.find('.qty').val()) + 1);
                enforceQuantity($existing);
                updateRowCalc($existing);
                recalculateAll();
                return;
            }

            var rate = num(currency.exchange_rate);
            var groupFactor = 1 + num(customer_group_rate);
            var basePrice = num(p.price) * rate * groupFactor;
            var wholesale = num(p.wholesale_price) * rate * groupFactor;

            var unitNames = parseCsv(p.unit_names);
            var unitOperators = parseCsv(p.unit_operators);
            var unitValues = parseCsv(p.unit_operation_values);
            var unitName = unitNames[0] || 'n/a';
            var stockQty = num(p.qty);

            var $tr = $('<tr class="exchange-product-row">');
            var cols = '<td class="product-title">';

            if (authUser > 2) {
                cols += '<strong>' + escapeHtml(p.name) + '</strong><br><span>' + escapeHtml(code) + '</span>';
            } else {
                cols += '<strong class="edit-product btn btn-link pl-0 pr-0" data-toggle="modal" data-target="#editModal">' +
                    escapeHtml(p.name) + ' <i class="ti ti-edit"></i></strong><br><span>' + escapeHtml(code) + '</span>';
            }
            if (authUser <= 2 && (p.type === 'standard' || p.type === 'combo') && !isImeiRow) {
                cols += ' <span class="text-muted">| ' + MSG_IN_STOCK + ': <span class="in-stock">' + stockQty + '</span></span>';
            }
            if (p.product_batch_id) {
                cols += '<br><small class="text-muted">Batch: ' + escapeHtml(p.batch_no || 'N/A') + '</small>';
            }

            cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + escapeAttr(code) + '">';
            cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + escapeAttr(p.id) + '">';
            cols += '<input type="hidden" class="product_type" name="product_type[]" value="' + escapeAttr(p.type || 'standard') + '">';
            cols += '<input type="hidden" class="product-batch-id" name="product_batch_id[]" value="' + escapeAttr(p.product_batch_id || '') + '">';
            cols += '</td>';

            /* qty cell */
            cols += '<td><div class="input-group">';
            if (!isImeiRow) {
                cols += '<span class="input-group-btn"><button type="button" class="btn btn-default minus mr-1" style="padding:5px 8px"><i class="ti ti-minus"></i></button></span>';
            }
            cols += '<input type="number" name="qty[]" class="form-control qty numkey input-number" style="font-size:13px;max-width:50px;padding:0 0;text-align:center" step="any" value="1" max="' + stockQty + '" required>';
            if (!isImeiRow) {
                cols += '<span class="input-group-btn"><button type="button" class="btn btn-default plus ml-1" style="padding:5px 8px"><i class="ti ti-plus"></i></button></span>';
            }
            cols += '</div></td>';

            cols += '<td class="product-price">' + fmt(basePrice) + '</td>';
            cols += '<td class="discount">0.00</td>';
            cols += '<td class="tax">0.00</td>';
            cols += '<td class="sub-total">0.00</td>';

            if (kind === 'return') {
                cols += '<td class="is-exchange text-center"><input type="checkbox" name="is_exchange[]" class="exchange-checkbox" checked value="' + escapeAttr(code) + '" style="width:18px;height:18px;cursor:pointer" title="Check to include in exchange"></td>';
            } else {
                cols += '<td><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="ti ti-trash"></i></button></td>';
            }

            cols += '<input type="hidden" class="product_price" value="' + basePrice + '">';
            cols += '<input type="hidden" class="sale-unit" name="sale_unit[]" value="' + escapeAttr(unitName) + '">';
            cols += '<input type="hidden" class="sale-unit-names" value="' + escapeAttr(unitNames.join(',')) + '">';
            cols += '<input type="hidden" class="sale-unit-operator" value="' + escapeAttr(unitOperators.join(',')) + '">';
            cols += '<input type="hidden" class="sale-unit-operation-value" value="' + escapeAttr(unitValues.join(',')) + '">';
            cols += '<input type="hidden" class="net_unit_price" name="net_unit_price[]" value="' + fmt(basePrice) + '">';
            cols += '<input type="hidden" class="discount-value" name="discount[]" value="0.00">';
            cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + num(p.tax_rate) + '">';
            cols += '<input type="hidden" class="tax-name" value="' + escapeAttr(p.tax_name || 'No Tax') + '">';
            cols += '<input type="hidden" class="tax-method" value="' + escapeAttr(p.tax_method || 1) + '">';
            cols += '<input type="hidden" class="tax-value" name="tax[]" value="0.00">';
            cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" value="0.00">';
            cols += '<input type="hidden" name="type[]" value="' + kind + '">';
            cols += '<input type="hidden" class="imei-number" name="imei_number[]" value="' + escapeAttr(isImeiRow ? p.imei_number : '') + '">';

            $tr.html(cols);
            $tr.data('base-price', basePrice)
                .data('wholesale-price', wholesale)
                .data('discount-per-unit', 0);

            $table.find('tbody').append($tr);

            if (isImeiRow) {
                $tr.find('.qty').val(1).prop('readonly', true);
            }
            updateRowCalc($tr);
            if (kind === 'new') enforceQuantity($tr);
            recalculateAll();
        }

        /* ---------------- edit modal (Order Product click) ---------------- */
        function modalUnit() {
            var $sel = $('select[name="edit_unit"]');
            if (!$sel.prop('disabled') && $sel.find(':selected').length) {
                var $opt = $sel.find(':selected');
                return { operator: $opt.data('operator') || '*', value: num($opt.data('operation-value')) || 1 };
            }
            return rowUnitData(currentEditRow);
        }

        function applyPriceOption() {
            if (!currentEditRow || !currentEditRow.length) return;
            var opt = $('#price_option').val();
            var $price = $('input[name="edit_unit_price"]');
            if (opt === 'custom') {
                $price.prop('readonly', false);
                return;
            }
            var baseVal = opt === 'wholesale'
                ? num(currentEditRow.data('wholesale-price'))
                : num(currentEditRow.data('base-price'));
            $price.val(fmt(unitDisplayPrice(baseVal, modalUnit()))).prop('readonly', true);
        }

        function renderImeiSection($row) {
            $(".imei-section").remove();
            var imeiVal = $.trim($row.find('.imei-number').val() || '');
            if (!imeiVal) return;
            var imeis = imeiVal.split(',').filter(Boolean);
            var html = '<div class="col-md-8 form-group imei-section"><label>IMEI or Serial Numbers</label><div class="table-responsive"><table id="imei-table" class="table table-hover"><tbody>';
            $.each(imeis, function (i, imei) {
                html += '<tr><td><input type="text" class="form-control imei-numbers" name="imei_numbers[]" value="' + escapeAttr(imei) + '" /></td>' +
                    '<td><button type="button" class="imei-del btn btn-sm btn-danger">X</button></td></tr>';
            });
            html += '</tbody></table></div></div>';
            $("#editModal .modal-element").append(html);
        }

        function openEditModal() {
            var $row = currentEditRow;
            if (!$row || !$row.length) return;
            var isReturn = isReturnRow($row);

            var productName = $.trim($row.find('.product-title strong').first().clone().children().remove().end().text());
            var productCode = $.trim($row.find('.product-code').val() || '');
            $('#modal_header').text(productName + ' [' + productCode + ']');

            $('input[name="edit_qty"]').val($row.find('.qty').val());
            $('input[name="edit_discount"]').val(fmt($row.data('discount-per-unit')));

            /* Price Option — rebuilt for every row, always selectable */
            var unit = rowUnitData($row);
            var base = num($row.data('base-price'));
            var $po = $('#price_option').empty();
            $po.append($('<option/>', { value: 'default', text: 'Default Price (' + fmt(unitDisplayPrice(base, unit)) + ')' }));
            var wholesale = num($row.data('wholesale-price'));
            if (wholesale > 0) {
                $po.append($('<option/>', { value: 'wholesale', text: 'Wholesale Price (' + fmt(unitDisplayPrice(wholesale, unit)) + ')' }));
            }
            $po.append($('<option/>', { value: 'custom', text: 'Custom Price' }));
            $po.val('default');

            $('input[name="edit_unit_price"]').val(fmt(unitDisplayPrice(base, unit))).prop('readonly', true);

            /* tax rate */
            var taxName = $.trim($row.find('.tax-name').val() || 'No Tax');
            var tIdx = tax_name_all.indexOf(taxName);
            $('select[name="edit_tax_rate"]').val(tIdx >= 0 ? tIdx : 0);

            /* unit selector — locked for return rows (qty max is in the sale unit) */
            var $unitSel = $('select[name="edit_unit"]').empty();
            if (unit.names.length > 1 && !isReturn) {
                $.each(unit.names, function (i, n) {
                    $unitSel.append('<option data-operator="' + escapeAttr(unit.operators[i] || '*') +
                        '" data-operation-value="' + escapeAttr(unit.values[i] || 1) + '" value="' + i + '">' +
                        escapeHtml(n) + '</option>');
                });
                $unitSel.val(String(unit.index)).prop('disabled', false);
            } else {
                $unitSel.append('<option value="0">' +
                    escapeHtml(unit.names[unit.index] || $.trim($row.find('.sale-unit').val()) || 'n/a') +
                    '</option>');
                $unitSel.prop('disabled', true);
            }
            $("#edit_unit").show();

            renderImeiSection($row);
            $('#editModal').modal('show');
        }

        $(document).on('click', '.edit-product', function (e) {
            e.preventDefault();
            currentEditRow = $(this).closest('tr');
            rowindex = currentEditRow.index();
            openEditModal();
        });

        $(document).on('change', '#price_option', applyPriceOption);
        $(document).on('change', 'select[name="edit_unit"]', function () {
            if (currentEditRow && $('#price_option').val() !== 'custom') applyPriceOption();
        });

        $(document).on('click', '#imei-table .imei-del', function () {
            if (!currentEditRow || !currentEditRow.length) return;
            var target = $(this).closest('tr').find('.imei-numbers').val();
            $(this).closest('tr').remove();
            var imeis = $.trim(currentEditRow.find('.imei-number').val() || '')
                .split(',').filter(function (s) { return s && s !== target; });
            currentEditRow.find('.imei-number').val(imeis.join(','));
            $('input[name="edit_qty"]').val(imeis.length);
            currentEditRow.find('.qty').val(imeis.length);
            if (!imeis.length) {
                $('#editModal').modal('hide');
                currentEditRow.remove();
                currentEditRow = null;
                recalculateAll();
                return;
            }
            updateRowCalc(currentEditRow);
            recalculateAll();
        });

        $(document).on('click', 'button[name="update_btn"]', function () {
            var $row = currentEditRow;
            if (!$row || !$row.length) { $('#editModal').modal('hide'); return; }

            var qty = num($('input[name="edit_qty"]').val());
            var discount = num($('input[name="edit_discount"]').val());
            var unitPrice = num($('input[name="edit_unit_price"]').val());

            if (discount < 0 || discount > unitPrice) {
                alert('Invalid Discount Input!');
                return;
            }
            if (qty < 1) {
                alert("Quantity can't be less than 1");
                return;
            }

            var imeiVal = $.trim($row.find('.imei-number').val() || '');
            if (imeiVal) {
                qty = imeiVal.split(',').filter(Boolean).length;
                $('input[name="edit_qty"]').val(qty);
            }

            /* apply unit change (new rows only) */
            var $unitSel = $('select[name="edit_unit"]');
            if (!$unitSel.prop('disabled')) {
                var $opt = $unitSel.find(':selected');
                var newName = $.trim($opt.text());
                if (newName) {
                    var newOp = $opt.data('operator') || '*';
                    var newV = num($opt.data('operation-value')) || 1;
                    $row.data('base-price', unitToBasePrice(unitPrice, { operator: newOp, value: newV }));
                    $row.find('.sale-unit').val(newName);
                }
            } else if ($('#price_option').val() === 'custom') {
                /* custom price on a locked unit: convert back to base */
                $row.data('base-price', unitToBasePrice(unitPrice, rowUnitData($row)));
            }

            /* tax */
            var tIdx = parseInt($('select[name="edit_tax_rate"]').val(), 10) || 0;
            $row.find('.tax-rate').val(num(tax_rate_all[tIdx]));
            $row.find('.tax-name').val(tax_name_all[tIdx] || 'No Tax');

            $row.data('discount-per-unit', discount);
            $row.find('.qty').val(qty);

            enforceQuantity($row);
            updateRowCalc($row);
            $('#editModal').modal('hide');
            currentEditRow = null;
            recalculateAll();
        });

        /* ---------------- table row events ---------------- */
        $(document).on('click', '.ibtnDel', function () {
            $(this).closest('tr').remove();
            recalculateAll();
        });

        $(document).on('click', 'table .plus, table .minus', function () {
            var $row = $(this).closest('tr');
            var delta = $(this).hasClass('plus') ? 1 : -1;
            var qty = num($row.find('.qty').val()) + delta;
            if (qty < 1) qty = 1;
            $row.find('.qty').val(qty);
            enforceQuantity($row);
            updateRowCalc($row);
            recalculateAll();
        });

        $(document).on('change', 'table.order-list .qty, table.return-order-list .qty', function () {
            var $row = $(this).closest('tr');
            enforceQuantity($row);
            updateRowCalc($row);
            recalculateAll();
        });

        $(document).on('change', 'input.exchange-checkbox', recalculateAll);
        $(document).on('blur change keyup', 'input[name="total_sale_discount"]', function () {
            if (num($(this).val()) < 0) { $(this).val(0); }
            recalculateAll();
        });
        $(document).on('change', 'select[name="order_tax_rate"]', recalculateAll);

        /* ---------------- barcode scanner ---------------- */
        var scannerObj = null, scannerActive = false, currentScannerType = 'new';

        function startScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                alert('Barcode scanner library could not be loaded. Please check your internet connection.');
                return;
            }
            if (scannerActive) return;
            if (!scannerObj) {
                try {
                    scannerObj = new Html5Qrcode('reader');
                } catch (err) {
                    alert('Scanner is not available on this device.');
                    return;
                }
            }
            scannerActive = true;
            $('#closeScannerBtn').show();
            scannerObj.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 300, height: 100 } },
                function (decodedText) {
                    stopScanner();
                    if (!decodedText) return;
                    var kind = currentScannerType;
                    $(kind === 'return' ? '#sale-product-search-input' : '#product-search-input').val(decodedText);
                    fetchSuggestions(String(decodedText).trim(), kind, true);
                },
                function () { /* per-frame decode errors — ignore */ }
            ).catch(function () {
                scannerActive = false;
                $('#closeScannerBtn').hide();
                alert('Unable to access the camera.');
            });
        }

        function stopScanner() {
            if (scannerObj && scannerActive) {
                scannerActive = false;
                scannerObj.stop().then(function () { $('#closeScannerBtn').hide(); })
                    .catch(function () { $('#closeScannerBtn').hide(); });
            } else {
                $('#closeScannerBtn').hide();
            }
        }

        function barcodeNew() { currentScannerType = 'new'; startScanner(); }
        function barcodeSale() { currentScannerType = 'return'; startScanner(); }

        $(document).on('click', '#closeScannerBtn', stopScanner);

        /* ---------------- misc ---------------- */
        function setCustomerGroupRate(id) {
            if (!id) { customer_group_rate = 0; return; }
            $.get('{{ url('exchange/getcustomergroup') }}/' + id, function (data) {
                customer_group_rate = num(data) / 100;
            }).fail(function () { customer_group_rate = 0; });
        }

        $(document).on('change', 'select[name="customer_id"]', function () {
            setCustomerGroupRate($(this).val());
        });

        /* reference number lookup — reload the page so the server renders the sale products */
        $('#saleSearchBtn').on('click', function (e) {
            e.preventDefault();
            var ref = $('#sale_product_search').val().trim();
            if (!ref) {
                alert('Please enter a reference number');
                return;
            }
            window.location.href = '{{ route('exchange.create') }}?reference_no=' + encodeURIComponent(ref);
        });

        /* prevent Enter from submitting the main form (search boxes handle Enter themselves) */
        $(window).keydown(function (e) {
            if (e.which == 13) {
                var $targ = $(e.target);
                if (!$targ.is("textarea") && !$targ.is(":button,:submit") &&
                    !$targ.is('#product-search-input') && !$targ.is('#sale-product-search-input')) {
                    return false;
                }
            }
        });

        /* submit guard */
        $('#payment-form').on('submit', function (e) {
            var hasReturn = $('table.return-order-list tbody .exchange-checkbox:checked').length > 0;
            var hasNew = $('table.order-list tbody tr').length > 0;

            if (!hasReturn && !hasNew) {
                alert('Please select product(s) to return or add new product(s)!');
                e.preventDefault();
                return;
            }

            var badQty = false;
            $("table.order-list tbody .qty, table.return-order-list tbody .qty").each(function () {
                if ($(this).val() === '') badQty = true;
            });
            if (badQty) {
                alert('One of products has no quantity!');
                e.preventDefault();
                return;
            }

            $("#submit-button").prop('disabled', true);
        });

        /* ---------------- init ---------------- */
        $(document).ready(function () {
            /* mirror server-rendered row data into jQuery data before anything else */
            $('table.return-order-list tbody tr').each(function () { initRowData($(this)); });

            $('select[name="customer_id"]').val($('input[name="customer_id_hidden"]').val());
            $('select[name="warehouse_id"]').val($('input[name="warehouse_id_hidden"]').val());
            $('select[name="biller_id"]').val($('input[name="biller_id_hidden"]').val());
            if ($.fn.selectpicker) { $('.selectpicker').selectpicker('refresh'); }

            wireAutocomplete($('#product-search-input'), 'new');
            wireAutocomplete($('#sale-product-search-input'), 'return');
            $('#product-search-input').focus();

            setCustomerGroupRate($('select[name="customer_id"]').val());
            recalculateAll();
        });
    </script>
@endpush
