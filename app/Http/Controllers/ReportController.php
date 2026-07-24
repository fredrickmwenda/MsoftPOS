<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\Product_Sale;
use App\Models\ProductQuotation;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Transfer;
use App\Models\Returns;
use App\Models\ProductReturn;
use App\Models\ReturnPurchase;
use App\Models\ProductTransfer;
use App\Models\PurchaseProductReturn;
use App\Models\Payment;
use App\Models\Warehouse;
use App\Models\Product_Warehouse;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\Biller;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Variant;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\CustomerGroup;
use App\Models\CategoryDepartment;
use App\Models\GeneralSetting;
// use DB;
use Illuminate\Support\Facades\DB;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB as FacadesDB;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class ReportController extends Controller
{

    private function isStaff(){
        return Auth::user()->roles->contains(function ($role) {
            return $role->id > 2;
        });
    }
    /**
     * Get percentage_filter from GeneralSetting model. Returns null if not set or 100.
     */
    private function getSalePercentageFromSetting()
    {
        $settings = GeneralSetting::first();
        if (!$settings || $settings->percentage_filter === null) {
            return null;
        }
        $pct = (int) $settings->percentage_filter;
        return ($pct >= 0 && $pct <= 100) ? $pct : null;
    }

    /**
     * Given a base Sale query and a percentage (0-99), return sale IDs that form the top X% of sales by value.
     * Returns empty array if percentage is null or 100 (no filter).
     */
    private function getSaleIdsForPercentageFilter($baseQuery, $percentage)
    {
        if ($percentage === null || $percentage >= 100) {
            return [];
        }
        $all_sales = (clone $baseQuery)->orderBy('grand_total', 'desc')->get(['id', 'grand_total']);
        $total_value = $all_sales->sum('grand_total');
        $target_value = $total_value * ($percentage / 100);
        $ids = [];
        $running = 0;
        foreach ($all_sales as $s) {
            $ids[] = $s->id;
            $running += $s->grand_total;
            if ($running >= $target_value) {
                break;
            }
        }
        return $ids;
    }

    public function reportDashboard()
    {
        
        return view('backend.report.report_dashboard');
    }

    public function productQuantityAlert()
    {
        if(Auth::user()->hasPermissionTo('product-qty-alert')){
            $lims_product_data = Product::select('name','code', 'image', 'qty', 'alert_quantity')->where('is_active', true)->whereColumn('alert_quantity', '>', 'qty')->get();
            return view('backend.report.qty_alert_report', compact('lims_product_data'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    function firstTimeCustomers() {
        $all_customers = Customer::where('is_active', true)->get();
        $first_time_customers = array();
        foreach ($all_customers as $customer) {
            $check = Sale::where([
                ['customer_id', $customer->id]
            ])->get();
            if (count($check) == 1) {
                $array = array(
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone_number' => $customer->phone_number,
                    'whatsapp' => $customer->whatsapp_number,
                    'order_date' => date('Y-m-d H:i:s', strtotime($check[0]->created_at))
                );

                array_push($first_time_customers, $array);
            }
        }

        return view('backend.report.first_time_customers', compact('first_time_customers'));
    }

    public function dailySaleObjective(Request $request)
    {
        if(Auth::user()->hasPermissionTo('dso-report')) {
            if($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            }
            else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d') )))));
                $ending_date = date("Y-m-d");
            }
            return view('backend.report.daily_sale_objective', compact('starting_date', 'ending_date'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function dailySaleObjectiveData(Request $request)
    {
        $starting_date = date("Y-m-d", strtotime("+1 day", strtotime($request->input('starting_date'))));
        $ending_date = date("Y-m-d", strtotime("+1 day", strtotime($request->input('ending_date'))));

        $columns = array(
            1 => 'created_at',
        );
        $totalData = DB::table('dso_alerts')
                    ->whereDate('created_at', '>=' , $starting_date)
                    ->whereDate('created_at', '<=' , $ending_date)
                    ->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if(empty($request->input('search.value'))) {
            $lims_dso_alert_data = DB::table('dso_alerts')
                                  ->whereDate('created_at', '>=' , $starting_date)
                                  ->whereDate('created_at', '<=' , $ending_date)
                                  ->offset($start)
                                  ->limit($limit)
                                  ->orderBy($order, $dir)
                                  ->get();
        }
        else
        {
            $search = $request->input('search.value');
            $lims_dso_alert_data = DB::table('dso_alerts')
                                  ->whereDate('dso_alerts.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                                  ->offset($start)
                                  ->limit($limit)
                                  ->orderBy($order, $dir)
                                  ->get();
        }
        $data = array();
        if(!empty($lims_dso_alert_data))
        {
            foreach ($lims_dso_alert_data as $key => $dso_alert_data)
            {
                $nestedData['id'] = $dso_alert_data->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime("-1 day", strtotime($dso_alert_data->created_at)));
                foreach (json_decode($dso_alert_data->product_info) as $index => $product_info) {
                    if($index)
                        $nestedData['product_info'] .= ', ';
                    $nestedData['product_info'] = $product_info->name.' ['.$product_info->code.']';
                }
                $nestedData['number_of_products'] = $dso_alert_data->number_of_products;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function productExpiry()
    {
        $date = date('Y-m-d', strtotime('+30 days'));
        $lims_product_data = DB::table('products')
                            ->join('product_batches', 'products.id', '=', 'product_batches.product_id')
                            ->whereDate('product_batches.expired_date', '<=', $date)
                            ->where([
                                ['products.is_active', true],
                                ['product_batches.qty', '>', 0]
                            ])
                            ->select('products.name', 'products.code', 'products.image', 'product_batches.batch_no', 'product_batches.batch_no', 'product_batches.expired_date', 'product_batches.qty')
                            ->get();
        return view('backend.report.product_expiry_report', compact('lims_product_data'));
    }

    public function warehouseStockReport(Request $request)
    {
        // ✅ If no dates are set, default to yesterday → today
        $start_date = $request->input('start_date') ?? now()->subDay()->toDateString();
        $end_date   = $request->input('end_date') ?? now()->toDateString();
        $warehouse_id = $request->input('warehouse_id');

        if (!$start_date && !$end_date) {
            $start_date = now()->subDay()->toDateString();  // yesterday
            $end_date   = now()->toDateString();            // today
        }

        // Get all active warehouses
        $warehouses = Warehouse::where('is_active', true);
        if ($warehouse_id) {
            $warehouses->where('id', $warehouse_id);
        }
        $warehouses = $warehouses->get();

        // Get all products with their warehouse quantities, filtered by date
        $products = Product::with(['product_warehouse' => function ($q) use ($start_date, $end_date, $warehouse_id) {
                $q->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
                if ($warehouse_id) {
                    $q->where('warehouse_id', $warehouse_id);
                }
            }])
            ->where('is_active', true);

        $product_stocks = [];

        foreach ($products as $product) {
            $quantities = [];
            $total = 0;

            foreach ($warehouses as $warehouse) {
                $qty = $product->product_warehouse
                    ->where('warehouse_id', $warehouse->id)
                    ->first()->qty ?? 0;

                $quantities[$warehouse->id] = $qty;
                $total += $qty;
            }

            if ($total > 0) {
                $product_stocks[] = [
                    'name'       => $product->name,
                    'quantities' => $quantities,
                    'total'      => $total
                ];
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'data' => $product_stocks,
                'warehouses' => $warehouses
            ]);
        }

        return view('backend.report.warehouse_stock_report', compact('warehouses', 'product_stocks', 'start_date', 'end_date', 'warehouse_id'));
    }

    public function warehouseStockReportData(Request $request)
    {
        $start_date = $request->input('start_date')  ?? now()->subDay()->toDateString();
        $end_date   = $request->input('end_date') ?? now()->toDateString();
        $warehouse_id = $request->input('warehouse_id');

        // Handle default date range
        if (!$start_date || !$end_date) {
            $start_date = now()->subDay()->toDateString();  // yesterday
            $end_date   = now()->toDateString();            // today
        }

        // Get relevant warehouses
        $warehouses = Warehouse::where('is_active', true);
        if ($warehouse_id) {
            $warehouses->where('id', $warehouse_id);
        }
        $warehouses = $warehouses->get();

        // Get products with warehouse quantities within the date range
        $products = Product::with(['product_warehouse' => function ($q) use ($start_date, $end_date, $warehouse_id) {
            $q->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            if ($warehouse_id) {
                $q->where('warehouse_id', $warehouse_id);
            }
        }])->where('is_active', true)->get();

        // Build DataTables-compatible data array
        $data = [];
        foreach ($products as $product) {
            $row = [];
            $row['checkbox'] = '';
            $row['product'] = $product->name;

            $total = 0;

            foreach ($warehouses as $warehouse) {
                $qty = $product->product_warehouse
                    ->where('warehouse_id', $warehouse->id)
                    ->first()->qty ?? 0;

                $row["warehouse_{$warehouse->id}"] = $qty;
                $total += $qty;
            }

            $row['total'] = $total;

            if ($total > 0) {
                $data[] = $row;
            }
        }

        return response()->json([
            "draw" => intval($request->input('draw')),  // pass draw from request
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);
    }

        
    public function warehouseStock(Request $request)
    {
        if(Auth::user()->hasPermissionTo('warehouse-stock-report')) {
            if(isset($request->warehouse_id))
                $warehouse_id = $request->warehouse_id;
            else
                $warehouse_id = 0;
            if(!$warehouse_id) {
                $total_item = DB::table('product_warehouse')
                            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
                            ->where([
                                ['products.is_active', true],
                                ['product_warehouse.qty', '>' , 0]
                            ])->count();

                $total_qty = Product::where('is_active', true)->sum('qty');
                $total_price = DB::table('products')->where('is_active', true)->sum(DB::raw('price * qty'));
                $total_cost = DB::table('products')->where('is_active', true)->sum(DB::raw('cost * qty'));
            }
            else {
                $total_item = DB::table('product_warehouse')
                            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
                            ->where([
                                ['products.is_active', true],
                                ['product_warehouse.qty', '>' , 0],
                                ['product_warehouse.warehouse_id', $warehouse_id]
                            ])->count();
                $total_qty = DB::table('product_warehouse')
                                ->join('products', 'product_warehouse.product_id', '=', 'products.id')
                                ->where([
                                    ['products.is_active', true],
                                    ['product_warehouse.warehouse_id', $warehouse_id]
                                ])->sum('product_warehouse.qty');
                $total_price = DB::table('product_warehouse')
                                ->join('products', 'product_warehouse.product_id', '=', 'products.id')
                                ->where([
                                    ['products.is_active', true],
                                    ['product_warehouse.warehouse_id', $warehouse_id]
                                ])->sum(DB::raw('products.price * product_warehouse.qty'));
                $total_cost = DB::table('product_warehouse')
                                ->join('products', 'product_warehouse.product_id', '=', 'products.id')
                                ->where([
                                    ['products.is_active', true],
                                    ['product_warehouse.warehouse_id', $warehouse_id]
                                ])->sum(DB::raw('products.cost * product_warehouse.qty'));
            }
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            return view('backend.report.warehouse_stock', compact('total_item', 'total_qty', 'total_price', 'total_cost', 'lims_warehouse_list', 'warehouse_id'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function dailySale($year, $month)
    {
        if(Auth::user()->hasPermissionTo('daily-sale')){
            $sale_percentage = $this->getSalePercentageFromSetting();
            $apply_percentage = $sale_percentage !== null && $sale_percentage < 100;
            if (!Auth::user()->hasPermissionTo('sale-percentage-filter')) {
                $apply_percentage = false;
            }

            $start = 1;
            $number_of_day = date('t', mktime(0, 0, 0, $month, 1, $year));
            while($start <= $number_of_day)
            {
                if($start < 10)
                    $date = $year.'-'.$month.'-0'.$start;
                else
                    $date = $year.'-'.$month.'-'.$start;

                $baseQuery = Sale::whereDate('created_at', $date);
                if ($apply_percentage) {
                    $sale_ids = $this->getSaleIdsForPercentageFilter($baseQuery, $sale_percentage);
                    if (count($sale_ids) > 0) {
                        $sale_data = Sale::whereIn('id', $sale_ids)->selectRaw(
                            'SUM(total_discount) AS total_discount, SUM(order_discount) AS order_discount, SUM(total_tax) AS total_tax, SUM(order_tax) AS order_tax, SUM(shipping_cost) AS shipping_cost, SUM(grand_total) AS grand_total'
                        )->first();
                        $total_discount[$start] = $sale_data->total_discount ?? 0;
                        $order_discount[$start] = $sale_data->order_discount ?? 0;
                        $total_tax[$start] = $sale_data->total_tax ?? 0;
                        $order_tax[$start] = $sale_data->order_tax ?? 0;
                        $shipping_cost[$start] = $sale_data->shipping_cost ?? 0;
                        $grand_total[$start] = $sale_data->grand_total ?? 0;
                    } else {
                        $total_discount[$start] = $order_discount[$start] = $total_tax[$start] = $order_tax[$start] = $shipping_cost[$start] = $grand_total[$start] = 0;
                    }
                } else {
                    $query1 = array(
                        'SUM(total_discount) AS total_discount',
                        'SUM(order_discount) AS order_discount',
                        'SUM(total_tax) AS total_tax',
                        'SUM(order_tax) AS order_tax',
                        'SUM(shipping_cost) AS shipping_cost',
                        'SUM(grand_total) AS grand_total'
                    );
                    $sale_data = Sale::whereDate('created_at', $date)->selectRaw(implode(',', $query1))->get();
                    $total_discount[$start] = $sale_data[0]->total_discount;
                    $order_discount[$start] = $sale_data[0]->order_discount;
                    $total_tax[$start] = $sale_data[0]->total_tax;
                    $order_tax[$start] = $sale_data[0]->order_tax;
                    $shipping_cost[$start] = $sale_data[0]->shipping_cost;
                    $grand_total[$start] = $sale_data[0]->grand_total;
                }
                $start++;
            }
            $start_day = date('w', strtotime($year.'-'.$month.'-01')) + 1;
            $prev_year = date('Y', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
            $prev_month = date('m', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
            $next_year = date('Y', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
            $next_month = date('m', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_biller_list = Biller::where('is_active', true)->get();
            $warehouse_id = 0;
            $biller_id = 0;
            return view('backend.report.daily_sale', compact('total_discount','order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'lims_warehouse_list', 'lims_biller_list', 'warehouse_id', 'biller_id'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function dailySaleByWarehouse(Request $request,$year,$month)
    {
        $data = $request->all();
        if($data['warehouse_id'] == 0)
            return redirect()->back();
        $sale_percentage = $this->getSalePercentageFromSetting();
        $apply_percentage = $sale_percentage !== null && $sale_percentage < 100;
        if (!Auth::user()->hasPermissionTo('sale-percentage-filter')) {
            $apply_percentage = false;
        }

        $start = 1;
        $number_of_day = date('t', mktime(0, 0, 0, $month, 1, $year));
        while($start <= $number_of_day)
        {
            if($start < 10)
                $date = $year.'-'.$month.'-0'.$start;
            else
                $date = $year.'-'.$month.'-'.$start;

            $sale_query = Sale::where('warehouse_id', $data['warehouse_id']);
            if(isset($data['biller_id']) && $data['biller_id'] != 0) {
                $sale_query->where('biller_id', $data['biller_id']);
            }
            $baseQuery = (clone $sale_query)->whereDate('created_at', $date);

            if ($apply_percentage) {
                $sale_ids = $this->getSaleIdsForPercentageFilter($baseQuery, $sale_percentage);
                if (count($sale_ids) > 0) {
                    $sale_data = Sale::whereIn('id', $sale_ids)->selectRaw(
                        'SUM(total_discount) AS total_discount, SUM(order_discount) AS order_discount, SUM(total_tax) AS total_tax, SUM(order_tax) AS order_tax, SUM(shipping_cost) AS shipping_cost, SUM(grand_total) AS grand_total'
                    )->first();
                    $total_discount[$start] = $sale_data->total_discount ?? 0;
                    $order_discount[$start] = $sale_data->order_discount ?? 0;
                    $total_tax[$start] = $sale_data->total_tax ?? 0;
                    $order_tax[$start] = $sale_data->order_tax ?? 0;
                    $shipping_cost[$start] = $sale_data->shipping_cost ?? 0;
                    $grand_total[$start] = $sale_data->grand_total ?? 0;
                } else {
                    $total_discount[$start] = $order_discount[$start] = $total_tax[$start] = $order_tax[$start] = $shipping_cost[$start] = $grand_total[$start] = 0;
                }
            } else {
                $query1 = array(
                    'SUM(total_discount) AS total_discount',
                    'SUM(order_discount) AS order_discount',
                    'SUM(total_tax) AS total_tax',
                    'SUM(order_tax) AS order_tax',
                    'SUM(shipping_cost) AS shipping_cost',
                    'SUM(grand_total) AS grand_total'
                );
                $sale_data = $sale_query->whereDate('created_at', $date)->selectRaw(implode(',', $query1))->get();
                $total_discount[$start] = $sale_data[0]->total_discount;
                $order_discount[$start] = $sale_data[0]->order_discount;
                $total_tax[$start] = $sale_data[0]->total_tax;
                $order_tax[$start] = $sale_data[0]->order_tax;
                $shipping_cost[$start] = $sale_data[0]->shipping_cost;
                $grand_total[$start] = $sale_data[0]->grand_total;
            }
            $start++;
        }
        $start_day = date('w', strtotime($year.'-'.$month.'-01')) + 1;
        $prev_year = date('Y', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
        $prev_month = date('m', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
        $next_year = date('Y', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
        $next_month = date('m', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $warehouse_id = $data['warehouse_id'];
        $biller_id = isset($data['biller_id']) ? $data['biller_id'] : 0;
        return view('backend.report.daily_sale', compact('total_discount','order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'lims_warehouse_list', 'lims_biller_list', 'warehouse_id', 'biller_id'));

    }

    public function dailyPurchase($year, $month)
    {
        if(Auth::user()->hasPermissionTo('daily-purchase')){
            $start = 1;
            $number_of_day = date('t', mktime(0, 0, 0, $month, 1, $year));
            while($start <= $number_of_day)
            {
                if($start < 10)
                    $date = $year.'-'.$month.'-0'.$start;
                else
                    $date = $year.'-'.$month.'-'.$start;
                $query1 = array(
                    'SUM(total_discount) AS total_discount',
                    'SUM(order_discount) AS order_discount',
                    'SUM(total_tax) AS total_tax',
                    'SUM(order_tax) AS order_tax',
                    'SUM(shipping_cost) AS shipping_cost',
                    'SUM(grand_total) AS grand_total'
                );
                $purchase_data = Purchase::whereDate('created_at', $date)->selectRaw(implode(',', $query1))->get();
                $total_discount[$start] = $purchase_data[0]->total_discount;
                $order_discount[$start] = $purchase_data[0]->order_discount;
                $total_tax[$start] = $purchase_data[0]->total_tax;
                $order_tax[$start] = $purchase_data[0]->order_tax;
                $shipping_cost[$start] = $purchase_data[0]->shipping_cost;
                $grand_total[$start] = $purchase_data[0]->grand_total;
                $start++;
            }
            $start_day = date('w', strtotime($year.'-'.$month.'-01')) + 1;
            $prev_year = date('Y', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
            $prev_month = date('m', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
            $next_year = date('Y', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
            $next_month = date('m', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $warehouse_id = 0;
            return view('backend.report.daily_purchase', compact('total_discount','order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'lims_warehouse_list', 'warehouse_id'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function dailyPurchaseByWarehouse(Request $request, $year, $month)
    {
        $data = $request->all();
        if($data['warehouse_id'] == 0)
            return redirect()->back();
        $start = 1;
        $number_of_day = date('t', mktime(0, 0, 0, $month, 1, $year));
        while($start <= $number_of_day)
        {
            if($start < 10)
                $date = $year.'-'.$month.'-0'.$start;
            else
                $date = $year.'-'.$month.'-'.$start;
            $query1 = array(
                'SUM(total_discount) AS total_discount',
                'SUM(order_discount) AS order_discount',
                'SUM(total_tax) AS total_tax',
                'SUM(order_tax) AS order_tax',
                'SUM(shipping_cost) AS shipping_cost',
                'SUM(grand_total) AS grand_total'
            );
            $purchase_data = Purchase::where('warehouse_id', $data['warehouse_id'])->whereDate('created_at', $date)->selectRaw(implode(',', $query1))->get();
            $total_discount[$start] = $purchase_data[0]->total_discount;
            $order_discount[$start] = $purchase_data[0]->order_discount;
            $total_tax[$start] = $purchase_data[0]->total_tax;
            $order_tax[$start] = $purchase_data[0]->order_tax;
            $shipping_cost[$start] = $purchase_data[0]->shipping_cost;
            $grand_total[$start] = $purchase_data[0]->grand_total;
            $start++;
        }
        $start_day = date('w', strtotime($year.'-'.$month.'-01')) + 1;
        $prev_year = date('Y', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
        $prev_month = date('m', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
        $next_year = date('Y', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
        $next_month = date('m', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $warehouse_id = $data['warehouse_id'];

        return view('backend.report.daily_purchase', compact('total_discount','order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function monthlySale($year)
    {
        if(Auth::user()->hasPermissionTo('monthly-sale')){
            $sale_percentage = $this->getSalePercentageFromSetting();
            $apply_percentage = $sale_percentage !== null && $sale_percentage < 100;
            if (!Auth::user()->hasPermissionTo('sale-percentage-filter')) {
                $apply_percentage = false;
            }

            $start = strtotime($year .'-01-01');
            $end = strtotime($year .'-12-31');
            while($start <= $end)
            {
                $start_date = $year . '-'. date('m', $start).'-'.'01';
                $end_date = $year . '-'. date('m', $start).'-'.date('t', mktime(0, 0, 0, (int)date('m', $start), 1, (int)$year));

                $baseQuery = Sale::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date);
                if ($apply_percentage) {
                    $sale_ids = $this->getSaleIdsForPercentageFilter($baseQuery, $sale_percentage);
                    if (count($sale_ids) > 0) {
                        $temp_total_discount = Sale::whereIn('id', $sale_ids)->sum('total_discount');
                        $temp_order_discount = Sale::whereIn('id', $sale_ids)->sum('order_discount');
                        $temp_total_tax = Sale::whereIn('id', $sale_ids)->sum('total_tax');
                        $temp_order_tax = Sale::whereIn('id', $sale_ids)->sum('order_tax');
                        $temp_shipping_cost = Sale::whereIn('id', $sale_ids)->sum('shipping_cost');
                        $temp_total = Sale::whereIn('id', $sale_ids)->sum('grand_total');
                    } else {
                        $temp_total_discount = $temp_order_discount = $temp_total_tax = $temp_order_tax = $temp_shipping_cost = $temp_total = 0;
                    }
                } else {
                    $temp_total_discount = $baseQuery->sum('total_discount');
                    $temp_order_discount = (clone $baseQuery)->sum('order_discount');
                    $temp_total_tax = (clone $baseQuery)->sum('total_tax');
                    $temp_order_tax = (clone $baseQuery)->sum('order_tax');
                    $temp_shipping_cost = (clone $baseQuery)->sum('shipping_cost');
                    $temp_total = (clone $baseQuery)->sum('grand_total');
                }
                $total_discount[] = number_format((float)$temp_total_discount, config('decimal'), '.', '');
                $order_discount[] = number_format((float)$temp_order_discount, config('decimal'), '.', '');
                $total_tax[] = number_format((float)$temp_total_tax, config('decimal'), '.', '');
                $order_tax[] = number_format((float)$temp_order_tax, config('decimal'), '.', '');
                $shipping_cost[] = number_format((float)$temp_shipping_cost, config('decimal'), '.', '');
                $total[] = number_format((float)$temp_total, config('decimal'), '.', '');
                $start = strtotime("+1 month", $start);
            }
            $lims_warehouse_list = Warehouse::where('is_active',true)->get();
            $warehouse_id = 0;
            return view('backend.report.monthly_sale', compact('year', 'total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'total', 'lims_warehouse_list', 'warehouse_id'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function monthlySaleByWarehouse(Request $request, $year)
    {
        $data = $request->all();
        if($data['warehouse_id'] == 0)
            return redirect()->back();

        $sale_percentage = $this->getSalePercentageFromSetting();
        $apply_percentage = $sale_percentage !== null && $sale_percentage < 100;
        if (!Auth::user()->hasPermissionTo('sale-percentage-filter')) {
            $apply_percentage = false;
        }

        $start = strtotime($year .'-01-01');
        $end = strtotime($year .'-12-31');
        while($start <= $end)
        {
            $start_date = $year . '-'. date('m', $start).'-'.'01';
            $end_date = $year . '-'. date('m', $start).'-'.date('t', mktime(0, 0, 0, (int)date('m', $start), 1, (int)$year));

            $baseQuery = Sale::where('warehouse_id', $data['warehouse_id'])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date);
            if ($apply_percentage) {
                $sale_ids = $this->getSaleIdsForPercentageFilter($baseQuery, $sale_percentage);
                if (count($sale_ids) > 0) {
                    $temp_total_discount = Sale::whereIn('id', $sale_ids)->sum('total_discount');
                    $temp_order_discount = Sale::whereIn('id', $sale_ids)->sum('order_discount');
                    $temp_total_tax = Sale::whereIn('id', $sale_ids)->sum('total_tax');
                    $temp_order_tax = Sale::whereIn('id', $sale_ids)->sum('order_tax');
                    $temp_shipping_cost = Sale::whereIn('id', $sale_ids)->sum('shipping_cost');
                    $temp_total = Sale::whereIn('id', $sale_ids)->sum('grand_total');
                } else {
                    $temp_total_discount = $temp_order_discount = $temp_total_tax = $temp_order_tax = $temp_shipping_cost = $temp_total = 0;
                }
            } else {
                $temp_total_discount = $baseQuery->sum('total_discount');
                $temp_order_discount = (clone $baseQuery)->sum('order_discount');
                $temp_total_tax = (clone $baseQuery)->sum('total_tax');
                $temp_order_tax = (clone $baseQuery)->sum('order_tax');
                $temp_shipping_cost = (clone $baseQuery)->sum('shipping_cost');
                $temp_total = (clone $baseQuery)->sum('grand_total');
            }
            $total_discount[] = number_format((float)$temp_total_discount, config('decimal'), '.', '');
            $order_discount[] = number_format((float)$temp_order_discount, config('decimal'), '.', '');
            $total_tax[] = number_format((float)$temp_total_tax, config('decimal'), '.', '');
            $order_tax[] = number_format((float)$temp_order_tax, config('decimal'), '.', '');
            $shipping_cost[] = number_format((float)$temp_shipping_cost, config('decimal'), '.', '');
            $total[] = number_format((float)$temp_total, config('decimal'), '.', '');
            $start = strtotime("+1 month", $start);
        }
        $lims_warehouse_list = Warehouse::where('is_active',true)->get();
        $warehouse_id = $data['warehouse_id'];
        return view('backend.report.monthly_sale', compact('year', 'total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'total', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function monthlyPurchase($year)
    {
        if(Auth::user()->hasPermissionTo('monthly-purchase')){
            $start = strtotime($year .'-01-01');
            $end = strtotime($year .'-12-31');
            while($start <= $end)
            {
                $start_date = $year . '-'. date('m', $start).'-'.'01';
                $end_date = $year . '-'. date('m', $start).'-'.'31';

                $query1 = array(
                    'SUM(total_discount) AS total_discount',
                    'SUM(order_discount) AS order_discount',
                    'SUM(total_tax) AS total_tax',
                    'SUM(order_tax) AS order_tax',
                    'SUM(shipping_cost) AS shipping_cost',
                    'SUM(grand_total) AS grand_total'
                );
                $purchase_data = Purchase::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query1))->get();

                $total_discount[] = number_format((float)$purchase_data[0]->total_discount, config('decimal'), '.', '');
                $order_discount[] = number_format((float)$purchase_data[0]->order_discount, config('decimal'), '.', '');
                $total_tax[] = number_format((float)$purchase_data[0]->total_tax, config('decimal'), '.', '');
                $order_tax[] = number_format((float)$purchase_data[0]->order_tax, config('decimal'), '.', '');
                $shipping_cost[] = number_format((float)$purchase_data[0]->shipping_cost, config('decimal'), '.', '');
                $grand_total[] = number_format((float)$purchase_data[0]->grand_total, config('decimal'), '.', '');
                $start = strtotime("+1 month", $start);
            }
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $warehouse_id = 0;
            return view('backend.report.monthly_purchase', compact('year', 'total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'lims_warehouse_list', 'warehouse_id'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function monthlyPurchaseByWarehouse(Request $request, $year)
    {
        $data = $request->all();
        if($data['warehouse_id'] == 0)
            return redirect()->back();

        $start = strtotime($year .'-01-01');
        $end = strtotime($year .'-12-31');
        while($start <= $end)
        {
            $start_date = $year . '-'. date('m', $start).'-'.'01';
            $end_date = $year . '-'. date('m', $start).'-'.'31';

            $query1 = array(
                'SUM(total_discount) AS total_discount',
                'SUM(order_discount) AS order_discount',
                'SUM(total_tax) AS total_tax',
                'SUM(order_tax) AS order_tax',
                'SUM(shipping_cost) AS shipping_cost',
                'SUM(grand_total) AS grand_total'
            );
            $purchase_data = Purchase::where('warehouse_id', $data['warehouse_id'])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query1))->get();

            $total_discount[] = number_format((float)$purchase_data[0]->total_discount, config('decimal'), '.', '');
            $order_discount[] = number_format((float)$purchase_data[0]->order_discount, config('decimal'), '.', '');
            $total_tax[] = number_format((float)$purchase_data[0]->total_tax, config('decimal'), '.', '');
            $order_tax[] = number_format((float)$purchase_data[0]->order_tax, config('decimal'), '.', '');
            $shipping_cost[] = number_format((float)$purchase_data[0]->shipping_cost, config('decimal'), '.', '');
            $grand_total[] = number_format((float)$purchase_data[0]->grand_total, config('decimal'), '.', '');
            $start = strtotime("+1 month", $start);
        }
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $warehouse_id = $data['warehouse_id'];
        return view('backend.report.monthly_purchase', compact('year', 'total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'lims_warehouse_list', 'warehouse_id'));
    }

 
    public function bestSeller(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('best-seller')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        // Default to today’s date if no input is provided
        $start_date = $request->input('start_date', date('Y-m-d'));
        $end_date   = $request->input('end_date', date('Y-m-d'));

        // Single query for the date range (default = today)
        $best_selling_qty = Product_Sale::select(DB::raw('product_id, sum(qty) as sold_qty'))
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->groupBy('product_id')
            ->orderByDesc('sold_qty')
            ->take(10)
            ->get();

        $product  = [];
        $sold_qty = [];

        foreach ($best_selling_qty as $best_seller) {
            $product_data = Product::find($best_seller->product_id);
            $product[]  = $product_data->name . ': ' . $product_data->code;
            $sold_qty[] = $best_seller->sold_qty;
        }

        // If no sales, pass empty arrays (view can handle)
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $warehouse_id = 0;
        $start_month = date("F Y"); // For display, now just current month/day context

        return view('backend.report.best_seller', compact(
            'product', 'sold_qty', 'start_month', 'lims_warehouse_list', 'warehouse_id',
            'start_date', 'end_date' // pass to view if needed for form inputs
        ));
    }

    public function bestSellerByWarehouse(Request $request)
    {
        $data = $request->all();
        if($data['warehouse_id'] == 0)
            return redirect()->back();

        $start = strtotime(date("Y-m", strtotime("-2 months")).'-01');
        $end = strtotime(date("Y").'-'.date("m").'-31');

        while($start <= $end)
        {
            $start_date = date("Y-m", $start).'-'.'01';
            $end_date = date("Y-m", $start).'-'.'31';

            $best_selling_qty = DB::table('sales')
                                ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->select(DB::raw('product_sales.product_id, sum(product_sales.qty) as sold_qty'))->where('sales.warehouse_id', $data['warehouse_id'])->whereDate('sales.created_at', '>=' , $start_date)->whereDate('sales.created_at', '<=' , $end_date)->groupBy('product_id')->orderBy('sold_qty', 'desc')->take(10)->get();

            if(!count($best_selling_qty)) {
                $product[] = '';
                $sold_qty[] = 0;
            }
            foreach ($best_selling_qty as $best_seller) {
                $product_data = Product::find($best_seller->product_id);
                $product[] = $product_data->name.': '.$product_data->code;
                $sold_qty[] = $best_seller->sold_qty;
            }
            $start = strtotime("+1 month", $start);
        }
        $start_month = date("F Y", strtotime('-2 month'));
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $warehouse_id = $data['warehouse_id'];
        return view('backend.report.best_seller', compact('product', 'sold_qty', 'start_month', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function profitLoss(Request $request)
    {
        $start_date = $request['start_date'];
        $end_date = $request['end_date'];
        $query1 = array(
            'SUM(grand_total) AS grand_total',
            'SUM(shipping_cost) AS shipping_cost',
            'SUM(paid_amount) AS paid_amount',
            'SUM(total_tax + order_tax) AS tax',
            'SUM(total_discount + order_discount) AS discount'
        );
        $query2 = array(
            'SUM(grand_total) AS grand_total',
            'SUM(total_tax + order_tax) AS tax'
        );
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();
        $product_sale_data = Product_Sale::select(DB::raw('product_id, product_batch_id, sale_unit_id, sum(qty) as sold_qty, sum(product_sales.return_qty) as return_qty, sum(total) as sold_amount'))
                            ->whereDate('created_at', '>=' , $start_date)
                            ->whereDate('created_at', '<=' , $end_date)
                            ->groupBy('product_id', 'product_batch_id')
                            ->get();
        config()->set('database.connections.mysql.strict', true);
            DB::reconnect();
        $data = $this->calculateAverageCOGS($product_sale_data);
        $product_cost = $data[0];
        $product_tax = $data[1];


        

        $purchase = Purchase::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query1))->get();
        $total_purchase = Purchase::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->count();
        $sale = Sale::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query1))->get();
        $total_sale = Sale::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->count();
        $return = Returns::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query2))->get();
        $total_return = Returns::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->count();
        $purchase_return = ReturnPurchase::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query2))->get();
        $total_purchase_return = ReturnPurchase::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->count();
        $expense = Expense::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('amount');
        $total_expense = Expense::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->count();
        $payroll = Payroll::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('amount');
        $total_payroll = Payroll::whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->count();
        $total_item = DB::table('product_warehouse')
                    ->join('products', 'product_warehouse.product_id', '=', 'products.id')
                    ->where([
                        ['products.is_active', true],
                        ['product_warehouse.qty', '>' , 0]
                    ])->count();
        $payment_recieved_number = DB::table('payments')->whereNotNull('sale_id')->whereDate('created_at', '>=' , $start_date)
            ->whereDate('created_at', '<=' , $end_date)->count();
        $payment_recieved = DB::table('payments')->whereNotNull('sale_id')->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('payments.amount');
        $credit_card_payment_sale = DB::table('payments')
                            ->where('paying_method', 'Credit Card')
                            ->whereNotNull('payments.sale_id')
                            ->whereDate('payments.created_at', '>=' , $start_date)
                            ->whereDate('payments.created_at', '<=' , $end_date)->sum('payments.amount');
        $cheque_payment_sale = DB::table('payments')
                            ->where('paying_method', 'Cheque')
                            ->whereNotNull('payments.sale_id')
                            ->whereDate('payments.created_at', '>=' , $start_date)
                            ->whereDate('payments.created_at', '<=' , $end_date)->sum('payments.amount');
        $gift_card_payment_sale = DB::table('payments')
                            ->where('paying_method', 'Gift Card')
                            ->whereNotNull('sale_id')
                            ->whereDate('created_at', '>=' , $start_date)
                            ->whereDate('created_at', '<=' , $end_date)
                            ->sum('amount');
        $paypal_payment_sale = DB::table('payments')
                            ->where('paying_method', 'Paypal')
                            ->whereNotNull('sale_id')
                            ->whereDate('created_at', '>=' , $start_date)
                            ->whereDate('created_at', '<=' , $end_date)
                            ->sum('amount');
        $deposit_payment_sale = DB::table('payments')
                            ->where('paying_method', 'Deposit')
                            ->whereNotNull('sale_id')
                            ->whereDate('created_at', '>=' , $start_date)
                            ->whereDate('created_at', '<=' , $end_date)
                            ->sum('amount');
        $cash_payment_sale =  $payment_recieved - $credit_card_payment_sale - $cheque_payment_sale - $gift_card_payment_sale - $paypal_payment_sale - $deposit_payment_sale;
        $payment_sent_number = DB::table('payments')->whereNotNull('purchase_id')->whereDate('created_at', '>=' , $start_date)
            ->whereDate('created_at', '<=' , $end_date)->count();
        $payment_sent = DB::table('payments')->whereNotNull('purchase_id')->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('payments.amount');
        $credit_card_payment_purchase = DB::table('payments')
                            ->where('paying_method', 'Gift Card')
                            ->whereNotNull('payments.purchase_id')
                            ->whereDate('payments.created_at', '>=' , $start_date)
                            ->whereDate('payments.created_at', '<=' , $end_date)->sum('payments.amount');
        $cheque_payment_purchase = DB::table('payments')
                            ->where('paying_method', 'Cheque')
                            ->whereNotNull('payments.purchase_id')
                            ->whereDate('payments.created_at', '>=' , $start_date)
                            ->whereDate('payments.created_at', '<=' , $end_date)->sum('payments.amount');
        $cash_payment_purchase =  $payment_sent - $credit_card_payment_purchase - $cheque_payment_purchase;
        $lims_warehouse_all = Warehouse::where('is_active',true)->get();
        $warehouse_name = [];
        $warehouse_sale = [];
        $warehouse_purchase = [];
        $warehouse_return = [];
        $warehouse_purchase_return = [];
        $warehouse_expense = [];
        foreach ($lims_warehouse_all as $warehouse) {
            $warehouse_name[] = $warehouse->name;
            $warehouse_sale[] = Sale::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query2))->get();
            $warehouse_purchase[] = Purchase::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query2))->get();
            $warehouse_return[] = Returns::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query2))->get();
            $warehouse_purchase_return[] = ReturnPurchase::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->selectRaw(implode(',', $query2))->get();
            $warehouse_expense[] = Expense::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('amount');
        }

        return view('backend.report.profit_loss', compact('purchase', 'product_cost', 'product_tax', 'total_purchase', 'sale', 'total_sale', 'return', 'purchase_return', 'total_return', 'total_purchase_return', 'expense', 'payroll', 'total_expense', 'total_payroll', 'payment_recieved', 'payment_recieved_number', 'cash_payment_sale', 'cheque_payment_sale', 'credit_card_payment_sale', 'gift_card_payment_sale', 'paypal_payment_sale', 'deposit_payment_sale', 'payment_sent', 'payment_sent_number', 'cash_payment_purchase', 'cheque_payment_purchase', 'credit_card_payment_purchase', 'warehouse_name', 'warehouse_sale', 'warehouse_purchase', 'warehouse_return', 'warehouse_purchase_return', 'warehouse_expense', 'start_date', 'end_date'));
    }

    
    public function profitLossData(Request $request)
    {
        // Selected year or default to current
        $selected_year = $request->input('year', date('Y'));
        $selected_month = $request->input('month', ''); // 01-12 or empty for full year

        $year = $selected_year;
        if (!empty($selected_month) && preg_match('/^(0[1-9]|1[0-2])$/', $selected_month)) {
            $start_date = $year . '-' . $selected_month . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
            $month_name = date('F', strtotime($start_date));
            $period_label = $month_name . ' ' . $year;
            $period_subtitle = 'For the Month Ending ' . date('F j, Y', strtotime($end_date));
        } else {
            $start_date = $year . '-01-01';
            $end_date = $year . '-12-31';
            $period_label = $year;
            $period_subtitle = 'For the Year Ending ' . $year;
        }

        // Get active currency
        $active_currency = Currency::where('is_active', true)->first();
        $currency_code = $active_currency ? $active_currency->code : '$';

        // Query arrays for aggregations
        $query1 = [
            'SUM(grand_total) AS grand_total',
            'SUM(shipping_cost) AS shipping_cost',
            'SUM(paid_amount) AS paid_amount',
            'SUM(total_tax + order_tax) AS tax',
            'SUM(total_discount + order_discount) AS discount'
        ];

        $query2 = [
            'SUM(grand_total) AS grand_total',
            'SUM(total_tax + order_tax) AS tax'
        ];

        // Handle strict mode for aggregated grouping
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

        // Get product sale data for period (used for revenue and tax extraction)
        $product_sale_data = Product_Sale::select(DB::raw('product_id, product_batch_id, sale_unit_id, variant_id, 
            sum(qty) as sold_qty, 
            sum(product_sales.return_qty) as return_qty, 
            sum(total) as sold_amount'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('product_id', 'product_batch_id', 'variant_id')
            ->get();

        config()->set('database.connections.mysql.strict', true);
        DB::reconnect();

        // Tax extraction – keep using the old method (or adjust if needed)
        $cogsData = $this->calculateAverageCOGS($product_sale_data);
        $product_tax = $cogsData[1] ?? 0;  // Tax on sold products

        // *** NEW: Total purchases (product cost) directly from product_purchases ***
        // Joins product_sales with product_purchases on product_id, calculates cost as:
        // sold_qty * (product_purchases.total_cost / product_purchases.qty)
        $product_cost = DB::table('product_sales')
            ->join('product_purchases', 'product_sales.product_id', '=', 'product_purchases.product_id')
            ->whereBetween('product_sales.created_at', [$start_date, $end_date])
            ->sum(DB::raw('product_sales.qty * (product_purchases.net_unit_cost / product_purchases.qty)'));

        // Total product revenue from sale data
        $product_revenue = $product_sale_data->sum('sold_amount');

        // Profit from product sales (informational)
        $product_profit = $product_revenue - $product_cost;

        // Get total purchases (summary of all purchase transactions, still for reference)
        $purchase = Purchase::whereBetween('created_at', [$start_date, $end_date])
            ->selectRaw(implode(',', $query1))
            ->first();

        // Get total sales revenue
        $sale = Sale::whereBetween('created_at', [$start_date, $end_date])
            ->selectRaw(implode(',', $query1))
            ->first();
        $total_sale = $sale->grand_total ?? 0;

        // Get sale returns
        $return = Returns::whereBetween('created_at', [$start_date, $end_date])
            ->selectRaw(implode(',', $query2))
            ->first();
        $sale_return_amount = $return->grand_total ?? 0;

        // Get purchase returns
        $purchase_return = ReturnPurchase::whereBetween('created_at', [$start_date, $end_date])
            ->selectRaw(implode(',', $query2))
            ->first();
        $purchase_return_amount = $purchase_return->grand_total ?? 0;

        // Get all expenses
        $expenses = Expense::whereBetween('created_at', [$start_date, $end_date])->get();
        $total_expense = $expenses->sum('amount');

        // Operating expenses (exclude payroll)
        $filtered_expenses = $expenses->filter(function($expense) {
            $exclude_categories = ['payroll', 'salary', 'wages', 'labour'];
            return !in_array(strtolower($expense->name), $exclude_categories);
        });
        $total_operating_expenses = $filtered_expenses->sum('amount');

        // Payroll (direct labor)
        $payroll = Payroll::whereBetween('created_at', [$start_date, $end_date])->sum('amount');
        $total_payroll_count = Payroll::whereBetween('created_at', [$start_date, $end_date])->count();

        // Calculate P&L metrics
        $total_cogs = $product_cost + $payroll;
        $gross_profit = $total_sale - $total_cogs;
        $operating_profit = $gross_profit - $total_operating_expenses;
        $net_profit_before_tax = $operating_profit;
        $net_profit = $net_profit_before_tax - $product_tax;

        return view('backend.report.profitloss', compact(
            'selected_year', 'selected_month', 'period_label', 'period_subtitle',
            'year', 'start_date', 'end_date', 'currency_code',
            'sale', 'total_sale', 'purchase',
            'return', 'sale_return_amount', 'purchase_return', 'purchase_return_amount',
            'product_cost', 'product_tax', 'payroll', 'total_payroll_count', 'total_cogs',
            'expenses', 'filtered_expenses', 'total_expense', 'total_operating_expenses',
            'gross_profit', 'operating_profit', 'net_profit_before_tax', 'net_profit'
        ));
    }

 
    public function calculateAverageCOGS($product_sale_data)
    {
        $product_cost = 0;
        $product_tax = 0;
        foreach ($product_sale_data as $key => $product_sale) {
            $product_data = Product::select('type', 'product_list', 'variant_list', 'qty_list')->find($product_sale->product_id);
            if(!$product_data) {
                continue;
            }
            if($product_data->type == 'combo') {
                $product_list = explode(",", $product_data->product_list);
                if($product_data->variant_list)
                    $variant_list = explode(",", $product_data->variant_list);
                else
                    $variant_list = [];
                $qty_list = explode(",", $product_data->qty_list);

                foreach ($product_list as $index => $product_id) {
                    if(count($variant_list) && $variant_list[$index]) {
                        $product_purchase_data = ProductPurchase::where([
                            ['product_id', $product_id],
                            ['variant_id', $variant_list[$index] ]
                        ])
                        ->select('recieved', 'purchase_unit_id', 'tax', 'total')
                        ->get();
                    }
                    else {
                        $product_purchase_data = ProductPurchase::where('product_id', $product_id)
                        ->select('recieved', 'purchase_unit_id', 'tax', 'total')
                        ->get();
                    }
                    $total_received_qty = 0;
                    $total_purchased_amount = 0;
                    $total_tax = 0;
                    $sold_qty = ($product_sale->sold_qty - $product_sale->return_qty) * $qty_list[$index];
                    foreach ($product_purchase_data as $key => $product_purchase) {
                        $purchase_unit_data = Unit::select('operator', 'operation_value')->find($product_purchase->purchase_unit_id);
                        if($purchase_unit_data->operator == '*')
                            $total_received_qty += $product_purchase->recieved * $purchase_unit_data->operation_value;
                        else
                            $total_received_qty += $product_purchase->recieved / $purchase_unit_data->operation_value;
                        $total_purchased_amount += $product_purchase->total;
                        $total_tax += $product_purchase->tax;
                    }
                    if($total_received_qty) {
                        $averageCost = $total_purchased_amount / $total_received_qty;
                        $averageTax = $total_tax / $total_received_qty;
                    }
                    else {
                        $averageCost = 0;
                        $averageTax = 0;
                    }
                    $product_cost += $sold_qty * $averageCost;
                    $product_tax += $sold_qty * $averageTax;
                }
            }
            else {
                if($product_sale->product_batch_id) {
                    $product_purchase_data = ProductPurchase::where([
                        ['product_id', $product_sale->product_id],
                        ['product_batch_id', $product_sale->product_batch_id]
                    ])
                    ->select('recieved', 'purchase_unit_id', 'tax', 'total')
                    ->get();
                }
                elseif($product_sale->variant_id) {
                    $product_purchase_data = ProductPurchase::where([
                        ['product_id', $product_sale->product_id],
                        ['variant_id', $product_sale->variant_id]
                    ])
                    ->select('recieved', 'purchase_unit_id', 'tax', 'total')
                    ->get();
                }
                else {
                    $product_purchase_data = ProductPurchase::where('product_id', $product_sale->product_id)
                    ->select('recieved', 'purchase_unit_id', 'tax', 'total')
                    ->get();
                }
                $total_received_qty = 0;
                $total_purchased_amount = 0;
                $total_tax = 0;
                if($product_sale->sale_unit_id) {
                    $sale_unit_data = Unit::select('operator', 'operation_value')->find($product_sale->sale_unit_id);
                    if($sale_unit_data->operator == '*')
                        $sold_qty = ($product_sale->sold_qty - $product_sale->return_qty) * $sale_unit_data->operation_value;
                    else
                        $sold_qty = ($product_sale->sold_qty - $product_sale->return_qty) / $sale_unit_data->operation_value;
                }
                else {
                    $sold_qty = ($product_sale->sold_qty - $product_sale->return_qty);
                }
                foreach ($product_purchase_data as $key => $product_purchase) {
                    $purchase_unit_data = Unit::select('operator', 'operation_value')->find($product_purchase->purchase_unit_id);
                    if($purchase_unit_data->operator == '*')
                        $total_received_qty += $product_purchase->recieved * $purchase_unit_data->operation_value;
                    else
                        $total_received_qty += $product_purchase->recieved / $purchase_unit_data->operation_value;
                    $total_purchased_amount += $product_purchase->total;
                    $total_tax += $product_purchase->tax;
                }
                if($total_received_qty) {
                    $averageCost = $total_purchased_amount / $total_received_qty;
                    $averageTax = $total_tax / $total_received_qty;
                }
                else {
                    $averageCost = 0;
                    $averageTax = 0;
                }
                $product_cost += $sold_qty * $averageCost;
                $product_tax += $sold_qty * $averageTax;
            }
        }
        return [$product_cost, $product_tax];
    }

    public function productReport(Request $request)
    {
        $data = $request->all();
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $warehouse_id = $data['warehouse_id'];
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        return view('backend.report.product_report',compact('start_date', 'end_date', 'warehouse_id', 'lims_warehouse_list'));
    }

    public function productReportData(Request $request)
    {
        $data = $request->all();
        // $start_date = $data['start_date'];
        // $end_date = $data['end_date'];
        $start_date = $data['start_date'] ?? date('Y-m-d');
        $end_date   = $data['end_date'] ?? date('Y-m-d');
        $warehouse_id = $data['warehouse_id'];
        $product_id = [];
        $variant_id = [];
        $product_name = [];
        $product_qty = [];

        $columns = array(
            1 => 'name'
        );

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        //return $request;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if($request->input('search.value')) {
            $search = $request->input('search.value');
            $totalData = Product::where([
                ['name', 'LIKE', "%{$search}%"],
                ['is_active', true]
            ])->count();
            $lims_product_all = Product::with('category')
                                ->select('id', 'name', 'code', 'category_id', 'qty', 'is_variant', 'price', 'cost')
                                ->where([
                                    ['name', 'LIKE', "%{$search}%"],
                                    ['is_active', true]
                                ])->offset($start)
                                  ->limit($limit)
                                  ->orderBy($order, $dir)
                                  ->get();
        }
        else {
            $totalData = Product::where('is_active', true)->count();
            $lims_product_all = Product::with('category')
                                ->select('id', 'name', 'code', 'category_id', 'qty', 'is_variant', 'price', 'cost')
                                ->where('is_active', true)
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();
        }

        $totalFiltered = $totalData;
        $data = [];
        foreach ($lims_product_all as $product) {
            $variant_id_all = [];
            if($warehouse_id == 0) {
                if($product->is_variant) {
                    $variant_id_all = ProductVariant::where('product_id', $product->id)->pluck('variant_id', 'item_code');
                    foreach ($variant_id_all as $item_code => $variant_id) {
                        $variant_data = Variant::select('name')->find($variant_id);
                        $nestedData['key'] = count($data);
                        $nestedData['name'] = $product->name . ' [' . $variant_data->name . ']'.'<br>'.$item_code;
                        $nestedData['category'] = $product->category->name;
                        //purchase data
                        $nestedData['purchased_amount'] = ProductPurchase::where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                        $lims_product_purchase_data = ProductPurchase::select('purchase_unit_id', 'qty')->where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                        $purchased_qty = 0;
                        if(count($lims_product_purchase_data)) {
                            foreach ($lims_product_purchase_data as $product_purchase) {
                                $unit = DB::table('units')->find($product_purchase->purchase_unit_id);
                                if($unit->operator == '*'){
                                    $purchased_qty += $product_purchase->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $purchased_qty += $product_purchase->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['purchased_qty'] = $purchased_qty;
                        //transfer data
                        /*$nestedData['transfered_amount'] = ProductTransfer::where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                        $lims_product_transfer_data = ProductTransfer::select('purchase_unit_id', 'qty')->where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                        $transfered_qty = 0;
                        if(count($lims_product_transfer_data)) {
                            foreach ($lims_product_transfer_data as $product_transfer) {
                                $unit = DB::table('units')->find($product_transfer->purchase_unit_id);
                                if($unit->operator == '*'){
                                    $transfered_qty += $product_transfer->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $transfered_qty += $product_transfer->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['transfered_qty'] = $transfered_qty;*/
                        //sale data
                        $nestedData['sold_amount'] = Product_Sale::where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                        $lims_product_sale_data = Product_Sale::select('sale_unit_id', 'qty')->where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                        $sold_qty = 0;
                        if(count($lims_product_sale_data)) {
                            foreach ($lims_product_sale_data as $product_sale) {
                                $unit = DB::table('units')->find($product_sale->sale_unit_id);
                                if($unit->operator == '*'){
                                    $sold_qty += $product_sale->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $sold_qty += $product_sale->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['sold_qty'] = $sold_qty;
                        //return data
                        $nestedData['returned_amount'] = ProductReturn::where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                        $lims_product_return_data = ProductReturn::select('sale_unit_id', 'qty')->where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                        $returned_qty = 0;
                        if(count($lims_product_return_data)) {
                            foreach ($lims_product_return_data as $product_return) {
                                $unit = DB::table('units')->find($product_return->sale_unit_id);
                                if($unit->operator == '*'){
                                    $returned_qty += $product_return->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $returned_qty += $product_return->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['returned_qty'] = $returned_qty;
                        //purchase return data
                        $nestedData['purchase_returned_amount'] = PurchaseProductReturn::where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                        $lims_product_purchase_return_data = PurchaseProductReturn::select('purchase_unit_id', 'qty')->where([
                                                ['product_id', $product->id],
                                                ['variant_id', $variant_id]
                                        ])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                        $purchase_returned_qty = 0;
                        if(count($lims_product_purchase_return_data)) {
                            foreach ($lims_product_purchase_return_data as $product_purchase_return) {
                                $unit = DB::table('units')->find($product_purchase_return->purchase_unit_id);
                                if($unit->operator == '*'){
                                    $purchase_returned_qty += $product_purchase_return->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $purchase_returned_qty += $product_purchase_return->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['purchase_returned_qty'] = $purchase_returned_qty;

                        if($nestedData['purchased_qty'] > 0)
                            $nestedData['profit'] = $nestedData['sold_amount'] - (($nestedData['purchased_amount'] / $nestedData['purchased_qty']) * $nestedData['sold_qty']);
                        else
                           $nestedData['profit'] =  $nestedData['sold_amount'];

                        $nestedData['in_stock'] = $product->qty;
                        if(config('currency_position') == 'prefix')
                            $nestedData['stock_worth'] = config('currency').' '.($nestedData['in_stock'] * $product->price).' / '.config('currency').' '.($nestedData['in_stock'] * $product->cost);
                        else
                            $nestedData['stock_worth'] = ($nestedData['in_stock'] * $product->price).' '.config('currency').' / '.($nestedData['in_stock'] * $product->cost).' '.config('currency');

                        $nestedData['profit'] = number_format((float)$nestedData['profit'], config('decimal'), '.', '');

                        /*if($nestedData['purchased_qty'] > 0 || $nestedData['transfered_qty'] > 0 || $nestedData['sold_qty'] > 0 || $nestedData['returned_qty'] > 0 || $nestedData['purchase_returned_qty']) {*/
                            $data[] = $nestedData;
                        //}
                    }
                }
                else {
                    $nestedData['key'] = count($data);
                    $nestedData['name'] = $product->name.'<br>'.$product->code;
                    $nestedData['category'] = $product->category->name;
                    //purchase data
                    $nestedData['purchased_amount'] = ProductPurchase::where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                    $lims_product_purchase_data = ProductPurchase::select('purchase_unit_id', 'qty')->where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                    $purchased_qty = 0;
                    if(count($lims_product_purchase_data)) {
                        foreach ($lims_product_purchase_data as $product_purchase) {
                            $unit = DB::table('units')->find($product_purchase->purchase_unit_id);
                            if($unit->operator == '*'){
                                $purchased_qty += $product_purchase->qty * $unit->operation_value;
                            }
                            elseif($unit->operator == '/'){
                                $purchased_qty += $product_purchase->qty / $unit->operation_value;
                            }
                        }
                    }
                    $nestedData['purchased_qty'] = $purchased_qty;
                    //transfer data
                    /*$nestedData['transfered_amount'] = ProductTransfer::where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                    $lims_product_transfer_data = ProductTransfer::select('purchase_unit_id', 'qty')->where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                    $transfered_qty = 0;
                    if(count($lims_product_transfer_data)) {
                        foreach ($lims_product_transfer_data as $product_transfer) {
                            $unit = DB::table('units')->find($product_transfer->purchase_unit_id);
                            if($unit->operator == '*'){
                                $transfered_qty += $product_transfer->qty * $unit->operation_value;
                            }
                            elseif($unit->operator == '/'){
                                $transfered_qty += $product_transfer->qty / $unit->operation_value;
                            }
                        }
                    }
                    $nestedData['transfered_qty'] = $transfered_qty;*/
                    //sale data
                    $nestedData['sold_amount'] = Product_Sale::where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                    $lims_product_sale_data = Product_Sale::select('sale_unit_id', 'qty')->where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                    $sold_qty = 0;
                    if(count($lims_product_sale_data)) {
                        foreach ($lims_product_sale_data as $product_sale) {
                            if($product_sale->sale_unit_id > 0) {
                                $unit = DB::table('units')->find($product_sale->sale_unit_id);
                                if($unit->operator == '*'){
                                    $sold_qty += $product_sale->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $sold_qty += $product_sale->qty / $unit->operation_value;
                                }
                            }
                            else
                                $sold_qty = $product_sale->qty;
                        }
                    }
                    $nestedData['sold_qty'] = $sold_qty;
                    //return data
                    $nestedData['returned_amount'] = ProductReturn::where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                    $lims_product_return_data = ProductReturn::select('sale_unit_id', 'qty')->where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                    $returned_qty = 0;
                    if(count($lims_product_return_data)) {
                        foreach ($lims_product_return_data as $product_return) {
                            $unit = DB::table('units')->find($product_return->sale_unit_id);
                            if($unit->operator == '*'){
                                $returned_qty += $product_return->qty * $unit->operation_value;
                            }
                            elseif($unit->operator == '/'){
                                $returned_qty += $product_return->qty / $unit->operation_value;
                            }
                        }
                    }
                    $nestedData['returned_qty'] = $returned_qty;
                    //purchase return data
                    $nestedData['purchase_returned_amount'] = PurchaseProductReturn::where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->sum('total');

                    $lims_product_purchase_return_data = PurchaseProductReturn::select('purchase_unit_id', 'qty')->where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();

                    $purchase_returned_qty = 0;
                    if(count($lims_product_purchase_return_data)) {
                        foreach ($lims_product_purchase_return_data as $product_purchase_return) {
                            $unit = DB::table('units')->find($product_purchase_return->purchase_unit_id);
                            if($unit->operator == '*'){
                                $purchase_returned_qty += $product_purchase_return->qty * $unit->operation_value;
                            }
                            elseif($unit->operator == '/'){
                                $purchase_returned_qty += $product_purchase_return->qty / $unit->operation_value;
                            }
                        }
                    }
                    $nestedData['purchase_returned_qty'] = $purchase_returned_qty;

                    if($nestedData['purchased_qty'] > 0)
                            $nestedData['profit'] = $nestedData['sold_amount'] - (($nestedData['purchased_amount'] / $nestedData['purchased_qty']) * $nestedData['sold_qty']);
                    else
                       $nestedData['profit'] =  $nestedData['sold_amount'];
                    $nestedData['in_stock'] = $product->qty;
                    if(config('currency_position') == 'prefix')
                        $nestedData['stock_worth'] = config('currency').' '.($nestedData['in_stock'] * $product->price).' / '.config('currency').' '.($nestedData['in_stock'] * $product->cost);
                    else
                        $nestedData['stock_worth'] = ($nestedData['in_stock'] * $product->price).' '.config('currency').' / '.($nestedData['in_stock'] * $product->cost).' '.config('currency');

                    $nestedData['profit'] = number_format((float)$nestedData['profit'], config('decimal'), '.', '');
                    /*if($nestedData['purchased_qty'] > 0 || $nestedData['transfered_qty'] > 0 || $nestedData['sold_qty'] > 0 || $nestedData['returned_qty'] > 0 || $nestedData['purchase_returned_qty']) {*/
                        $data[] = $nestedData;
                    //}
                }
            }
            else {
                if($product->is_variant) {
                    $variant_id_all = ProductVariant::where('product_id', $product->id)->pluck('variant_id', 'item_code');

                    foreach ($variant_id_all as $item_code => $variant_id) {
                        $variant_data = Variant::select('name')->find($variant_id);
                        $nestedData['key'] = count($data);
                        $nestedData['name'] = $product->name . ' [' . $variant_data->name . ']'.'<br>'.$item_code;
                        $nestedData['category'] = $product->category->name;
                        //purchase data
                        $nestedData['purchased_amount'] = DB::table('purchases')
                                    ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                        ['product_purchases.product_id', $product->id],
                                        ['product_purchases.variant_id', $variant_id],
                                        ['purchases.warehouse_id', $warehouse_id]
                                    ])->whereDate('purchases.created_at','>=', $start_date)->whereDate('purchases.created_at','<=', $end_date)->sum('total');
                        $lims_product_purchase_data = DB::table('purchases')
                                    ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                        ['product_purchases.product_id', $product->id],
                                        ['product_purchases.variant_id', $variant_id],
                                        ['purchases.warehouse_id', $warehouse_id]
                                    ])->whereDate('purchases.created_at','>=', $start_date)->whereDate('purchases.created_at','<=', $end_date)
                                        ->select('product_purchases.purchase_unit_id', 'product_purchases.qty')
                                        ->get();

                        $purchased_qty = 0;
                        if(count($lims_product_purchase_data)) {
                            foreach ($lims_product_purchase_data as $product_purchase) {
                                $unit = DB::table('units')->find($product_purchase->purchase_unit_id);
                                if($unit->operator == '*'){
                                    $purchased_qty += $product_purchase->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $purchased_qty += $product_purchase->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['purchased_qty'] = $purchased_qty;
                        //transfer data
                        /*$nestedData['transfered_amount'] = DB::table('transfers')
                                ->join('product_transfer', 'transfers.id', '=', 'product_transfer.transfer_id')
                                ->where([
                                    ['product_transfer.product_id', $product->id],
                                    ['product_transfer.variant_id', $variant_id],
                                    ['transfers.to_warehouse_id', $warehouse_id]
                                ])->whereDate('transfers.created_at', '>=', $start_date)
                                  ->whereDate('transfers.created_at', '<=' , $end_date)
                                  ->sum('total');
                        $lims_product_transfer_data = DB::table('transfers')
                                ->join('product_transfer', 'transfers.id', '=', 'product_transfer.transfer_id')
                                ->where([
                                    ['product_transfer.product_id', $product->id],
                                    ['product_transfer.variant_id', $variant_id],
                                    ['transfers.to_warehouse_id', $warehouse_id]
                                ])->whereDate('transfers.created_at', '>=', $start_date)
                                  ->whereDate('transfers.created_at', '<=' , $end_date)
                                  ->select('product_transfer.purchase_unit_id', 'product_transfer.qty')
                                  ->get();

                        $transfered_qty = 0;
                        if(count($lims_product_transfer_data)) {
                            foreach ($lims_product_transfer_data as $product_transfer) {
                                $unit = DB::table('units')->find($product_transfer->purchase_unit_id);
                                if($unit->operator == '*'){
                                    $transfered_qty += $product_transfer->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $transfered_qty += $product_transfer->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['transfered_qty'] = $transfered_qty;*/
                        //sale data
                        $nestedData['sold_amount'] = DB::table('sales')
                                    ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                        ['product_sales.product_id', $product->id],
                                        ['variant_id', $variant_id],
                                        ['sales.warehouse_id', $warehouse_id]
                                    ])->whereDate('sales.created_at','>=', $start_date)->whereDate('sales.created_at','<=', $end_date)->sum('total');
                        $lims_product_sale_data = DB::table('sales')
                                    ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                        ['product_sales.product_id', $product->id],
                                        ['variant_id', $variant_id],
                                        ['sales.warehouse_id', $warehouse_id]
                                    ])->whereDate('sales.created_at','>=', $start_date)
                                    ->whereDate('sales.created_at','<=', $end_date)
                                    ->select('product_sales.sale_unit_id', 'product_sales.qty')
                                    ->get();

                        $sold_qty = 0;
                        if(count($lims_product_sale_data)) {
                            foreach ($lims_product_sale_data as $product_sale) {
                                $unit = DB::table('units')->find($product_sale->sale_unit_id);
                                if($unit->operator == '*'){
                                    $sold_qty += $product_sale->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $sold_qty += $product_sale->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['sold_qty'] = $sold_qty;
                        //return data
                        $nestedData['returned_amount'] = DB::table('returns')
                                ->join('product_returns', 'returns.id', '=', 'product_returns.return_id')
                                ->where([
                                    ['product_returns.product_id', $product->id],
                                    ['product_returns.variant_id', $variant_id],
                                    ['returns.warehouse_id', $warehouse_id]
                                ])->whereDate('returns.created_at', '>=', $start_date)
                                  ->whereDate('returns.created_at', '<=' , $end_date)
                                  ->sum('total');

                        $lims_product_return_data = DB::table('returns')
                                ->join('product_returns', 'returns.id', '=', 'product_returns.return_id')
                                ->where([
                                    ['product_returns.product_id', $product->id],
                                    ['product_returns.variant_id', $variant_id],
                                    ['returns.warehouse_id', $warehouse_id]
                                ])->whereDate('returns.created_at', '>=', $start_date)
                                  ->whereDate('returns.created_at', '<=' , $end_date)
                                  ->select('product_returns.sale_unit_id', 'product_returns.qty')
                                  ->get();

                        $returned_qty = 0;
                        if(count($lims_product_return_data)) {
                            foreach ($lims_product_return_data as $product_return) {
                                $unit = DB::table('units')->find($product_return->sale_unit_id);
                                if($unit->operator == '*'){
                                    $returned_qty += $product_return->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $returned_qty += $product_return->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['returned_qty'] = $returned_qty;
                        //purchase return data
                        $nestedData['purchase_returned_amount'] = DB::table('return_purchases')
                                ->join('purchase_product_return', 'return_purchases.id', '=', 'purchase_product_return.return_id')
                                ->where([
                                    ['purchase_product_return.product_id', $product->id],
                                    ['purchase_product_return.variant_id', $variant_id],
                                    ['return_purchases.warehouse_id', $warehouse_id]
                                ])->whereDate('return_purchases.created_at', '>=', $start_date)
                                  ->whereDate('return_purchases.created_at', '<=' , $end_date)
                                  ->sum('total');
                        $lims_product_purchase_return_data = DB::table('return_purchases')
                                ->join('purchase_product_return', 'return_purchases.id', '=', 'purchase_product_return.return_id')
                                ->where([
                                    ['purchase_product_return.product_id', $product->id],
                                    ['purchase_product_return.variant_id', $variant_id],
                                    ['return_purchases.warehouse_id', $warehouse_id]
                                ])->whereDate('return_purchases.created_at', '>=', $start_date)
                                  ->whereDate('return_purchases.created_at', '<=' , $end_date)
                                  ->select('purchase_product_return.purchase_unit_id', 'purchase_product_return.qty')
                                  ->get();

                        $purchase_returned_qty = 0;
                        if(count($lims_product_purchase_return_data)) {
                            foreach ($lims_product_purchase_return_data as $product_purchase_return) {
                                $unit = DB::table('units')->find($product_purchase_return->purchase_unit_id);
                                if($unit->operator == '*'){
                                    $purchase_returned_qty += $product_purchase_return->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $purchase_returned_qty += $product_purchase_return->qty / $unit->operation_value;
                                }
                            }
                        }
                        $nestedData['purchase_returned_qty'] = $purchase_returned_qty;

                        if($nestedData['purchased_qty'] > 0)
                            $nestedData['profit'] = $nestedData['sold_amount'] - (($nestedData['purchased_amount'] / $nestedData['purchased_qty']) * $nestedData['sold_qty']);
                        else
                           $nestedData['profit'] =  $nestedData['sold_amount'];
                        $product_warehouse = Product_Warehouse::where([
                            ['product_id', $product->id],
                            ['variant_id', $variant_id],
                            ['warehouse_id', $warehouse_id]
                        ])->select('qty')->first();
                        if($product_warehouse)
                            $nestedData['in_stock'] = $product_warehouse->qty;
                        else
                            $nestedData['in_stock'] = 0;
                        if(config('currency_position') == 'prefix')
                            $nestedData['stock_worth'] = config('currency').' '.($nestedData['in_stock'] * $product->price).' / '.config('currency').' '.($nestedData['in_stock'] * $product->cost);
                        else
                            $nestedData['stock_worth'] = ($nestedData['in_stock'] * $product->price).' '.config('currency').' / '.($nestedData['in_stock'] * $product->cost).' '.config('currency');

                        $nestedData['profit'] = number_format((float)$nestedData['profit'], config('decimal'), '.', '');

                        $data[] = $nestedData;
                    }
                }
                else {
                    $nestedData['key'] = count($data);
                    $nestedData['name'] = $product->name.'<br>'.$product->code;
                    $nestedData['category'] = $product->category->name;
                    //purchase data
                    $nestedData['purchased_amount'] = DB::table('purchases')
                                ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                    ['product_purchases.product_id', $product->id],
                                    ['purchases.warehouse_id', $warehouse_id]
                                ])->whereDate('purchases.created_at','>=', $start_date)->whereDate('purchases.created_at','<=', $end_date)->sum('total');
                    $lims_product_purchase_data = DB::table('purchases')
                                ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                    ['product_purchases.product_id', $product->id],
                                    ['purchases.warehouse_id', $warehouse_id]
                                ])->whereDate('purchases.created_at','>=', $start_date)->whereDate('purchases.created_at','<=', $end_date)
                                    ->select('product_purchases.purchase_unit_id', 'product_purchases.qty')
                                    ->get();

                    $purchased_qty = 0;
                    if(count($lims_product_purchase_data)) {
                        foreach ($lims_product_purchase_data as $product_purchase) {
                            $unit = DB::table('units')->find($product_purchase->purchase_unit_id);
                            if($unit->operator == '*'){
                                $purchased_qty += $product_purchase->qty * $unit->operation_value;
                            }
                            elseif($unit->operator == '/'){
                                $purchased_qty += $product_purchase->qty / $unit->operation_value;
                            }
                        }
                    }
                    $nestedData['purchased_qty'] = $purchased_qty;
                    //transfer data
                    /*$nestedData['transfered_amount'] = DB::table('transfers')
                            ->join('product_transfer', 'transfers.id', '=', 'product_transfer.transfer_id')
                            ->where([
                                ['product_transfer.product_id', $product->id],
                                ['transfers.to_warehouse_id', $warehouse_id]
                            ])->whereDate('transfers.created_at', '>=', $start_date)
                              ->whereDate('transfers.created_at', '<=' , $end_date)
                              ->sum('total');
                    $lims_product_transfer_data = DB::table('transfers')
                            ->join('product_transfer', 'transfers.id', '=', 'product_transfer.transfer_id')
                            ->where([
                                ['product_transfer.product_id', $product->id],
                                ['transfers.to_warehouse_id', $warehouse_id]
                            ])->whereDate('transfers.created_at', '>=', $start_date)
                              ->whereDate('transfers.created_at', '<=' , $end_date)
                              ->select('product_transfer.purchase_unit_id', 'product_transfer.qty')
                              ->get();

                    $transfered_qty = 0;
                    if(count($lims_product_transfer_data)) {
                        foreach ($lims_product_transfer_data as $product_transfer) {
                            $unit = DB::table('units')->find($product_transfer->purchase_unit_id);
                            if($unit->operator == '*'){
                                $transfered_qty += $product_transfer->qty * $unit->operation_value;
                            }
                            elseif($unit->operator == '/'){
                                $transfered_qty += $product_transfer->qty / $unit->operation_value;
                            }
                        }
                    }
                    $nestedData['transfered_qty'] = $transfered_qty;*/
                    //sale data
                    $nestedData['sold_amount'] = DB::table('sales')
                                ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                    ['product_sales.product_id', $product->id],
                                    ['sales.warehouse_id', $warehouse_id]
                                ])->whereDate('sales.created_at','>=', $start_date)->whereDate('sales.created_at','<=', $end_date)->sum('total');
                    $lims_product_sale_data = DB::table('sales')
                                ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                    ['product_sales.product_id', $product->id],
                                    ['sales.warehouse_id', $warehouse_id]
                                ])->whereDate('sales.created_at','>=', $start_date)
                                ->whereDate('sales.created_at','<=', $end_date)
                                ->select('product_sales.sale_unit_id', 'product_sales.qty')
                                ->get();

                    $sold_qty = 0;
                    if(count($lims_product_sale_data)) {
                        foreach ($lims_product_sale_data as $product_sale) {
                            if($product_sale->sale_unit_id) {
                                $unit = DB::table('units')->find($product_sale->sale_unit_id);
                                if($unit->operator == '*'){
                                    $sold_qty += $product_sale->qty * $unit->operation_value;
                                }
                                elseif($unit->operator == '/'){
                                    $sold_qty += $product_sale->qty / $unit->operation_value;
                                }
                            }
                        }
                    }
                    $nestedData['sold_qty'] = $sold_qty;
                    //return data
                    $nestedData['returned_amount'] = DB::table('returns')
                            ->join('product_returns', 'returns.id', '=', 'product_returns.return_id')
                            ->where([
                                ['product_returns.product_id', $product->id],
                                ['returns.warehouse_id', $warehouse_id]
                            ])->whereDate('returns.created_at', '>=', $start_date)
                              ->whereDate('returns.created_at', '<=' , $end_date)
                              ->sum('total');

                    $lims_product_return_data = DB::table('returns')
                            ->join('product_returns', 'returns.id', '=', 'product_returns.return_id')
                            ->where([
                                ['product_returns.product_id', $product->id],
                                ['returns.warehouse_id', $warehouse_id]
                            ])->whereDate('returns.created_at', '>=', $start_date)
                              ->whereDate('returns.created_at', '<=' , $end_date)
                              ->select('product_returns.sale_unit_id', 'product_returns.qty')
                              ->get();

                    $returned_qty = 0;
                    if(count($lims_product_return_data)) {
                        foreach ($lims_product_return_data as $product_return) {
                            $unit = DB::table('units')->find($product_return->sale_unit_id);
                            if($unit->operator == '*'){
                                $returned_qty += $product_return->qty * $unit->operation_value;
                            }
                            elseif($unit->operator == '/'){
                                $returned_qty += $product_return->qty / $unit->operation_value;
                            }
                        }
                    }
                    $nestedData['returned_qty'] = $returned_qty;
                    //purchase return data
                    $nestedData['purchase_returned_amount'] = DB::table('return_purchases')
                            ->join('purchase_product_return', 'return_purchases.id', '=', 'purchase_product_return.return_id')
                            ->where([
                                ['purchase_product_return.product_id', $product->id],
                                ['return_purchases.warehouse_id', $warehouse_id]
                            ])->whereDate('return_purchases.created_at', '>=', $start_date)
                              ->whereDate('return_purchases.created_at', '<=' , $end_date)
                              ->sum('total');
                    $lims_product_purchase_return_data = DB::table('return_purchases')
                            ->join('purchase_product_return', 'return_purchases.id', '=', 'purchase_product_return.return_id')
                            ->where([
                                ['purchase_product_return.product_id', $product->id],
                                ['return_purchases.warehouse_id', $warehouse_id]
                            ])->whereDate('return_purchases.created_at', '>=', $start_date)
                              ->whereDate('return_purchases.created_at', '<=' , $end_date)
                              ->select('purchase_product_return.purchase_unit_id', 'purchase_product_return.qty')
                              ->get();

                    $purchase_returned_qty = 0;
                    if(count($lims_product_purchase_return_data)) {
                        foreach ($lims_product_purchase_return_data as $product_purchase_return) {
                            $unit = DB::table('units')->find($product_purchase_return->purchase_unit_id);
                            if($unit->operator == '*'){
                                $purchase_returned_qty += $product_purchase_return->qty * $unit->operation_value;
                            }
                            elseif($unit->operator == '/'){
                                $purchase_returned_qty += $product_purchase_return->qty / $unit->operation_value;
                            }
                        }
                    }
                    $nestedData['purchase_returned_qty'] = $purchase_returned_qty;

                    if($nestedData['purchased_qty'] > 0)
                            $nestedData['profit'] = $nestedData['sold_amount'] - (($nestedData['purchased_amount'] / $nestedData['purchased_qty']) * $nestedData['sold_qty']);
                    else
                       $nestedData['profit'] =  $nestedData['sold_amount'];

                    $product_warehouse = Product_Warehouse::where([
                        ['product_id', $product->id],
                        ['warehouse_id', $warehouse_id]
                    ])->select('qty')->first();
                    if($product_warehouse)
                        $nestedData['in_stock'] = $product_warehouse->qty;
                    else
                        $nestedData['in_stock'] = 0;
                    if(config('currency_position') == 'prefix')
                        $nestedData['stock_worth'] = config('currency').' '.($nestedData['in_stock'] * $product->price).' / '.config('currency').' '.($nestedData['in_stock'] * $product->cost);
                    else
                        $nestedData['stock_worth'] = ($nestedData['in_stock'] * $product->price).' '.config('currency').' / '.($nestedData['in_stock'] * $product->cost).' '.config('currency');

                    $nestedData['profit'] = number_format((float)$nestedData['profit'], config('decimal'), '.', '');

                    $data[] = $nestedData;
                }
            }
        }
        /* Grand totals (all data) for product report footer */
        $purchased_amount = $warehouse_id == 0
            ? DB::table('product_purchases')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('total')
            : DB::table('product_purchases')->join('purchases', 'product_purchases.purchase_id', '=', 'purchases.id')->where('purchases.warehouse_id', $warehouse_id)->whereDate('product_purchases.created_at', '>=', $start_date)->whereDate('product_purchases.created_at', '<=', $end_date)->sum('product_purchases.total');
        $sold_amount = $warehouse_id == 0
            ? DB::table('product_sales')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('total')
            : DB::table('product_sales')->join('sales', 'product_sales.sale_id', '=', 'sales.id')->where('sales.warehouse_id', $warehouse_id)->whereDate('product_sales.created_at', '>=', $start_date)->whereDate('product_sales.created_at', '<=', $end_date)->sum('product_sales.total');
        $returned_amount = $warehouse_id == 0
            ? DB::table('product_returns')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('total')
            : DB::table('product_returns')->join('returns', 'product_returns.return_id', '=', 'returns.id')->where('returns.warehouse_id', $warehouse_id)->whereDate('product_returns.created_at', '>=', $start_date)->whereDate('product_returns.created_at', '<=', $end_date)->sum('product_returns.total');
        $purchase_returned_amount = $warehouse_id == 0
            ? DB::table('purchase_product_return')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('total')
            : DB::table('purchase_product_return')->join('return_purchases', 'purchase_product_return.return_id', '=', 'return_purchases.id')->where('return_purchases.warehouse_id', $warehouse_id)->whereDate('purchase_product_return.created_at', '>=', $start_date)->whereDate('purchase_product_return.created_at', '<=', $end_date)->sum('purchase_product_return.total');
        $grand_totals = [
            'purchased_amount' => (float) $purchased_amount,
            'sold_amount' => (float) $sold_amount,
            'returned_amount' => (float) $returned_amount,
            'purchase_returned_amount' => (float) $purchase_returned_amount,
        ];
        /*$totalData = count($data);
        $totalFiltered = $totalData;*/
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
            "grand_totals"    => $grand_totals
        );

        echo json_encode($json_data);
    }

    public function purchaseReport(Request $request)
    {
        $data = $request->all();
        $today = date('Y-m-d');
        $start_date = !empty($data['start_date']) ? $data['start_date'] : $today;
        $end_date   = !empty($data['end_date']) ? $data['end_date'] : $today;
        $warehouse_id = $data['warehouse_id'] ?? 0;
        $product_id = [];
        $variant_id = [];
        $product_name = [];
        $product_qty = [];
        $lims_product_all = Product::select('id', 'name', 'qty', 'is_variant')->where('is_active', true)->get();
        foreach ($lims_product_all as $product) {
            $lims_product_purchase_data = null;
            $variant_id_all = [];
            if($warehouse_id == 0) {
                if($product->is_variant)
                    $variant_id_all = ProductPurchase::distinct('variant_id')->where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->pluck('variant_id');
                else
                    $lims_product_purchase_data = ProductPurchase::where('product_id', $product->id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->first();
            }
            else {
                if($product->is_variant)
                    $variant_id_all = DB::table('purchases')
                        ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
                        ->distinct('variant_id')
                        ->where([
                            ['product_purchases.product_id', $product->id],
                            ['purchases.warehouse_id', $warehouse_id]
                        ])->whereDate('purchases.created_at','>=', $start_date)
                          ->whereDate('purchases.created_at','<=', $end_date)
                          ->pluck('variant_id');
                else
                    $lims_product_purchase_data = DB::table('purchases')
                        ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                ['product_purchases.product_id', $product->id],
                                ['purchases.warehouse_id', $warehouse_id]
                        ])->whereDate('purchases.created_at','>=', $start_date)
                          ->whereDate('purchases.created_at','<=', $end_date)
                          ->first();
            }

            if($lims_product_purchase_data) {
                $product_name[] = $product->name;
                $product_id[] = $product->id;
                $variant_id[] = null;
                if($warehouse_id == 0)
                    $product_qty[] = $product->qty;
                else
                    $product_qty[] = Product_Warehouse::where([
                                    ['product_id', $product->id],
                                    ['warehouse_id', $warehouse_id]
                                ])->sum('qty');
            }
            elseif(count($variant_id_all)) {
                foreach ($variant_id_all as $key => $variantId) {
                    $variant_data = Variant::find($variantId);
                    $product_name[] = $product->name.' ['.$variant_data->name.']';
                    $product_id[] = $product->id;
                    $variant_id[] = $variant_data->id;
                    if($warehouse_id == 0)
                        $product_qty[] = ProductVariant::FindExactProduct($product->id, $variant_data->id)->first()->qty;
                    else
                        $product_qty[] = Product_Warehouse::where([
                                        ['product_id', $product->id],
                                        ['variant_id', $variant_data->id],
                                        ['warehouse_id', $warehouse_id]
                                    ])->first()->qty;

                }
            }
        }
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        return view('backend.report.purchase_report',compact('product_id', 'variant_id', 'product_name', 'product_qty', 'start_date', 'end_date', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function saleReport(Request $request)
    {
        $data = $request->all();
        $today = date('Y-m-d');
        $start_date = !empty($data['start_date']) ? $data['start_date'] : $today;
        $end_date   = !empty($data['end_date']) ? $data['end_date'] : $today;
        $warehouse_id = (int) ($data['warehouse_id'] ?? 0);
        $biller_id = (int) ($data['biller_id'] ?? 0);
        $user_id = (int) ($data['user_id'] ?? 0);
        $category_id = (int) ($data['category_id'] ?? 0);
        $department_id = (int) ($data['department_id'] ?? 0);
        $payment_mode = $data['payment_mode'] ?? '0';
        // Same check as Sale index: request (form) > session > GeneralSetting, so filtering keeps percentage in sync
        $percentage_filter = GeneralSetting::first()->percentage_filter;
        if (isset($data['percentage_filter']) && $data['percentage_filter'] !== '' && $data['percentage_filter'] !== null) {
            $percentage_filter = (int) $data['percentage_filter'];
            if ($percentage_filter < 0 || $percentage_filter > 100) {
                $percentage_filter = null;
            }
        }
        if ($percentage_filter === null && session('percentage_filter') !== null) {
            $percentage_filter = (int) session('percentage_filter');
        }
        if ($percentage_filter === null) {
            $percentage_filter = $this->getSalePercentageFromSetting();
        }
        $filter_by_percentage = $percentage_filter !== null && $percentage_filter < 100;
        if (!Auth::user()->hasPermissionTo('sale-percentage-filter')) {
            $filter_by_percentage = false;
        }
        $sale_ids_for_percentage = [];

        if ($filter_by_percentage) {
            $baseSalesQuery = Sale::whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date);
            if ($warehouse_id > 0) {
                $baseSalesQuery->where('warehouse_id', $warehouse_id);
            }
            if ($biller_id > 0) {
                $baseSalesQuery->where('biller_id', $biller_id);
            }
            if ($user_id > 0) {
                $baseSalesQuery->where('user_id', $user_id);
            }
            $sale_ids_for_percentage = $this->getSaleIdsForPercentageFilter($baseSalesQuery, $percentage_filter);
        }

        // Sale-first: one aggregated query for product_sales + sales in date range
        $aggregatedQuery = DB::table('product_sales')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->leftJoin('units', 'product_sales.sale_unit_id', '=', 'units.id')
            ->join('products', 'product_sales.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->whereDate('sales.created_at', '>=', $start_date)
            ->whereDate('sales.created_at', '<=', $end_date)
            ->when($warehouse_id > 0, function ($q) use ($warehouse_id) {
                $q->where('sales.warehouse_id', $warehouse_id);
            })
            ->when($biller_id > 0, function ($q) use ($biller_id) {
                $q->where('sales.biller_id', $biller_id);
            })
            ->when($user_id > 0, function ($q) use ($user_id) {
                $q->where('sales.user_id', $user_id);
            })
            ->when($category_id > 0, function ($q) use ($category_id) {
                $q->where('products.category_id', $category_id);
            })
            ->when($department_id > 0, function ($q) use ($department_id) {
                $q->where('categories.department_id', $department_id);
            })
            ->when($payment_mode !== '0', function ($q) use ($payment_mode) {
                $q->whereExists(function ($sub) use ($payment_mode) {
                    $sub->select(DB::raw(1))
                        ->from('payments')
                        ->whereColumn('payments.sale_id', 'sales.id')
                        ->where('payments.paying_method', $payment_mode);
                });
            })
            ->when($filter_by_percentage, function ($q) use ($sale_ids_for_percentage) {
                if (count($sale_ids_for_percentage) > 0) {
                    $q->whereIn('sales.id', $sale_ids_for_percentage);
                } else {
                    $q->whereIn('sales.id', [0]);
                }
            });
        $aggregated = $aggregatedQuery
            ->selectRaw(
                'product_sales.product_id, product_sales.variant_id, ' .
                'SUM(product_sales.total) as sold_amount, ' .
                'SUM(CASE ' .
                'WHEN units.operator = ? THEN product_sales.qty * COALESCE(units.operation_value, 1) ' .
                'WHEN units.operator = ? THEN product_sales.qty / NULLIF(COALESCE(units.operation_value, 1), 0) ' .
                'ELSE product_sales.qty END) as sold_qty',
                ['*', '/']
            )
            ->groupBy('product_sales.product_id', 'product_sales.variant_id')
            ->get();

        $report_rows = [];

        if ($aggregated->isNotEmpty()) {
            $productIds = $aggregated->pluck('product_id')->unique()->values()->all();
            $variantIds = $aggregated->whereNotNull('variant_id')->pluck('variant_id')->unique()->values()->all();

            $products = Product::whereIn('id', $productIds)
                ->with(['category.department'])
                ->get()
                ->keyBy('id');

            $variants = $variantIds
                ? Variant::whereIn('id', $variantIds)->get()->keyBy('id')
                : collect();

            $productVariants = collect();
            if (!empty($variantIds)) {
                $productVariants = ProductVariant::whereIn('product_id', $productIds)
                    ->whereIn('variant_id', $variantIds)
                    ->get()
                    ->keyBy(function ($pv) {
                        return $pv->product_id . '_' . $pv->variant_id;
                    });
            }

            foreach ($aggregated as $row) {
                $product = $products->get($row->product_id);
                $product_name = $product ? $product->name : 'N/A';
                if ($row->variant_id) {
                    $variant = $variants->get($row->variant_id);
                    $product_name .= ' [' . ($variant ? $variant->name : '') . ']';
                    $pv = $productVariants->get($row->product_id . '_' . $row->variant_id);
                    $in_stock = $pv ? (float) $pv->qty : 0;
                } else {
                    $in_stock = $product ? (float) $product->qty : 0;
                }
                $category = $product && $product->relationLoaded('category') ? $product->category : null;
                $department = $category && $category->relationLoaded('department') ? $category->department : null;

                $report_rows[] = [
                    'product_name'   => $product_name,
                    'department_name' => $department ? $department->name : 'N/A',
                    'category_name'  => $category ? $category->name : 'N/A',
                    'sold_amount'    => (float) $row->sold_amount,
                    'sold_qty'       => (float) $row->sold_qty,
                    'in_stock'       => $in_stock,
                ];
            }
        }

        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = User::where('is_active', true)->where('role_id', 5)->get();
        $lims_user_list = User::where('is_active', true)->whereIn('role_id', [2, 4, 6])->get();
        $lims_category_list = Category::where('is_active', true)->get();
        $lims_department_list = CategoryDepartment::where('is_active', true)->get();

        return view('backend.report.sale_report', compact(
            'report_rows',
            'start_date', 'end_date', 'warehouse_id', 'lims_warehouse_list',
            'lims_biller_list', 'lims_user_list', 'lims_category_list', 'lims_department_list',
            'biller_id', 'user_id', 'category_id', 'department_id', 'payment_mode',
            'percentage_filter'
        ));
    }
 
    public function saleReportChart(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = strtotime($request->end_date);
        $warehouse_id = $request->warehouse_id;
        $time_period = $request->time_period;
        if($time_period == 'monthly') {
            for($i = strtotime($start_date); $i <= $end_date; $i = strtotime('+1 month', $i)) {
                $date_points[] = date('Y-m-d', $i);
            }
        }
        else {
            for($i = strtotime('Saturday', strtotime($start_date)); $i <= $end_date; $i = strtotime('+1 week', $i)) {
                $date_points[] = date('Y-m-d', $i);
            }
        }
        $date_points[] = $request->end_date;
        //return $date_points;
        foreach ($date_points as $key => $date_point) {
            $q = DB::table('sales')
                ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                ->whereDate('sales.created_at', '>=', $start_date)
                ->whereDate('sales.created_at', '<', $date_point);
            if($warehouse_id)
                $qty = $q->where('sales.warehouse_id', $warehouse_id);
            if(isset($request->product_list)) {
                $product_ids = Product::whereIn('code', explode(",", trim($request->product_list)))->pluck('id')->toArray();
                $q->whereIn('product_sales.product_id', $product_ids);
            }
            $qty = $q->sum('product_sales.qty');
            $sold_qty[$key] = $qty;
            $start_date = $date_point;
        }
        $lims_warehouse_list = Warehouse::where('is_active', true)->select('id', 'name')->get();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        return view('backend.report.sale_report_chart', compact('start_date', 'end_date', 'warehouse_id', 'time_period', 'sold_qty', 'date_points', 'lims_warehouse_list'));
    }

  
     public function paymentReportByDate(Request $request)
    {
        $data = $request->all();

        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        $method = $request->payment_methods ?? null;
        $supplier_id = $request->supplier_id ?? null;

        // Start the base query
        $query = Payment::query();

        // Filter by payment method
        if (!empty($method)) {
            $query->where('paying_method', $method);
        }

        // Filter by date range
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date);
        }

        // Filter by supplier (via purchase_id relation)
        if (!empty($supplier_id)) {
            $query->whereHas('purchase', function ($q) use ($supplier_id) {
                $q->where('supplier_id', $supplier_id);
            });
        }

        // Execute the query
        $lims_payment_data = $query->get();

        return view('backend.report.payment_report', compact(
            'lims_payment_data',
            'start_date',
            'end_date',
            'method',
            'supplier_id'
        ));
    }

    public function warehouseReport(Request $request)
    {
        $warehouse_id = $request->input('warehouse_id', 0);
        $biller_id = $request->input('biller_id', 0);

        if($request->filled('start_date') && $request->filled('end_date')) {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
        } else {
            $start_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
            $end_date = date("Y-m-d");
        }
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = User::where('is_active', true)
                                    ->where('role_id', 5)
                                    ->get();
        return view('backend.report.warehouse_report',compact('start_date', 'end_date', 'warehouse_id', 'biller_id', 'lims_warehouse_list', 'lims_biller_list'));
    } 

    public function warehouseSaleData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $warehouse_id = $request->input('warehouse_id');
        $biller_id = $request->input('biller_id');
        $q = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.warehouse_id', $warehouse_id)
            ->whereDate('sales.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('sales.created_at', '<=' ,$request->input('end_date'));

        // Apply cashier filter if provided
        if($biller_id && $biller_id != 0) {
            $q = $q->where('sales.biller_id', $biller_id);
        }

        $totalData = $q->count();
        $totalFiltered = $totalData;

        $totalsQ = DB::table('sales')->where('warehouse_id', $warehouse_id)
            ->whereDate('created_at', '>=', $request->input('start_date'))
            ->whereDate('created_at', '<=', $request->input('end_date'));
        if($biller_id && $biller_id != 0) $totalsQ = $totalsQ->where('biller_id', $biller_id);
        $totalsRow = $totalsQ->selectRaw('COALESCE(SUM(grand_total),0) as grand_total, COALESCE(SUM(paid_amount),0) as paid')->first();
        $grand_totals = [
            'grand_total' => (float) $totalsRow->grand_total,
            'paid'        => (float) $totalsRow->paid,
            'due'         => (float) $totalsRow->grand_total - (float) $totalsRow->paid,
        ];

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'sales.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('sales.id', 'sales.reference_no', 'sales.grand_total', 'sales.paid_amount', 'sales.sale_status', 'sales.created_at', 'customers.name as customer')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $sales = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('sales.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $sales =  $q->orwhere([
                                ['sales.reference_no', 'LIKE', "%{$search}%"],
                                ['sales.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['sales.created_at', 'LIKE', "%{$search}%"],
                                ['sales.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['sales.reference_no', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['sales.created_at', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $sales =  $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($sales))
        {
            foreach ($sales as $key => $sale)
            {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($sale->created_at));
                $nestedData['reference_no'] = $sale->reference_no;
                $nestedData['customer'] = $sale->customer;
                $product_sale_data = DB::table('sales')->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                                    ->join('products', 'product_sales.product_id', '=', 'products.id')
                                    ->where('sales.id', $sale->id)
                                    ->select('products.name as product_name', 'product_sales.qty', 'product_sales.sale_unit_id')
                                    ->get();
                foreach ($product_sale_data as $index => $product_sale) {
                    if($product_sale->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_sale->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_sale->product_name.' ('.number_format($product_sale->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_sale->product_name.' ('.number_format($product_sale->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($sale->grand_total, cache()->get('general_setting')->decimal);
                $nestedData['paid'] = number_format($sale->paid_amount, cache()->get('general_setting')->decimal);
                $nestedData['due'] = number_format($sale->grand_total - $sale->paid_amount, cache()->get('general_setting')->decimal);
                if($sale->sale_status == 1){
                    $nestedData['sale_status'] = '<div class="badge badge-success">'.trans('file.Completed').'</div>';
                    $sale_status = trans('file.Completed');
                }
                elseif($sale->sale_status == 2){
                    $nestedData['sale_status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $sale_status = trans('file.Pending');
                }
                else{
                    $nestedData['sale_status'] = '<div class="badge badge-warning">'.trans('file.Draft').'</div>';
                    $sale_status = trans('file.Draft');
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
            "grand_totals"    => $grand_totals
        );
        echo json_encode($json_data);
    }

    public function warehousePurchaseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $warehouse_id = $request->input('warehouse_id');
        $biller_id = $request->input('biller_id');
        $q = DB::table('purchases')
            //->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->where('purchases.warehouse_id', $warehouse_id)
            ->whereDate('purchases.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('purchases.created_at', '<=' ,$request->input('end_date'));

        // Apply cashier filter if provided
        if($biller_id && $biller_id != 0) {
            $q = $q->where('purchases.biller_id', $biller_id);
        }

        $totalData = $q->count();
        $totalFiltered = $totalData;

        $totalsQ = DB::table('purchases')->where('warehouse_id', $warehouse_id)
            ->whereDate('created_at', '>=', $request->input('start_date'))
            ->whereDate('created_at', '<=', $request->input('end_date'));
        if($biller_id && $biller_id != 0) $totalsQ = $totalsQ->where('biller_id', $biller_id);
        $totalsRow = $totalsQ->selectRaw('COALESCE(SUM(grand_total),0) as grand_total, COALESCE(SUM(paid_amount),0) as paid')->first();
        $grand_totals = [
            'grand_total' => (float) $totalsRow->grand_total,
            'paid'        => (float) $totalsRow->paid,
            'due'         => (float) $totalsRow->grand_total - (float) $totalsRow->paid,
        ];

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'purchases.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('purchases.id', 'purchases.reference_no', 'purchases.supplier_id', 'purchases.grand_total', 'purchases.paid_amount', 'purchases.status', 'purchases.created_at')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $purchases = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('purchases.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $purchases =  $q->orwhere([
                                ['purchases.reference_no', 'LIKE', "%{$search}%"],
                                ['purchases.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['purchases.created_at', 'LIKE', "%{$search}%"],
                                ['purchases.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['purchases.reference_no', 'LIKE', "%{$search}%"],
                                    ['purchases.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['purchases.created_at', 'LIKE', "%{$search}%"],
                                    ['purchases.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $purchases =  $q->orwhere('purchases.created_at', 'LIKE', "%{$search}%")->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('purchases.created_at', 'LIKE', "%{$search}%")->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($purchases))
        {
            foreach ($purchases as $key => $purchase)
            {
                $nestedData['id'] = $purchase->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($purchase->created_at));
                $nestedData['reference_no'] = $purchase->reference_no;
                if($purchase->supplier_id) {
                    $supplier = DB::table('suppliers')->select('name')->where('id',$purchase->supplier_id)->first();
                    $nestedData['supplier'] = $supplier->name;
                }
                else
                    $nestedData['supplier'] = 'N/A';
                $product_purchase_data = DB::table('purchases')->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
                                    ->join('products', 'product_purchases.product_id', '=', 'products.id')
                                    ->where('purchases.id', $purchase->id)
                                    ->select('products.name as product_name', 'product_purchases.qty', 'product_purchases.purchase_unit_id')
                                    ->get();
                foreach ($product_purchase_data as $index => $product_purchase) {
                    if($product_purchase->purchase_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_purchase->purchase_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_purchase->product_name.' ('.number_format($product_purchase->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_purchase->product_name.' ('.number_format($product_purchase->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($purchase->grand_total, cache()->get('general_setting')->decimal);
                $nestedData['paid'] = number_format($purchase->paid_amount, cache()->get('general_setting')->decimal);
                $nestedData['balance'] = number_format($purchase->grand_total - $purchase->paid_amount, cache()->get('general_setting')->decimal);
                if($purchase->status == 1){
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Completed').'</div>';
                    $status = trans('file.Completed');
                }
                elseif($purchase->status == 2){
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $status = trans('file.Pending');
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-warning">'.trans('file.Draft').'</div>';
                    $status = trans('file.Draft');
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
            "grand_totals"    => $grand_totals
        );
        echo json_encode($json_data);
    }

    public function warehouseQuotationData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $warehouse_id = $request->input('warehouse_id');
        $biller_id = $request->input('biller_id');
        $q = DB::table('quotations')
            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
            ->join('warehouses', 'quotations.warehouse_id', '=', 'warehouses.id')
            ->where('quotations.warehouse_id', $warehouse_id)
            ->whereDate('quotations.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('quotations.created_at', '<=' ,$request->input('end_date'));

        // Apply cashier filter if provided
        if($biller_id && $biller_id != 0) {
            $q = $q->where('quotations.biller_id', $biller_id);
        }

        $totalData = $q->count();
        $totalFiltered = $totalData;

        $totalsQ = DB::table('quotations')->where('warehouse_id', $warehouse_id)
            ->whereDate('created_at', '>=', $request->input('start_date'))
            ->whereDate('created_at', '<=', $request->input('end_date'));
        if($biller_id && $biller_id != 0) $totalsQ = $totalsQ->where('biller_id', $biller_id);
        $totalsRow = $totalsQ->selectRaw('COALESCE(SUM(grand_total),0) as grand_total')->first();
        $grand_totals = ['grand_total' => (float) $totalsRow->grand_total];

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'quotations.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('quotations.id', 'quotations.reference_no', 'quotations.supplier_id', 'quotations.grand_total', 'quotations.quotation_status', 'quotations.created_at', 'suppliers.name as supplier_name', 'customers.name as customer_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $quotations = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $quotations =  $q->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.created_at', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['quotations.created_at', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $quotations =  $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($quotations))
        {
            foreach ($quotations as $key => $quotation)
            {
                $nestedData['id'] = $quotation->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($quotation->created_at));
                $nestedData['reference_no'] = $quotation->reference_no;
                $nestedData['customer'] = $quotation->customer_name;
                if($quotation->supplier_id) {
                    $nestedData['supplier'] = $quotation->supplier_name;
                }
                else
                    $nestedData['supplier'] = 'N/A';
                $product_quotation_data = DB::table('quotations')->join('product_quotation', 'quotations.id', '=', 'product_quotation.quotation_id')
                                    ->join('products', 'product_quotation.product_id', '=', 'products.id')
                                    ->where('quotations.id', $quotation->id)
                                    ->select('products.name as product_name', 'product_quotation.qty', 'product_quotation.sale_unit_id')
                                    ->get();
                foreach ($product_quotation_data as $index => $product_return) {
                    if($product_return->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($quotation->grand_total, cache()->get('general_setting')->decimal);
                if($quotation->quotation_status == 1){
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Sent').'</div>';
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
            "grand_totals"    => $grand_totals
        );
        echo json_encode($json_data);
    }

    public function warehouseReturnData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $warehouse_id = $request->input('warehouse_id');
        $biller_id = $request->input('biller_id');
        $q = DB::table('returns')
            ->join('customers', 'returns.customer_id', '=', 'customers.id')
            ->leftJoin('billers', 'returns.biller_id', '=', 'billers.id')
            ->where('returns.warehouse_id', $warehouse_id)
            ->whereDate('returns.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('returns.created_at', '<=' ,$request->input('end_date'));

        // Apply cashier filter if provided
        if($biller_id && $biller_id != 0) {
            $q = $q->where('returns.biller_id', $biller_id);
        }

        $totalData = $q->count();
        $totalFiltered = $totalData;

        $totalsQ = DB::table('returns')->where('warehouse_id', $warehouse_id)
            ->whereDate('created_at', '>=', $request->input('start_date'))
            ->whereDate('created_at', '<=', $request->input('end_date'));
        if($biller_id && $biller_id != 0) $totalsQ = $totalsQ->where('biller_id', $biller_id);
        $totalsRow = $totalsQ->selectRaw('COALESCE(SUM(grand_total),0) as grand_total')->first();
        $grand_totals = ['grand_total' => (float) $totalsRow->grand_total];

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'returns.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('returns.id', 'returns.reference_no', 'returns.grand_total', 'returns.created_at', 'customers.name as customer_name', 'billers.name as biller_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $returns = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('returns.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $returns =  $q->orwhere([
                                ['returns.reference_no', 'LIKE', "%{$search}%"],
                                ['returns.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['returns.created_at', 'LIKE', "%{$search}%"],
                                ['returns.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['returns.reference_no', 'LIKE', "%{$search}%"],
                                    ['returns.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['returns.created_at', 'LIKE', "%{$search}%"],
                                    ['returns.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $returns =  $q->orwhere('returns.created_at', 'LIKE', "%{$search}%")->orwhere('returns.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('returns.created_at', 'LIKE', "%{$search}%")->orwhere('returns.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($returns))
        {
            foreach ($returns as $key => $sale)
            {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($sale->created_at));
                $nestedData['reference_no'] = $sale->reference_no;
                $nestedData['customer'] = $sale->customer_name;
                $nestedData['biller'] = $sale->biller_name;
                $product_return_data = DB::table('returns')->join('product_returns', 'returns.id', '=', 'product_returns.return_id')
                                    ->join('products', 'product_returns.product_id', '=', 'products.id')
                                    ->where('returns.id', $sale->id)
                                    ->select('products.name as product_name', 'product_returns.qty', 'product_returns.sale_unit_id')
                                    ->get();
                foreach ($product_return_data as $index => $product_return) {
                    if($product_return->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($sale->grand_total, cache()->get('general_setting')->decimal);
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
            "grand_totals"    => $grand_totals
        );
        echo json_encode($json_data);
    }

    public function warehouseExpenseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $warehouse_id = $request->input('warehouse_id');
        $biller_id = $request->input('biller_id');
        $q = DB::table('expenses')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->where('expenses.warehouse_id', $warehouse_id)
            ->whereDate('expenses.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('expenses.created_at', '<=' ,$request->input('end_date'));

        // Apply cashier filter if provided
        if($biller_id && $biller_id != 0) {
            $q = $q->where('expenses.biller_id', $biller_id);
        }

        $totalData = $q->count();
        $totalFiltered = $totalData;

        $totalsQ = DB::table('expenses')->where('warehouse_id', $warehouse_id)
            ->whereDate('created_at', '>=', $request->input('start_date'))
            ->whereDate('created_at', '<=', $request->input('end_date'));
        if($biller_id && $biller_id != 0) $totalsQ = $totalsQ->where('biller_id', $biller_id);
        $totalsRow = $totalsQ->selectRaw('COALESCE(SUM(amount),0) as amount')->first();
        $grand_totals = ['amount' => (float) $totalsRow->amount];

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'expenses.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('expenses.id', 'expenses.reference_no', 'expenses.amount', 'expenses.created_at', 'expenses.note', 'expense_categories.name as category')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $expenses = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('expenses.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $expenses =  $q->orwhere([
                                ['expenses.reference_no', 'LIKE', "%{$search}%"],
                                ['expenses.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['expenses.created_at', 'LIKE', "%{$search}%"],
                                ['expenses.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['expenses.reference_no', 'LIKE', "%{$search}%"],
                                    ['expenses.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['expenses.created_at', 'LIKE', "%{$search}%"],
                                    ['expenses.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $expenses =  $q->orwhere('expenses.created_at', 'LIKE', "%{$search}%")->orwhere('expenses.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('expenses.created_at', 'LIKE', "%{$search}%")->orwhere('expenses.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($expenses))
        {
            foreach ($expenses as $key => $expense)
            {
                $nestedData['id'] = $expense->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($expense->created_at));
                $nestedData['reference_no'] = $expense->reference_no;
                $nestedData['category'] = $expense->category;
                $nestedData['amount'] = number_format($expense->amount, cache()->get('general_setting')->decimal);
                $nestedData['note'] = $expense->note;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
            "grand_totals"    => $grand_totals
        );
        echo json_encode($json_data);
    }

    public function userReport(Request $request)
    {
        $data = $request->all();

        $user_id = $data['user_id'];
        $start_date = $request->input('start_date') ?? date('Y-m-d');
        $end_date = $request->input('end_date') ?? date('Y-m-d');
        $lims_product_sale_data = [];
        $lims_product_purchase_data = [];
        $lims_product_quotation_data = [];
        $lims_product_transfer_data = [];

        $lims_sale_data = Sale::with('customer', 'warehouse')->where('user_id', $user_id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->orderBy('created_at', 'desc')->get();
        $lims_purchase_data = Purchase::with('warehouse')->where('user_id', $user_id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->orderBy('created_at', 'desc')->get();
        $lims_quotation_data = Quotation::with('customer', 'warehouse')->where('user_id', $user_id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->orderBy('created_at', 'desc')->get();
        $lims_transfer_data = Transfer::with('fromWarehouse', 'toWarehouse')->where('user_id', $user_id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->orderBy('created_at', 'desc')->get();
        $lims_payment_data = DB::table('payments')
                           ->where('user_id', $user_id)
                           ->whereDate('payments.created_at', '>=' , $start_date)
                           ->whereDate('payments.created_at', '<=' , $end_date)
                           ->orderBy('created_at', 'desc')
                           ->get();
        $lims_expense_data = Expense::with('warehouse', 'expenseCategory')->where('user_id', $user_id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->orderBy('created_at', 'desc')->get();
        $lims_payroll_data = Payroll::with('employee')->where('user_id', $user_id)->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->orderBy('created_at', 'desc')->get();

        foreach ($lims_sale_data as $key => $sale) {
            $lims_product_sale_data[$key] = Product_Sale::where('sale_id', $sale->id)->get();
        }
        foreach ($lims_purchase_data as $key => $purchase) {
            $lims_product_purchase_data[$key] = ProductPurchase::where('purchase_id', $purchase->id)->get();
        }
        foreach ($lims_quotation_data as $key => $quotation) {
            $lims_product_quotation_data[$key] = ProductQuotation::where('quotation_id', $quotation->id)->get();
        }
        foreach ($lims_transfer_data as $key => $transfer) {
            $lims_product_transfer_data[$key] = ProductTransfer::where('transfer_id', $transfer->id)->get();
        }

        $lims_user_list = User::where('is_active', true)->where('role_id', '!=', 1)->get();
        return view('backend.report.user_report', compact('lims_sale_data','user_id', 'start_date', 'end_date', 'lims_product_sale_data', 'lims_payment_data', 'lims_user_list', 'lims_purchase_data', 'lims_product_purchase_data', 'lims_quotation_data', 'lims_product_quotation_data', 'lims_transfer_data', 'lims_product_transfer_data', 'lims_expense_data', 'lims_payroll_data') );
    }

    public function userSaleData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $user_id = $request->input('user_id');
        $q = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->where('sales.user_id', $user_id)
            ->whereDate('sales.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('sales.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'sales.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('sales.id', 'sales.reference_no', 'sales.grand_total', 'sales.paid_amount', 'sales.sale_status', 'sales.created_at', 'customers.name as customer', 'warehouses.name as warehouse')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $sales = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('sales.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $sales =  $q->orwhere([
                                ['sales.reference_no', 'LIKE', "%{$search}%"],
                                ['sales.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['sales.created_at', 'LIKE', "%{$search}%"],
                                ['sales.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['sales.reference_no', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['sales.created_at', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $sales =  $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($sales))
        {
            foreach ($sales as $key => $sale)
            {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($sale->created_at));
                $nestedData['reference_no'] = $sale->reference_no;
                $nestedData['customer'] = $sale->customer;
                $nestedData['warehouse'] = $sale->warehouse;
                $product_sale_data = DB::table('sales')->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                                    ->join('products', 'product_sales.product_id', '=', 'products.id')
                                    ->where('sales.id', $sale->id)
                                    ->select('products.name as product_name', 'product_sales.qty', 'product_sales.sale_unit_id')
                                    ->get();
                foreach ($product_sale_data as $index => $product_sale) {
                    if($product_sale->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_sale->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_sale->product_name.' ('.number_format($product_sale->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_sale->product_name.' ('.number_format($product_sale->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($sale->grand_total, cache()->get('general_setting')->decimal);
                $nestedData['paid'] = number_format($sale->paid_amount, cache()->get('general_setting')->decimal);
                $nestedData['due'] = number_format($sale->grand_total - $sale->paid_amount, cache()->get('general_setting')->decimal);
                if($sale->sale_status == 1){
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Completed').'</div>';
                    $sale_status = trans('file.Completed');
                }
                elseif($sale->sale_status == 2){
                    $nestedData['sale_status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $sale_status = trans('file.Pending');
                }
                else{
                    $nestedData['sale_status'] = '<div class="badge badge-warning">'.trans('file.Draft').'</div>';
                    $sale_status = trans('file.Draft');
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function userPurchaseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $user_id = $request->input('user_id');
        $q = DB::table('purchases')
            ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->where('purchases.user_id', $user_id)
            ->whereDate('purchases.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('purchases.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'purchases.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('purchases.id', 'purchases.reference_no', 'purchases.supplier_id', 'purchases.grand_total', 'purchases.paid_amount', 'purchases.status', 'purchases.created_at', 'warehouses.name as warehouse')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $purchases = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('purchases.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $purchases =  $q->orwhere([
                                ['purchases.reference_no', 'LIKE', "%{$search}%"],
                                ['purchases.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['purchases.created_at', 'LIKE', "%{$search}%"],
                                ['purchases.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['purchases.reference_no', 'LIKE', "%{$search}%"],
                                    ['purchases.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['purchases.created_at', 'LIKE', "%{$search}%"],
                                    ['purchases.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $purchases =  $q->orwhere('purchases.created_at', 'LIKE', "%{$search}%")->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('purchases.created_at', 'LIKE', "%{$search}%")->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($purchases))
        {
            foreach ($purchases as $key => $purchase)
            {
                $nestedData['id'] = $purchase->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($purchase->created_at));
                $nestedData['reference_no'] = $purchase->reference_no;
                $nestedData['warehouse'] = $purchase->warehouse;
                if($purchase->supplier_id) {
                    $supplier = DB::table('suppliers')->select('name')->where('id',$purchase->supplier_id)->first();
                    $nestedData['supplier'] = $supplier->name;
                }
                else
                    $nestedData['supplier'] = 'N/A';
                $product_purchase_data = DB::table('purchases')->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
                                    ->join('products', 'product_purchases.product_id', '=', 'products.id')
                                    ->where('purchases.id', $purchase->id)
                                    ->select('products.name as product_name', 'product_purchases.qty', 'product_purchases.purchase_unit_id')
                                    ->get();
                foreach ($product_purchase_data as $index => $product_purchase) {
                    if($product_purchase->purchase_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_purchase->purchase_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_purchase->product_name.' ('.number_format($product_purchase->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_purchase->product_name.' ('.number_format($product_purchase->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($purchase->grand_total, cache()->get('general_setting')->decimal);
                $nestedData['paid'] = number_format($purchase->paid_amount, cache()->get('general_setting')->decimal);
                $nestedData['balance'] = number_format($purchase->grand_total - $purchase->paid_amount, cache()->get('general_setting')->decimal);
                if($purchase->status == 1){
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Completed').'</div>';
                    $status = trans('file.Completed');
                }
                elseif($purchase->status == 2){
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $status = trans('file.Pending');
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-warning">'.trans('file.Draft').'</div>';
                    $status = trans('file.Draft');
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function userQuotationData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $user_id = $request->input('user_id');
        $q = DB::table('quotations')
            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
            ->join('warehouses', 'quotations.warehouse_id', '=', 'warehouses.id')
            ->where('quotations.user_id', $user_id)
            ->whereDate('quotations.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('quotations.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'quotations.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('quotations.id', 'quotations.reference_no', 'quotations.grand_total', 'quotations.quotation_status', 'quotations.created_at', 'warehouses.name as warehouse_name', 'customers.name as customer_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $quotations = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $quotations =  $q->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.created_at', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['quotations.created_at', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $quotations =  $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($quotations))
        {
            foreach ($quotations as $key => $quotation)
            {
                $nestedData['id'] = $quotation->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($quotation->created_at));
                $nestedData['reference_no'] = $quotation->reference_no;
                $nestedData['customer'] = $quotation->customer_name;
                $nestedData['warehouse'] = $quotation->warehouse_name;
                $product_quotation_data = DB::table('quotations')->join('product_quotation', 'quotations.id', '=', 'product_quotation.quotation_id')
                                    ->join('products', 'product_quotation.product_id', '=', 'products.id')
                                    ->where('quotations.id', $quotation->id)
                                    ->select('products.name as product_name', 'product_quotation.qty', 'product_quotation.sale_unit_id')
                                    ->get();
                foreach ($product_quotation_data as $index => $product_return) {
                    if($product_return->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($quotation->grand_total, cache()->get('general_setting')->decimal);
                if($quotation->quotation_status == 1){
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Sent').'</div>';
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function userTransferData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $user_id = $request->input('user_id');
        $q = DB::table('transfers')
           ->join('warehouses as fromWarehouse', 'transfers.from_warehouse_id', '=', 'fromWarehouse.id')
           ->join('warehouses as toWarehouse', 'transfers.to_warehouse_id', '=', 'toWarehouse.id')
           ->where('transfers.user_id', $user_id)
           ->whereDate('transfers.created_at', '>=' , $request->input('start_date'))
           ->whereDate('transfers.created_at', '<=' , $request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'transfers.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('transfers.id', 'transfers.status', 'transfers.created_at', 'transfers.reference_no', 'transfers.grand_total', 'fromWarehouse.name as fromWarehouse', 'toWarehouse.name as toWarehouse')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $transfers = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('transfers.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $transfers =  $q->orwhere([
                                ['transfers.reference_no', 'LIKE', "%{$search}%"],
                                ['transfers.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['transfers.created_at', 'LIKE', "%{$search}%"],
                                ['transfers.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['transfers.reference_no', 'LIKE', "%{$search}%"],
                                    ['transfers.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['transfers.created_at', 'LIKE', "%{$search}%"],
                                    ['transfers.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $transfers =  $q->orwhere('transfers.created_at', 'LIKE', "%{$search}%")->orwhere('transfers.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('transfers.created_at', 'LIKE', "%{$search}%")->orwhere('transfers.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($transfers))
        {
            foreach ($transfers as $key => $transfer)
            {
                $nestedData['id'] = $transfer->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($transfer->created_at));
                $nestedData['reference_no'] = $transfer->reference_no;
                $nestedData['fromWarehouse'] = $transfer->fromWarehouse;
                $nestedData['toWarehouse'] = $transfer->toWarehouse;
                $product_transfer_data = DB::table('product_transfer')
                                    ->where('transfer_id', $transfer->id)
                                    ->get();
                foreach ($product_transfer_data as $index => $product_transfer) {
                    $product = DB::table('products')->find($product_transfer->product_id);
                    if($product_transfer->variant_id) {
                        $variant = DB::table('variants')->find($product_transfer->variant_id);
                        $product->name .= ' ['.$variant->name.']';
                    }
                    $unit = DB::table('units')->find($product_transfer->purchase_unit_id);
                    if($index){
                        if($unit){
                            $nestedData['product'] .= $product->name.' ('.$product_transfer->qty.' '.$unit->unit_code.')';
                        }else{
                            $nestedData['product'] .= $product->name.' ('.$product_transfer->qty.')';
                        }
                    }else{
                        if($unit){
                            $nestedData['product'] = $product->name.' ('.$product_transfer->qty.' '.$unit->unit_code.')';
                        }else{
                            $nestedData['product'] = $product->name.' ('.$product_transfer->qty.')';
                        }
                    }
                }
                $nestedData['grandTotal'] = number_format($transfer->grand_total, cache()->get('general_setting')->decimal);
                if($transfer->status == 1){
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Completed').'</div>';
                }
                elseif($transfer->status == 2) {
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Sent').'</div>';
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function userPaymentData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $user_id = $request->input('user_id');
        $q = DB::table('payments')
           ->where('payments.user_id', $user_id)
           ->whereDate('payments.created_at', '>=' , $request->input('start_date'))
           ->whereDate('payments.created_at', '<=' , $request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'payments.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('payments.*')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $payments = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('payments.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $payments =  $q->orwhere([
                                ['payments.payment_reference', 'LIKE', "%{$search}%"],
                                ['payments.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['payments.created_at', 'LIKE', "%{$search}%"],
                                ['payments.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['payments.payment_reference', 'LIKE', "%{$search}%"],
                                    ['payments.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['payments.created_at', 'LIKE', "%{$search}%"],
                                    ['payments.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $payments =  $q->orwhere('payments.created_at', 'LIKE', "%{$search}%")->orwhere('payments.payment_reference', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('payments.created_at', 'LIKE', "%{$search}%")->orwhere('payments.payment_reference', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($payments))
        {
            foreach ($payments as $key => $payment)
            {
                $nestedData['id'] = $payment->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($payment->created_at));
                $nestedData['reference_no'] = $payment->payment_reference;
                $nestedData['amount'] = number_format($payment->amount, cache()->get('general_setting')->decimal);
                $nestedData['paying_method'] = $payment->paying_method;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function userPayrollData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $user_id = $request->input('user_id');
        $q = DB::table('payrolls')
           ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
           ->where('payrolls.user_id', $user_id)
           ->whereDate('payrolls.created_at', '>=' , $request->input('start_date'))
           ->whereDate('payrolls.created_at', '<=' , $request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'payrolls.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('payrolls.id', 'payrolls.created_at', 'payrolls.reference_no', 'payrolls.amount', 'payrolls.paying_method', 'employees.name as employee')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $payrolls = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('payrolls.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $payrolls =  $q->orwhere([
                                ['payrolls.reference_no', 'LIKE', "%{$search}%"],
                                ['payrolls.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['payrolls.created_at', 'LIKE', "%{$search}%"],
                                ['payrolls.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['payrolls.reference_no', 'LIKE', "%{$search}%"],
                                    ['payrolls.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['payrolls.created_at', 'LIKE', "%{$search}%"],
                                    ['payrolls.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $payrolls =  $q->orwhere('payrolls.created_at', 'LIKE', "%{$search}%")->orwhere('payrolls.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('payrolls.created_at', 'LIKE', "%{$search}%")->orwhere('payrolls.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($payrolls))
        {
            foreach ($payrolls as $key => $payroll)
            {
                $nestedData['id'] = $payroll->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($payroll->created_at));
                $nestedData['reference_no'] = $payroll->reference_no;
                $nestedData['employee'] = $payroll->employee;
                $nestedData['amount'] = number_format($payroll->amount, cache()->get('general_setting')->decimal);
                if($payroll->paying_method == 0)
                    $nestedData['method'] = 'Cash';
                elseif($payroll->paying_method == 1)
                    $nestedData['method'] = 'Cheque';
                else
                    $nestedData['method'] = 'Credit Card';
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function userExpenseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $user_id = $request->input('user_id');
        $q = DB::table('expenses')
            ->join('warehouses', 'expenses.warehouse_id', '=', 'warehouses.id')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->where('expenses.user_id', $user_id)
            ->whereDate('expenses.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('expenses.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'expenses.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('expenses.id', 'expenses.reference_no', 'expenses.amount', 'expenses.created_at', 'expenses.note', 'expense_categories.name as category', 'warehouses.name as warehouse')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $expenses = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('expenses.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $expenses =  $q->orwhere([
                                ['expenses.reference_no', 'LIKE', "%{$search}%"],
                                ['expenses.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['expenses.created_at', 'LIKE', "%{$search}%"],
                                ['expenses.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['expenses.reference_no', 'LIKE', "%{$search}%"],
                                    ['expenses.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['expenses.created_at', 'LIKE', "%{$search}%"],
                                    ['expenses.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $expenses =  $q->orwhere('expenses.created_at', 'LIKE', "%{$search}%")->orwhere('expenses.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('expenses.created_at', 'LIKE', "%{$search}%")->orwhere('expenses.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($expenses))
        {
            foreach ($expenses as $key => $expense)
            {
                $nestedData['id'] = $expense->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($expense->created_at));
                $nestedData['reference_no'] = $expense->reference_no;
                $nestedData['warehouse'] = $expense->warehouse;
                $nestedData['category'] = $expense->category;
                $nestedData['amount'] = number_format($expense->amount, cache()->get('general_setting')->decimal);
                $nestedData['note'] = $expense->note;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerReport(Request $request)
    {
        $customer_id = $request->input('customer_id');
        if($request->input('start_date')) {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
        }
        else {
            $start_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
            $end_date = date("Y-m-d");
        }
        $lims_customer_list = Customer::where('is_active', true)->get();
        return view('backend.report.customer_report',compact('start_date', 'end_date', 'customer_id', 'lims_customer_list'));
    }

    public function customerSaleData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $customer_id = $request->input('customer_id');
        $q = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->where('sales.customer_id', $customer_id)
            ->whereDate('sales.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('sales.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'sales.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('sales.id', 'sales.reference_no', 'sales.total_price', 'sales.grand_total', 'sales.paid_amount', 'sales.sale_status', 'sales.created_at', 'warehouses.name as warehouse_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $sales = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('sales.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $sales =  $q->orwhere([
                                ['sales.reference_no', 'LIKE', "%{$search}%"],
                                ['sales.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['sales.created_at', 'LIKE', "%{$search}%"],
                                ['sales.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['sales.reference_no', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['sales.created_at', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $sales =  $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($sales))
        {
            foreach ($sales as $key => $sale)
            {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($sale->created_at));
                $nestedData['reference_no'] = $sale->reference_no;
                $nestedData['warehouse'] = $sale->warehouse_name;
                $product_sale_data = DB::table('sales')->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                                    ->join('products', 'product_sales.product_id', '=', 'products.id')
                                    ->where('sales.id', $sale->id)
                                    ->select('products.name as product_name', 'product_sales.qty', 'product_sales.sale_unit_id')
                                    ->get();
                foreach ($product_sale_data as $index => $product_sale) {
                    if($product_sale->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_sale->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_sale->product_name.' ('.number_format($product_sale->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_sale->product_name.' ('.number_format($product_sale->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                //calculating product purchase cost
                config()->set('database.connections.mysql.strict', false);
                DB::reconnect();
                $product_sale_data = Sale::join('product_sales', 'sales.id','=', 'product_sales.sale_id')
                    ->select(DB::raw('product_sales.product_id, product_sales.product_batch_id, product_sales.sale_unit_id, sum(product_sales.qty) as sold_qty, sum(product_sales.total) as sold_amount'))
                    ->where('sales.id', $sale->id)
                    ->whereDate('sales.created_at', '>=' , $request->input('start_date'))
                    ->whereDate('sales.created_at', '<=' , $request->input('end_date'))
                    ->groupBy('product_sales.product_id', 'product_sales.product_batch_id')
                    ->get();
                config()->set('database.connections.mysql.strict', true);
                DB::reconnect();
                $product_cost = $this->calculateAverageCOGS($product_sale_data);
                $nestedData['total_cost'] = number_format($product_cost[0], cache()->get('general_setting')->decimal);
                $nestedData['grand_total'] = number_format($sale->grand_total, cache()->get('general_setting')->decimal);
                $nestedData['paid'] = number_format($sale->paid_amount, cache()->get('general_setting')->decimal);
                $nestedData['due'] = number_format($sale->grand_total - $sale->paid_amount, cache()->get('general_setting')->decimal);
                if($sale->sale_status == 1){
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Completed').'</div>';
                    $sale_status = trans('file.Completed');
                }
                elseif($sale->sale_status == 2){
                    $nestedData['sale_status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $sale_status = trans('file.Pending');
                }
                else{
                    $nestedData['sale_status'] = '<div class="badge badge-warning">'.trans('file.Draft').'</div>';
                    $sale_status = trans('file.Draft');
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerPaymentData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $customer_id = $request->input('customer_id');
        $q = DB::table('payments')
           ->join('sales', 'payments.sale_id', '=', 'sales.id')
           ->join('customers', 'customers.id', '=', 'sales.customer_id')
           ->where('sales.customer_id', $customer_id)
           ->whereDate('payments.created_at', '>=' , $request->input('start_date'))
           ->whereDate('payments.created_at', '<=' , $request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'payments.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('payments.*', 'sales.reference_no as sale_reference')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $payments = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('payments.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $payments =  $q->orwhere([
                                ['payments.payment_reference', 'LIKE', "%{$search}%"],
                                ['payments.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['payments.created_at', 'LIKE', "%{$search}%"],
                                ['payments.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['payments.payment_reference', 'LIKE', "%{$search}%"],
                                    ['payments.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['payments.created_at', 'LIKE', "%{$search}%"],
                                    ['payments.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $payments =  $q->orwhere('payments.created_at', 'LIKE', "%{$search}%")->orwhere('payments.payment_reference', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('payments.created_at', 'LIKE', "%{$search}%")->orwhere('payments.payment_reference', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($payments))
        {
            foreach ($payments as $key => $payment)
            {
                $nestedData['id'] = $payment->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($payment->created_at));
                $nestedData['reference_no'] = $payment->payment_reference;
                $nestedData['sale_reference'] = $payment->sale_reference;
                $nestedData['amount'] = number_format($payment->amount, cache()->get('general_setting')->decimal);
                $nestedData['paying_method'] = $payment->paying_method;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerQuotationData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $customer_id = $request->input('customer_id');
        $q = DB::table('quotations')
            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
            ->join('warehouses', 'quotations.warehouse_id', '=', 'warehouses.id')
            ->where('quotations.customer_id', $customer_id)
            ->whereDate('quotations.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('quotations.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'quotations.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('quotations.id', 'quotations.reference_no', 'quotations.supplier_id', 'quotations.grand_total', 'quotations.quotation_status', 'quotations.created_at', 'suppliers.name as supplier_name', 'warehouses.name as warehouse_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $quotations = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $quotations =  $q->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.created_at', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['quotations.created_at', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $quotations =  $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($quotations))
        {
            foreach ($quotations as $key => $quotation)
            {
                $nestedData['id'] = $quotation->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($quotation->created_at));
                $nestedData['reference_no'] = $quotation->reference_no;
                $nestedData['warehouse'] = $quotation->warehouse_name;
                if($quotation->supplier_id) {
                    $nestedData['supplier'] = $quotation->supplier_name;
                }
                else
                    $nestedData['supplier'] = 'N/A';
                $product_quotation_data = DB::table('quotations')->join('product_quotation', 'quotations.id', '=', 'product_quotation.quotation_id')
                                    ->join('products', 'product_quotation.product_id', '=', 'products.id')
                                    ->where('quotations.id', $quotation->id)
                                    ->select('products.name as product_name', 'product_quotation.qty', 'product_quotation.sale_unit_id')
                                    ->get();
                foreach ($product_quotation_data as $index => $product_return) {
                    if($product_return->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($quotation->grand_total, cache()->get('general_setting')->decimal);
                if($quotation->quotation_status == 1){
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Sent').'</div>';
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerReturnData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $customer_id = $request->input('customer_id');
        $q = DB::table('returns')
            ->join('customers', 'returns.customer_id', '=', 'customers.id')
            ->join('warehouses', 'returns.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('billers', 'returns.biller_id', '=', 'billers.id')
            ->where('returns.customer_id', $customer_id)
            ->whereDate('returns.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('returns.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'returns.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('returns.id', 'returns.reference_no', 'returns.grand_total', 'returns.created_at', 'warehouses.name as warehouse_name', 'billers.name as biller_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $returns = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('returns.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $returns =  $q->orwhere([
                                ['returns.reference_no', 'LIKE', "%{$search}%"],
                                ['returns.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['returns.created_at', 'LIKE', "%{$search}%"],
                                ['returns.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['returns.reference_no', 'LIKE', "%{$search}%"],
                                    ['returns.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['returns.created_at', 'LIKE', "%{$search}%"],
                                    ['returns.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $returns =  $q->orwhere('returns.created_at', 'LIKE', "%{$search}%")->orwhere('returns.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('returns.created_at', 'LIKE', "%{$search}%")->orwhere('returns.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($returns))
        {
            foreach ($returns as $key => $sale)
            {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($sale->created_at));
                $nestedData['reference_no'] = $sale->reference_no;
                $nestedData['warehouse'] = $sale->warehouse_name;
                $nestedData['biller'] = $sale->biller_name;
                $product_return_data = DB::table('returns')->join('product_returns', 'returns.id', '=', 'product_returns.return_id')
                                    ->join('products', 'product_returns.product_id', '=', 'products.id')
                                    ->where('returns.id', $sale->id)
                                    ->select('products.name as product_name', 'product_returns.qty', 'product_returns.sale_unit_id')
                                    ->get();
                foreach ($product_return_data as $index => $product_return) {
                    if($product_return->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($sale->grand_total, cache()->get('general_setting')->decimal);
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerGroupReport(Request $request)
    {
        $customer_group_id = $request->input('customer_group_id');
        if($request->input('starting_date')) {
            $starting_date = $request->input('starting_date');
            $ending_date = $request->input('ending_date');
        }
        else {
            $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
            $ending_date = date("Y-m-d");
        }
        $lims_customer_group_list = CustomerGroup::where('is_active', true)->get();
        return view('backend.report.customer_group_report',compact('starting_date', 'ending_date', 'customer_group_id', 'lims_customer_group_list'));
    }

    public function customerGroupSaleData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $customer_group_id = $request->input('customer_group_id');
        $customer_ids = Customer::where('customer_group_id', $customer_group_id)->pluck('id');
        $q = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->whereIn('sales.customer_id', $customer_ids)
            ->whereDate('sales.created_at', '>=' ,$request->input('starting_date'))
            ->whereDate('sales.created_at', '<=' ,$request->input('ending_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'sales.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('sales.id', 'sales.reference_no', 'sales.grand_total', 'sales.paid_amount', 'sales.sale_status', 'sales.created_at', 'customers.name as customer_name', 'customers.phone_number as customer_number', 'warehouses.name as warehouse_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $sales = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('sales.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $sales =  $q->orwhere([
                                ['sales.reference_no', 'LIKE', "%{$search}%"],
                                ['sales.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['sales.created_at', 'LIKE', "%{$search}%"],
                                ['sales.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['sales.reference_no', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['sales.created_at', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $sales =  $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($sales))
        {
            foreach ($sales as $key => $sale)
            {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($sale->created_at));
                $nestedData['reference_no'] = $sale->reference_no;
                $nestedData['warehouse'] = $sale->warehouse_name;
                $nestedData['customer'] = $sale->customer_name.' ['.($sale->customer_number).']';
                $product_sale_data = DB::table('sales')->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                                    ->join('products', 'product_sales.product_id', '=', 'products.id')
                                    ->where('sales.id', $sale->id)
                                    ->select('products.name as product_name', 'product_sales.qty', 'product_sales.sale_unit_id')
                                    ->get();
                foreach ($product_sale_data as $index => $product_sale) {
                    if($product_sale->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_sale->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_sale->product_name.' ('.number_format($product_sale->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_sale->product_name.' ('.number_format($product_sale->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($sale->grand_total, cache()->get('general_setting')->decimal);
                $nestedData['paid'] = number_format($sale->paid_amount, cache()->get('general_setting')->decimal);
                $nestedData['due'] = number_format($sale->grand_total - $sale->paid_amount, cache()->get('general_setting')->decimal);
                if($sale->sale_status == 1){
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Completed').'</div>';
                    $sale_status = trans('file.Completed');
                }
                elseif($sale->sale_status == 2){
                    $nestedData['sale_status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $sale_status = trans('file.Pending');
                }
                else{
                    $nestedData['sale_status'] = '<div class="badge badge-warning">'.trans('file.Draft').'</div>';
                    $sale_status = trans('file.Draft');
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerGroupPaymentData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $customer_group_id = $request->input('customer_group_id');
        $customer_ids = Customer::where('customer_group_id', $customer_group_id)->pluck('id');
        $q = DB::table('payments')
           ->join('sales', 'payments.sale_id', '=', 'sales.id')
           ->join('customers', 'customers.id', '=', 'sales.customer_id')
           ->whereIn('sales.customer_id', $customer_ids)
           ->whereDate('payments.created_at', '>=' , $request->input('starting_date'))
           ->whereDate('payments.created_at', '<=' , $request->input('ending_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'sales.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('payments.*', 'sales.reference_no as sale_reference', 'customers.name as customer_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $payments = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('payments.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $payments =  $q->orwhere([
                                ['payments.payment_reference', 'LIKE', "%{$search}%"],
                                ['payments.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['payments.created_at', 'LIKE', "%{$search}%"],
                                ['payments.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['payments.payment_reference', 'LIKE', "%{$search}%"],
                                    ['payments.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['payments.created_at', 'LIKE', "%{$search}%"],
                                    ['payments.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $payments =  $q->orwhere('payments.created_at', 'LIKE', "%{$search}%")->orwhere('payments.payment_reference', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('payments.created_at', 'LIKE', "%{$search}%")->orwhere('payments.payment_reference', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($payments))
        {
            foreach ($payments as $key => $payment)
            {
                $nestedData['id'] = $payment->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($payment->created_at));
                $nestedData['reference_no'] = $payment->payment_reference;
                $nestedData['sale_reference'] = $payment->sale_reference;
                $nestedData['customer'] = $payment->customer_name;
                $nestedData['amount'] = number_format($payment->amount, cache()->get('general_setting')->decimal);
                $nestedData['paying_method'] = $payment->paying_method;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerGroupQuotationData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $customer_group_id = $request->input('customer_group_id');
        $customer_ids = Customer::where('customer_group_id', $customer_group_id)->pluck('id');
        $q = DB::table('quotations')
            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
            ->join('warehouses', 'quotations.warehouse_id', '=', 'warehouses.id')
            ->whereIn('quotations.customer_id', $customer_ids)
            ->whereDate('quotations.created_at', '>=' ,$request->input('starting_date'))
            ->whereDate('quotations.created_at', '<=' ,$request->input('ending_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'quotations.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('quotations.id', 'quotations.reference_no', 'quotations.supplier_id', 'quotations.grand_total', 'quotations.quotation_status', 'quotations.created_at', 'customers.name as customer_name', 'customers.phone_number as customer_number', 'suppliers.name as supplier_name', 'warehouses.name as warehouse_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $quotations = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $quotations =  $q->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.created_at', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['quotations.created_at', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $quotations =  $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($quotations))
        {
            foreach ($quotations as $key => $quotation)
            {
                $nestedData['id'] = $quotation->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($quotation->created_at));
                $nestedData['reference_no'] = $quotation->reference_no;
                $nestedData['warehouse'] = $quotation->warehouse_name;
                $nestedData['customer'] = $quotation->customer_name.' ['.($quotation->customer_number).']';
                if($quotation->supplier_id) {
                    $nestedData['supplier'] = $quotation->supplier_name;
                }
                else
                    $nestedData['supplier'] = 'N/A';
                $product_quotation_data = DB::table('quotations')->join('product_quotation', 'quotations.id', '=', 'product_quotation.quotation_id')
                                    ->join('products', 'product_quotation.product_id', '=', 'products.id')
                                    ->where('quotations.id', $quotation->id)
                                    ->select('products.name as product_name', 'product_quotation.qty', 'product_quotation.sale_unit_id')
                                    ->get();
                foreach ($product_quotation_data as $index => $product_return) {
                    if($product_return->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($quotation->grand_total, cache()->get('general_setting')->decimal);
                if($quotation->quotation_status == 1){
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Sent').'</div>';
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerGroupReturnData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $customer_group_id = $request->input('customer_group_id');
        $customer_ids = Customer::where('customer_group_id', $customer_group_id)->pluck('id');
        $q = DB::table('returns')
            ->join('customers', 'returns.customer_id', '=', 'customers.id')
            ->join('warehouses', 'returns.warehouse_id', '=', 'warehouses.id')
            ->whereIn('returns.customer_id', $customer_ids)
            ->whereDate('returns.created_at', '>=' ,$request->input('starting_date'))
            ->whereDate('returns.created_at', '<=' ,$request->input('ending_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'returns.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('returns.id', 'returns.reference_no', 'returns.grand_total', 'returns.created_at', 'customers.name as customer_name', 'customers.phone_number as customer_number', 'warehouses.name as warehouse_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $returns = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('returns.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $returns =  $q->orwhere([
                                ['returns.reference_no', 'LIKE', "%{$search}%"],
                                ['returns.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['returns.created_at', 'LIKE', "%{$search}%"],
                                ['returns.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['returns.reference_no', 'LIKE', "%{$search}%"],
                                    ['returns.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['returns.created_at', 'LIKE', "%{$search}%"],
                                    ['returns.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $returns =  $q->orwhere('returns.created_at', 'LIKE', "%{$search}%")->orwhere('returns.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('returns.created_at', 'LIKE', "%{$search}%")->orwhere('returns.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($returns))
        {
            foreach ($returns as $key => $sale)
            {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($sale->created_at));
                $nestedData['reference_no'] = $sale->reference_no;
                $nestedData['warehouse'] = $sale->warehouse_name;
                $nestedData['customer'] = $sale->customer_name.' ['.($sale->customer_number).']';
                $product_return_data = DB::table('returns')->join('product_returns', 'returns.id', '=', 'product_returns.return_id')
                                    ->join('products', 'product_returns.product_id', '=', 'products.id')
                                    ->where('returns.id', $sale->id)
                                    ->select('products.name as product_name', 'product_returns.qty', 'product_returns.sale_unit_id')
                                    ->get();
                foreach ($product_return_data as $index => $product_return) {
                    if($product_return->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($sale->grand_total, cache()->get('general_setting')->decimal);
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function supplierReport(Request $request)
    {
        $company_name = $request->input('company_name');
        if($request->input('start_date')) {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
        }
        else {
            $start_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
            $end_date = date("Y-m-d");
        }
        // Get suppliers filtered by company name
        $lims_supplier_list = Supplier::where('is_active', true)
            ->where('company_name', $company_name)
            ->get();
        
        // If only one supplier with this company, use that supplier_id
        // Otherwise, we'll need to aggregate data from all suppliers with this company name
        $supplier_id = $lims_supplier_list->count() == 1 ? $lims_supplier_list->first()->id : null;
        
        return view('backend.report.supplier_report',compact('start_date', 'end_date', 'supplier_id', 'lims_supplier_list', 'company_name'));
    }

    public function supplierPurchaseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $supplier_id = $request->input('supplier_id');
        $q = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->where('purchases.supplier_id', $supplier_id)
            ->whereDate('purchases.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('purchases.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'purchases.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('purchases.id', 'purchases.reference_no', 'purchases.grand_total', 'purchases.paid_amount', 'purchases.status', 'purchases.created_at', 'warehouses.name as warehouse_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $purchases = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('purchases.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $purchases =  $q->orwhere([
                                ['purchases.reference_no', 'LIKE', "%{$search}%"],
                                ['purchases.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['purchases.created_at', 'LIKE', "%{$search}%"],
                                ['purchases.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['purchases.reference_no', 'LIKE', "%{$search}%"],
                                    ['purchases.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['purchases.created_at', 'LIKE', "%{$search}%"],
                                    ['purchases.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $purchases =  $q->orwhere('purchases.created_at', 'LIKE', "%{$search}%")->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('purchases.created_at', 'LIKE', "%{$search}%")->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($purchases))
        {
            foreach ($purchases as $key => $purchase)
            {
                $nestedData['id'] = $purchase->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($purchase->created_at));
                $nestedData['reference_no'] = $purchase->reference_no;
                $nestedData['warehouse'] = $purchase->warehouse_name;
                $product_purchase_data = DB::table('purchases')->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
                                    ->join('products', 'product_purchases.product_id', '=', 'products.id')
                                    ->where('purchases.id', $purchase->id)
                                    ->select('products.name as product_name', 'product_purchases.qty', 'product_purchases.purchase_unit_id')
                                    ->get();
                foreach ($product_purchase_data as $index => $product_purchase) {
                    if($product_purchase->purchase_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_purchase->purchase_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_purchase->product_name.' ('.number_format($product_purchase->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_purchase->product_name.' ('.number_format($product_purchase->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($purchase->grand_total, cache()->get('general_setting')->decimal);
                $nestedData['paid'] = number_format($purchase->paid_amount, cache()->get('general_setting')->decimal);
                $nestedData['balance'] = number_format($purchase->grand_total - $purchase->paid_amount, cache()->get('general_setting')->decimal);
                if($purchase->status == 1){
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Completed').'</div>';
                    $status = trans('file.Completed');
                }
                elseif($purchase->status == 2){
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $status = trans('file.Pending');
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-warning">'.trans('file.Draft').'</div>';
                    $status = trans('file.Draft');
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function supplierPaymentData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $supplier_id = $request->input('supplier_id');
        $q = DB::table('payments')
           ->join('purchases', 'payments.purchase_id', '=', 'purchases.id')
           ->where('purchases.supplier_id', $supplier_id)
           ->whereDate('payments.created_at', '>=' , $request->input('start_date'))
           ->whereDate('payments.created_at', '<=' , $request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'payments.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('payments.*', 'purchases.reference_no as purchase_reference')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $payments = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('payments.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $payments =  $q->orwhere([
                                ['payments.payment_reference', 'LIKE', "%{$search}%"],
                                ['payments.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['payments.created_at', 'LIKE', "%{$search}%"],
                                ['payments.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['payments.payment_reference', 'LIKE', "%{$search}%"],
                                    ['payments.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['payments.created_at', 'LIKE', "%{$search}%"],
                                    ['payments.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $payments =  $q->orwhere('payments.created_at', 'LIKE', "%{$search}%")->orwhere('payments.payment_reference', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('payments.created_at', 'LIKE', "%{$search}%")->orwhere('payments.payment_reference', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($payments))
        {
            foreach ($payments as $key => $payment)
            {
                $nestedData['id'] = $payment->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($payment->created_at));
                $nestedData['reference_no'] = $payment->payment_reference;
                $nestedData['purchase_reference'] = $payment->purchase_reference;
                $nestedData['amount'] = number_format($payment->amount, cache()->get('general_setting')->decimal);
                $nestedData['paying_method'] = $payment->paying_method;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function supplierReturnData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $supplier_id = $request->input('supplier_id');
        $q = DB::table('return_purchases')
            ->join('suppliers', 'return_purchases.supplier_id', '=', 'suppliers.id')
            ->join('warehouses', 'return_purchases.warehouse_id', '=', 'warehouses.id')
            ->where('return_purchases.supplier_id', $supplier_id)
            ->whereDate('return_purchases.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('return_purchases.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'return_purchases.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('return_purchases.id', 'return_purchases.reference_no', 'return_purchases.grand_total', 'return_purchases.created_at', 'warehouses.name as warehouse_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $return_purchases = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('return_purchases.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $return_purchases =  $q->orwhere([
                                ['return_purchases.reference_no', 'LIKE', "%{$search}%"],
                                ['return_purchases.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['return_purchases.created_at', 'LIKE', "%{$search}%"],
                                ['return_purchases.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['return_purchases.reference_no', 'LIKE', "%{$search}%"],
                                    ['return_purchases.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['return_purchases.created_at', 'LIKE', "%{$search}%"],
                                    ['return_purchases.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $return_purchases =  $q->orwhere('return_purchases.created_at', 'LIKE', "%{$search}%")->orwhere('return_purchases.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('return_purchases.created_at', 'LIKE', "%{$search}%")->orwhere('return_purchases.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($return_purchases))
        {
            foreach ($return_purchases as $key => $return)
            {
                $nestedData['id'] = $return->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($return->created_at));
                $nestedData['reference_no'] = $return->reference_no;
                $nestedData['warehouse'] = $return->warehouse_name;
                $product_return_data = DB::table('return_purchases')->join('purchase_product_return', 'return_purchases.id', '=', 'purchase_product_return.return_id')
                                    ->join('products', 'purchase_product_return.product_id', '=', 'products.id')
                                    ->where('return_purchases.id', $return->id)
                                    ->select('products.name as product_name', 'purchase_product_return.qty', 'purchase_product_return.purchase_unit_id')
                                    ->get();
                foreach ($product_return_data as $index => $product_return) {
                    if($product_return->purchase_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->purchase_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($return->grand_total, cache()->get('general_setting')->decimal);
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function supplierQuotationData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $supplier_id = $request->input('supplier_id');
        $q = DB::table('quotations')
            ->join('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
            ->leftJoin('customers', 'quotations.customer_id', '=', 'customers.id')
            ->join('warehouses', 'quotations.warehouse_id', '=', 'warehouses.id')
            ->where('quotations.supplier_id', $supplier_id)
            ->whereDate('quotations.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('quotations.created_at', '<=' ,$request->input('end_date'));

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start_date');
        $order = 'quotations.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $q = $q->select('quotations.id', 'quotations.reference_no', 'quotations.supplier_id', 'quotations.grand_total', 'quotations.quotation_status', 'quotations.created_at', 'customers.name as customer_name', 'warehouses.name as warehouse_name')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir);
        if(empty($request->input('search.value'))) {
            $quotations = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = $q->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
            if($this->isStaff() && config('staff_access') == 'own') {
                $quotations =  $q->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.created_at', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->get();
                $totalFiltered = $q->orwhere([
                                    ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['quotations.created_at', 'LIKE', "%{$search}%"],
                                    ['quotations.user_id', Auth::id()]
                                ])
                                ->count();
            }
            else {
                $quotations =  $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->get();
                $totalFiltered = $q->orwhere('quotations.created_at', 'LIKE', "%{$search}%")->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($quotations))
        {
            foreach ($quotations as $key => $quotation)
            {
                $nestedData['id'] = $quotation->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($quotation->created_at));
                $nestedData['reference_no'] = $quotation->reference_no;
                $nestedData['warehouse'] = $quotation->warehouse_name;
                $nestedData['customer'] = $quotation->customer_name;
                $product_quotation_data = DB::table('quotations')->join('product_quotation', 'quotations.id', '=', 'product_quotation.quotation_id')
                                    ->join('products', 'product_quotation.product_id', '=', 'products.id')
                                    ->where('quotations.id', $quotation->id)
                                    ->select('products.name as product_name', 'product_quotation.qty', 'product_quotation.sale_unit_id')
                                    ->get();
                foreach ($product_quotation_data as $index => $product_return) {
                    if($product_return->sale_unit_id) {
                        $unit_data = DB::table('units')->select('unit_code')->find($product_return->sale_unit_id);
                        $unitCode = $unit_data->unit_code;
                    }
                    else
                        $unitCode = '';
                    if($index)
                        $nestedData['product'] .= '<br>'.$product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                    else
                        $nestedData['product'] = $product_return->product_name.' ('.number_format($product_return->qty, cache()->get('general_setting')->decimal).' '.$unitCode.')';
                }
                $nestedData['grand_total'] = number_format($quotation->grand_total, cache()->get('general_setting')->decimal);
                if($quotation->quotation_status == 1){
                    $nestedData['status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-success">'.trans('file.Sent').'</div>';
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function customerDueReportByDate(Request $request)
    {
        $data = $request->all();
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $q = Sale::where('payment_status', '!=', 4)
            ->whereDate('created_at', '>=' , $start_date)
            ->whereDate('created_at', '<=' , $end_date);
        if($request->customer_id)
            $q = $q->where('customer_id', $request->customer_id);
        $lims_sale_data = $q->get();
        return view('backend.report.due_report', compact('lims_sale_data', 'start_date', 'end_date'));
    }

    public function customerDueReportData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $q = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('payment_status', '!=', 4)
            ->whereDate('sales.created_at', '>=' ,$request->input('start_date'))
            ->whereDate('sales.created_at', '<=' ,$request->input('end_date'));

            $totalData = $q->count();
            $totalFiltered = $totalData;

            if($request->input('length') != -1)
                $limit = $request->input('length');
            else
                $limit = $totalData;
            $start = $request->input('start');
            $order = 'sales.'.$columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $q = $q->select('sales.id', 'sales.reference_no', 'sales.grand_total', 'sales.created_at', 'sales.paid_amount', 'customers.name as customer_name', 'customers.phone_number as customer_phone_number')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);

            if(empty($request->input('search.value'))) {
                $sales = $q->get();
            }
            else
            {
                $search = $request->input('search.value');
                $q = $q->whereDate('sales.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))));
                if($this->isStaff() && config('staff_access') == 'own') {
                    $sales =  $q->orwhere([
                                    ['sales.reference_no', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['sales.created_at', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['customers.name', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->orwhere([
                                    ['customers.phone_number', 'LIKE', "%{$search}%"],
                                    ['sales.user_id', Auth::id()]
                                ])
                                ->get();
                    $totalFiltered = $q->orwhere([
                                        ['sales.reference_no', 'LIKE', "%{$search}%"],
                                        ['sales.user_id', Auth::id()]
                                    ])
                                    ->orwhere([
                                        ['sales.created_at', 'LIKE', "%{$search}%"],
                                        ['sales.user_id', Auth::id()]
                                    ])
                                    ->orwhere([
                                        ['customers.name', 'LIKE', "%{$search}%"],
                                        ['sales.user_id', Auth::id()]
                                    ])
                                    ->orwhere([
                                        ['customers.phone_number', 'LIKE', "%{$search}%"],
                                        ['sales.user_id', Auth::id()]
                                    ])
                                    ->count();
                }
                else {
                    $sales =  $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->orwhere('customers.name', 'LIKE', "%{$search}%")->orwhere('customers.phone_number', 'LIKE', "%{$search}%")->get();

                    $totalFiltered = $q->orwhere('sales.created_at', 'LIKE', "%{$search}%")->orwhere('sales.reference_no', 'LIKE', "%{$search}%")->orwhere('customers.name', 'LIKE', "%{$search}%")->orwhere('customers.phone_number', 'LIKE', "%{$search}%")->count();
                }
            }
            $data = array();
            if(!empty($sales))
            {
                foreach ($sales as $key => $sale)
                {
                    $nestedData['id'] = $sale->id;
                    $nestedData['key'] = $key;
                    $nestedData['date'] = date(config('date_format'), strtotime($sale->created_at));
                    $nestedData['reference_no'] = $sale->reference_no;
                    $nestedData['customer'] = $sale->customer_name.' ('.$sale->customer_phone_number.')';
                    $nestedData['grand_total'] = number_format($sale->grand_total, cache()->get('general_setting')->decimal);

                    $returned_amount = DB::table('returns')->where('sale_id', $sale->id)->sum('grand_total');

                    $nestedData['returned_amount'] = number_format($returned_amount, cache()->get('general_setting')->decimal);
                    if($sale->paid_amount)
                        $nestedData['paid'] = number_format($sale->paid_amount, cache()->get('general_setting')->decimal);
                    else
                        $nestedData['paid'] = number_format(0, cache()->get('general_setting')->decimal);
                    $nestedData['due'] = number_format(($sale->grand_total - $returned_amount - $sale->paid_amount), cache()->get('general_setting')->decimal);

                    $data[] = $nestedData;
                }
            }
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );

        echo json_encode($json_data);
    }

    public function supplierDueReportByDate(Request $request)
    {
        $data = $request->all();
        $start_date = $data['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $end_date = $data['end_date'] ?? now()->format('Y-m-d');
        $q = Purchase::where('payment_status', 1)
            ->whereDate('created_at', '>=' , $start_date)
            ->whereDate('created_at', '<=' , $end_date);
        if($request->supplier_id)
            $q = $q->where('supplier_id', $request->supplier_id);
        $lims_purchase_data = $q->orderBy('created_at', 'desc')->get();

        $general_setting = cache()->get('general_setting') ?? \App\Models\GeneralSetting::first();

        $supplier_summaries = [];
        foreach ($lims_purchase_data->groupBy('supplier_id') as $supplier_id => $purchases) {
            if (!$supplier_id) continue;
            $supplier = Supplier::find($supplier_id);
            if (!$supplier) continue;
            $total_grand = 0;
            $total_returned = 0;
            $total_paid = 0;
            $purchase_list = [];
            foreach ($purchases as $p) {
                $returned = DB::table('return_purchases')->where('purchase_id', $p->id)->sum('grand_total');
                $due = $p->grand_total - $returned - ($p->paid_amount ?? 0);
                $total_grand += $p->grand_total;
                $total_returned += $returned;
                $total_paid += ($p->paid_amount ?? 0);
                $purchase_list[] = (object)[
                    'id' => $p->id,
                    'reference_no' => $p->reference_no,
                    'created_at' => $p->created_at,
                    'grand_total' => $p->grand_total,
                    'returned_amount' => $returned,
                    'paid_amount' => $p->paid_amount ?? 0,
                    'due' => $due,
                ];
            }
            $supplier_summaries[] = [
                'supplier_id' => $supplier_id,
                'supplier_name' => $supplier->name,
                'supplier_phone' => $supplier->phone_number ?? '',
                'purchases_count' => count($purchase_list),
                'total_grand_total' => $total_grand,
                'total_returned' => $total_returned,
                'total_paid' => $total_paid,
                'total_due' => $total_grand - $total_returned - $total_paid,
                'purchases' => $purchase_list,
            ];
        }
        usort($supplier_summaries, function ($a, $b) { return $b['total_due'] <=> $a['total_due']; });

        return view('backend.report.supplier_due_report', compact('lims_purchase_data', 'supplier_summaries', 'start_date', 'end_date', 'general_setting'));
    }

  
    public function departmentReport()
    {
        $dateFrom = request('date_from') ?? date('Y-m-d');
        $dateTo = request('date_to') ?? date('Y-m-d');
        $departments = CategoryDepartment::where('is_active', true)->get();
        $department_data = [];

        foreach ($departments as $department) {
            $categories = Category::where('department_id', $department->id)->pluck('id');
            $total_products = Product::whereIn('category_id', $categories)->count();

            // Base query for sales in the period
            $salesQuery = DB::table('product_sales')
                ->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereIn('products.category_id', $categories)
                ->whereBetween('product_sales.created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59'
                ]);

            // Total revenue (kept internal for profit margin)
            $total_revenue = $salesQuery->sum('product_sales.total');

            // Total purchases (cost of goods sold) – from product_purchases table
            // Unit cost = product_purchases.total_cost / product_purchases.qty
            $total_purchases = DB::table('product_sales')
                ->join('products', 'product_sales.product_id', '=', 'products.id')
                ->join('product_purchases', 'product_sales.product_id', '=', 'product_purchases.product_id')
                ->whereIn('products.category_id', $categories)
                ->whereBetween('product_sales.created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59'
                ])
                ->sum(DB::raw('product_sales.qty * (product_purchases.net_unit_cost / product_purchases.qty)'));

            $categories_count = Category::where('department_id', $department->id)->count();

            $department_data[] = [
                'id'               => $department->id,
                'name'             => $department->name,
                'image'            => $department->image,
                'categories_count' => $categories_count,
                'products_count'   => $total_products,
                'total_revenue'     => $total_revenue ?? 0,
                'total_purchases'  => $total_purchases ?? 0,
                'profit_margin'    => ($total_revenue ?? 0) - ($total_purchases ?? 0),
            ];
        }

        return view('backend.report.department_report', compact('department_data'));
    }

    /**
     * Sales Person Report – show form and aggregated data by sales person.
     */ 
    public function salesPersonReport(Request $request)
    {
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_user_list = User::where('is_active', true)->whereIn('role_id', [2, 4, 6])->get();
            $start_date = $request->input('start_date', date('Y-m-d')); // was: date('Y-m').'-01'
            $end_date   = $request->input('end_date',   date('Y-m-d')); //
        $warehouse_id = (int) $request->input('warehouse_id', 0);
        $user_id = (int) $request->input('user_id', 0);
       

        $query = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->whereDate('sales.created_at', '>=', $start_date)
            ->whereDate('sales.created_at', '<=', $end_date)
            ->select(
                'sales.user_id',
                'users.name as sales_person_name',
                DB::raw('COUNT(sales.id) as sale_count'),
                DB::raw('COALESCE(SUM(sales.grand_total), 0) as total_sales'),
                DB::raw('COALESCE(SUM(sales.paid_amount), 0) as total_paid')
            )
            ->groupBy('sales.user_id', 'users.name');

        if ($warehouse_id > 0) {
            $query->where('sales.warehouse_id', $warehouse_id);
        }
        if ($user_id > 0) {
            $query->where('sales.user_id', $user_id);
        }

        $report_rows = $query->orderByDesc('total_sales')->get();

        return view('backend.report.sales_person_report', compact(
            'report_rows',
            'start_date',
            'end_date',
            'warehouse_id',
            'user_id',
            'lims_warehouse_list',
            'lims_user_list'
        ));
    }

    /**
     * AJAX: Return sales details for Sales Person Report modal (date, products, amount).
     */
    public function salesPersonReportDetails(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $user_id = (int) $request->input('user_id', 0);
        $warehouse_id = (int) $request->input('warehouse_id', 0);
        if (empty($start_date) || empty($end_date) || $user_id <= 0) {
            return response()->json(['rows' => [], 'message' => 'Missing parameters.']);
        }

        $sales = Sale::with(['product_sales.product', 'product_sales.variant'])
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date);
        if ($warehouse_id > 0) {
            $sales->where('warehouse_id', $warehouse_id);
        }
        $sales = $sales->orderBy('created_at', 'desc')->get();

        $rows = [];
        foreach ($sales as $sale) {
            $date = $sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '';
            $amount = (float) $sale->grand_total;
            $lines = [];
            foreach ($sale->product_sales ?? [] as $ps) {
                $name = $ps->product ? $ps->product->name : 'Product #' . $ps->product_id;
                if ($ps->variant_id && $ps->relationLoaded('variant') && $ps->variant) {
                    $name .= ' (' . $ps->variant->name . ')';
                }
                $lines[] = $name . ' × ' . (int) $ps->qty;
            }
            $products = $lines ? implode(', ', $lines) : '—';
            $rows[] = [
                'date' => $date,
                'products' => $products,
                'amount' => $amount,
            ];
        }

        return response()->json(['rows' => $rows]);
    }

 

      /**
     * Main payment method report.
     * Filters by date range, payment method, and sales officer.
     */
    public function paymentMethodReport(Request $request)
    {
        $start_date = $request->input('start_date', date('Y-m-d'));
        $end_date   = $request->input('end_date', date('Y-m-d'));
        $payment_method = $request->input('payment_method', '');
        $user_id = (int) $request->input('user_id', 0);

        // Join with sales to get paid_amount from the sale record
        $query = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date);

        // Filter by payment method
        if (!empty($payment_method)) {
            $query->where('payments.paying_method', $payment_method);
        }

        // Filter by sales officer (payments.user_id)
        if ($user_id > 0) {
            $query->where('payments.user_id', $user_id);
        }

        $report_rows = $query
            ->select(
                'payments.paying_method',
                DB::raw('COUNT(payments.id) as transaction_count'),
                DB::raw('COALESCE(SUM(sales.paid_amount), 0) as total_amount')   // <-- changed
            )
            ->groupBy('payments.paying_method')
            ->orderByDesc('total_amount')
            ->get();

        return view('backend.report.payment_method_report', compact(
            'report_rows',
            'start_date',
            'end_date',
            'payment_method',
            'user_id'
        ));
    }

    /**
     * AJAX endpoint for the modal: shows individual sales for a selected payment method
     * and date range (filters are respected, including the sales officer if supplied).
     */
    public function paymentMethodDetails(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $payment_method = $request->input('payment_method');
        $user_id = (int) $request->input('user_id', 0);   // optional sales officer filter

        $query = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date)
            ->where('payments.paying_method', $payment_method);

        if ($user_id > 0) {
            $query->where('payments.user_id', $user_id);
        }

        $details = $query
            ->select(
                DB::raw('DATE(payments.created_at) as date'),
                'sales.reference_no as products',      // you can later join product details if needed
                'sales.paid_amount as amount'           // <-- changed to sales.paid_amount
            )
            ->orderBy('payments.created_at')
            ->get();

        return response()->json(['rows' => $details]);
    }

        /**
         * AJAX: Return payment/sale details for Payment Method Report modal (date, products, amount).
         */
    public function paymentMethodReportDetails(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $paying_method = $request->input('payment_method');
        if (empty($start_date) || empty($end_date) || $paying_method === null || $paying_method === '') {
            return response()->json(['rows' => [], 'message' => 'Missing parameters.']);
        }

        $query = Payment::with(['sale.product_sales.product', 'sale.product_sales.variant', 'purchase'])
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date);
        if ($paying_method === 'N/A' || $paying_method === '') {
            $query->where(function ($q) {
                $q->whereNull('paying_method')->orWhere('paying_method', '');
            });
        } else {
            $query->where('paying_method', $paying_method);
        }
        $payments = $query->orderBy('created_at', 'desc')->get();

        $rows = [];
        foreach ($payments as $payment) {
            $date = $payment->created_at ? $payment->created_at->format('Y-m-d H:i') : '';
            $products = '—';
            $amount = 0;

            if ($payment->sale_id && $payment->sale) {
                $date = $payment->sale->created_at ? $payment->sale->created_at->format('Y-m-d H:i') : $date;
                // Amount from the sale’s paid_amount, not the payment record
                $amount = (float) $payment->sale->paid_amount;

                $lines = [];
                foreach ($payment->sale->product_sales ?? [] as $ps) {
                    $name = $ps->product ? $ps->product->name : 'Product #' . $ps->product_id;
                    if ($ps->variant_id && $ps->relationLoaded('variant') && $ps->variant) {
                        $name .= ' (' . $ps->variant->name . ')';
                    }
                    $lines[] = $name . ' × ' . (int) $ps->qty;
                }
                $products = $lines ? implode(', ', $lines) : '—';
            } elseif ($payment->purchase_id && $payment->purchase) {
                $products = 'Purchase #' . ($payment->purchase->reference_no ?? $payment->purchase_id);
                // For purchases, keep the payment amount (no sale to link to)
                $amount = (float) $payment->amount;
            } else {
                // Fallback: use the payment amount directly
                $amount = (float) $payment->amount;
            }

            $rows[] = [
                'date'     => $date,
                'products' => $products,
                'amount'   => $amount,
            ];
        }

        return response()->json(['rows' => $rows]);
    }




    public function stockTaking()
    {
        $categories = Category::where(
            'is_active',
            true
        )->get();

        return view(
            'backend.report.stock_taking',
            compact('categories')
        );
    }


    public function stockTakingData(Request $request)
    {
        $q = DB::table('stock_count_items')
            ->join('stock_counts', 'stock_count_items.stock_count_id', '=', 'stock_counts.id')
            ->join('products', 'stock_count_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('warehouses', 'stock_counts.warehouse_id', '=', 'warehouses.id')
            ->join('users', 'stock_counts.user_id', '=', 'users.id')
            ->whereDate('stock_counts.created_at', '>=', $request->from_date)
            ->whereDate('stock_counts.created_at', '<=', $request->to_date);

        if ($request->status) {
            $q->where('stock_counts.status', $request->status);
        }

        if ($request->category_id) {
            $q->where('products.category_id', $request->category_id);
        }

        /* ── Totals for the footer (all filtered rows) ── */
        $totals = (clone $q)->select(
            DB::raw('SUM(stock_count_items.system_qty * products.cost) as sys_cost_total'),
            DB::raw('SUM(stock_count_items.system_qty * products.price) as sys_selling_total'),
            DB::raw('SUM(stock_count_items.physical_qty * products.cost) as phy_cost_total'),
            DB::raw('SUM(stock_count_items.physical_qty * products.price) as phy_selling_total'),
            DB::raw('SUM(stock_count_items.variance * products.cost) as variance_cost_total'),
            DB::raw('SUM(stock_count_items.variance * products.price) as variance_selling_total')
        )->first();

        $totalData     = $q->count();
        $totalFiltered = $totalData;

        $limit = $request->length;
        $start = $request->start;

        $items = $q->select(
                'stock_count_items.id',
                'stock_count_items.system_qty',
                'stock_count_items.physical_qty',
                'stock_count_items.variance',
                'products.name as product_name',
                'products.cost as product_cost',
                'products.price as product_price',
                DB::raw('(SELECT expired_date FROM product_batches WHERE product_batches.product_id = products.id ORDER BY expired_date DESC LIMIT 1) as expired_date')
            )
            ->offset($start)
            ->limit($limit)
            ->orderBy('stock_counts.created_at', 'desc')
            ->get();

        $data = [];

        foreach ($items as $item) {
            $cost  = $item->product_cost  ?? 0;
            $price = $item->product_price ?? 0;

            $sysCostTotal    = $item->system_qty   * $cost;
            $sysSellingTotal = $item->system_qty   * $price;
            $phyCostTotal    = $item->physical_qty * $cost;
            $phySellingTotal = $item->physical_qty * $price;
            $varianceCost    = $item->variance     * $cost;
            $varianceSelling = $item->variance     * $price;

            $nestedData['product_name']      = $item->product_name;
            $nestedData['expired_date']       = $item->expired_date
                ? date(config('date_format'), strtotime($item->expired_date))
                : '-';
            $nestedData['system_qty']        = number_format($item->system_qty, 2);
            $nestedData['sys_cost_total']    = number_format($sysCostTotal, 2);
            $nestedData['sys_selling_total'] = number_format($sysSellingTotal, 2);
            $nestedData['physical_qty']      = number_format($item->physical_qty, 2);
            $nestedData['phy_cost_total']    = 'GH₵ ' . number_format($phyCostTotal, 2);
            $nestedData['phy_selling_total'] = 'GH₵ ' . number_format($phySellingTotal, 2);
            $nestedData['variance_qty']      = number_format($item->variance, 2);
            $nestedData['variance_cost']     = number_format($varianceCost, 2);
            $nestedData['variance_selling']  = number_format($varianceSelling, 2);

            $data[] = $nestedData;
        }

        return response()->json([
            "draw"            => intval($request->draw),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
            "totals"          => [
                "sys_cost_total"       => 'GH₵ ' . number_format($totals->sys_cost_total ?? 0, 2),
                "sys_selling_total"    => 'GH₵ ' . number_format($totals->sys_selling_total ?? 0, 2),
                "phy_cost_total"       => 'GH₵ ' . number_format($totals->phy_cost_total ?? 0, 2),
                "phy_selling_total"    => 'GH₵ ' . number_format($totals->phy_selling_total ?? 0, 2),
                "variance_cost_total"  => number_format($totals->variance_cost_total ?? 0, 2),
                "variance_selling_total" => number_format($totals->variance_selling_total ?? 0, 2),
            ]
        ]);
    }
}
