<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use DB;
use Auth;
use Cache;
use Illuminate\Support\Facades\URL;

class Common
{
    public function handle(Request $request, Closure $next)
    {
        /*if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            URL::forceScheme('https');
        }*/
        //get general setting value
        $general_setting =  Cache::remember('general_setting', 60*60*24*365, function () {
            return DB::table('general_settings')->latest()->first();
        });
        // dd(
        //     $general_setting
        // );

        // $todayDate = date("Y-m-d");
        // if($general_setting->expiry_date) {
        //     $expiry_date = date("Y-m-d", strtotime($general_setting->expiry_date));
        //     if($todayDate > $expiry_date) {
        //         auth()->logout();
        //         return redirect()->route('contactForRenewal');
        //     }
        // }
        //setting language
        if(isset($_COOKIE['language'])) {
            \App::setLocale($_COOKIE['language']);
        }
        else {
            \App::setLocale('en');
        }
        //setting theme
        if(isset($_COOKIE['theme'])) {
            View::share('theme', $_COOKIE['theme']);
        }
        else {
            View::share('theme', 'light');
        }
        $currency = Cache::remember('currency', 60*60*24*365, function () {
            $settingData = DB::table('general_settings')->select('currency')->latest()->first();
            return \App\Models\Currency::find($settingData->currency);
        });

        View::share('general_setting', $general_setting);
        View::share('currency', $currency);
        // config(['staff_access' => $general_setting->staff_access, 'date_format' => $general_setting->date_format, 'currency' => $currency->code, 'currency_position' => $general_setting->currency_position, 'decimal' => $general_setting->decimal, 'is_zatca' => $general_setting->is_zatca, 'company_name' => $general_setting->company_name, 'vat_registration_number' => $general_setting->vat_registration_number, 'without_stock' => $general_setting->without_stock]);
        config([
            'staff_access' => $general_setting->staff_access,
            'date_format' => $general_setting->date_format,
            'currency' => $currency->code,
            'currency_position' => $general_setting->currency_position,
            'decimal' => $general_setting->decimal,
            'is_zatca' => $general_setting->is_zatca,
            'company_name' => $general_setting->company_name,
            'vat_registration_number' => $general_setting->vat_registration_number,
            'without_stock' => $general_setting->without_stock
        ]);

        $alert_product = DB::table('products')->where('is_active', true)->whereColumn('alert_quantity', '>', 'qty')->count();
        $dso_alert_product = DB::table('dso_alerts')->select('number_of_products')->whereDate('created_at', date("Y-m-d"))->first();
        if($dso_alert_product)
            $dso_alert_product_no = $dso_alert_product->number_of_products;
        else
            $dso_alert_product_no = 0;
        View::share(['alert_product' => $alert_product, 'dso_alert_product_no' => $dso_alert_product_no]);
        $role = Cache::remember('user_role', 60*60*24*365, function () {
            return DB::table('roles')->find(Auth::user()->role_id);
        });
        View::share('role', $role);

        try {
            $permission_list = Cache::remember('permissions', 60*60*24*365, function () {
                return DB::table('permissions')->get();
            });
            $role_has_permissions = Cache::remember('role_has_permissions', 60*60*24*365, function () {
                return DB::table('role_has_permissions')->where('role_id', Auth::user()->role_id)->get();
            });
            $role_has_permissions_list = Cache::remember('role_has_permissions_list'.Auth::user()->role_id, 60*60*24*365, function () {
                return DB::table('permissions')->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')->where('role_id', Auth::user()->role_id)->select('permissions.name')->get();
            });
        } catch (\Throwable $e) {
            // Permission tables not used or missing (e.g. model_has_permissions): grant all so sidebar/views render
            $permission_list = collect();
            $role_has_permissions = collect();
            $role_has_permissions_list = collect($this->allPermissionNamesForFallback())->map(function ($name) {
                return (object) ['name' => $name];
            });
        }
        View::share('permission_list', $permission_list);
        View::share('role_has_permissions', $role_has_permissions);
        View::share('role_has_permissions_list', $role_has_permissions_list);

        $categories_list = Cache::remember('category_list', 60*60*24*365, function () {
            return DB::table('categories')->where('is_active', true)->get();
        });
       $departments_list = Cache::remember('category_list', 60*60*24*365, function () {
            return DB::table('category_departments')->where('is_active', true)->get();
        });
        View::share('categories_list', $categories_list, $departments_list);
        return $next($request);
    }

    /**
     * Permission names used in sidebar/views when permission tables are not used.
     */
    private function allPermissionNamesForFallback(): array
    {
        return [
            'revenue_profit_summary', 'cash_flow', 'monthly_summary', 'yearly_report', 'category',
            'products-index', 'products-add', 'products-edit', 'products-delete', 'purchases-index', 'purchases-add',
            'purchases-edit', 'purchases-delete', 'purchase-payment-index', 'purchase-payment-add', 'purchase-payment-edit',
            'purchase-payment-delete', 'sales-index', 'sales-add', 'sales-edit', 'sales-delete', 'sale-payment-index',
            'sale-payment-add', 'sale-payment-edit', 'sale-payment-delete', 'sale-percentage-filter', 'expenses-index',
            'expenses-add', 'expenses-edit', 'expenses-delete', 'approvals-index', 'approve-payments', 'quotes-index',
            'quotes-add', 'quotes-edit', 'quotes-delete', 'transfers-index', 'transfers-add', 'transfers-edit',
            'transfers-delete', 'returns-index', 'returns-add', 'returns-edit', 'returns-delete', 'purchase-return-index',
            'purchase-return-add', 'purchase-return-edit', 'purchase-return-delete', 'account-index', 'money-transfer',
            'balance-sheet', 'account-statement', 'department', 'attendance', 'payroll', 'employees-index', 'employees-add',
            'employees-edit', 'employees-delete', 'users-index', 'users-add', 'users-edit', 'users-delete', 'customers-index',
            'customers-add', 'customers-edit', 'customers-delete', 'billers-index', 'billers-add', 'billers-edit',
            'billers-delete', 'suppliers-index', 'suppliers-add', 'suppliers-edit', 'suppliers-delete', 'profit-loss',
            'best-seller', 'product-report', 'daily-sale', 'monthly-sale', 'daily-purchase', 'monthly-purchase',
            'sale-report', 'payment-report', 'purchase-report', 'warehouse-report', 'warehouse-stock-report',
            'product-expiry-report', 'product-qty-alert', 'dso-report', 'user-report', 'customer-report', 'supplier-report',
            'due-report', 'supplier-due-report', 'backup_database', 'general_setting', 'mail_setting', 'sms_setting',
            'create_sms', 'pos_setting', 'hrm_setting', 'reward_point_setting', 'stock_count', 'adjustment',
            'product_history', 'print_barcode', 'empty_database', 'send_notification', 'discount_plan', 'discount',
            'warehouse', 'customer_group', 'brand', 'unit', 'currency', 'tax', 'gift_card', 'coupon', 'holiday',
            'delivery', 'today_sale', 'today_profit', 'all_notification', 'sale-report-chart', 'custom_field',
        ];
    }
}
