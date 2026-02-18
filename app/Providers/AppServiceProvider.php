<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use DB;
use Illuminate\Support\Facades\URL;

// Import all models
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Warehouse;
use App\Models\Biller;
use App\Models\Tax;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Table;
use App\Models\Coupon;
use App\Models\PosSetting;
use App\Models\Roles;
use App\Models\HirePurchaseInstallment;

// Import all observers
use App\Observers\ProductObserver;
use App\Observers\CustomerObserver;
use App\Observers\CustomerGroupObserver;
use App\Observers\WarehouseObserver;
use App\Observers\BillerObserver;
use App\Observers\TaxObserver;
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\TableObserver;
use App\Observers\CouponObserver;
use App\Observers\PosSettingObserver;
use App\Observers\RoleObserver;
use App\Observers\HirePurchaseInstallmentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        // Schema::defaultStringLength(191);
        
        // Register model observers for cache invalidation
        Product::observe(ProductObserver::class);
        Customer::observe(CustomerObserver::class);
        CustomerGroup::observe(CustomerGroupObserver::class);
        Warehouse::observe(WarehouseObserver::class);
        Biller::observe(BillerObserver::class);
        Tax::observe(TaxObserver::class);
        Brand::observe(BrandObserver::class);
        Category::observe(CategoryObserver::class);
        Table::observe(TableObserver::class);
        Coupon::observe(CouponObserver::class);
        PosSetting::observe(PosSettingObserver::class);
        Roles::observe(RoleObserver::class);
        HirePurchaseInstallment::observe(HirePurchaseInstallmentObserver::class);
    }
}

