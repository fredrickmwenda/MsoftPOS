<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Product_Warehouse;
use App\Models\Product;
use App\Models\Adjustment;
use App\Models\ProductAdjustment;
use Illuminate\Support\Facades\DB;
use App\Models\StockCount;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AdjustmentController extends Controller
{
    public function index()
    {
        if (Auth::user()->hasPermissionTo('adjustment')) {
            $lims_adjustment_all = Adjustment::orderBy('id', 'desc')->get();
            return view('backend.adjustment.index', compact('lims_adjustment_all'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    /**
     * ====================================================================
     * getProduct($id)
     *
     * Returns [product_code[], product_name[], product_qty[]] for every
     * product (incl. variant items) physically present in warehouse $id.
     *
     * ✅ FIX: variant products are looked up by product_variants.id
     *    (= product_warehouse.variant_id), not by product_variants.variant_id
     *    (= variants.id). The old FindExactProduct() scope filtered by the
     *    wrong column and always returned NULL, so variant products were
     *    silently skipped — the adjustment search bar couldn't find them.
     * ====================================================================
     */
    public function getProduct($id)
    {
        $product_code = [];
        $product_name = [];
        $product_qty  = [];

        // ---- Standard (non-variant, non-batch) products in this warehouse
        $lims_product_warehouse_data = DB::table('products')
            ->join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
            ->whereNull('products.is_variant')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $id],
            ])
            ->select('product_warehouse.qty', 'products.code', 'products.name')
            ->get();

        foreach ($lims_product_warehouse_data as $product_warehouse) {
            $product_qty[]  = $product_warehouse->qty;
            $product_code[] = $product_warehouse->code;
            $product_name[] = $product_warehouse->name;
        }

        // ---- Variant products in this warehouse
        $lims_product_withVariant_warehouse_data = DB::table('products')
            ->join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
            ->whereNotNull('products.is_variant')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $id],
            ])
            ->select(
                'products.name',
                'product_warehouse.qty',
                'product_warehouse.product_id',
                'product_warehouse.variant_id'   // this stores product_variants.id
            )
            ->get();

        $log_variant_total  = $lims_product_withVariant_warehouse_data->count();
        $log_variant_loaded = 0;
        $log_variant_fallback = 0;

        foreach ($lims_product_withVariant_warehouse_data as $product_warehouse) {
            // ✅ Primary lookup: by product_variants.id (= product_warehouse.variant_id)
            $product_variant = ProductVariant::select('id', 'product_id', 'variant_id', 'item_code', 'name')
                ->where('id', $product_warehouse->variant_id)
                ->where('product_id', $product_warehouse->product_id)
                ->first();

            $lookup_method = 'primary_key';

            // 🪵 Fallback: legacy data might have stored variants.id in
            //    product_warehouse.variant_id. Try the old scope as a safety net.
            if (!$product_variant) {
                $product_variant = ProductVariant::select('id', 'product_id', 'variant_id', 'item_code', 'name')
                    ->FindExactProduct($product_warehouse->product_id, $product_warehouse->variant_id)
                    ->first();
                $lookup_method = $product_variant ? 'findexact_fallback' : 'not_found';
                if ($product_variant) {
                    $log_variant_fallback++;
                }
            }

            Log::info('Adjustment getProduct variant lookup', [
                'warehouse_id'   => $id,
                'product_id'     => $product_warehouse->product_id,
                'product_name'   => $product_warehouse->name,
                'pw_variant_id'  => $product_warehouse->variant_id,
                'lookup_method'  => $lookup_method,
                'pv_found'       => $product_variant ? 'YES' : 'NO',
                'pv_id'          => $product_variant->id ?? null,
                'pv_item_code'   => $product_variant->item_code ?? null,
                'qty_in_warehouse' => $product_warehouse->qty,
            ]);

            if ($product_variant) {
                $product_qty[]  = $product_warehouse->qty;
                // ✅ Use the variant item_code (e.g. "Wine long/S-97031263") —
                //    this is what the search bar will show and what the user
                //    will type to find this specific Color/Size combo.
                $product_code[] = $product_variant->item_code;
                // ✅ Append the variant name to the product name so the
                //    autocomplete dropdown shows "Mana Product [Wine long/S]"
                //    instead of just "Mana Product".
                $product_name[] = $product_warehouse->name . ' [' . $product_variant->name . ']';
                $log_variant_loaded++;
            }
        }

        Log::info('Adjustment getProduct summary', [
            'warehouse_id'        => $id,
            'standard_rows'       => count($lims_product_warehouse_data),
            'variant_rows_total'  => $log_variant_total,
            'variant_rows_loaded' => $log_variant_loaded,
            'variant_fallbacks'   => $log_variant_fallback,
            'total_in_array'      => count($product_code),
        ]);

        $product_data[] = $product_code;
        $product_data[] = $product_name;
        $product_data[] = $product_qty;
        return $product_data;
    }

    /**
     * ====================================================================
     * limsProductSearch
     *
     * Called from the adjustment create/edit page when the user picks a
     * product from the autocomplete dropdown.
     *
     * The search string format is either:
     *   "82933710"                    → standard product code
     *   "Wine long/S-97031263"        → variant item_code (Color/Size combo)
     *   "Mana Product [Wine long/S]"  → name + variant name (from our dropdown)
     *
     * ✅ FIX: variant products are now looked up by item_code (unique per
     *    pivot row), and we return product_variants.id (not variants.id)
     *    so store()/update() can find the matching Product_Warehouse row.
     * ====================================================================
     */
    public function limsProductSearch(Request $request)
    {
        $raw_data = $request['data'];
        $warehouse_id = $request->input('warehouse_id');

        // Strip a trailing "(...)" suffix if present (autocomplete sometimes
        // appends the name in parentheses).
        $product_code = explode("(", $raw_data);
        $product_code[0] = rtrim($product_code[0], " ");
        $search_term = $product_code[0];

        // 1) Try to find by exact main product code (standard products)
        $lims_product_data = Product::where([
            ['code', $search_term],
            ['is_active', true],
        ])->first();

        $product_variant_id = null;
        $return_code        = null;

        // 2) If not found, try variant item_code (variant products)
        if (!$lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.is_variant',
                    'product_variants.id as product_variant_id',
                    'product_variants.variant_id as pv_variant_id',
                    'product_variants.item_code',
                    'product_variants.name as pv_name',
                    'product_variants.qty as pv_qty'
                )
                ->where([
                    ['product_variants.item_code', $search_term],
                    ['products.is_active', true],
                ])->first();
        }

        if (!$lims_product_data) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        // Build the response array
        $product = [];
        $product[] = $lims_product_data->name;

        if ($lims_product_data->is_variant) {
            $product[] = $lims_product_data->item_code;
            $product_variant_id = $lims_product_data->product_variant_id;
            $return_code = $lims_product_data->item_code;
        } else {
            $product[] = $lims_product_data->code;
            $return_code = $lims_product_data->code;
        }

        // ✅ Stock qty comes from product_warehouse for the selected warehouse
        //    — same source as getProduct() uses for the dropdown. This makes
        //    the Stock Quantity column in the order table match the dropdown.
        //
        //    Previously this returned product_variants.qty (total across all
        //    warehouses) which was off by however many units had been
        //    adjusted in other warehouses or by the old buggy controller that
        //    only updated product_variants.qty without touching
        //    product_warehouse.qty.
        $stock_qty = 0;
        if ($warehouse_id) {
            if ($lims_product_data->is_variant && $product_variant_id) {
                // Variant product → look up by product_variants.id (= the
                // product_variant_id we just resolved). Fallback to the old
                // variant_id column for any legacy warehouse rows.
                $pw = Product_Warehouse::where([
                    ['product_id', $lims_product_data->id],
                    ['variant_id', $product_variant_id],
                    ['warehouse_id', $warehouse_id],
                ])->first();
                if (!$pw) {
                    $pw = Product_Warehouse::where([
                        ['product_id', $lims_product_data->id],
                        ['variant_id', $lims_product_data->pv_variant_id ?? $lims_product_data->product_variant_id],
                        ['warehouse_id', $warehouse_id],
                    ])->first();
                }
                $stock_qty = $pw ? $pw->qty : 0;
            } else {
                // Standard product → look up by product_id + warehouse_id
                $pw = Product_Warehouse::where([
                    ['product_id', $lims_product_data->id],
                    ['warehouse_id', $warehouse_id],
                ])->first();
                $stock_qty = $pw ? $pw->qty : ($lims_product_data->qty ?? 0);
            }
        } else {
            // No warehouse selected — fall back to the model's qty
            $stock_qty = $lims_product_data->is_variant
                ? ($lims_product_data->pv_qty ?? 0)
                : ($lims_product_data->qty ?? 0);
        }

        $product[] = $lims_product_data->id;
        $product[] = $product_variant_id;
        $product[] = $stock_qty;

        Log::info('Adjustment limsProductSearch', [
            'search_term'        => $search_term,
            'warehouse_id'       => $warehouse_id,
            'product_id'         => $lims_product_data->id,
            'product_name'       => $lims_product_data->name,
            'is_variant'         => $lims_product_data->is_variant,
            'return_code'        => $return_code,
            'product_variant_id' => $product_variant_id,
            'stock_qty'          => $stock_qty,
            'pv_qty'             => $lims_product_data->pv_qty ?? null,
        ]);

        return $product;
    }

    public function create()
    {
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        return view('backend.adjustment.create', compact('lims_warehouse_list'));
    }

    /**
     * ====================================================================
     * store
     *
     * ✅ FIX: variant lookups use product_variants.id (primary key)
     *    everywhere — both for Product_Warehouse and ProductAdjustment.
     *    This matches what ProductController now writes to
     *    product_warehouse.variant_id, so the warehouse row is actually
     *    found and the stock gets adjusted.
     * ====================================================================
     */
    public function store(Request $request)
    {
        $data = $request->except('document');

        if (isset($data['stock_count_id'])) {
            $lims_stock_count_data = StockCount::find($data['stock_count_id']);
            $lims_stock_count_data->is_adjusted = true;
            $lims_stock_count_data->save();
        }

        $data['reference_no'] = 'adr-' . date("Ymd") . '-' . date("his");

        $document = $request->document;
        if ($document) {
            $documentName = $document->getClientOriginalName();
            $document->move('public/documents/adjustment', $documentName);
            $data['document'] = $documentName;
        }

        $lims_adjustment_data = Adjustment::create($data);

        $product_id         = $data['product_id'];
        $product_code       = $data['product_code'];
        $product_variant_id = $data['product_variant_id'] ?? [];   // ✅ new field from limsProductSearch
        $qty                = $data['qty'];
        $action             = $data['action'];

        foreach ($product_id as $key => $pro_id) {
            $lims_product_data = Product::find($pro_id);

            if ($lims_product_data->is_variant) {
                // ✅ Look up the ProductVariant by its PRIMARY KEY (= the
                //    product_variant_id returned from limsProductSearch).
                //    Fall back to FindExactProductWithCode if the form didn't
                //    send product_variant_id (legacy front-end).
                $lims_product_variant_data = null;
                if (!empty($product_variant_id[$key])) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty', 'item_code', 'name')
                        ->where('id', $product_variant_id[$key])
                        ->where('product_id', $pro_id)
                        ->first();
                }
                if (!$lims_product_variant_data) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty', 'item_code', 'name')
                        ->FindExactProductWithCode($pro_id, $product_code[$key])
                        ->first();
                }

                if (!$lims_product_variant_data) {
                    Log::warning('Adjustment store — variant not found', [
                        'product_id' => $pro_id,
                        'product_code' => $product_code[$key] ?? null,
                        'product_variant_id' => $product_variant_id[$key] ?? null,
                    ]);
                    continue;
                }

                // ✅ Look up Product_Warehouse by product_variants.id
                //    (NOT variants.id)
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $pro_id],
                    ['variant_id', $lims_product_variant_data->id],
                    ['warehouse_id', $data['warehouse_id']],
                ])->first();

                // 🪵 Fallback: legacy data might have used variants.id
                if (!$lims_product_warehouse_data) {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $pro_id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                }

                if ($action[$key] == '-') {
                    $lims_product_variant_data->qty -= $qty[$key];
                } elseif ($action[$key] == '+') {
                    $lims_product_variant_data->qty += $qty[$key];
                }
                $lims_product_variant_data->save();

                // ✅ Store product_variants.id in product_adjustments.variant_id
                //    so the index page and edit page can look it up by primary
                //    key. (Old code stored variants.id here, which broke the
                //    index page lookup.)
                $variant_id = $lims_product_variant_data->id;
            } else {
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $pro_id],
                    ['warehouse_id', $data['warehouse_id']],
                ])->first();
                $variant_id = null;
            }

            if ($lims_product_warehouse_data) {
                if ($action[$key] == '-') {
                    $lims_product_data->qty -= $qty[$key];
                    $lims_product_warehouse_data->qty -= $qty[$key];
                } elseif ($action[$key] == '+') {
                    $lims_product_data->qty += $qty[$key];
                    $lims_product_warehouse_data->qty += $qty[$key];
                }
                $lims_product_warehouse_data->save();
            } else {
                // No warehouse row exists yet — for '+' action, create one
                if ($action[$key] == '+' && $lims_product_data->is_variant && isset($lims_product_variant_data)) {
                    $lims_product_warehouse_data = Product_Warehouse::create([
                        'product_id'   => $pro_id,
                        'variant_id'   => $lims_product_variant_data->id,
                        'warehouse_id' => $data['warehouse_id'],
                        'qty'          => $qty[$key],
                    ]);
                }
            }

            $lims_product_data->save();

            $product_adjustment['product_id']     = $pro_id;
            $product_adjustment['variant_id']     = $variant_id;       // ✅ product_variants.id
            $product_adjustment['adjustment_id']  = $lims_adjustment_data->id;
            $product_adjustment['qty']            = $qty[$key];
            $product_adjustment['action']         = $action[$key];
            ProductAdjustment::create($product_adjustment);
        }

        return redirect('qty_adjustment')->with('message', 'Data inserted successfully');
    }

    public function edit($id)
    {
        $lims_adjustment_data = Adjustment::find($id);
        $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        return view('backend.adjustment.edit', compact('lims_adjustment_data', 'lims_warehouse_list', 'lims_product_adjustment_data'));
    }

    /**
     * ====================================================================
     * update
     *
     * ✅ FIX: same variant lookup fix as store(). Also when reverting
     *    old adjustments, we look up the ProductVariant by primary key
     *    first (new convention) with a fallback to the old variant_id
     *    column (legacy data).
     * ====================================================================
     */
    public function update(Request $request, $id)
    {
        $data = $request->except('document');
        $lims_adjustment_data = Adjustment::find($id);

        $document = $request->document;
        if ($document) {
            $this->fileDelete('documents/adjustment/', $lims_adjustment_data->document);
            $documentName = $document->getClientOriginalName();
            $document->move('public/documents/adjustment', $documentName);
            $data['document'] = $documentName;
        }

        $lims_adjustment_data = Adjustment::find($id);
        $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();

        $product_id         = $data['product_id'];
        $product_variant_id = $data['product_variant_id'] ?? [];
        $product_code       = $data['product_code'];
        $qty                = $data['qty'];
        $action             = $data['action'];

        $old_product_variant_id = [];
        $old_product_id = [];

        // ---- 1. Reverse every existing ProductAdjustment row
        foreach ($lims_product_adjustment_data as $key => $product_adjustment_data) {
            $old_product_id[] = $product_adjustment_data->product_id;
            $lims_product_data = Product::find($product_adjustment_data->product_id);

            if ($product_adjustment_data->variant_id) {
                // ✅ Try primary key first (new convention)
                $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')
                    ->where('id', $product_adjustment_data->variant_id)
                    ->where('product_id', $product_adjustment_data->product_id)
                    ->first();

                // 🪵 Fallback to old convention (variant_id = variants.id)
                if (!$lims_product_variant_data) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')
                        ->where([
                            ['product_id', $product_adjustment_data->product_id],
                            ['variant_id', $product_adjustment_data->variant_id],
                        ])->first();
                }

                if ($lims_product_variant_data) {
                    $old_product_variant_id[$key] = $lims_product_variant_data->id;

                    if ($product_adjustment_data->action == '-') {
                        $lims_product_variant_data->qty += $product_adjustment_data->qty;
                    } elseif ($product_adjustment_data->action == '+') {
                        $lims_product_variant_data->qty -= $product_adjustment_data->qty;
                    }
                    $lims_product_variant_data->save();

                    // ✅ Look up Product_Warehouse by product_variants.id
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_adjustment_data->product_id],
                        ['variant_id', $lims_product_variant_data->id],
                        ['warehouse_id', $lims_adjustment_data->warehouse_id],
                    ])->first();
                    // 🪵 Fallback
                    if (!$lims_product_warehouse_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $product_adjustment_data->product_id],
                            ['variant_id', $lims_product_variant_data->variant_id],
                            ['warehouse_id', $lims_adjustment_data->warehouse_id],
                        ])->first();
                    }
                } else {
                    $old_product_variant_id[$key] = null;
                    $lims_product_warehouse_data = null;
                }
            } else {
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $product_adjustment_data->product_id],
                    ['warehouse_id', $lims_adjustment_data->warehouse_id],
                ])->first();
            }

            if ($lims_product_warehouse_data) {
                if ($product_adjustment_data->action == '-') {
                    $lims_product_data->qty += $product_adjustment_data->qty;
                    $lims_product_warehouse_data->qty += $product_adjustment_data->qty;
                } elseif ($product_adjustment_data->action == '+') {
                    $lims_product_data->qty -= $product_adjustment_data->qty;
                    $lims_product_warehouse_data->qty -= $product_adjustment_data->qty;
                }
                $lims_product_warehouse_data->save();
            }
            $lims_product_data->save();

            // Delete the old ProductAdjustment row if the product is no longer
            // in the submitted list, OR (for variants) if the specific variant
            // is no longer in the submitted list.
            if ($product_adjustment_data->variant_id && !(in_array($old_product_variant_id[$key], $product_variant_id))) {
                $product_adjustment_data->delete();
            } elseif (!(in_array($old_product_id[$key], $product_id))) {
                $product_adjustment_data->delete();
            }
        }

        // ---- 2. Apply the new submitted products
        foreach ($product_id as $key => $pro_id) {
            $lims_product_data = Product::find($pro_id);

            if ($lims_product_data->is_variant) {
                // ✅ Look up ProductVariant by primary key first
                $lims_product_variant_data = null;
                if (!empty($product_variant_id[$key])) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty', 'item_code', 'name')
                        ->where('id', $product_variant_id[$key])
                        ->where('product_id', $pro_id)
                        ->first();
                }
                if (!$lims_product_variant_data) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty', 'item_code', 'name')
                        ->FindExactProductWithCode($pro_id, $product_code[$key])
                        ->first();
                }

                if (!$lims_product_variant_data) {
                    continue;
                }

                // ✅ Look up Product_Warehouse by product_variants.id
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $pro_id],
                    ['variant_id', $lims_product_variant_data->id],
                    ['warehouse_id', $data['warehouse_id']],
                ])->first();
                // 🪵 Fallback
                if (!$lims_product_warehouse_data) {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $pro_id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                }

                if ($action[$key] == '-') {
                    $lims_product_variant_data->qty -= $qty[$key];
                } elseif ($action[$key] == '+') {
                    $lims_product_variant_data->qty += $qty[$key];
                }
                $lims_product_variant_data->save();

                $variant_id = $lims_product_variant_data->id;   // ✅ product_variants.id
            } else {
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $pro_id],
                    ['warehouse_id', $data['warehouse_id']],
                ])->first();
                $variant_id = null;
            }

            if ($lims_product_warehouse_data) {
                if ($action[$key] == '-') {
                    $lims_product_data->qty -= $qty[$key];
                    $lims_product_warehouse_data->qty -= $qty[$key];
                } elseif ($action[$key] == '+') {
                    $lims_product_data->qty += $qty[$key];
                    $lims_product_warehouse_data->qty += $qty[$key];
                }
                $lims_product_warehouse_data->save();
            }
            $lims_product_data->save();

            $product_adjustment['product_id']    = $pro_id;
            $product_adjustment['variant_id']    = $variant_id;
            $product_adjustment['adjustment_id'] = $id;
            $product_adjustment['qty']           = $qty[$key];
            $product_adjustment['action']        = $action[$key];

            if ($product_adjustment['variant_id'] && in_array($product_variant_id[$key], $old_product_variant_id)) {
                ProductAdjustment::where([
                    ['product_id', $pro_id],
                    ['variant_id', $product_adjustment['variant_id']],
                    ['adjustment_id', $id],
                ])->update($product_adjustment);
            } elseif ($product_adjustment['variant_id'] === null && in_array($pro_id, $old_product_id)) {
                ProductAdjustment::where([
                    ['adjustment_id', $id],
                    ['product_id', $pro_id],
                ])->update($product_adjustment);
            } else {
                ProductAdjustment::create($product_adjustment);
            }
        }

        $lims_adjustment_data->update($data);
        return redirect('qty_adjustment')->with('message', 'Data updated successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $adjustment_id = $request['adjustmentIdArray'];
        foreach ($adjustment_id as $id) {
            $this->_reverseAdjustment($id);
            $lims_adjustment_data = Adjustment::find($id);
            $this->fileDelete('documents/adjustment/', $lims_adjustment_data->document);
            $lims_adjustment_data->delete();
        }
        return 'Data deleted successfully';
    }

    public function destroy($id)
    {
        $this->_reverseAdjustment($id);
        $lims_adjustment_data = Adjustment::find($id);
        $this->fileDelete('documents/adjustment/', $lims_adjustment_data->document);
        $lims_adjustment_data->delete();

        return redirect('qty_adjustment')->with('not_permitted', 'Data deleted successfully');
    }

    /**
     * Helper — reverse every ProductAdjustment row for an Adjustment,
     * then delete the ProductAdjustment rows. Used by destroy() and
     * deleteBySelection() so the stock rollback logic lives in one place.
     *
     * ✅ FIX: variant lookups use product_variants.id (primary key) with
     *    fallback to variants.id for legacy data.
     */
    private function _reverseAdjustment($adjustment_id)
    {
        $lims_adjustment_data = Adjustment::find($adjustment_id);
        if (!$lims_adjustment_data) return;

        $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $adjustment_id)->get();

        foreach ($lims_product_adjustment_data as $product_adjustment_data) {
            $lims_product_data = Product::find($product_adjustment_data->product_id);
            if (!$lims_product_data) {
                $product_adjustment_data->delete();
                continue;
            }

            if ($product_adjustment_data->variant_id) {
                // ✅ Try primary key first (new convention: variant_id = product_variants.id)
                $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')
                    ->where('id', $product_adjustment_data->variant_id)
                    ->where('product_id', $product_adjustment_data->product_id)
                    ->first();

                // 🪵 Fallback (legacy: variant_id = variants.id)
                if (!$lims_product_variant_data) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')
                        ->where([
                            ['product_id', $product_adjustment_data->product_id],
                            ['variant_id', $product_adjustment_data->variant_id],
                        ])->first();
                }

                if ($lims_product_variant_data) {
                    if ($product_adjustment_data->action == '-') {
                        $lims_product_variant_data->qty += $product_adjustment_data->qty;
                    } elseif ($product_adjustment_data->action == '+') {
                        $lims_product_variant_data->qty -= $product_adjustment_data->qty;
                    }
                    $lims_product_variant_data->save();

                    // ✅ Look up Product_Warehouse by product_variants.id
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_adjustment_data->product_id],
                        ['variant_id', $lims_product_variant_data->id],
                        ['warehouse_id', $lims_adjustment_data->warehouse_id],
                    ])->first();
                    // 🪵 Fallback
                    if (!$lims_product_warehouse_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $product_adjustment_data->product_id],
                            ['variant_id', $lims_product_variant_data->variant_id],
                            ['warehouse_id', $lims_adjustment_data->warehouse_id],
                        ])->first();
                    }
                } else {
                    $lims_product_warehouse_data = null;
                }
            } else {
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $product_adjustment_data->product_id],
                    ['warehouse_id', $lims_adjustment_data->warehouse_id],
                ])->first();
            }

            if ($lims_product_warehouse_data) {
                if ($product_adjustment_data->action == '-') {
                    $lims_product_data->qty += $product_adjustment_data->qty;
                    $lims_product_warehouse_data->qty += $product_adjustment_data->qty;
                } elseif ($product_adjustment_data->action == '+') {
                    $lims_product_data->qty -= $product_adjustment_data->qty;
                    $lims_product_warehouse_data->qty -= $product_adjustment_data->qty;
                }
                $lims_product_warehouse_data->save();
            }
            $lims_product_data->save();
            $product_adjustment_data->delete();
        }
    }
}
