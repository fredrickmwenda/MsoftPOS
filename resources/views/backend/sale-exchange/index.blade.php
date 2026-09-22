@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
@endpush

@section('content')

    @php
        // ── General Setting ──────────────────────────────────────────────
        if (!isset($general_setting) || !$general_setting) {
            if (class_exists(\App\Models\GeneralSetting::class)) {
                $general_setting = \App\Models\GeneralSetting::latest()->first();
            } elseif (class_exists(\App\GeneralSetting::class)) {
                $general_setting = \App\GeneralSetting::latest()->first();
            }
            if (!$general_setting) {
                $general_setting = new \stdClass();
                $general_setting->site_title   = 'JoexPOS';
                $general_setting->decimal       = 2;
            }
        }
        // ── Auth User Role ID from role_user pivot ────────────────────
        $authUser = $authUser ?? (Auth::user()->roles->min('id') ?? 999);
    @endphp

    <style>
        .product-return-list tbody tr td span.badge {
            display: inline-block;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .product-return-list tbody tr[style*='#fff5f5']:hover {
            background-color: #ffe5e5 !important;
            transition: background-color 0.2s ease;
        }
        .product-return-list tbody tr[style*='#f0fff4']:hover {
            background-color: #e0ffe5 !important;
            transition: background-color 0.2s ease;
        }
        .date-filter-row .input-group {
            margin-bottom: 8px;
        }
        .date-filter-row .input-group-text {
            font-size: 12px;
            font-weight: 700;
            color: #0d5a39;
            background: #f0f4f2;
            border: 1px solid #d1d5db;
            min-width: 55px;
        }
        .date-filter-row input[type="date"] {
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
        @media (max-width: 768px) {
            .product-return-list tbody tr td span.badge {
                font-size: 8px;
                padding: 1px 4px;
            }
            .date-filter-row .col-md-4 {
                margin-bottom: 10px;
            }
        }
    </style>

    <section>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">{{ __('file.Sale Exchange List') }}</h3>
                </div>
                <form action="{{ route('exchange.index') }}" method="GET">
                    <div class="row mb-3 date-filter-row">
                        {{-- ── Date filter: two native date inputs ── --}}
                        <div class="col-md-4 offset-md-2 mt-3">
                            <label class="d-block mb-1">{{ __('file.date') }}</label>
                            <div class="input-group mb-1">
                                <span class="input-group-text">From</span>
                                <input type="date"
                                       name="starting_date"
                                       class="form-control"
                                       value="{{ $starting_date }}"
                                       required />
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">To</span>
                                <input type="date"
                                       name="ending_date"
                                       class="form-control"
                                       value="{{ $ending_date }}"
                                       required />
                            </div>
                        </div>

                        {{-- ── Warehouse filter ── --}}
                        <div class="col-md-4 mt-3 @if ($authUser > 2) d-none @endif">
                            <label class="d-block mb-1">{{ __('file.Warehouse') }}</label>
                            <div class="d-flex">
                                <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control"
                                    data-live-search="true" data-live-search-style="begins">
                                    <option value="0">{{ __('file.All Warehouse') }}</option>
                                    @foreach ($lims_warehouse_list as $warehouse)
                                        <option value="{{ $warehouse->id }}" @if($warehouse->id == $warehouse_id) selected @endif>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- ── Submit button ── --}}
                        <div class="col-md-2 mt-3">
                            <label class="d-block mb-1 invisible">.</label>
                            <button class="btn btn-primary btn-block" type="submit">
                                {{ __('file.submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @if (in_array('exchange-add', $all_permission))
                <a href="{{ route('exchange.create') }}" class="btn btn-info">
                    <i class="ti ti-plus"></i> {{ __('file.Add Exchange') }}
                </a>
            @endif
        </div>

        <div class="table-responsive">
            <table id="return-table" class="table return-list" style="width:100%">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ __('file.date') }}</th>
                        <th>{{ __('file.reference') }}</th>
                        <th>{{ __('file.Sale Reference') }}</th>
                        <th>{{ __('file.Warehouse') }}</th>
                        <th>{{ __('file.Biller') }}</th>
                        <th>{{ __('file.customer') }}</th>
                        <th>{{ __('file.Payment Type') }}</th>
                        <th>{{ __('file.grand total') }}</th>
                        <th class="not-exported">{{ __('file.action') }}</th>
                    </tr>
                </thead>
                <tfoot class="tfoot active">
                    <tr>
                        <th></th><th>{{ __('file.Total') }}</th><th></th><th></th><th></th>
                        <th></th><th></th><th></th><th></th><th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="return-details" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="container mt-3 pb-2 border-bottom">
                        <div class="row">
                            <div class="col-md-6 d-print-none">
                                <button id="print-btn" type="button" class="btn btn-default btn-sm">
                                    <i class="ti ti-printer"></i> {{ __('file.Print') }}
                                </button>
                            </div>
                            <div class="col-md-6 d-print-none">
                                <button type="button" class="close" data-dismiss="modal">
                                    <span><i class="ti ti-x"></i></span>
                                </button>
                            </div>
                            <div class="col-md-12">
                                <h3 class="modal-title text-center">{{ $general_setting->site_title }}</h3>
                            </div>
                            <div class="col-md-12 text-center">
                                <i style="font-size:15px;">{{ __('file.Exchange Details') }}</i>
                            </div>
                        </div>
                    </div>
                    <div id="return-content" class="modal-body"></div>
                    <br>
                    <table class="table table-bordered product-return-list">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('file.product') }}</th>
                                <th>{{ __('file.Batch No') }}</th>
                                <th>{{ __('file.Qty') }}</th>
                                <th>{{ __('file.Unit Price') }}</th>
                                <th>{{ __('file.Tax') }}</th>
                                <th>{{ __('file.Discount') }}</th>
                                <th>{{ __('file.Subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div id="return-footer" class="modal-body"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
        JAVASCRIPT
    ════════════════════════════════════════════════════════════════ --}}

    <script>
        var DECIMAL            = {{ (int)($general_setting->decimal ?? 2) }};
        var AUTH_USER          = {{ (int)$authUser }};
        var ALL_PERMISSION     = @json($all_permission);
        var USER_VERIFIED      = @json(env('USER_VERIFIED', 1));
        var EXCHANGE_DATA_URL  = "{{ route('exchange.data') }}";
        var PRODUCT_EXCHG_URL  = "{{ route('exchange.product_exchange', ['id' => '__ID__']) }}";
        var DELETE_URL         = "{{ route('exchange.deletebyselection') }}";
        var LANG_DATE          = "{{ __('file.date') }}";
        var LANG_REFERENCE     = "{{ __('file.reference') }}";
        var LANG_SALE_REF      = "{{ __('file.Sale Reference') }}";
        var LANG_WAREHOUSE     = "{{ __('file.Warehouse') }}";
        var LANG_CURRENCY      = "{{ __('file.Currency') }}";
        var LANG_EXCH_RATE     = "{{ __('file.Exchange Rate') }}";
        var LANG_ATTACH_DOC    = "{{ __('file.Attach Document') }}";
        var LANG_FROM          = "{{ __('file.From') }}";
        var LANG_TO            = "{{ __('file.To') }}";
        var LANG_TOTAL         = "{{ __('file.Total') }}";
        var LANG_EXCH_NOTE     = "{{ __('file.Exchange Note') }}";
        var LANG_STAFF_NOTE    = "{{ __('file.Staff Note') }}";
        var LANG_CREATED_BY    = "{{ __('file.Created By') }}";
        var LANG_RECORDS_PAGE  = "{{ __('file.records per page') }}";
        var LANG_SHOWING       = "{{ __('file.Showing') }}";
        var LANG_SEARCH        = "{{ __('file.Search') }}";
        var return_id = [];
    </script>

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.6.2/js/dataTables.select.min.js"></script>

    <script>
        $(function() {

            function confirmDelete() {
                return confirm("Are you sure want to delete?");
            }

            // Row click → view details
            $(document).on("click", "tr.return-link td:not(:first-child, :last-child)", function() {
                returnDetails($(this).parent().data('return'));
            });
            $(document).on("click", ".view", function() {
                returnDetails($(this).closest('tr').data('return'));
            });

            // Print
            $("#print-btn").on("click", function() {
                var content = document.getElementById("return-details").innerHTML;
                var w = window.open('', '_blank');
                w.document.write('<html><head><title>Print<\/title>');
                w.document.write('<style>body{font-family:sans-serif}table,th,td{border:1px solid #ccc;border-collapse:collapse;padding:6px}th{text-align:left}.text-center{text-align:center}.d-print-none{display:none}<\/style>');
                w.document.write('<\/head><body>');
                w.document.write(content);
                w.document.write('<\/body><\/html>');
                w.document.close();
                setTimeout(function() { w.print(); w.close(); }, 200);
            });

            // ─── Read date values from the two native date inputs ───
            var starting_date = $("input[name='starting_date']").val();
            var ending_date   = $("input[name='ending_date']").val();
            var warehouse_id   = $("#warehouse_id").val();

            var dt = $('#return-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: EXCHANGE_DATA_URL,
                    data: {
                        all_permission: ALL_PERMISSION,
                        starting_date: starting_date,
                        ending_date:   ending_date,
                        warehouse_id:   warehouse_id
                    },
                    dataType: "json",
                    type: "POST"
                },
                createdRow: function(row, data) {
                    $(row).addClass('return-link').attr('data-return', data['exchange']);
                },
                columns: [
                    { data: "key" },
                    { data: "date" },
                    { data: "reference_no" },
                    { data: "sale_reference" },
                    { data: "warehouse" },
                    { data: "biller" },
                    { data: "customer" },
                    { data: "payment_type" },
                    { data: "amount" },
                    { data: "options" }
                ],
                language: {
                    lengthMenu: '_MENU_ ' + LANG_RECORDS_PAGE,
                    info: '<small>' + LANG_SHOWING + ' _START_ - _END_ (_TOTAL_)</small>',
                    search: LANG_SEARCH,
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"><\/i>',
                        next: '<i class="ti ti-chevron-right"><\/i>'
                    }
                },
                order: [[1, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0,3,4,5,6,7,8,9] },
                    {
                        render: function(data, type) {
                            if (type === 'display') {
                                return '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label><\/label><\/div>';
                            }
                            return data;
                        },
                        checkboxes: {
                            selectRow: true,
                            selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label><\/label><\/div>'
                        },
                        targets: [0]
                    }
                ],
                select: { style: 'multi', selector: 'td:first-child' },
                lengthMenu: [[10,25,50,-1],[10,25,50,"All"]],
                dom: '<"row"lfB>rtip',
                rowId: 'ObjectID',
                buttons: [{
                    extend: 'pdf', text: '<i class="ti ti-file-type-pdf"><\/i>',
                    exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                    action: function(e, d, b, c) { datatable_sum(d,true); $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this,e,d,b,c); datatable_sum(d,false); },
                    footer: true
                }, {
                    extend: 'excel', text: '<i class="ti ti-file-type-xls"><\/i>',
                    exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                    action: function(e, d, b, c) { datatable_sum(d,true); $.fn.dataTable.ext.buttons.excelHtml5.action.call(this,e,d,b,c); datatable_sum(d,false); },
                    footer: true
                }, {
                    extend: 'csv', text: '<i class="ti ti-file-type-csv"><\/i>',
                    exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                    action: function(e, d, b, c) { datatable_sum(d,true); $.fn.dataTable.ext.buttons.csvHtml5.action.call(this,e,d,b,c); datatable_sum(d,false); },
                    footer: true
                }, {
                    extend: 'print', text: '<i class="ti ti-printer"><\/i>',
                    exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                    action: function(e, d, b, c) { datatable_sum(d,true); $.fn.dataTable.ext.buttons.print.action.call(this,e,d,b,c); datatable_sum(d,false); },
                    footer: true
                }, {
                    text: '<i class="ti ti-x"><\/i>',
                    className: 'buttons-delete',
                    action: function(e, d) {
                        if (USER_VERIFIED != '1') { alert('Disabled for demo!'); return; }
                        return_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                var ed = $(this).closest('tr').data('return');
                                if (ed) { try { return_id[i-1] = JSON.parse(ed)[13]; } catch(e) {} }
                            }
                        });
                        if (return_id.length && confirm("Delete selected?")) {
                            $.ajax({
                                type: 'POST', url: DELETE_URL,
                                data: { returnIdArray: return_id },
                                success: function(msg) { alert(msg); d.rows({page:'current',selected:true}).remove().draw(false); }
                            });
                        } else if (!return_id.length) { alert('Nothing selected!'); }
                    }
                }, {
                    extend: 'colvis', text: '<i class="ti ti-eye"><\/i>', columns: ':gt(0)'
                }],
                drawCallback: function() { datatable_sum(this.api(), false); }
            });

            // ─── Sum footer ─────────────────────────────────────────────
            function datatable_sum(dt_sel, first) {
                if (dt_sel.rows('.selected').any() && first) {
                    var rows = dt_sel.rows('.selected').indexes();
                    $(dt_sel.column(8).footer()).html(
                        dt_sel.cells(rows, 8, {page:'current'}).data().sum().toFixed(DECIMAL)
                    );
                } else {
                    $(dt_sel.column(8).footer()).html(
                        dt_sel.column(8).data().sum().toFixed(DECIMAL)
                    );
                }
            }

            // ─── Exchange details modal ─────────────────────────────────
            function returnDetails(exchangeData) {
                var r = typeof exchangeData === 'string' ? JSON.parse(exchangeData) : exchangeData;

                var h = '<strong>' + LANG_DATE + ': <\/strong>' + r[0] + '<br>';
                h += '<strong>' + LANG_REFERENCE + ': <\/strong>' + r[1] + '<br>';
                h += '<strong>' + LANG_SALE_REF + ': <\/strong>' + (r[24] || 'N/A') + '<br>';
                h += '<strong>' + LANG_WAREHOUSE + ': <\/strong>' + r[2] + '<br>';
                h += '<strong>' + LANG_CURRENCY + ': <\/strong>' + (r[26] || 'BDT');
                if (r[27]) { h += '<br><strong>' + LANG_EXCH_RATE + ': <\/strong>' + r[27] + '<br>'; }
                else { h += '<br><strong>' + LANG_EXCH_RATE + ': <\/strong>N/A<br>'; }
                if (r[25]) {
                    h += '<strong>' + LANG_ATTACH_DOC + ': <\/strong>' +
                        '<a href="documents/sale_exchange/' + r[25] + '" target="_blank">Download<\/a><br>';
                }
                h += '<br><div class="row"><div class="col-md-6">';
                h += '<strong>' + LANG_FROM + ':<\/strong><br>' + r[3] + '<br>' + r[4] + '<br>' +
                     r[5] + '<br>' + r[6] + '<br>' + r[7] + '<br>' + r[8];
                h += '<\/div><div class="col-md-6"><div class="float-right">';
                h += '<strong>' + LANG_TO + ':<\/strong><br>' + r[9] + '<br>' + r[10] + '<br>' +
                     r[11] + '<br>' + r[12];
                h += '<\/div><\/div><\/div>';

                $('#return-content').html(h);

                // Load products via AJAX
                var url = PRODUCT_EXCHG_URL.replace('__ID__', r[13]);

                $.get(url, function(data) {
                    $(".product-return-list tbody").remove();
                    var body = $("<tbody>");

                    if (data.returned && data.returned.length) {
                        body.append('<tr style="background:#f8d7da;">' +
                            '<td colspan="8" style="text-align:center;font-weight:bold;color:#721c24;">RETURNED PRODUCTS<\/td><\/tr>');
                        $.each(data.returned, function(i, p) {
                            body.append(
                                '<tr style="background:#fff5f5;">' +
                                '<td><strong>' + (i+1) + '<\/strong><\/td>' +
                                '<td>' + p.name_code + ' <span class="badge badge-danger" style="font-size:10px;">RETURNED<\/span><\/td>' +
                                '<td>' + (p.batch_no || '-') + '<\/td>' +
                                '<td>' + p.qty + ' ' + (p.unit_code || '') + '<\/td>' +
                                '<td>' + p.unit_price + '<\/td>' +
                                '<td>' + p.tax + ' (' + p.tax_rate + '%)<\/td>' +
                                '<td>' + p.discount + '<\/td>' +
                                '<td>' + p.subtotal + '<\/td>' +
                                '<\/tr>'
                            );
                        });
                    }

                    if (data.new && data.new.length) {
                        body.append('<tr style="background:#d4edda;">' +
                            '<td colspan="8" style="text-align:center;font-weight:bold;color:#155724;">NEW PRODUCTS<\/td><\/tr>');
                        $.each(data.new, function(i, p) {
                            body.append(
                                '<tr style="background:#f0fff4;">' +
                                '<td><strong>' + (i+1) + '<\/strong><\/td>' +
                                '<td>' + p.name_code + ' <span class="badge badge-success" style="font-size:10px;">NEW<\/span><\/td>' +
                                '<td>' + (p.batch_no || '-') + '<\/td>' +
                                '<td>' + p.qty + ' ' + (p.unit_code || '') + '<\/td>' +
                                '<td>' + p.unit_price + '<\/td>' +
                                '<td>' + p.tax + ' (' + p.tax_rate + '%)<\/td>' +
                                '<td>' + p.discount + '<\/td>' +
                                '<td>' + p.subtotal + '<\/td>' +
                                '<\/tr>'
                            );
                        });
                    }

                    if (data.totals) {
                        body.append(
                            '<tr><td colspan="5"><strong>' + LANG_TOTAL + ' ' +
                            (r[28] === 'pay' ? 'Refund' : 'Received') + ' Amount:<\/strong><\/td>' +
                            '<td><\/td><td><\/td>' +
                            '<td><strong>' + (data.totals.amount || '0.00') + '<\/strong><\/td><\/tr>'
                        );
                    }

                    $("table.product-return-list").append(body);

                }).fail(function(xhr, status, error) {
                    console.error('Load error:', error, xhr.responseText);
                    $(".product-return-list tbody").remove();
                    $("table.product-return-list").append(
                        '<tbody><tr><td colspan="8" style="text-align:center;color:#dc3545;">' +
                        '<strong>Error loading details.<\/strong><br>' +
                        '<small>' + (xhr.responseText || error) + '<\/small><\/td><\/tr><\/tbody>'
                    );
                });

                // Footer
                var f = '<p><strong>' + LANG_EXCH_NOTE + ':<\/strong> ' + (r[20] || 'N/A') + '<\/p>';
                f += '<p><strong>' + LANG_STAFF_NOTE + ':<\/strong> ' + (r[21] || 'N/A') + '<\/p>';
                f += '<strong>' + LANG_CREATED_BY + ':<\/strong><br>' + r[22] + '<br>' + r[23];
                $('#return-footer').html(f);

                $('#return-details').modal('show');
            }

            // Expose for inline onclick if needed
            window.returnDetails = returnDetails;

            // Hide delete button if no permission
            if (ALL_PERMISSION.indexOf("exchanges-delete") === -1) {
                $('.buttons-delete').addClass('d-none');
            }
        });
    </script>
@endsection