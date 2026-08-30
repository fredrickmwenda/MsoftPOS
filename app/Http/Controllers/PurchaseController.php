<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Tax;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\ProductPurchase;
use App\Models\Product_Warehouse;
use App\Models\Payment;
use App\Models\PaymentWithCheque;
use App\Models\PaymentWithCreditCard;
use App\Models\PosSetting;
use App\Models\Currency;
use App\Models\CustomField;
use DB;
use App\Models\GeneralSetting;
use Stripe\Stripe;
use Auth;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Traits\TenantInfo;

class PurchaseController extends Controller
{
    use TenantInfo;

    private function isStaff(){
        return Auth::user()->roles->contains(function ($role) {
            return $role->id > 2;
        });
    }

    public function index(Request $request)
    {
       
        if(Auth::user()->hasPermissionTo('purchases-index')) {
            if($request->input('warehouse_id'))
                $warehouse_id = $request->input('warehouse_id');
            else
                $warehouse_id = 0;

            if($request->input('purchase_status'))
                $purchase_status = $request->input('purchase_status');
            else
                $purchase_status = 0;

            if($request->input('payment_status'))
                $payment_status = $request->input('payment_status');
            else
                $payment_status = 0;

            if($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            }
            else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
                $ending_date = date("Y-m-d");
            }
            
            // Handle percentage filter (show only top X% of purchases by value)
            // Priority: User input > Session > Admin default settings
           $percentage_filter = GeneralSetting::first()->percentage_filter;
            
            $is_admin_filter = false;
            
            if ($percentage_filter !== null && $percentage_filter !== '' && $percentage_filter < 100) {
                // If percentage filter is set in general settings, use it
                $percentage_filter = (int) $percentage_filter;
                $is_admin_filter = true;
                session(['purchase_percentage_filter' => $percentage_filter]);
            } else {
                $settings = GeneralSetting::first();
                if($settings && $settings->percentage_filter !== null) {
                    $percentage_filter = (int) $settings->percentage_filter;
                    $is_admin_filter = true;
                } 
                session(['purchase_percentage_filter' => null]);
            }

            // Only users with purchase-percentage-filter permission may use the filter

            
            $all_permission = Auth::user()->getAllPermissions();

            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }
            $lims_pos_setting_data = PosSetting::select('stripe_public_key')->latest()->first();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_account_list = Account::where('is_active', true)->get();
            $custom_fields = CustomField::where([
                                ['belongs_to', 'purchase'],
                                ['is_table', true]
                            ])->pluck('name');
            $field_name = [];
            foreach($custom_fields as $fieldName) {
                $field_name[] = str_replace(" ", "_", strtolower($fieldName));
            }
            return view('backend.purchase.index', compact( 'lims_account_list', 'lims_warehouse_list', 'all_permission', 'lims_pos_setting_data', 'warehouse_id', 'starting_date', 'ending_date', 'purchase_status', 'payment_status', 'custom_fields', 'field_name', 'percentage_filter', 'is_admin_filter'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

 public function purchaseData(Request $request)
{
    $columns = array(
        1 => 'created_at',
        2 => 'reference_no',
        5 => 'grand_total',
        6 => 'paid_amount',
    );

    $warehouse_id    = $request->input('warehouse_id');
    $purchase_status = $request->input('purchase_status');
    $payment_status  = $request->input('payment_status');

    /* ------------------------------------------------------------------
     * 1.  Date range – default to CURRENT CALENDAR YEAR (yearly basis)
     * ------------------------------------------------------------------ */
    $starting_date = $request->input('starting_date');
    $ending_date   = $request->input('ending_date');

    if (empty($starting_date) || empty($ending_date)) {
        $starting_date = session('purchase_filter_starting_date');
        $ending_date   = session('purchase_filter_ending_date');
    }
    if (empty($starting_date) || empty($ending_date)) {
        $starting_date = date('Y-01-01');
        $ending_date   = date('Y-12-31');
    }

    /* ------------------------------------------------------------------
     * 2.  Percentage filter value
     * ------------------------------------------------------------------ */
    $percentage_filter = GeneralSetting::first()->percentage_filter ?? null;

    if ($percentage_filter === null || $percentage_filter === '') {
        if (session('purchase_percentage_filter') !== null) {
            $percentage_filter = (int) session('purchase_percentage_filter');
        } else {
            $settings = GeneralSetting::first();
            $percentage_filter = $settings && $settings->percentage_filter !== null
                ? (int) $settings->percentage_filter
                : null;
        }
    } else {
        $percentage_filter = (int) $percentage_filter;
    }

    if ($percentage_filter !== null && ($percentage_filter < 0 || $percentage_filter > 100)) {
        $percentage_filter = null;
    }

    $filter_by_percentage = $percentage_filter !== null && $percentage_filter < 100 && $percentage_filter > 0;
    $filtered_purchase_ids = [];

    /* ------------------------------------------------------------------
     * 3.  Base query
     * ------------------------------------------------------------------ */
    $baseQuery = Purchase::whereDate('created_at', '>=', $starting_date)
                         ->whereDate('created_at', '<=', $ending_date);

    if ($this->isStaff() && config('staff_access') == 'own') {
        $baseQuery = $baseQuery->where('user_id', Auth::id());
    }
    if ($warehouse_id) {
        $baseQuery = $baseQuery->where('warehouse_id', $warehouse_id);
    }
    if ($purchase_status) {
        $baseQuery = $baseQuery->where('status', $purchase_status);
    }
    if ($payment_status) {
        $baseQuery = $baseQuery->where('payment_status', $payment_status);
    }

    /* ------------------------------------------------------------------
     * 4.  100 % total (exact baseline for the percentage math)
     * ------------------------------------------------------------------ */
    $total_100 = (clone $baseQuery)->sum('grand_total');

    /* ------------------------------------------------------------------
     * 5.  Percentage filter – accumulate chronologically to pick rows,
     *     but the CARD will show the exact mathematical percentage.
     * ------------------------------------------------------------------ */
    if ($filter_by_percentage) {
        $all_purchases = (clone $baseQuery)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'grand_total']);

        $target_value = $total_100 * ($percentage_filter / 100);
        $running = 0;

        foreach ($all_purchases as $purchase) {
            $filtered_purchase_ids[] = $purchase->id;
            $running += $purchase->grand_total;
            if ($running >= $target_value) {
                break;
            }
        }

        if (count($filtered_purchase_ids) > 0) {
            $baseQuery = $baseQuery->whereIn('id', $filtered_purchase_ids);
        } else {
            $baseQuery = $baseQuery->whereIn('id', []);
        }
    }

    /* ------------------------------------------------------------------
     * 6.  DataTable counts
     * ------------------------------------------------------------------ */
    $totalData     = $baseQuery->count();
    $totalFiltered = $totalData;

    $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')];
    $dir   = $request->input('order.0.dir');

    /* ------------------------------------------------------------------
     * 7.  Custom fields
     * ------------------------------------------------------------------ */
    $custom_fields = CustomField::where([
        ['belongs_to', 'purchase'],
        ['is_table', true]
    ])->pluck('name');

    $field_names = [];
    foreach ($custom_fields as $fieldName) {
        $field_names[] = str_replace(" ", "_", strtolower($fieldName));
    }

    /* ------------------------------------------------------------------
     * 8.  Fetch data
     * ------------------------------------------------------------------ */
    if (empty($request->input('search.value'))) {
        // ----- No search -----
        $purchases = $baseQuery->with('supplier', 'warehouse')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        if ($filter_by_percentage) {
            $percentage_total = $total_100 * ($percentage_filter / 100);
        } else {
            $percentage_total = $total_100;
        }
    } else {
        // ----- With search -----
        $search = $request->input('search.value');

        $q = Purchase::leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->whereDate('purchases.created_at', '>=', $starting_date)
            ->whereDate('purchases.created_at', '<=', $ending_date)
            ->where(function ($query) use ($search, $field_names) {
                $query->where('purchases.reference_no', 'LIKE', "%{$search}%")
                      ->orWhere('suppliers.name', 'LIKE', "%{$search}%");

                foreach ($field_names as $field_name) {
                    $query->orWhere('purchases.' . $field_name, 'LIKE', "%{$search}%");
                }
            })
            ->select('purchases.*');

        if ($this->isStaff() && config('staff_access') == 'own') {
            $q = $q->where('purchases.user_id', Auth::id());
        }
        if ($warehouse_id) {
            $q = $q->where('purchases.warehouse_id', $warehouse_id);
        }
        if ($purchase_status) {
            $q = $q->where('purchases.status', $purchase_status);
        }
        if ($payment_status) {
            $q = $q->where('purchases.payment_status', $payment_status);
        }

        // Apply percentage IDs
        if ($filter_by_percentage && count($filtered_purchase_ids) > 0) {
            $q = $q->whereIn('purchases.id', $filtered_purchase_ids);
        } elseif ($filter_by_percentage) {
            $q = $q->whereIn('purchases.id', []);
        }

        // 100 % total of the search results
        $total_100_search = (clone $q)->sum('purchases.grand_total');
        $totalFiltered    = $q->count();

        $purchases = $q->with('supplier', 'warehouse')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        if ($filter_by_percentage) {
            $percentage_total = $total_100_search * ($percentage_filter / 100);
        } else {
            $percentage_total = $total_100_search;
        }
    }

    /* ------------------------------------------------------------------
     * 9.  Build DataTables response
     * ------------------------------------------------------------------ */
    $data = array();

    if (!empty($purchases)) {
        foreach ($purchases as $key => $purchase) {
            $nestedData['id']           = $purchase->id;
            $nestedData['key']          = $key;
            $nestedData['date']         = date(config('date_format'), strtotime($purchase->created_at->toDateString()));
            $nestedData['reference_no'] = $purchase->reference_no;

            if ($purchase->supplier_id) {
                $supplier = $purchase->supplier;
            } else {
                $supplier = new Supplier();
            }
            $nestedData['supplier'] = $supplier->name;

            if ($purchase->status == 1) {
                $nestedData['purchase_status'] = '<div class="badge badge-success">' . trans('file.Recieved') . '</div>';
                $purchase_status_text = trans('file.Recieved');
            } elseif ($purchase->status == 2) {
                $nestedData['purchase_status'] = '<div class="badge badge-success">' . trans('file.Partial') . '</div>';
                $purchase_status_text = trans('file.Partial');
            } elseif ($purchase->status == 3) {
                $nestedData['purchase_status'] = '<div class="badge badge-danger">' . trans('file.Pending') . '</div>';
                $purchase_status_text = trans('file.Pending');
            } else {
                $nestedData['purchase_status'] = '<div class="badge badge-danger">' . trans('file.Ordered') . '</div>';
                $purchase_status_text = trans('file.Ordered');
            }

            if ($purchase->payment_status == 1)
                $nestedData['payment_status'] = '<div class="badge badge-danger">' . trans('file.Due') . '</div>';
            else if ($purchase->payment_status == 3)
                $nestedData['payment_status'] = '<div class="badge badge-warning">' . trans('file.Pending') . '</div>';
            else if ($purchase->payment_status == 4)
                $nestedData['payment_status'] = '<div class="badge badge-danger">' . 'Rejected' . '</div>';
            else
                $nestedData['payment_status'] = '<div class="badge badge-success">' . trans('file.Paid') . '</div>';

            $nestedData['grand_total'] = number_format($purchase->grand_total, config('decimal'));

            $returned_amount = DB::table('return_purchases')->where('purchase_id', $purchase->id)->sum('grand_total');
            $nestedData['returned_amount'] = number_format($returned_amount, config('decimal'));
            $nestedData['paid_amount']     = number_format($purchase->paid_amount, config('decimal'));
            $nestedData['due']             = number_format($purchase->grand_total - $returned_amount - $purchase->paid_amount, config('decimal'));

            foreach ($field_names as $field_name) {
                $nestedData[$field_name] = $purchase->$field_name;
            }

            $nestedData['options'] = '<div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                            <li>
                                <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> ' . trans('file.View') . '</button>
                            </li>';
            if (in_array("purchases-edit", $request['all_permission']))
                $nestedData['options'] .= '<li>
                    <a href="' . route('purchases.edit', $purchase->id) . '" class="btn btn-link"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</a>
                    </li>';
            if (in_array("purchase-payment-index", $request['all_permission']))
                $nestedData['options'] .= '<li>
                        <button type="button" class="get-payment btn btn-link" data-id = "' . $purchase->id . '"><i class="fa fa-money"></i> ' . trans('file.View Payment') . '</button>
                    </li>';
            if (in_array("purchase-payment-add", $request['all_permission']))
                $nestedData['options'] .= '<li>
                        <a href="' . route('purchases.show', $purchase->id) . '" class="btn btn-link"><i class="fa fa-eye"></i> Show Payments</a>
                        </li>';
            if (in_array("purchases-delete", $request['all_permission']))
                $nestedData['options'] .= \Form::open(["route" => ["purchases.destroy", $purchase->id], "method" => "DELETE"]) . '
                        <li>
                        <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>
                        </li>' . \Form::close() . '
                    </ul>
                </div>';

            $user = User::find($purchase->user_id);
            if ($purchase->currency_id) {
                $currency = Currency::select('code')->find($purchase->currency_id);
                $currency_code = $currency ? $currency->code : 'N/A';
            } else {
                $currency_code = 'N/A';
            }

            $nestedData['purchase'] = array('[ "' . date(config('date_format'), strtotime($purchase->created_at->toDateString())) . '"', ' "' . $purchase->reference_no . '"', ' "' . $purchase_status_text . '"', ' "' . $purchase->id . '"', ' "' . $purchase->warehouse->name . '"', ' "' . $purchase->warehouse->phone . '"', ' "' . preg_replace('/\s+/S', " ", $purchase->warehouse->address) . '"', ' "' . $supplier->name . '"', ' "' . $supplier->company_name . '"', ' "' . $supplier->email . '"', ' "' . $supplier->phone_number . '"', ' "' . $supplier->address . '"', ' "' . $supplier->city . '"', ' "' . $purchase->total_tax . '"', ' "' . $purchase->total_discount . '"', ' "' . $purchase->total_cost . '"', ' "' . $purchase->order_tax . '"', ' "' . $purchase->order_tax_rate . '"', ' "' . $purchase->order_discount . '"', ' "' . $purchase->shipping_cost . '"', ' "' . $purchase->grand_total . '"', ' "' . $purchase->paid_amount . '"', ' "' . preg_replace('/\s+/S', " ", $purchase->note) . '"', ' "' . $user->name . '"', ' "' . $user->email . '"', ' "' . $purchase->document . '"', ' "' . $currency_code . '"', ' "' . $purchase->exchange_rate . '"]');

            $data[] = $nestedData;
        }
    }

    $json_data = array(
        "draw"            => intval($request->input('draw')),
        "recordsTotal"    => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "total_purchase"  => $percentage_total,
        "data"            => $data
    );

    echo json_encode($json_data);
}

    public function create()
    {
        
        if(Auth::user()->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $currency_list = Currency::where('is_active', true)->get();
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.purchase.create', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'currency_list', 'custom_fields'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function productWithoutVariant()
    {
        return Product::ActiveStandard()->select('id', 'name', 'code', 'price', 'qty', 'shelf')
                ->whereNull('is_variant')->get();
    }

    public function productWithVariant()
    {
        return Product::join('product_variants', 'products.id', 'product_variants.product_id')
            ->ActiveStandard()
            ->whereNotNull('is_variant')
            ->select('products.id', 'products.name', 'product_variants.item_code')
            ->orderBy('position')
            ->get();
    }

    public function newProductWithVariant()
    {
        return Product::ActiveStandard()
                ->whereNotNull('is_variant')
                ->whereNotNull('variant_data')
                ->select('id', 'name', 'variant_data')
                ->get();
    }

    public function store(Request $request)
    {
        $data = $request->except('document');
        $data['user_id'] = Auth::id();
        $data['reference_no'] = 'pr-' . date("Ymd") . '-'. date("his");
        
        // Handle order taxes
        if(isset($data['order_tax_ids']) && !empty($data['order_tax_ids'])) {
            $order_tax_ids = explode(',', $data['order_tax_ids']);
            $total_order_tax_rate = 0;
            foreach($order_tax_ids as $tax_id) {
                $tax = Tax::find($tax_id);
                if($tax) {
                    $total_order_tax_rate += $tax->rate;
                }
            }
            $data['order_tax_rate'] = $total_order_tax_rate;
            if(isset($data['order_tax_names'])) {
                $data['order_tax_names'] = $data['order_tax_names'];
            }
        } else {
            $data['order_tax_rate'] = 0;
        }

        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move('public/documents/purchase', $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move('public/documents/purchase', $documentName);
            }
            $data['document'] = $documentName;
        }
        
        if(isset($data['created_at']))
            $data['created_at'] = date("Y-m-d H:i:s", strtotime($data['created_at']));
        else
            $data['created_at'] = date("Y-m-d H:i:s");

        $lims_purchase_data = Purchase::create($data);
        
        $custom_field_data = [];
        $custom_fields = CustomField::where('belongs_to', 'purchase')->select('name', 'type')->get();
        foreach ($custom_fields as $type => $custom_field) {
            $field_name = str_replace(' ', '_', strtolower($custom_field->name));
            if(isset($data[$field_name])) {
                if($custom_field->type == 'checkbox' || $custom_field->type == 'multi_select')
                    $custom_field_data[$field_name] = implode(",", $data[$field_name]);
                else
                    $custom_field_data[$field_name] = $data[$field_name];
            }
        }
        if(count($custom_field_data))
            DB::table('purchases')->where('id', $lims_purchase_data->id)->update($custom_field_data);
        
        $product_id = $data['product_id'];
        $product_code = $data['product_code'];
        $qty = $data['qty'];
        $recieved = $data['recieved'];
        $batch_no = $data['batch_no'];
        $expired_date = $data['expired_date'];
        $purchase_unit = $data['purchase_unit'];
        $net_unit_cost = $data['net_unit_cost'];
        $discount = $data['discount'];
        $tax_rate = $data['tax_rate'];
        $tax_names = $data['tax_names'] ?? [];
        $tax = $data['tax'];
        $total = $data['subtotal'];
        $imei_numbers = $data['imei_number'] ?? [];
        $product_purchase = [];

        foreach ($product_id as $i => $id) {
            $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$i])->first();

            // ---- FIX: prevent division by zero ----
            $operator = $lims_purchase_unit_data->operator ?? '/';
            $operation_value = $lims_purchase_unit_data->operation_value ?? 0;

            if ($operator == '*') {
                $quantity = $recieved[$i] * $operation_value;
            } elseif ($operator == '/') {
                if ($operation_value == 0) {
                    // Handle error: set quantity to 0 or throw validation error
                    // For example, redirect back with error message
                    return redirect()->back()->withErrors(['purchase_unit' => 'Operation value cannot be zero for unit: ' . $purchase_unit[$i]]);
                }
                $quantity = $recieved[$i] / $operation_value;
            } else {
                // Fallback: treat as multiplication by 1 (no conversion)
                $quantity = $recieved[$i];
            }
            // ---- end of fix ----

            $lims_product_data = Product::find($id);
            $price = $lims_product_data->price;
            
            if($batch_no[$i]) {
                $product_batch_data = ProductBatch::where([
                                        ['product_id', $lims_product_data->id],
                                        ['batch_no', $batch_no[$i]]
                                    ])->first();
                if($product_batch_data) {
                    $product_batch_data->expired_date = $expired_date[$i];
                    $product_batch_data->qty += $quantity;
                    $product_batch_data->save();
                }
                else {
                    $product_batch_data = ProductBatch::create([
                                            'product_id' => $lims_product_data->id,
                                            'batch_no' => $batch_no[$i],
                                            'expired_date' => $expired_date[$i],
                                            'qty' => $quantity
                                        ]);
                }
                $product_purchase['product_batch_id'] = $product_batch_data->id;
            }
            else {
                $product_purchase['product_batch_id'] = null;
            }

            if($lims_product_data->is_variant) {
                $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')
                    ->FindExactProductWithCode($lims_product_data->id, $product_code[$i])
                    ->first();
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $id],
                    ['variant_id', $lims_product_variant_data->variant_id],
                    ['warehouse_id', $data['warehouse_id']]
                ])->first();
                $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                
                $lims_product_variant_data->qty += $quantity;
                $lims_product_variant_data->save();
            }
            else {
                $product_purchase['variant_id'] = null;
                if($product_purchase['product_batch_id']) {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                                                    ['product_id', $id],
                                                    ['warehouse_id', $data['warehouse_id']],
                                                ])
                                                ->whereNotNull('price')
                                                ->select('price')
                                                ->first();
                    if($lims_product_warehouse_data)
                        $price = $lims_product_warehouse_data->price;
                    else
                        $price = null;
                        
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['product_batch_id', $product_purchase['product_batch_id']],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                }
            }
            
            $lims_product_data->qty = $lims_product_data->qty + $quantity;
            $lims_product_data->save();
            
            if ($lims_product_warehouse_data) {
                $lims_product_warehouse_data->qty = $lims_product_warehouse_data->qty + $quantity;
                $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
            }
            else {
                $lims_product_warehouse_data = new Product_Warehouse();
                $lims_product_warehouse_data->product_id = $id;
                $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                $lims_product_warehouse_data->qty = $quantity;
                if($price)
                    $lims_product_warehouse_data->price = $price;
                if($lims_product_data->is_variant)
                    $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
            }
            
            if(isset($imei_numbers[$i]) && $imei_numbers[$i]) {
                if($lims_product_warehouse_data->imei_number)
                    $lims_product_warehouse_data->imei_number .= ',' . $imei_numbers[$i];
                else
                    $lims_product_warehouse_data->imei_number = $imei_numbers[$i];
            }
            $lims_product_warehouse_data->save();

            $product_purchase['purchase_id'] = $lims_purchase_data->id;
            $product_purchase['product_id'] = $id;
            $product_purchase['imei_number'] = $imei_numbers[$i] ?? null;
            $product_purchase['qty'] = $qty[$i];
            $product_purchase['recieved'] = $recieved[$i];
            $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
            $product_purchase['net_unit_cost'] = $net_unit_cost[$i];
            $product_purchase['discount'] = $discount[$i];
            $product_purchase['tax_rates'] = (!empty($tax_rate[$i])) ? $tax_rate[$i] : '';
            $product_purchase['tax_names'] = (isset($tax_names[$i]) && !empty($tax_names[$i])) ? $tax_names[$i] : '';
            $product_purchase['tax'] = $tax[$i];
            $product_purchase['total'] = $total[$i];
            
            ProductPurchase::create($product_purchase);
        }

        return redirect('purchases')->with('message', 'Purchase created successfully');
    }

    public function limsProductSearch(Request $request)
    {
        $data = $request->input('data');
        $warehouse_id = $request->input('warehouse_id');

        // Handle "Code: ..." format
        if (strpos($data, 'Code:') === 0) {
            $parts = explode('?', $data);
            $codePart = trim(str_replace('Code:', '', $parts[0]));
            $data = $codePart;
            if (isset($parts[1])) $data .= '?' . $parts[1];
            if (isset($parts[2])) $data .= '?' . $parts[2];
        }

        $product_info = explode('?', $data);
        $product_code = $product_info[0];
        $qty = isset($product_info[1]) ? $product_info[1] : 1;

        // 🔹 Get product IDs available in the selected warehouse via product_warehouse table
        $warehouse_product_ids = [];
        if ($warehouse_id) {
            $warehouse_product_ids = \DB::table('product_warehouse')
                ->where('warehouse_id', $warehouse_id)
                ->pluck('product_id')
                ->toArray();
        }

        $lims_product_data = Product::where([
                ['code', $product_code],
                ['is_active', true]
            ])
            ->whereNull('is_variant');

        // 🔹 Filter by warehouse products if warehouse is selected
        if (!empty($warehouse_product_ids)) {
            $lims_product_data = $lims_product_data->whereIn('id', $warehouse_product_ids);
        }

        $lims_product_data = $lims_product_data->first();

        $product = [];

        if (!$lims_product_data) {
            // Try variant products — also filtered by warehouse
            $variantQuery = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->where([
                    ['product_variants.item_code', $product_code],
                    ['products.is_active', true]
                ])
                ->whereNotNull('is_variant')
                ->select('products.*', 'product_variants.item_code', 'product_variants.additional_cost');

            if (!empty($warehouse_product_ids)) {
                $variantQuery = $variantQuery->whereIn('products.id', $warehouse_product_ids);
            }

            $lims_product_data = $variantQuery->first();
        }

        if ($lims_product_data && isset($lims_product_data->additional_cost)) {
            $lims_product_data->cost += $lims_product_data->additional_cost;
        }

        if(!$lims_product_data) {
            return response()->json(['error' => 'Product not found in the selected warehouse'], 404);
        }

        $product[] = $lims_product_data->name;
        if($lims_product_data->is_variant)
            $product[] = $lims_product_data->item_code;
        else
            $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->cost;
        $product[] = $lims_product_data->price;

        // Handle multiple taxes
        $tax_ids = [];
        $tax_names = [];
        $total_tax_rate = 0;

        if ($lims_product_data->product_taxes->isNotEmpty()) {
            foreach ($lims_product_data->product_taxes as $product_tax) {
                $tax_ids[] = $product_tax->tax_id;
                $tax = Tax::find($product_tax->tax_id);
                if ($tax) {
                    $tax_names[] = $tax->name;
                    $total_tax_rate += $tax->rate;
                }
            }
        }

        $product[] = implode(',', $tax_ids);
        $product[] = implode(',', $tax_names);
        $product[] = $lims_product_data->tax_method;

        $units = Unit::where("base_unit", $lims_product_data->unit_id)
                    ->orWhere('id', $lims_product_data->unit_id)
                    ->get();
        $unit_name = array();
        $unit_operator = array();
        $unit_operation_value = array();
        foreach ($units as $unit) {
            if ($lims_product_data->purchase_unit_id == $unit->id) {
                array_unshift($unit_name, $unit->unit_name);
                array_unshift($unit_operator, $unit->operator);
                array_unshift($unit_operation_value, $unit->operation_value);
            } else {
                $unit_name[]  = $unit->unit_name;
                $unit_operator[] = $unit->operator;
                $unit_operation_value[] = $unit->operation_value;
            }
        }

        $product[] = implode(",", $unit_name) . ',';
        $product[] = implode(",", $unit_operator) . ',';
        $product[] = implode(",", $unit_operation_value) . ',';
        $product[] = $lims_product_data->id;
        $product[] = $lims_product_data->is_batch;
        $product[] = $lims_product_data->is_imei;

        return $product;
    }
    /**
     * 🔹 Fetch product list filtered by warehouse via product_warehouse table
     * Used for the autocomplete source on the purchase create form
     */


    /**
     * 🔹 Quick store a supplier via AJAX from the purchase create modal
     */


    public function productPurchaseData($id)
    {
        try {
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $product_purchase = [];
            foreach ($lims_product_purchase_data as $key => $product_purchase_data) {
                $product = Product::find($product_purchase_data->product_id);
                $unit = Unit::find($product_purchase_data->purchase_unit_id);
                if($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::FindExactProduct($product->id, $product_purchase_data->variant_id)->select('item_code')->first();
                    $product->code = $lims_product_variant_data->item_code;
                }
                if($product_purchase_data->product_batch_id) {
                    $product_batch_data = ProductBatch::select('batch_no')->find($product_purchase_data->product_batch_id);
                    $product_purchase[7][$key] = $product_batch_data->batch_no;
                }
                else
                    $product_purchase[7][$key] = 'N/A';
                $product_purchase[0][$key] = $product->name . ' [' . $product->code.']';
                if($product_purchase_data->imei_number) {
                    $product_purchase[0][$key] .= '<br>IMEI or Serial Number: '. $product_purchase_data->imei_number;
                }
                $product_purchase[1][$key] = $product_purchase_data->qty;
                $product_purchase[2][$key] = $unit->unit_code;
                $product_purchase[3][$key] = $product_purchase_data->tax;
                $product_purchase[4][$key] = $product_purchase_data->tax_rate;
                $product_purchase[5][$key] = $product_purchase_data->discount;
                $product_purchase[6][$key] = $product_purchase_data->total;
            }
            return $product_purchase;
        }
        catch (Exception $e) {
            /*return response()->json('errors' => [$e->getMessage());*/
            //return response()->json(['errors' => [$e->getMessage()]], 422);
            return 'Something is wrong!';
        }

    }

    public function purchaseByCsv()
    {
        
        if(Auth::user()->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();

            return view('backend.purchase.import', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function importPurchase(Request $request)
    {
        //get the file
        $upload=$request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        //checking if this is a CSV file
        if($ext != 'csv')
            return redirect()->back()->with('message', 'Please upload a CSV file');

        $filePath=$upload->getRealPath();
        $file_handle = fopen($filePath, 'r');
        $i = 0;
        //validate the file
        while (!feof($file_handle) ) {
            $current_line = fgetcsv($file_handle);
            if($current_line && $i > 0){
                $product_data[] = Product::where([
                                    ['code', $current_line[0]],
                                    ['is_active', true]
                                ])->first();
                if(!$product_data[$i-1])
                    return redirect()->back()->with('message', 'Product with this code '.$current_line[0].' does not exist!');
                $unit[] = Unit::where('unit_code', $current_line[2])->first();
                if(!$unit[$i-1])
                    return redirect()->back()->with('message', 'Purchase unit does not exist!');
                if(strtolower($current_line[5]) != "no tax"){
                    $tax[] = Tax::where('name', $current_line[5])->first();
                    if(!$tax[$i-1])
                        return redirect()->back()->with('message', 'Tax name does not exist!');
                }
                else
                    $tax[$i-1]['rate'] = 0;

                $qty[] = $current_line[1];
                $cost[] = $current_line[3];
                $discount[] = $current_line[4];
            }
            $i++;
        }

        $data = $request->except('file');
        $data['reference_no'] = 'pr-' . date("Ymd") . '-'. date("his");
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move('public/documents/purchase', $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move('public/documents/purchase', $documentName);
            }
            $data['document'] = $documentName;
        }
        $item = 0;
        $grand_total = $data['shipping_cost'];
        $data['user_id'] = Auth::id();
        Purchase::create($data);
        $lims_purchase_data = Purchase::latest()->first();

        foreach ($product_data as $key => $product) {
            if($product['tax_method'] == 1){
                $net_unit_cost = $cost[$key] - $discount[$key];
                $product_tax = $net_unit_cost * ($tax[$key]['rate'] / 100) * $qty[$key];
                $total = ($net_unit_cost * $qty[$key]) + $product_tax;
            }
            elseif($product['tax_method'] == 2){
                $net_unit_cost = (100 / (100 + $tax[$key]['rate'])) * ($cost[$key] - $discount[$key]);
                $product_tax = ($cost[$key] - $discount[$key] - $net_unit_cost) * $qty[$key];
                $total = ($cost[$key] - $discount[$key]) * $qty[$key];
            }
            if($data['status'] == 1){
                if($unit[$key]['operator'] == '*')
                    $quantity = $qty[$key] * $unit[$key]['operation_value'];
                elseif($unit[$key]['operator'] == '/')
                    $quantity = $qty[$key] / $unit[$key]['operation_value'];
                $product['qty'] += $quantity;
                $product_warehouse = Product_Warehouse::where([
                    ['product_id', $product['id']],
                    ['warehouse_id', $data['warehouse_id']]
                ])->first();
                if($product_warehouse) {
                    $product_warehouse->qty += $quantity;
                    $product_warehouse->save();
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $product['id'];
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $quantity;
                    $lims_product_warehouse_data->save();
                }
                $product->save();
            }

            $product_purchase = new ProductPurchase();
            $product_purchase->purchase_id = $lims_purchase_data->id;
            $product_purchase->product_id = $product['id'];
            $product_purchase->qty = $qty[$key];
            if($data['status'] == 1)
                $product_purchase->recieved = $qty[$key];
            else
                $product_purchase->recieved = 0;
            $product_purchase->purchase_unit_id = $unit[$key]['id'];
            $product_purchase->net_unit_cost = number_format((float)$net_unit_cost, config('decimal'), '.', '');
            $product_purchase->discount = $discount[$key] * $qty[$key];
            $product_purchase->tax_rate = $tax[$key]['rate'];
            $product_purchase->tax = number_format((float)$product_tax, config('decimal'), '.', '');
            $product_purchase->total = number_format((float)$total, config('decimal'), '.', '');
            $product_purchase->save();
            $lims_purchase_data->total_qty += $qty[$key];
            $lims_purchase_data->total_discount += $discount[$key] * $qty[$key];
            $lims_purchase_data->total_tax += number_format((float)$product_tax, config('decimal'), '.', '');
            $lims_purchase_data->total_cost += number_format((float)$total, config('decimal'), '.', '');
        }
        $lims_purchase_data->item = $key + 1;
        $lims_purchase_data->order_tax = ($lims_purchase_data->total_cost - $lims_purchase_data->order_discount) * ($data['order_tax_rate'] / 100);
        $lims_purchase_data->grand_total = ($lims_purchase_data->total_cost + $lims_purchase_data->order_tax + $lims_purchase_data->shipping_cost) - $lims_purchase_data->order_discount;
        $lims_purchase_data->save();
        return redirect('purchases');
    }

    public function edit($id)
    {
       
        if(Auth::user()->hasPermissionTo('purchases-edit')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            if($lims_purchase_data->exchange_rate)
                $currency_exchange_rate = $lims_purchase_data->exchange_rate;
            else
                $currency_exchange_rate = 1;
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.purchase.edit', compact('lims_warehouse_list', 'lims_supplier_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data', 'currency_exchange_rate', 'custom_fields'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');

    }

    public function update(Request $request, $id)
    {
        $lims_purchase_data = Purchase::find($id);
        $data = $request->except('document');
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $this->fileDelete('documents/purchase/', $lims_purchase_data->document);

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move('public/documents/purchase', $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move('public/documents/purchase', $documentName);
            }
            $data['document'] = $documentName;
        }
        //return dd($data);
        DB::beginTransaction();
        try {
            $balance = $data['grand_total'] - $data['paid_amount'];
            if ($balance < 0 || $balance > 0) {
                $data['payment_status'] = 1;
            } else {
                $data['payment_status'] = 2;
            }
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

            $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at'])));
            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $batch_no = $data['batch_no'];
            $expired_date = $data['expired_date'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $imei_number = $new_imei_number = $data['imei_number'];
            $product_purchase = [];

            foreach ($lims_product_purchase_data as $product_purchase_data) {

                $old_recieved_value = $product_purchase_data->recieved;
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);

                if ($lims_purchase_unit_data->operator == '*') {
                    $old_recieved_value = $old_recieved_value * $lims_purchase_unit_data->operation_value;
                } else {
                    $old_recieved_value = $old_recieved_value / $lims_purchase_unit_data->operation_value;
                }
                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $lims_product_data->id],
                        ['variant_id', $product_purchase_data->variant_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                    ])->first();
                    $lims_product_variant_data->qty -= $old_recieved_value;
                    $lims_product_variant_data->save();
                }
                elseif($product_purchase_data->product_batch_id) {
                    $product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $product_batch_data->qty -= $old_recieved_value;
                    $product_batch_data->save();

                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                if($product_purchase_data->imei_number) {
                    $position = array_search($lims_product_data->id, $product_id);
                    if($imei_number[$position]) {
                        $prev_imei_numbers = explode(",", $product_purchase_data->imei_number);
                        $new_imei_numbers = explode(",", $imei_number[$position]);
                        foreach ($prev_imei_numbers as $prev_imei_number) {
                            if(($pos = array_search($prev_imei_number, $new_imei_numbers)) !== false) {
                                unset($new_imei_numbers[$pos]);
                            }
                        }
                        $new_imei_number[$position] = implode(",", $new_imei_numbers);
                    }
                }
                $lims_product_data->qty -= $old_recieved_value;
                if($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty -= $old_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();
                $product_purchase_data->delete();
            }

            foreach ($product_id as $key => $pro_id) {
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
                if ($lims_purchase_unit_data->operator == '*') {
                    $new_recieved_value = $recieved[$key] * $lims_purchase_unit_data->operation_value;
                } else {
                    $new_recieved_value = $recieved[$key] / $lims_purchase_unit_data->operation_value;
                }

                $lims_product_data = Product::find($pro_id);
                $price = null;
                //dealing with product barch
                if($batch_no[$key]) {
                    $product_batch_data = ProductBatch::where([
                                            ['product_id', $lims_product_data->id],
                                            ['batch_no', $batch_no[$key]]
                                        ])->first();
                    if($product_batch_data) {
                        $product_batch_data->qty += $new_recieved_value;
                        $product_batch_data->expired_date = $expired_date[$key];
                        $product_batch_data->save();
                    }
                    else {
                        $product_batch_data = ProductBatch::create([
                                                'product_id' => $lims_product_data->id,
                                                'batch_no' => $batch_no[$key],
                                                'expired_date' => $expired_date[$key],
                                                'qty' => $new_recieved_value
                                            ]);
                    }
                    $product_purchase['product_batch_id'] = $product_batch_data->id;
                }
                else
                    $product_purchase['product_batch_id'] = null;

                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $pro_id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['warehouse_id']]
                    ])->first();
                    $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                    //add quantity to product variant table
                    $lims_product_variant_data->qty += $new_recieved_value;
                    $lims_product_variant_data->save();
                }
                else {
                    $product_purchase['variant_id'] = null;
                    if($product_purchase['product_batch_id']) {
                        //checking for price
                        $lims_product_warehouse_data = Product_Warehouse::where([
                                                        ['product_id', $pro_id],
                                                        ['warehouse_id', $data['warehouse_id'] ],
                                                    ])
                                                    ->whereNotNull('price')
                                                    ->select('price')
                                                    ->first();
                        if($lims_product_warehouse_data)
                            $price = $lims_product_warehouse_data->price;
                            
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['product_batch_id', $product_purchase['product_batch_id'] ],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                    else {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                }

                $lims_product_data->qty += $new_recieved_value;
                if($lims_product_warehouse_data){
                    $lims_product_warehouse_data->qty += $new_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                    if($lims_product_data->is_variant)
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $new_recieved_value;
                    if($price)
                        $lims_product_warehouse_data->price = $price;
                }
                //dealing with imei numbers
                if($imei_number[$key]) {
                    if($lims_product_warehouse_data->imei_number) {
                        $lims_product_warehouse_data->imei_number .= ',' . $new_imei_number[$key];
                    }
                    else {
                        $lims_product_warehouse_data->imei_number = $new_imei_number[$key];
                    }
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_purchase['purchase_id'] = $id ;
                $product_purchase['product_id'] = $pro_id;
                $product_purchase['qty'] = $qty[$key];
                $product_purchase['recieved'] = $recieved[$key];
                $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
                $product_purchase['net_unit_cost'] = $net_unit_cost[$key];
                $product_purchase['discount'] = $discount[$key];
                $product_purchase['tax_rate'] = $tax_rate[$key];
                $product_purchase['tax'] = $tax[$key];
                $product_purchase['total'] = $total[$key];
                $product_purchase['imei_number'] = $imei_number[$key];
                ProductPurchase::create($product_purchase);
            }
            DB::commit();
        }
        catch(Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
        $lims_purchase_data->update($data);
        //inserting data for custom fields
        $custom_field_data = [];
        $custom_fields = CustomField::where('belongs_to', 'purchase')->select('name', 'type')->get();
        foreach ($custom_fields as $type => $custom_field) {
            $field_name = str_replace(' ', '_', strtolower($custom_field->name));
            if(isset($data[$field_name])) {
                if($custom_field->type == 'checkbox' || $custom_field->type == 'multi_select')
                    $custom_field_data[$field_name] = implode(",", $data[$field_name]);
                else
                    $custom_field_data[$field_name] = $data[$field_name];
            }
        }
        if(count($custom_field_data))
            DB::table('purchases')->where('id', $lims_purchase_data->id)->update($custom_field_data);
        return redirect('purchases')->with('message', 'Purchase updated successfully');
    }

    public function show($id)
    {
        $lims_purchase_data = Purchase::with(['supplier', 'warehouse'])->findOrFail($id);
        
        $lims_product_purchase_data = ProductPurchase::with('product')->where('purchase_id', $id)->get();
        $total = $lims_product_purchase_data->sum('total');
        
        // Option 1: If there's no status column, remove the where clause
        $total_paid = Payment::where('purchase_id', $id)->sum('amount');
        
        // Option 2: Check for a different column name
        // Common alternatives: 'payment_status', 'approval_status', 'is_active', 'deleted_at'
        // $total_paid = Payment::where('purchase_id', $id)
        //     ->whereNull('deleted_at') // if using soft deletes
        //     ->sum('amount');
        
        $lims_pos_setting_data = PosSetting::select('stripe_public_key')->latest()->first();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_account_list = Account::where('is_active', true)->get();
        $currency_exchange_rate = $lims_purchase_data->exchange_rate ?? 1;

        return view('backend.purchase.payment', compact(
            'lims_purchase_data',
            'lims_product_purchase_data',
            'currency_exchange_rate',
            'lims_pos_setting_data',
            'lims_warehouse_list',
            'lims_account_list',
            'total',
            'total_paid'
        ));
    }


    public function addPayment(Request $request)
    {
        $data = $request->all();
        info('this is my payment data: ' . json_encode($data));
       

        // Check purchase
        $lims_purchase_data = Purchase::findOrFail($data['purchase_id']);
        info("the purchase data is: " . json_encode($lims_purchase_data));

        if ($data['paying_amount'] <= 0) {
            return redirect()->back()->with('not_permitted', 'Sorry! Paying amount can\'t be less than total amount');
        }

        // ✅ If multiple payments exist
        if (!empty($data['paid_by_id_select']) && is_array($data['paid_by_id_select'])) {
            info('multiple payment methods detected.');
            foreach ($data['paid_by_id_select'] as $index => $method_id) {
                $split_amount = $data['split_amount'][$index] ?? 0;
                info('this is my split amount: ' . $split_amount);

                if ($split_amount <= 0) continue; // skip invalid or empty amounts

                $payment = new Payment();
                $payment->user_id = Auth::id();
                $payment->purchase_id = $lims_purchase_data->id;
                $payment->account_id = $data['account_id'];
                $payment->payment_reference = 'ppr-' . date("Ymd") . '-' . date("His") . '-' . ($index + 1);
                $payment->amount = $split_amount;
                $payment->change = 0; // handle per method if needed

                // determine payment method
                switch ($method_id) {
                    case 1: $paying_method = 'Cash'; break;
                    case 2: $paying_method = 'Gift Card'; break;
                    case 3: $paying_method = 'Credit Card'; break;
                    case 8: $paying_method = 'MobileMoney'; break;
                    default: $paying_method = 'Cheque'; break;
                }

                $payment->paying_method = $paying_method;
                $payment->payment_note = $data['payment_note'] ?? null;

                // handle mobile money (if applicable)
                if ($paying_method === 'MobileMoney' && !empty($data['selected_mobile_op'])) {
                    $payment->mobile_money_operator = $data['selected_mobile_op'];
                    $payment->mobile_number = $data['mobile_number'] ?? '';
                }

                $payment->approval_status = 'pending'; // not yet approved
                $payment->save();
            }
        }
        // ✅ Also  handle single payment as before
       
 
        $lims_payment_data = new Payment();
        $lims_payment_data->user_id = Auth::id();
        $lims_payment_data->purchase_id = $lims_purchase_data->id;
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->payment_reference = 'ppr-' . date("Ymd") . '-' . date("His");
        $lims_payment_data->amount = $data['amount'];
        $lims_payment_data->change = $data['paying_amount'] - $data['amount'];

        switch ($data['paid_by_id']) {
            case 1: $paying_method = 'Cash'; break;
            case 2: $paying_method = 'Gift Card'; break;
            case 3: $paying_method = 'Credit Card'; break;
            case 8: $paying_method = 'MobileMoney'; break;
            default: $paying_method = 'Cheque'; break;
        }

        $lims_payment_data->paying_method = $paying_method;
        $lims_payment_data->payment_note = $data['payment_note'] ?? null;

        if (!empty($data['selected_mobile_op']) && empty($data['paid_by_id_select'])) {
            $lims_payment_data->mobile_money_operator = $data['selected_mobile_op'];
            $lims_payment_data->mobile_number = $data['mobile_number'] ?? '';
        }

        $lims_payment_data->approval_status = 'waiting_authorization'; // not yet approved
        $lims_payment_data->save();
        

        // ✅ Mark purchase as waiting approval
        $lims_purchase_data->payment_status = 3; // waiting approval
        $lims_purchase_data->save();

        return redirect()->route('purchases.show', $lims_purchase_data->id)->with('message', 'Payment(s) added successfully and awaiting approval.');

        // return redirect()->back()->with('message', 'Payment(s) added successfully and awaiting approval.');
    }

    public function getPayment($id)
    {
        $lims_payment_list = Payment::where('purchase_id', $id)->get();
        $date = [];
        $payment_reference = [];
        $paid_amount = [];
        $paying_method = [];
        $payment_id = [];
        $payment_note = [];
        $cheque_no = [];
        $change = [];
        $paying_amount = [];
        $account_name = [];
        $account_id = [];
        foreach ($lims_payment_list as $payment) {
            $date[] = date(config('date_format'), strtotime($payment->created_at->toDateString())) . ' '. $payment->created_at->toTimeString();
            $payment_reference[] = $payment->payment_reference;
            $paid_amount[] = $payment->amount;
            $change[] = $payment->change;
            $paying_method[] = $payment->paying_method;
            $paying_amount[] = $payment->amount + $payment->change;
            if($payment->paying_method == 'Cheque'){
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id',$payment->id)->first();
                $cheque_no[] = $lims_payment_cheque_data->cheque_no;
            }
            else{
                $cheque_no[] = null;
            }
            $payment_id[] = $payment->id;
            $payment_note[] = $payment->payment_note;
            $lims_account_data = Account::find($payment->account_id);
            if($lims_account_data) {
                $account_name[] = $lims_account_data->name;
                $account_id[] = $lims_account_data->id;
            }
            else {
                $account_name[] = 'N/A';
                $account_id[] = 0;
            }
        }
        $payments[] = $date;
        $payments[] = $payment_reference;
        $payments[] = $paid_amount;
        $payments[] = $paying_method;
        $payments[] = $payment_id;
        $payments[] = $payment_note;
        $payments[] = $cheque_no;
        $payments[] = $change;
        $payments[] = $paying_amount;
        $payments[] = $account_name;
        $payments[] = $account_id;

        return $payments;
    }

    public function getPaymentData($id)
    {
        $lims_payment_list = Payment::where('purchase_id', $id)->get();
        $payments = [];

        foreach ($lims_payment_list as $payment) {
            $lims_account_data = Account::find($payment->account_id);
            $account_name = $lims_account_data ? $lims_account_data->name : 'N/A';

            $lims_payment_cheque_data = null;
            if ($payment->paying_method == 'Cheque') {
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $payment->id)->first();
            }

            $payments[] = [
                date(config('date_format'), strtotime($payment->created_at->toDateString())) . ' ' . $payment->created_at->toTimeString(),
                $payment->payment_reference,
                $payment->amount,
                $payment->paying_method,
                $payment->id,
                $payment->payment_note,
                $lims_payment_cheque_data->cheque_no ?? '',
                $payment->change,
                $payment->amount + $payment->change,
                $account_name,
                $payment->account_id,
                $payment->approval_status, // add status for conditional buttons
            ];
        }
        info('payment data: ' . json_encode($payments));

        return response()->json(['data' => $payments]);
    }

    public function updatePayment(Request $request)
    {
        $data = $request->all();
        $lims_payment_data = Payment::find($data['payment_id']);
        $lims_purchase_data = Purchase::find($lims_payment_data->purchase_id);
        //updating purchase table
        $amount_dif = $lims_payment_data->amount - $data['edit_amount'];
        $lims_purchase_data->paid_amount = $lims_purchase_data->paid_amount - $amount_dif;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();

        //updating payment data
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->amount = $data['edit_amount'];
        $lims_payment_data->change = $data['edit_paying_amount'] - $data['edit_amount'];
        $lims_payment_data->payment_note = $data['edit_payment_note'];
        $lims_pos_setting_data = PosSetting::latest()->first();
        if($data['edit_paid_by_id'] == 1)
            $lims_payment_data->paying_method = 'Cash';
        elseif ($data['edit_paid_by_id'] == 2)
            $lims_payment_data->paying_method = 'Gift Card';
        elseif ($data['edit_paid_by_id'] == 3 && $lims_pos_setting_data->stripe_secret_key) {
            \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['edit_amount'];
            if($lims_payment_data->paying_method == 'Credit Card'){
                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $lims_payment_data->id)->first();

                \Stripe\Refund::create(array(
                  "charge" => $lims_payment_with_credit_card_data->charge_id,
                ));

                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'source' => $token,
                ]);

                $lims_payment_with_credit_card_data->charge_id = $charge->id;
                $lims_payment_with_credit_card_data->save();
            }
            elseif($lims_pos_setting_data->stripe_secret_key) {
                // Charge the Customer
                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'source' => $token,
                ]);

                $data['charge_id'] = $charge->id;
                PaymentWithCreditCard::create($data);
            }
            $lims_payment_data->paying_method = 'Credit Card';
        }
        else{
            if($lims_payment_data->paying_method == 'Cheque'){
                $lims_payment_data->paying_method = 'Cheque';
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $data['payment_id'])->first();
                $lims_payment_cheque_data->cheque_no = $data['edit_cheque_no'];
                $lims_payment_cheque_data->save();
            }
            else{
                $lims_payment_data->paying_method = 'Cheque';
                $data['cheque_no'] = $data['edit_cheque_no'];
                PaymentWithCheque::create($data);
            }
        }
        $lims_payment_data->save();
        return redirect('purchases')->with('message', 'Payment updated successfully');
    }

    public function deletePayment(Request $request)
    {
        $lims_payment_data = Payment::find($request['id']);
        $lims_purchase_data = Purchase::where('id', $lims_payment_data->purchase_id)->first();
        $lims_purchase_data->paid_amount -= $lims_payment_data->amount;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();
        $lims_pos_setting_data = PosSetting::latest()->first();

        if($lims_payment_data->paying_method == 'Credit Card' && $lims_pos_setting_data->stripe_secret_key) {
            $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $request['id'])->first();
            \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            \Stripe\Refund::create(array(
              "charge" => $lims_payment_with_credit_card_data->charge_id,
            ));

            $lims_payment_with_credit_card_data->delete();
        }
        elseif ($lims_payment_data->paying_method == 'Cheque') {
            $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $request['id'])->first();
            $lims_payment_cheque_data->delete();
        }
        $lims_payment_data->delete();
        return redirect('purchases')->with('not_permitted', 'Payment deleted successfully');
    }

    public function authorizePayment(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        info('authorizePayment called', ['payment_id' => $id, 'request' => $request->all()]);
        $purchase = Purchase::findOrFail($payment->purchase_id);

        // Update payment status
        $payment->approval_status = 'waiting_approval';
       $payment->authorized_by = Auth::id();
        $payment->authorized_at = now(); 
        // $payment->authorization_note = $request->authorization_note ?? null;
        $payment->save(); 

        // $purchase->payment_status = 4; // waiting approval
        // $purchase->save();

        // // Get all admin and owner users
        // $adminUsers = User::whereHas('role', function ($q) {
        //     $q->whereIn('name', ['Admin', 'Owner']);
        // })->get();

        // // Send notifications via email
        // Notification::send($adminUsers, new PaymentAuthorizationNotification($payment, $purchase));

        return redirect()->back()->with('message', 'Payment sent for approval successfully');
    }
    
    public function approvePayment(Request $request, $id)
    {
        info('approvePayment called', ['payment_id' => $id, 'request' => $request->all()]);

        $payment = Payment::findOrFail($id);
        $purchase = Purchase::findOrFail($payment->purchase_id);

        if ($payment->approval_status === 'approved') {
            return redirect()->back()->with('message', 'This payment is already approved.');
        }

        // 🔹 Mark payment as approved
        $payment->approval_status = 'approved';
        $payment->approved_by = Auth::id();
        $payment->approved_at = now();
        $payment->approval_note = $request->approval_note ?? null;
        $payment->save();

        // 🔹 Update purchase totals only now
        $purchase->paid_amount += $payment->amount;
        $balance = $purchase->grand_total - $purchase->paid_amount;

        if ($balance > 0)
            $purchase->payment_status = 1; // Partial/Due
        elseif ($balance <= 0)
            $purchase->payment_status = 2; // Paid

        $purchase->save();

        return redirect()->back()->with('message', 'Payment approved successfully.');
    }


    public function rejectPayment(Request $request, $id)
    {
        $user = Auth::user();
        $payment = \App\Models\Payment::where('purchase_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();

        if (!$payment->purchase_id) {
            return response()->json(['message' => 'Payment is not linked to any purchase.'], 400);
        }
        $purchase = Purchase::findOrFail($id);

        

        // Reverse the amount paid for this payment
        $purchase->paid_amount -= $payment->amount;
        if ($purchase->paid_amount < 0) {
            $purchase->paid_amount = 0;
        }

        // Update purchase payment status based on new paid amount
        $balance = $purchase->grand_total - $purchase->paid_amount;
        if ($balance > 0) {
            $purchase->payment_status = 4; // Rejected // Partial/Due
        } elseif ($balance == 0) {
            $purchase->payment_status = 2; // Paid
        }
        // $purchase->payment_status = 4; // Rejected
        $purchase->save();
        $payment->delete();

        return redirect()->back()->with('message', 'Payment rejected successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $purchase_id = $request['purchaseIdArray'];
        foreach ($purchase_id as $id) {
            $lims_purchase_data = Purchase::find($id);
            $this->fileDelete('documents/purchase/', $lims_purchase_data->document);

            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*')
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                else
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;

                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_purchase_data->product_id, $product_purchase_data->variant_id, $lims_purchase_data->warehouse_id)
                        ->first();
                    $lims_product_variant_data->qty -= $recieved_qty;
                    $lims_product_variant_data->save();
                }
                elseif($product_purchase_data->product_batch_id) {
                    $lims_product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                    ])->first();

                    $lims_product_batch_data->qty -= $recieved_qty;
                    $lims_product_batch_data->save();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_purchase_data->product_id, $lims_purchase_data->warehouse_id)
                        ->first();
                }

                $lims_product_data->qty -= $recieved_qty;
                $lims_product_warehouse_data->qty -= $recieved_qty;

                $lims_product_warehouse_data->save();
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            $lims_pos_setting_data = PosSetting::latest()->first();
            foreach ($lims_payment_data as $payment_data) {
                if($payment_data->paying_method == "Cheque"){
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                }
                elseif($payment_data->paying_method == "Credit Card" && $lims_pos_setting_data->stripe_secret_key) {
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(array(
                      "charge" => $payment_with_credit_card_data->charge_id,
                    ));

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
        }
        return 'Purchase deleted successfully!';
    }

    public function destroy($id)
    {
        if(Auth::user()->hasPermissionTo('purchases-delete')){
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*')
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                else
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;

                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_purchase_data->product_id, $product_purchase_data->variant_id, $lims_purchase_data->warehouse_id)
                        ->first();
                    $lims_product_variant_data->qty -= $recieved_qty;
                    $lims_product_variant_data->save();
                }
                elseif($product_purchase_data->product_batch_id) {
                    $lims_product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                    ])->first();

                    $lims_product_batch_data->qty -= $recieved_qty;
                    $lims_product_batch_data->save();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_purchase_data->product_id, $lims_purchase_data->warehouse_id)
                        ->first();
                }
                //deduct imei number if available
                if($product_purchase_data->imei_number) {
                    $imei_numbers = explode(",", $product_purchase_data->imei_number);
                    $all_imei_numbers = explode(",", $lims_product_warehouse_data->imei_number);
                    foreach ($imei_numbers as $number) {
                        if (($j = array_search($number, $all_imei_numbers)) !== false) {
                            unset($all_imei_numbers[$j]);
                        }
                    }
                    $lims_product_warehouse_data->imei_number = implode(",", $all_imei_numbers);
                }

                $lims_product_data->qty -= $recieved_qty;
                $lims_product_warehouse_data->qty -= $recieved_qty;

                $lims_product_warehouse_data->save();
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            $lims_pos_setting_data = PosSetting::latest()->first();
            foreach ($lims_payment_data as $payment_data) {
                if($payment_data->paying_method == "Cheque"){
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                }
                elseif($payment_data->paying_method == "Credit Card" && $lims_pos_setting_data->stripe_secret_key) {
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(array(
                      "charge" => $payment_with_credit_card_data->charge_id,
                    ));

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
            $this->fileDelete('documents/purchase/', $lims_purchase_data->document);

            return redirect('purchases')->with('not_permitted', 'Purchase deleted successfully');;
        }

    }

    public function updateFromClient(Request $request, $id)
    {
        $data = $request->except('document');
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move('public/documents/purchase', $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move('public/documents/purchase', $documentName);
            }
            $data['document'] = $documentName;
        }
        //return dd($data);
        DB::beginTransaction();
        try {
            $balance = $data['grand_total'] - $data['paid_amount'];
            if ($balance < 0 || $balance > 0) {
                $data['payment_status'] = 1;
            } else {
                $data['payment_status'] = 2;
            }
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

            $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at'])));
            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $batch_no = $data['batch_no'];
            $expired_date = $data['expired_date'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $imei_number = $new_imei_number = $data['imei_number'];
            $product_purchase = [];
            $lims_product_warehouse_data = null;

            foreach ($lims_product_purchase_data as $product_purchase_data) {

                $old_recieved_value = $product_purchase_data->recieved;
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);

                if ($lims_purchase_unit_data->operator == '*') {
                    $old_recieved_value = $old_recieved_value * $lims_purchase_unit_data->operation_value;
                } else {
                    $old_recieved_value = $old_recieved_value / $lims_purchase_unit_data->operation_value;
                }
                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    if($lims_product_variant_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $lims_product_data->id],
                            ['variant_id', $product_purchase_data->variant_id],
                            ['warehouse_id', $lims_purchase_data->warehouse_id]
                        ])->first();
                        $lims_product_variant_data->qty -= $old_recieved_value;
                        $lims_product_variant_data->save();
                    }
                }
                elseif($product_purchase_data->product_batch_id) {
                    $product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $product_batch_data->qty -= $old_recieved_value;
                    $product_batch_data->save();

                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                if($product_purchase_data->imei_number) {
                    $position = array_search($lims_product_data->id, $product_id);
                    if($imei_number[$position]) {
                        $prev_imei_numbers = explode(",", $product_purchase_data->imei_number);
                        $new_imei_numbers = explode(",", $imei_number[$position]);
                        foreach ($prev_imei_numbers as $prev_imei_number) {
                            if(($pos = array_search($prev_imei_number, $new_imei_numbers)) !== false) {
                                unset($new_imei_numbers[$pos]);
                            }
                        }
                        $new_imei_number[$position] = implode(",", $new_imei_numbers);
                    }
                }
                $lims_product_data->qty -= $old_recieved_value;
                if($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty -= $old_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();
                $product_purchase_data->delete();
            }

            foreach ($product_id as $key => $pro_id) {
                $price = null;
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
                if ($lims_purchase_unit_data->operator == '*') {
                    $new_recieved_value = $recieved[$key] * $lims_purchase_unit_data->operation_value;
                } else {
                    $new_recieved_value = $recieved[$key] / $lims_purchase_unit_data->operation_value;
                }

                $lims_product_data = Product::find($pro_id);
                //dealing with product barch
                if($batch_no[$key]) {
                    $product_batch_data = ProductBatch::where([
                                            ['product_id', $lims_product_data->id],
                                            ['batch_no', $batch_no[$key]]
                                        ])->first();
                    if($product_batch_data) {
                        $product_batch_data->qty += $new_recieved_value;
                        $product_batch_data->expired_date = $expired_date[$key];
                        $product_batch_data->save();
                    }
                    else {
                        $product_batch_data = ProductBatch::create([
                                                'product_id' => $lims_product_data->id,
                                                'batch_no' => $batch_no[$key],
                                                'expired_date' => $expired_date[$key],
                                                'qty' => $new_recieved_value
                                            ]);
                    }
                    $product_purchase['product_batch_id'] = $product_batch_data->id;
                }
                else
                    $product_purchase['product_batch_id'] = null;

                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                    if($lims_product_variant_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['variant_id', $lims_product_variant_data->variant_id],
                            ['warehouse_id', $data['warehouse_id']]
                        ])->first();
                        $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                        //add quantity to product variant table
                        $lims_product_variant_data->qty += $new_recieved_value;
                        $lims_product_variant_data->save();
                    }
                }
                else {
                    $product_purchase['variant_id'] = null;
                    if($product_purchase['product_batch_id']) {
                        //checking for price
                        $lims_product_warehouse_data = Product_Warehouse::where([
                                                        ['product_id', $pro_id],
                                                        ['warehouse_id', $data['warehouse_id'] ],
                                                    ])
                                                    ->whereNotNull('price')
                                                    ->select('price')
                                                    ->first();
                        if($lims_product_warehouse_data)
                            $price = $lims_product_warehouse_data->price;

                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['product_batch_id', $product_purchase['product_batch_id'] ],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                    else {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                }

                $lims_product_data->qty += $new_recieved_value;
                if($lims_product_warehouse_data){
                    $lims_product_warehouse_data->qty += $new_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                    if($lims_product_data->is_variant && $lims_product_variant_data)
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $new_recieved_value;
                    if($price)
                        $lims_product_warehouse_data->price = $price;
                }
                //dealing with imei numbers
                if($imei_number[$key]) {
                    if($lims_product_warehouse_data->imei_number) {
                        $lims_product_warehouse_data->imei_number .= ',' . $new_imei_number[$key];
                    }
                    else {
                        $lims_product_warehouse_data->imei_number = $new_imei_number[$key];
                    }
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_purchase['purchase_id'] = $id ;
                $product_purchase['product_id'] = $pro_id;
                $product_purchase['qty'] = $qty[$key];
                $product_purchase['recieved'] = $recieved[$key];
                $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
                $product_purchase['net_unit_cost'] = $net_unit_cost[$key];
                $product_purchase['discount'] = $discount[$key];
                $product_purchase['tax_rate'] = $tax_rate[$key];
                $product_purchase['tax'] = $tax[$key];
                $product_purchase['total'] = $total[$key];
                $product_purchase['imei_number'] = $imei_number[$key];
                ProductPurchase::create($product_purchase);
            }
            DB::commit();
        }
        catch(Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
        $lims_purchase_data->update($data);
        return redirect('purchases')->with('message', 'Purchase updated successfully');
    }

    /**
     * Given a base Purchase query and a percentage (0-99), return purchase IDs that form the top X% of purchases by value.
     * Returns empty array if percentage is null or 100 (no filter).
     */
    private function getPurchaseIdsForPercentageFilter($baseQuery, $percentage)
    {
        if ($percentage === null || $percentage >= 100) {
            return ['ids' => [], 'total_purchase' => 0];
        }

        $all_purchases = (clone $baseQuery)->orderBy('grand_total', 'desc')->get(['id', 'grand_total']);
        $total_value = $all_purchases->sum('grand_total');
        $target_value = $total_value * ($percentage / 100);

        $ids = [];
        $running = 0;
        foreach ($all_purchases as $p) {
            $ids[] = $p->id;
            $running += $p->grand_total;
            if ($running >= $target_value) {
                break;
            }
        }

        info(['ids' => $ids, 'total_purchase' => $running]);

        return ['ids' => $ids, 'total_purchase' => $running];
    }
}
