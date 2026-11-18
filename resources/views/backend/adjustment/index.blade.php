@extends('backend.layout.main')
@section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        <a href="{{route('qty_adjustment.create')}}" class="btn btn-info"><i class="dripicons-plus"></i> {{trans('file.Add Adjustment')}}</a>
    </div>
    <div class="table-responsive">
        <table id="adjustment-table" class="table purchase-list">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Date')}}</th>
                    <th>{{trans('file.reference')}}</th>
                    <th>{{trans('file.Warehouse')}}</th>
                    <th>{{trans('file.product')}}s</th>
                    <th> Qty Adjusted </th>
                    <th>Stock Qty</th>
                    <th>{{trans('file.Note')}}</th>
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lims_adjustment_all as $key=>$adjustment)
                <tr data-id="{{$adjustment->id}}">
                    <td>{{$key}}</td>
                    <td>{{ date('d-m-Y', strtotime($adjustment->created_at->toDateString())) . ' '. $adjustment->created_at->toTimeString() }}</td>
                    <td>{{ $adjustment->reference_no }}</td>
                    <?php $warehouse = DB::table('warehouses')->find($adjustment->warehouse_id) ?>
                    <td>{{ optional($warehouse)->name }}</td>

                    {{-- ================================================================ --}}
                    {{-- PRODUCTS COLUMN                                                  --}}
                    {{--   Variant products  → "Product Name [Variant Name]"             --}}
                    {{--                       e.g. "Mana Product [Wine long/S]"         --}}
                    {{--   Standard products → "Product Name [Product Code]"             --}}
                    {{--                                                                  --}}
                    {{--   ✅ FIX: look up the variant by product_variants.id (primary   --}}
                    {{--   key), which is what product_adjustments.variant_id actually   --}}
                    {{--   stores. Fallback to the old variant_id column for any legacy  --}}
                    {{--   rows created before the controller fix.                       --}}
                    {{-- ================================================================ --}}
                    <td>
                    <?php
                        $product_adjustment_data = DB::table('product_adjustments')
                            ->where('adjustment_id', $adjustment->id)
                            ->get();

                        foreach ($product_adjustment_data as $i => $product_adjustment) {
                            if ($i) echo '<br>';

                            if ($product_adjustment->variant_id) {
                                // ✅ Primary lookup: product_adjustments.variant_id
                                //    stores product_variants.id (the pivot row PK).
                                $product = DB::table('products')
                                    ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                                    ->select(
                                        'products.name as product_name',
                                        'product_variants.name as variant_name',
                                        'product_variants.item_code as code'
                                    )
                                    ->where('product_variants.id', $product_adjustment->variant_id)
                                    ->where('product_variants.product_id', $product_adjustment->product_id)
                                    ->first();

                                // 🪵 Fallback: legacy rows where variant_id stored
                                //    variants.id (the value-type id). This may match
                                //    multiple Color variants — pick the first one.
                                if (!$product) {
                                    $product = DB::table('products')
                                        ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                                        ->select(
                                            'products.name as product_name',
                                            'product_variants.name as variant_name',
                                            'product_variants.item_code as code'
                                        )
                                        ->where('product_variants.product_id', $product_adjustment->product_id)
                                        ->where('product_variants.variant_id', $product_adjustment->variant_id)
                                        ->first();
                                }

                                if ($product) {
                                    // ✅ Display: "Product Name [Variant Name]"
                                    //    e.g. "Mana Product [Wine long/S]"
                                    echo e($product->product_name) . ' [' . e($product->variant_name) . ']';
                                } else {
                                    echo '<span class="text-danger">Product not found (variant_id=' . e($product_adjustment->variant_id) . ')</span>';
                                }
                            } else {
                                // Standard (non-variant) product
                                $product = DB::table('products')
                                    ->select('name', 'code')
                                    ->find($product_adjustment->product_id);

                                if ($product) {
                                    echo e($product->name) . ' [' . e($product->code) . ']';
                                } else {
                                    echo '<span class="text-danger">Product not found</span>';
                                }
                            }
                        }
                    ?>
                    </td>
                    <td>{{ $adjustment->total_qty }}</td>

                    {{-- ================================================================ --}}
                    {{-- STOCK QTY COLUMN                                                 --}}
                    {{--   For variant products → pulls qty from product_variants.qty    --}}
                    {{--   For standard products → pulls qty from products.qty           --}}
                    {{--                                                                  --}}
                    {{--   ✅ FIX: same primary-key lookup. The stock shown here is the  --}}
                    {{--   CURRENT qty in product_variants (after the adjustment was     --}}
                    {{--   applied), not the qty at the time of adjustment. The           --}}
                    {{--   AdjustmentController::store() / update() / destroy() methods  --}}
                    {{--   already keep products.qty, product_variants.qty AND           --}}
                    {{--   product_warehouse.qty all in sync when an adjustment runs.    --}}
                    {{-- ================================================================ --}}
                    <td>
                    <?php
                        $total_stock_qty = 0;
                        foreach ($product_adjustment_data as $i => $product_adjustment) {
                            if ($product_adjustment->variant_id) {
                                // ✅ Primary lookup by product_variants.id
                                $product_stock = DB::table('product_variants')
                                    ->where('id', $product_adjustment->variant_id)
                                    ->where('product_id', $product_adjustment->product_id)
                                    ->first();

                                // 🪵 Fallback for legacy rows
                                if (!$product_stock) {
                                    $product_stock = DB::table('product_variants')
                                        ->where('product_id', $product_adjustment->product_id)
                                        ->where('variant_id', $product_adjustment->variant_id)
                                        ->first();
                                }
                            } else {
                                $product_stock = DB::table('products')
                                    ->find($product_adjustment->product_id);
                            }

                            if ($product_stock) {
                                $total_stock_qty += $product_stock->qty;
                            }
                        }
                        echo $total_stock_qty;
                    ?>
                    </td>
                    <td>{{$adjustment->note}}</td>

                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans('file.action')}}<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <a href="{{ route('qty_adjustment.edit', $adjustment->id) }}" class="btn btn-link"><i class="dripicons-document-edit"></i> {{trans('file.edit')}}</a>
                                </li>
                                <li class="divider"></li>
                                {{ Form::open(['route' => ['qty_adjustment.destroy', $adjustment->id], 'method' => 'DELETE'] ) }}
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
        </table>
    </div>
</section>

@endsection
@push('scripts')
<script type="text/javascript">
    $("ul#product").siblings('a').attr('aria-expanded','true');
    $("ul#product").addClass("show");
    $("ul#product #adjustment-list-menu").addClass("active");

    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }

    var adjustment_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var table = $('#adjustment-table').DataTable( {
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
                'targets': [0, 6]
            },
            {
                'checkboxes': {
                   'selectRow': true
                },
                'targets': 0
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
                footer:true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer:true
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        adjustment_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                adjustment_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(adjustment_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'qty_adjustment/deletebyselection',
                                data:{
                                    adjustmentIdArray: adjustment_id
                                },
                                success:function(data){
                                    alert(data);
                                }
                            });
                            dt.rows({ page: 'current', selected: true }).remove().draw(false);
                        }
                        else if(!adjustment_id.length)
                            alert('Nothing is selected!');
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
</script>
@endpush
