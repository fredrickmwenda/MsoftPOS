<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Product_Warehouse;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Transfer;
use App\Models\ProductTransfer;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    private function isStaff()
    {
        return Auth::user()->roles->contains(function ($role) {
            return $role->id > 2;
        });
    }

    public function index(Request $request)
    {
        if (Auth::user()->hasPermissionTo('transfers-index')) {
            $all_permission = Auth::user()->getAllPermissions();
            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            $from_warehouse_id = $request->input('from_warehouse_id', 0);
            $to_warehouse_id   = $request->input('to_warehouse_id', 0);

            if ($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date   = $request->input('ending_date');
            } else {
                $starting_date = date("Y-m-d", strtotime('-1 year'));
                $ending_date   = date("Y-m-d");
            }

            $lims_warehouse_list = Warehouse::select('name', 'id')->where('is_active', true)->get();
            return view('backend.transfer.index', compact('starting_date', 'ending_date', 'from_warehouse_id', 'to_warehouse_id', 'all_permission', 'lims_warehouse_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function transferData(Request $request)
    {
        $columns = array(1 => 'created_at', 2 => 'reference_no');

        $from_warehouse_id = $request->input('from_warehouse_id');
        $to_warehouse_id   = $request->input('to_warehouse_id');
        $q = Transfer::whereDate('created_at', '>=', $request->input('starting_date'))
            ->whereDate('created_at', '<=', $request->input('ending_date'));
        if ($this->isStaff() && config('staff_access') == 'own')
            $q = $q->where('user_id', Auth::id());
        if ($from_warehouse_id)
            $q = $q->where('from_warehouse_id', $from_warehouse_id);
        if ($to_warehouse_id)
            $q = $q->where('to_warehouse_id', $to_warehouse_id);

        $totalData     = $q->count();
        $totalFiltered = $totalData;

        $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
        $start = $request->input('start');
        $order = 'transfers.' . $columns[$request->input('order.0.column')];
        $dir   = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $q = Transfer::with('fromWarehouse', 'toWarehouse', 'user')
                ->whereDate('created_at', '>=', $request->input('starting_date'))
                ->whereDate('created_at', '<=', $request->input('ending_date'))
                ->offset($start)->limit($limit)->orderBy($order, $dir);
            if ($this->isStaff() && config('staff_access') == 'own')
                $q = $q->where('user_id', Auth::id());
            if ($from_warehouse_id)
                $q = $q->where('from_warehouse_id', $from_warehouse_id);
            if ($to_warehouse_id)
                $q = $q->where('to_warehouse_id', $to_warehouse_id);
            $transfers = $q->get();
        } else {
            $search = $request->input('search.value');
            $q = Transfer::whereDate('transfers.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                ->offset($start)->limit($limit)->orderBy($order, $dir);
            if ($this->isStaff() && config('staff_access') == 'own') {
                $transfers = $q->select('transfers.*')->with('fromWarehouse', 'toWarehouse', 'user')
                    ->where('transfers.user_id', Auth::id())
                    ->orwhere([['reference_no', 'LIKE', "%{$search}%"], ['user_id', Auth::id()]])->get();
                $totalFiltered = $q->where('transfers.user_id', Auth::id())->count();
            } else {
                $transfers = $q->select('transfers.*')->with('fromWarehouse', 'toWarehouse', 'user')
                    ->orwhere('reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('transfers.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }

        $data = array();
        if (!empty($transfers)) {
            foreach ($transfers as $key => $transfer) {
                $nestedData['id']             = $transfer->id;
                $nestedData['key']            = $key;
                $nestedData['date']           = date(config('date_format'), strtotime($transfer->created_at->toDateString()));
                $nestedData['reference_no']   = $transfer->reference_no;
                $nestedData['from_warehouse'] = $transfer->fromWarehouse->name;
                $nestedData['to_warehouse']   = $transfer->toWarehouse->name;
                $nestedData['total_cost']     = number_format($transfer->total_cost, config('decimal'));
                $nestedData['total_tax']      = number_format($transfer->total_tax, config('decimal'));
                $nestedData['grand_total']    = number_format($transfer->grand_total, config('decimal'));

                if ($transfer->status == 1) {
                    $nestedData['status'] = '<div class="badge badge-danger">' . trans('file.Completed') . '</div>';
                    $status = trans('file.Completed');
                } elseif ($transfer->status == 2) {
                    $nestedData['status'] = '<div class="badge badge-danger">' . trans('file.Pending') . '</div>';
                    $status = trans('file.Pending');
                } elseif ($transfer->status == 3) {
                    $nestedData['status'] = '<div class="badge badge-warning">' . trans('file.Sent') . '</div>';
                    $status = trans('file.Sent');
                }

                $nestedData['options'] = '<div class="btn-group"><button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu"><li><button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> ' . trans('file.View') . '</button></li>';
                if (in_array("transfers-edit", $request['all_permission']))
                    $nestedData['options'] .= '<li><a href="' . route('transfers.edit', $transfer->id) . '" class="btn btn-link"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</a></li>';
                if (in_array("transfers-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["transfers.destroy", $transfer->id], "method" => "DELETE"]) . '<li><button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button></li>' . \Form::close() . '</ul></div>';

                $nestedData['transfer'] = array(
                    '[ "' . date(config('date_format'), strtotime($transfer->created_at->toDateString())) . '"',
                    ' "' . $transfer->reference_no . '"',
                    ' "' . $status . '"',
                    ' "' . $transfer->id . '"',
                    ' "' . $transfer->fromWarehouse->name . '"',
                    ' "' . $transfer->fromWarehouse->phone . '"',
                    ' "' . preg_replace('/\s+/S', " ", $transfer->fromWarehouse->address) . '"',
                    ' "' . $transfer->toWarehouse->name . '"',
                    ' "' . $transfer->toWarehouse->phone . '"',
                    ' "' . preg_replace('/\s+/S', " ", $transfer->toWarehouse->address) . '"',
                    ' "' . $transfer->total_tax . '"',
                    ' "' . $transfer->total_cost . '"',
                    ' "' . $transfer->shipping_cost . '"',
                    ' "' . $transfer->grand_total . '"',
                    ' "' . preg_replace('/[\n\r]/', "<br>", $transfer->note) . '"',
                    ' "' . $transfer->user->name . '"',
                    ' "' . $transfer->user->email . '"]'
                );
                $data[] = $nestedData;
            }
        }
        echo json_encode(array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ));
    }

    public function create()
    {
        if (Auth::user()->hasPermissionTo('transfers-add')) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            return view('backend.transfer.create', compact('lims_warehouse_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    /**
     * ====================================================================
     * getProduct($id)
     *
     * ✅ FIX: variant products looked up by product_variants.id (primary
     *    key) instead of FindExactProduct (which filters by the wrong
     *    variant_id column). Variant name appended to product name for
     *    easy identification in the dropdown.
     * ====================================================================
     */
    public function getProduct($id)
    {
        // Standard products (no variant, no batch)
        $lims_product_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $id],
                ['product_warehouse.qty', '>', 0],
            ])
            ->whereNull('product_warehouse.variant_id')
            ->whereNull('product_warehouse.product_batch_id')
            ->select('product_warehouse.*')
            ->get();

        // Products with batches
        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect();
        $lims_product_with_batch_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $id],
                ['product_warehouse.qty', '>', 0],
            ])
            ->whereNull('product_warehouse.variant_id')
            ->whereNotNull('product_warehouse.product_batch_id')
            ->select('product_warehouse.*')
            ->groupBy('product_warehouse.product_id')
            ->get();
        config()->set('database.connections.mysql.strict', true);
        \DB::reconnect();

        // Products with variants
        $lims_product_with_variant_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $id],
                ['product_warehouse.qty', '>', 0],
            ])
            ->whereNotNull('product_warehouse.variant_id')
            ->select('product_warehouse.*')
            ->get();

        $product_code  = [];
        $product_name  = [];
        $product_qty   = [];
        $product_price = [];
        $product_shelf = [];

        // Standard products
        foreach ($lims_product_warehouse_data as $pw) {
            $p = Product::select('name', 'code', 'price', 'shelf')->find($pw->product_id);
            $product_qty[]   = $pw->qty;
            $product_price[] = $pw->price ?? 'NA';
            $product_code[]  = $p->code;
            $product_name[]  = $p->name;
            $product_shelf[] = $p->shelf ?? 'NA';
        }

        // Batch products
        foreach ($lims_product_with_batch_warehouse_data as $pw) {
            $p = Product::select('name', 'code', 'price', 'shelf')->find($pw->product_id);
            $product_qty[]   = $pw->qty;
            $product_price[] = $pw->price ?? 'NA';
            $product_code[]  = $p->code;
            $product_name[]  = $p->name;
            $product_shelf[] = $p->shelf ?? 'NA';
        }

        // ✅ Variant products — primary key lookup + variant name in display
        foreach ($lims_product_with_variant_warehouse_data as $pw) {
            $p = Product::select('name', 'code', 'shelf')->find($pw->product_id);
            if (!$p) continue;

            // ✅ Look up ProductVariant by primary key (= product_warehouse.variant_id)
            $pv = ProductVariant::select('id', 'item_code', 'name')
                ->where('id', $pw->variant_id)
                ->where('product_id', $pw->product_id)
                ->first();

            // 🪵 Fallback for legacy rows
            if (!$pv) {
                $pv = ProductVariant::select('id', 'item_code', 'name')
                    ->FindExactProduct($pw->product_id, $pw->variant_id)
                    ->first();
            }

            if ($pv) {
                $product_qty[]   = $pw->qty;
                $product_price[] = $pw->price ?? 'NA';
                $product_code[]  = $pv->item_code;
                // ✅ Append variant name: "Mana Product [Wine long/S]"
                $product_name[]  = $p->name . ' [' . $pv->name . ']';
                $product_shelf[] = $p->shelf ?? 'NA';
            }
        }

        return [$product_code, $product_name, $product_qty, $product_price, $product_shelf];
    }

    /**
     * ====================================================================
     * limsProductSearch
     *
     * ✅ FIX: returns product_variant_id (= product_variants.id) so the
     *    controller can look up Product_Warehouse by primary key.
     * ====================================================================
     */
    public function limsProductSearch(Request $request)
    {
        $data = $request->input('data');
        $parts        = explode('?', $data);
        $product_code = trim($parts[0] ?? '');

        if ($product_code === '')
            return response()->json(['error' => 'Empty product code'], 400);

        $product_variant_id = null;
        $lims_product_data = Product::where([
            ['code', $product_code],
            ['is_active', true],
        ])->first();

        if (!$lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*',
                    'product_variants.id as product_variant_id',
                    'product_variants.item_code',
                    'product_variants.additional_cost',
                    'product_variants.name as pv_name')
                ->where('product_variants.item_code', $product_code)
                ->first();

            if ($lims_product_data) {
                $product_variant_id = $lims_product_data->product_variant_id;
                $lims_product_data->code = $lims_product_data->item_code;
                $lims_product_data->cost += $lims_product_data->additional_cost;
            }
        }

        if (!$lims_product_data)
            return response()->json(['error' => 'Product not found'], 404);

        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->cost;

        if ($lims_product_data->tax_id) {
            $lims_tax_data = Tax::find($lims_product_data->tax_id);
            $product[] = $lims_tax_data->rate;
            $product[] = $lims_tax_data->name;
        } else {
            $product[] = 0;
            $product[] = 'No Tax';
        }
        $product[] = $lims_product_data->tax_method;

        $units = Unit::where("base_unit", $lims_product_data->unit_id)->orWhere('id', $lims_product_data->unit_id)->get();
        $unit_name = $unit_operator = $unit_operation_value = [];
        foreach ($units as $unit) {
            if ($lims_product_data->purchase_unit_id == $unit->id) {
                array_unshift($unit_name, $unit->unit_name);
                array_unshift($unit_operator, $unit->operator);
                array_unshift($unit_operation_value, $unit->operation_value);
            } else {
                $unit_name[]            = $unit->unit_name;
                $unit_operator[]        = $unit->operator;
                $unit_operation_value[] = $unit->operation_value;
            }
        }

        $product[] = implode(",", $unit_name) . ',';
        $product[] = implode(",", $unit_operator) . ',';
        $product[] = implode(",", $unit_operation_value) . ',';
        $product[] = $lims_product_data->id;
        $product[] = $product_variant_id;          // ✅ index 10 — product_variants.id
        $product[] = $lims_product_data->is_batch;
        $product[] = $lims_product_data->is_imei;

        return response()->json($product);
    }

    /**
     * ====================================================================
     * store
     *
     * ✅ FIX: uses product_variant_id (from form) to look up ProductVariant
     *    by primary key, then looks up Product_Warehouse by
     *    product_variants.id. Stores product_variants.id in
     *    product_transfers.variant_id.
     * ====================================================================
     */
    public function store(Request $request)
    {
        $data = $request->except('document');
        $data['user_id']      = Auth::id();
        $data['reference_no'] = 'tr-' . date("Ymd") . '-' . date("his");
        $data['created_at']   = isset($data['created_at']) ? date("Y-m-d H:i:s", strtotime($data['created_at'])) : date("Y-m-d H:i:s");

        $document = $request->document;
        if ($document) {
            $v = Validator::make(['extension' => strtolower($request->document->getClientOriginalExtension())], ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']);
            if ($v->fails()) return redirect()->back()->withErrors($v->errors());
            $documentName = $document->getClientOriginalName();
            $document->move('public/documents/transfer', $documentName);
            $data['document'] = $documentName;
        }

        $lims_transfer_data = Transfer::create($data);

        $product_id         = $data['product_id'];
        $imei_number        = $data['imei_number'];
        $product_batch_id   = $data['product_batch_id'];
        $product_code       = $data['product_code'];
        $product_variant_ids = $data['product_variant_id'] ?? [];   // ✅ from form
        $qty                = $data['qty'];
        $purchase_unit      = $data['purchase_unit'];
        $net_unit_cost      = $data['net_unit_cost'];
        $tax_rate           = $data['tax_rate'];
        $tax                = $data['tax'];
        $total              = $data['subtotal'];
        $product_transfer   = [];

        foreach ($product_id as $i => $id) {
            $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$i])->first();
            $product_transfer['variant_id']         = null;
            $product_transfer['product_batch_id']   = null;

            $lims_product_data = Product::select('is_variant')->find($id);

            if ($lims_product_data->is_variant) {
                // ✅ Look up ProductVariant by primary key (product_variant_id from form)
                $lims_product_variant_data = null;
                if (!empty($product_variant_ids[$i])) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id')
                        ->where('id', $product_variant_ids[$i])->where('product_id', $id)->first();
                }
                // 🪵 Fallback: FindExactProductWithCode by item_code
                if (!$lims_product_variant_data) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id')
                        ->FindExactProductWithCode($id, $product_code[$i])->first();
                }
                if (!$lims_product_variant_data) continue;

                // ✅ Look up Product_Warehouse by product_variants.id (primary key)
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $id],
                    ['variant_id', $lims_product_variant_data->id],
                    ['warehouse_id', $data['from_warehouse_id']],
                ])->first();
                // 🪵 Fallback
                if (!$lims_product_warehouse_data) {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['from_warehouse_id']],
                    ])->first();
                }

                // ✅ Store product_variants.id in product_transfers.variant_id
                $product_transfer['variant_id'] = $lims_product_variant_data->id;
            } elseif ($product_batch_id[$i]) {
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_batch_id', $product_batch_id[$i]],
                    ['warehouse_id', $data['from_warehouse_id']],
                ])->first();
                $product_transfer['product_batch_id'] = $product_batch_id[$i];
            } else {
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $id],
                    ['warehouse_id', $data['from_warehouse_id']],
                ])->first();
            }

            if ($data['status'] != 2) {
                if ($lims_purchase_unit_data->operator == '*')
                    $quantity = $qty[$i] * $lims_purchase_unit_data->operation_value;
                else
                    $quantity = $lims_purchase_unit_data->operation_value > 0 ? ($qty[$i] / $lims_purchase_unit_data->operation_value) : $qty[$i];

                if ($imei_number[$i] && $lims_product_warehouse_data) {
                    $imei_numbers = explode(",", $imei_number[$i]);
                    $all_imei_numbers = explode(",", $lims_product_warehouse_data->imei_number ?: '');
                    foreach ($imei_numbers as $number) {
                        if (($j = array_search($number, $all_imei_numbers)) !== false) unset($all_imei_numbers[$j]);
                    }
                    $lims_product_warehouse_data->imei_number = implode(",", $all_imei_numbers);
                }
            } else {
                $quantity = 0;
            }

            if ($lims_product_warehouse_data) {
                $lims_product_warehouse_data->qty -= $quantity;
                $lims_product_warehouse_data->save();
            }

            // Add to destination warehouse if status = Completed
            if ($data['status'] == 1) {
                if ($lims_product_data->is_variant && isset($lims_product_variant_data)) {
                    // ✅ Look up dest warehouse by product_variants.id
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['variant_id', $lims_product_variant_data->id],
                        ['warehouse_id', $data['to_warehouse_id']],
                    ])->first();
                    if (!$lims_product_warehouse_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $id],
                            ['variant_id', $lims_product_variant_data->variant_id],
                            ['warehouse_id', $data['to_warehouse_id']],
                        ])->first();
                    }
                } elseif ($product_batch_id[$i]) {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_batch_id', $product_batch_id[$i]],
                        ['warehouse_id', $data['to_warehouse_id']],
                    ])->first();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['warehouse_id', $data['to_warehouse_id']],
                    ])->first();
                }

                if ($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty += $quantity;
                } else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id         = $id;
                    $lims_product_warehouse_data->product_batch_id   = $product_transfer['product_batch_id'];
                    $lims_product_warehouse_data->variant_id         = $product_transfer['variant_id'];
                    $lims_product_warehouse_data->warehouse_id       = $data['to_warehouse_id'];
                    $lims_product_warehouse_data->qty                = $quantity;
                }
                if ($imei_number[$i]) {
                    $lims_product_warehouse_data->imei_number = $lims_product_warehouse_data->imei_number
                        ? $lims_product_warehouse_data->imei_number . ',' . $imei_number[$i]
                        : $imei_number[$i];
                }
                $lims_product_warehouse_data->save();
            }

            $product_transfer['transfer_id']       = $lims_transfer_data->id;
            $product_transfer['product_id']        = $id;
            $product_transfer['imei_number']       = $imei_number[$i];
            $product_transfer['qty']               = $qty[$i];
            $product_transfer['purchase_unit_id']  = $lims_purchase_unit_data->id;
            $product_transfer['net_unit_cost']     = $net_unit_cost[$i];
            $product_transfer['tax_rate']          = $tax_rate[$i];
            $product_transfer['tax']               = $tax[$i];
            $product_transfer['total']             = $total[$i];
            ProductTransfer::create($product_transfer);
        }

        return redirect('transfers')->with('message', 'Transfer created successfully');
    }

    /**
     * ====================================================================
     * productTransferData($id)
     *
     * ✅ FIX: variant lookup by primary key. Displays variant name +
     *    item_code in the transfer details modal.
     * ====================================================================
     */
    public function productTransferData($id)
    {
        $lims_product_transfer_data = ProductTransfer::where('transfer_id', $id)->get();
        $product_transfer = [];
        foreach ($lims_product_transfer_data as $key => $ptd) {
            $product = Product::find($ptd->product_id);
            if (!$product) continue;
            $unit = Unit::find($ptd->purchase_unit_id);

            if ($ptd->variant_id) {
                // ✅ Primary key lookup
                $pv = ProductVariant::select('item_code', 'name')
                    ->where('id', $ptd->variant_id)
                    ->where('product_id', $ptd->product_id)
                    ->first();
                // 🪵 Fallback
                if (!$pv) {
                    $pv = ProductVariant::select('item_code', 'name')
                        ->FindExactProduct($ptd->product_id, $ptd->variant_id)->first();
                }
                if ($pv) {
                    $product->code = $pv->item_code;
                    // ✅ Display: "Product Name [Variant Name] [item_code]"
                    $product_transfer[0][$key] = $product->name . ' [' . $pv->name . '] [' . $pv->item_code . ']';
                } else {
                    $product_transfer[0][$key] = $product->name . ' [variant not found]';
                }
            } else {
                $product_transfer[0][$key] = $product->name . ' [' . $product->code . ']';
            }

            if ($ptd->imei_number)
                $product_transfer[0][$key] .= '<br>IMEI or Serial Number: ' . $ptd->imei_number;

            $product_transfer[1][$key] = $ptd->qty;
            $product_transfer[2][$key] = $unit ? $unit->unit_code : '';
            $product_transfer[3][$key] = $ptd->tax;
            $product_transfer[4][$key] = $ptd->tax_rate;
            $product_transfer[5][$key] = $ptd->total;
            $product_transfer[6][$key] = $ptd->product_batch_id
                ? (ProductBatch::select('batch_no')->find($ptd->product_batch_id)->batch_no ?? 'N/A')
                : 'N/A';
        }
        return $product_transfer;
    }

    public function transferByCsv()
    {
        if (Auth::user()->hasPermissionTo('transfers-add')) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            return view('backend.transfer.import', compact('lims_warehouse_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function importTransfer(Request $request)
    {
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv')
            return redirect()->back()->with('message', 'Please upload a CSV file');

        $filePath = $upload->getRealPath();
        $file_handle = fopen($filePath, 'r');
        $i = 0;
        while (!feof($file_handle)) {
            $current_line = fgetcsv($file_handle);
            if ($current_line && $i > 0) {
                $product_data[] = Product::where('code', $current_line[0])->first();
                if (!$product_data[$i - 1]) return redirect()->back()->with('message', 'Product does not exist!');
                $unit[] = Unit::where('unit_code', $current_line[2])->first();
                if (!$unit[$i - 1]) return redirect()->back()->with('message', 'Purchase unit does not exist!');
                if (strtolower($current_line[4]) != "no tax") {
                    $tax[] = Tax::where('name', $current_line[4])->first();
                    if (!$tax[$i - 1]) return redirect()->back()->with('message', 'Tax name does not exist!');
                } else $tax[$i - 1]['rate'] = 0;
                $qty[] = $current_line[1];
                $cost[] = $current_line[3];
            }
            $i++;
        }

        $data = $request->except('file');
        $data['reference_no'] = 'tr-' . date("Ymd") . '-' . date("his");
        $document = $request->document;
        if ($document) {
            $v = Validator::make(['extension' => strtolower($request->document->getClientOriginalExtension())], ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']);
            if ($v->fails()) return redirect()->back()->withErrors($v->errors());
            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = $data['reference_no'] . '.' . $ext;
            $document->move('public/documents/transfer', $documentName);
            $data['document'] = $documentName;
        }
        $data['user_id'] = Auth::id();
        Transfer::create($data);
        $lims_transfer_data = Transfer::latest()->first();

        foreach ($product_data as $key => $product) {
            if ($product['tax_method'] == 1) {
                $net_unit_cost = $cost[$key];
                $product_tax = $net_unit_cost * ($tax[$key]['rate'] / 100) * $qty[$key];
                $total = ($net_unit_cost * $qty[$key]) + $product_tax;
            } else {
                $net_unit_cost = (100 / (100 + $tax[$key]['rate'])) * $cost[$key];
                $product_tax = ($cost[$key] - $net_unit_cost) * $qty[$key];
                $total = $cost[$key] * $qty[$key];
            }
            if ($data['status'] == 1) {
                $quantity = $unit[$key]['operator'] == '*' ? $qty[$key] * $unit[$key]['operation_value'] : $qty[$key] / $unit[$key]['operation_value'];
                $pw = Product_Warehouse::where([['product_id', $product['id']], ['warehouse_id', $data['from_warehouse_id']]])->first();
                if ($pw) { $pw->qty -= $quantity; $pw->save(); }
                $pw = Product_Warehouse::where([['product_id', $product['id']], ['warehouse_id', $data['to_warehouse_id']]])->first();
                if ($pw) { $pw->qty += $quantity; $pw->save(); }
                else { $pw = new Product_Warehouse(); $pw->product_id = $product['id']; $pw->warehouse_id = $data['to_warehouse_id']; $pw->qty = $quantity; $pw->save(); }
            } elseif ($data['status'] == 3) {
                $quantity = $unit[$key]['operator'] == '*' ? $qty[$key] * $unit[$key]['operation_value'] : $qty[$key] / $unit[$key]['operation_value'];
                $pw = Product_Warehouse::where([['product_id', $product['id']], ['warehouse_id', $data['from_warehouse_id']]])->first();
                if ($pw) { $pw->qty -= $quantity; $pw->save(); }
            }
            $pt = new ProductTransfer();
            $pt->transfer_id = $lims_transfer_data->id;
            $pt->product_id = $product['id'];
            $pt->qty = $qty[$key];
            $pt->purchase_unit_id = $unit[$key]['id'];
            $pt->net_unit_cost = number_format((float)$net_unit_cost, config('decimal'), '.', '');
            $pt->tax_rate = $tax[$key]['rate'];
            $pt->tax = number_format((float)$product_tax, config('decimal'), '.', '');
            $pt->total = number_format((float)$total, config('decimal'), '.', '');
            $pt->save();
            $lims_transfer_data->total_qty += $qty[$key];
            $lims_transfer_data->total_tax += number_format((float)$product_tax, config('decimal'), '.', '');
            $lims_transfer_data->total_cost += number_format((float)$total, config('decimal'), '.', '');
        }
        $lims_transfer_data->item = $key + 1;
        $lims_transfer_data->grand_total = $lims_transfer_data->total_cost + $lims_transfer_data->shipping_cost;
        $lims_transfer_data->save();
        return redirect('transfers')->with('message', 'Transfer imported successfully');
    }

    public function edit($id)
    {
        if (Auth::user()->hasPermissionTo('transfers-edit')) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_transfer_data = Transfer::find($id);
            $lims_product_transfer_data = ProductTransfer::where('transfer_id', $id)->get();
            return view('backend.transfer.edit', compact('lims_warehouse_list', 'lims_transfer_data', 'lims_product_transfer_data'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    /**
     * ====================================================================
     * update
     *
     * ✅ FIX: same primary-key variant lookup pattern as store(). When
     *    reversing old transfer rows, tries primary key first with
     *    fallback to the old variant_id column.
     * ====================================================================
     */
    public function update(Request $request, $id)
    {
        $data = $request->except('document');
        $document = $request->document;
        $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at'])));
        $lims_transfer_data = Transfer::find($id);

        if ($document) {
            $v = Validator::make(['extension' => strtolower($request->document->getClientOriginalExtension())], ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']);
            if ($v->fails()) return redirect()->back()->withErrors($v->errors());
            $this->fileDelete('documents/transfer/', $lims_transfer_data->document);
            $documentName = $document->getClientOriginalName();
            $document->move('public/documents/transfer', $documentName);
            $data['document'] = $documentName;
        }

        $lims_product_transfer_data = ProductTransfer::where('transfer_id', $id)->get();
        $product_id          = $data['product_id'];
        $imei_number         = $data['imei_number'];
        $product_batch_id    = $data['product_batch_id'];
        $product_variant_id  = $data['product_variant_id'] ?? [];
        $qty                 = $data['qty'];
        $purchase_unit       = $data['purchase_unit'];
        $net_unit_cost       = $data['net_unit_cost'];
        $tax_rate            = $data['tax_rate'];
        $tax                 = $data['tax'];
        $total               = $data['subtotal'];
        $product_transfer    = [];
        $old_product_id = [];
        $old_product_variant_id = [];

        // ---- 1. Reverse existing transfer rows
        foreach ($lims_product_transfer_data as $key => $ptd) {
            $old_product_id[] = $ptd->product_id;
            $old_product_variant_id[$key] = null;
            $lims_transfer_unit_data = Unit::find($ptd->purchase_unit_id);
            if ($lims_transfer_unit_data->operator == '*')
                $quantity = $ptd->qty * $lims_transfer_unit_data->operation_value;
            else
                $quantity = $lims_transfer_unit_data->operation_value > 0 ? ($ptd->qty / $lims_transfer_unit_data->operation_value) : $ptd->qty;

            if ($lims_transfer_data->status == 1) {
                if ($ptd->variant_id) {
                    // ✅ Primary key lookup for reversal
                    $pv = ProductVariant::select('id', 'variant_id')->where('id', $ptd->variant_id)->where('product_id', $ptd->product_id)->first();
                    if (!$pv) $pv = ProductVariant::select('id', 'variant_id')->FindExactProduct($ptd->product_id, $ptd->variant_id)->first();
                    if ($pv) {
                        $old_product_variant_id[$key] = $pv->id;
                        $lims_product_from_warehouse_data = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                        if (!$lims_product_from_warehouse_data)
                            $lims_product_from_warehouse_data = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->variant_id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                        $lims_product_to_warehouse_data = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->id], ['warehouse_id', $lims_transfer_data->to_warehouse_id]])->first();
                        if (!$lims_product_to_warehouse_data)
                            $lims_product_to_warehouse_data = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->variant_id], ['warehouse_id', $lims_transfer_data->to_warehouse_id]])->first();
                    }
                } elseif ($ptd->product_batch_id) {
                    $lims_product_from_warehouse_data = Product_Warehouse::where([['product_batch_id', $ptd->product_batch_id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                    $lims_product_to_warehouse_data = Product_Warehouse::where([['product_batch_id', $ptd->product_batch_id], ['warehouse_id', $lims_transfer_data->to_warehouse_id]])->first();
                } else {
                    $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithoutVariant($ptd->product_id, $lims_transfer_data->from_warehouse_id)->first();
                    $lims_product_to_warehouse_data = Product_Warehouse::FindProductWithoutVariant($ptd->product_id, $lims_transfer_data->to_warehouse_id)->first();
                }

                // IMEI handling
                if ($ptd->imei_number && $lims_product_from_warehouse_data) {
                    $lims_product_from_warehouse_data->imei_number = $lims_product_from_warehouse_data->imei_number
                        ? $lims_product_from_warehouse_data->imei_number . ',' . $ptd->imei_number : $ptd->imei_number;
                }

                if ($lims_product_from_warehouse_data) { $lims_product_from_warehouse_data->qty += $quantity; $lims_product_from_warehouse_data->save(); }
                if ($lims_product_to_warehouse_data) {
                    $lims_product_to_warehouse_data->qty -= $quantity; $lims_product_to_warehouse_data->save();
                    if ($ptd->imei_number) {
                        $imei_numbers = explode(",", $ptd->imei_number);
                        $all_imei_numbers = explode(",", $lims_product_to_warehouse_data->imei_number ?: '');
                        foreach ($imei_numbers as $number) { if (($j = array_search($number, $all_imei_numbers)) !== false) unset($all_imei_numbers[$j]); }
                        $lims_product_to_warehouse_data->imei_number = implode(",", $all_imei_numbers);
                        $lims_product_to_warehouse_data->save();
                    }
                }
            } elseif ($lims_transfer_data->status == 3) {
                if ($ptd->variant_id) {
                    $pv = ProductVariant::select('id', 'variant_id')->where('id', $ptd->variant_id)->where('product_id', $ptd->product_id)->first();
                    if (!$pv) $pv = ProductVariant::select('id', 'variant_id')->FindExactProduct($ptd->product_id, $ptd->variant_id)->first();
                    if ($pv) {
                        $old_product_variant_id[$key] = $pv->id;
                        $lims_product_from_warehouse_data = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                        if (!$lims_product_from_warehouse_data)
                            $lims_product_from_warehouse_data = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->variant_id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                    }
                } elseif ($ptd->product_batch_id) {
                    $lims_product_from_warehouse_data = Product_Warehouse::where([['product_batch_id', $ptd->product_batch_id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                } else {
                    $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithoutVariant($ptd->product_id, $lims_transfer_data->from_warehouse_id)->first();
                }
                if ($ptd->imei_number && $lims_product_from_warehouse_data) {
                    $lims_product_from_warehouse_data->imei_number = $lims_product_from_warehouse_data->imei_number
                        ? $lims_product_from_warehouse_data->imei_number . ',' . $ptd->imei_number : $ptd->imei_number;
                }
                if ($lims_product_from_warehouse_data) { $lims_product_from_warehouse_data->qty += $quantity; $lims_product_from_warehouse_data->save(); }
            }

            // Delete old row if product/variant no longer in submitted list
            if ($ptd->variant_id && !(in_array($old_product_variant_id[$key], $product_variant_id)))
                $ptd->delete();
            elseif (!(in_array($old_product_id[$key], $product_id)))
                $ptd->delete();
        }

        // ---- 2. Apply new submitted products
        foreach ($product_id as $key => $pro_id) {
            $lims_product_data = Product::select('is_variant')->find($pro_id);
            $lims_transfer_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
            $variant_id = null;
            $product_transfer['product_batch_id'] = null;

            if ($lims_transfer_unit_data->operator == '*')
                $quantity = $qty[$key] * $lims_transfer_unit_data->operation_value;
            else
                $quantity = $lims_transfer_unit_data->operation_value > 0 ? ($qty[$key] / $lims_transfer_unit_data->operation_value) : $qty[$key];

            if ($data['status'] == 1) {
                if ($lims_product_data->is_variant) {
                    // ✅ Look up by primary key
                    $pv = null;
                    if (!empty($product_variant_id[$key])) {
                        $pv = ProductVariant::select('id', 'variant_id')->where('id', $product_variant_id[$key])->where('product_id', $pro_id)->first();
                    }
                    if (!$pv) {
                        $pv = ProductVariant::select('id', 'variant_id')->FindExactProductWithCode($pro_id, $product_code[$key] ?? '')->first();
                    }
                    if (!$pv) continue;

                    $lims_product_from_warehouse_data = Product_Warehouse::where([['product_id', $pro_id], ['variant_id', $pv->id], ['warehouse_id', $data['from_warehouse_id']]])->first();
                    if (!$lims_product_from_warehouse_data)
                        $lims_product_from_warehouse_data = Product_Warehouse::where([['product_id', $pro_id], ['variant_id', $pv->variant_id], ['warehouse_id', $data['from_warehouse_id']]])->first();
                    $lims_product_to_warehouse_data = Product_Warehouse::where([['product_id', $pro_id], ['variant_id', $pv->id], ['warehouse_id', $data['to_warehouse_id']]])->first();
                    if (!$lims_product_to_warehouse_data)
                        $lims_product_to_warehouse_data = Product_Warehouse::where([['product_id', $pro_id], ['variant_id', $pv->variant_id], ['warehouse_id', $data['to_warehouse_id']]])->first();
                    $variant_id = $pv->id;  // ✅ store product_variants.id
                } elseif ($product_batch_id[$key]) {
                    $lims_product_from_warehouse_data = Product_Warehouse::where([['product_batch_id', $product_batch_id[$key]], ['warehouse_id', $data['from_warehouse_id']]])->first();
                    $lims_product_to_warehouse_data = Product_Warehouse::where([['product_batch_id', $product_batch_id[$key]], ['warehouse_id', $data['to_warehouse_id']]])->first();
                    $product_transfer['product_batch_id'] = $product_batch_id[$key];
                } else {
                    $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['from_warehouse_id'])->first();
                    $lims_product_to_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['to_warehouse_id'])->first();
                }

                // IMEI
                if ($imei_number[$key] && $lims_product_from_warehouse_data) {
                    $imei_numbers = explode(",", $imei_number[$key]);
                    $all_imei_numbers = explode(",", $lims_product_from_warehouse_data->imei_number ?: '');
                    foreach ($imei_numbers as $number) { if (($j = array_search($number, $all_imei_numbers)) !== false) unset($all_imei_numbers[$j]); }
                    $lims_product_from_warehouse_data->imei_number = implode(",", $all_imei_numbers);
                }

                if ($lims_product_from_warehouse_data) { $lims_product_from_warehouse_data->qty -= $quantity; $lims_product_from_warehouse_data->save(); }

                if ($lims_product_to_warehouse_data) {
                    $lims_product_to_warehouse_data->qty += $quantity;
                } else {
                    $lims_product_to_warehouse_data = new Product_Warehouse();
                    $lims_product_to_warehouse_data->product_id       = $pro_id;
                    $lims_product_to_warehouse_data->variant_id        = $variant_id;
                    $lims_product_to_warehouse_data->product_batch_id  = $product_transfer['product_batch_id'];
                    $lims_product_to_warehouse_data->warehouse_id      = $data['to_warehouse_id'];
                    $lims_product_to_warehouse_data->qty               = $quantity;
                }
                if ($imei_number[$key]) {
                    $lims_product_to_warehouse_data->imei_number = $lims_product_to_warehouse_data->imei_number
                        ? $lims_product_to_warehouse_data->imei_number . ',' . $imei_number[$key] : $imei_number[$key];
                }
                $lims_product_to_warehouse_data->save();
            } elseif ($data['status'] == 3) {
                if ($lims_product_data->is_variant) {
                    $pv = null;
                    if (!empty($product_variant_id[$key])) {
                        $pv = ProductVariant::select('id', 'variant_id')->where('id', $product_variant_id[$key])->where('product_id', $pro_id)->first();
                    }
                    if (!$pv) {
                        $pv = ProductVariant::select('id', 'variant_id')->FindExactProductWithCode($pro_id, $product_code[$key] ?? '')->first();
                    }
                    if (!$pv) continue;

                    $lims_product_from_warehouse_data = Product_Warehouse::where([['product_id', $pro_id], ['variant_id', $pv->id], ['warehouse_id', $data['from_warehouse_id']]])->first();
                    if (!$lims_product_from_warehouse_data)
                        $lims_product_from_warehouse_data = Product_Warehouse::where([['product_id', $pro_id], ['variant_id', $pv->variant_id], ['warehouse_id', $data['from_warehouse_id']]])->first();
                    $variant_id = $pv->id;
                } elseif ($product_batch_id[$key]) {
                    $lims_product_from_warehouse_data = Product_Warehouse::where([['product_batch_id', $product_batch_id[$key]], ['warehouse_id', $data['from_warehouse_id']]])->first();
                    $product_transfer['product_batch_id'] = $product_batch_id[$key];
                } else {
                    $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['from_warehouse_id'])->first();
                }
                if ($imei_number[$key] && $lims_product_from_warehouse_data) {
                    $imei_numbers = explode(",", $imei_number[$key]);
                    $all_imei_numbers = explode(",", $lims_product_from_warehouse_data->imei_number ?: '');
                    foreach ($imei_numbers as $number) { if (($j = array_search($number, $all_imei_numbers)) !== false) unset($all_imei_numbers[$j]); }
                    $lims_product_from_warehouse_data->imei_number = implode(",", $all_imei_numbers);
                }
                if ($lims_product_from_warehouse_data) { $lims_product_from_warehouse_data->qty -= $quantity; $lims_product_from_warehouse_data->save(); }
            }

            $product_transfer['product_id']       = $pro_id;
            $product_transfer['variant_id']       = $variant_id;
            $product_transfer['imei_number']      = $imei_number[$key];
            $product_transfer['transfer_id']      = $id;
            $product_transfer['qty']              = $qty[$key];
            $product_transfer['purchase_unit_id'] = $lims_transfer_unit_data->id;
            $product_transfer['net_unit_cost']    = $net_unit_cost[$key];
            $product_transfer['tax_rate']         = $tax_rate[$key];
            $product_transfer['tax']              = $tax[$key];
            $product_transfer['total']            = $total[$key];

            if ($lims_product_data->is_variant && in_array($product_variant_id[$key] ?? null, $old_product_variant_id)) {
                ProductTransfer::where([['transfer_id', $id], ['product_id', $pro_id], ['variant_id', $variant_id]])->update($product_transfer);
            } elseif ($variant_id == null && in_array($pro_id, $old_product_id)) {
                ProductTransfer::where([['transfer_id', $id], ['product_id', $pro_id]])->update($product_transfer);
            } else
                ProductTransfer::create($product_transfer);
        }

        $lims_transfer_data->update($data);
        return redirect('transfers')->with('message', 'Transfer updated successfully');
    }

    /**
     * ====================================================================
     * Helper — reverses a transfer's stock movements, used by both
     * deleteBySelection() and destroy().
     *
     * ✅ FIX: variant lookups use product_variants.id (primary key) with
     *    fallback.
     * ====================================================================
     */
    private function _reverseTransfer($transfer_id)
    {
        $lims_transfer_data = Transfer::find($transfer_id);
        if (!$lims_transfer_data) return;

        $lims_product_transfer_data = ProductTransfer::where('transfer_id', $transfer_id)->get();
        foreach ($lims_product_transfer_data as $ptd) {
            $lims_transfer_unit_data = Unit::find($ptd->purchase_unit_id);
            if (!$lims_transfer_unit_data) { $ptd->delete(); continue; }
            if ($lims_transfer_unit_data->operator == '*')
                $quantity = $ptd->qty * $lims_transfer_unit_data->operation_value;
            else
                $quantity = $lims_transfer_unit_data->operation_value > 0 ? ($ptd->qty / $lims_transfer_unit_data->operation_value) : $ptd->qty;

            if ($lims_transfer_data->status == 1) {
                // Add back to from_warehouse
                if ($ptd->variant_id) {
                    $pv = ProductVariant::select('id', 'variant_id')->where('id', $ptd->variant_id)->where('product_id', $ptd->product_id)->first();
                    if (!$pv) $pv = ProductVariant::select('id', 'variant_id')->FindExactProduct($ptd->product_id, $ptd->variant_id)->first();
                    if ($pv) {
                        $fw = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                        if (!$fw) $fw = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->variant_id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                        $tw = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->id], ['warehouse_id', $lims_transfer_data->to_warehouse_id]])->first();
                        if (!$tw) $tw = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->variant_id], ['warehouse_id', $lims_transfer_data->to_warehouse_id]])->first();
                    }
                } elseif ($ptd->product_batch_id) {
                    $fw = Product_Warehouse::where([['product_batch_id', $ptd->product_batch_id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                    $tw = Product_Warehouse::where([['product_batch_id', $ptd->product_batch_id], ['warehouse_id', $lims_transfer_data->to_warehouse_id]])->first();
                } else {
                    $fw = Product_Warehouse::FindProductWithoutVariant($ptd->product_id, $lims_transfer_data->from_warehouse_id)->first();
                    $tw = Product_Warehouse::FindProductWithoutVariant($ptd->product_id, $lims_transfer_data->to_warehouse_id)->first();
                }
                if ($fw) {
                    $fw->qty += $quantity;
                    if ($ptd->imei_number) $fw->imei_number = $fw->imei_number ? $fw->imei_number . ',' . $ptd->imei_number : $ptd->imei_number;
                    $fw->save();
                }
                if ($tw) {
                    $tw->qty -= $quantity;
                    if ($ptd->imei_number) {
                        $imei_numbers = explode(",", $ptd->imei_number);
                        $all_imei_numbers = explode(",", $tw->imei_number ?: '');
                        foreach ($imei_numbers as $number) { if (($j = array_search($number, $all_imei_numbers)) !== false) unset($all_imei_numbers[$j]); }
                        $tw->imei_number = implode(",", $all_imei_numbers);
                    }
                    $tw->save();
                }
            } elseif ($lims_transfer_data->status == 3) {
                if ($ptd->variant_id) {
                    $pv = ProductVariant::select('id', 'variant_id')->where('id', $ptd->variant_id)->where('product_id', $ptd->product_id)->first();
                    if (!$pv) $pv = ProductVariant::select('id', 'variant_id')->FindExactProduct($ptd->product_id, $ptd->variant_id)->first();
                    if ($pv) {
                        $fw = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                        if (!$fw) $fw = Product_Warehouse::where([['product_id', $ptd->product_id], ['variant_id', $pv->variant_id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                    }
                } elseif ($ptd->product_batch_id) {
                    $fw = Product_Warehouse::where([['product_batch_id', $ptd->product_batch_id], ['warehouse_id', $lims_transfer_data->from_warehouse_id]])->first();
                } else {
                    $fw = Product_Warehouse::FindProductWithoutVariant($ptd->product_id, $lims_transfer_data->from_warehouse_id)->first();
                }
                if ($fw) {
                    $fw->qty += $quantity;
                    if ($ptd->imei_number) $fw->imei_number = $fw->imei_number ? $fw->imei_number . ',' . $ptd->imei_number : $ptd->imei_number;
                    $fw->save();
                }
            }
            $ptd->delete();
        }
    }

    public function deleteBySelection(Request $request)
    {
        $transfer_id = $request['transferIdArray'];
        foreach ($transfer_id as $id) {
            $this->_reverseTransfer($id);
            $lims_transfer_data = Transfer::find($id);
            $this->fileDelete('documents/transfer/', $lims_transfer_data->document);
            $lims_transfer_data->delete();
        }
        return 'Transfer deleted successfully!';
    }

    public function destroy($id)
    {
        $this->_reverseTransfer($id);
        $lims_transfer_data = Transfer::find($id);
        $this->fileDelete('documents/transfer/', $lims_transfer_data->document);
        $lims_transfer_data->delete();
        return redirect('transfers')->with('not_permitted', 'Transfer deleted successfully');
    }
}
