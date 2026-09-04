<?php

namespace App\Providers;

use App\Models\Adjustment;
use App\Models\BlackListCustomer;
use App\Models\CashRegister;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Discount;
use App\Models\DiscountPlanCustomer;
use App\Models\DiscountPlanDiscount;
use App\Models\Expense;
use App\Models\HirePurchaseInstallment;
use App\Models\HrmSetting;
use App\Models\MoneyTransfer;
use App\Models\Payment;
use App\Models\PosSetting;
use App\Models\Product;
use App\Models\Product_Sale;
use App\Models\Product_Supplier;
use App\Models\Product_Warehouse;
use App\Models\ProductAdjustment;
use App\Models\ProductBatch;
use App\Models\ProductPurchase;
use App\Models\ProductQuotation;
use App\Models\ProductReturn;
use App\Models\ProductTransfer;
use App\Models\ProductVariant;
use App\Models\PurchaseProductReturn;
use App\Models\ReturnPurchase;
use App\Models\Returns;
use App\Models\Sale;
use App\Models\SaleAdditionalCost;
use App\Models\StockCount;
use App\Models\Tax;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

// Import your models and observers
use App\Models\Warehouse;
use App\Observers\WarehouseObserver;
use App\Models\User;
use App\Observers\AdjustmentObserver;
use App\Observers\BlackListCustomerObserver;
use App\Observers\CashRegisterObserver;
use App\Observers\CouponObserver;
use App\Observers\CustomerObserver;
use App\Observers\DepositObserver;
use App\Observers\DiscountObserver;
use App\Observers\DiscountPlanCustomerObserver;
use App\Observers\DiscountPlanDiscountObserver;
use App\Observers\ExpenseObserver;
use App\Observers\HirePurchaseInstallmentObserver;
use App\Observers\HrmSettingObserver;
use App\Observers\MoneyTransferObserver;
use App\Observers\PaymentObserver;
use App\Observers\PosSettingObserver;
use App\Observers\ProductAdjustmentObserver;
use App\Observers\ProductBatchObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductPurchaseObserver;
use App\Observers\ProductQuotationObserver;
use App\Observers\ProductReturnObserver;
use App\Observers\ProductSaleObserver;
use App\Observers\ProductSupplierObserver;
use App\Observers\ProductTransferObserver;
use App\Observers\ProductVariantObserver;
use App\Observers\ProductWarehouseObserver;
use App\Observers\PurchaseProductReturnObserver;
use App\Observers\ReturnObserver;
use App\Observers\ReturnPurchaseObserver;
use App\Observers\SaleAdditionalCostObserver;
use App\Observers\SaleObserver;
use App\Observers\StockCountObserver;
use App\Observers\TaxObserver;
use App\Observers\UserObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Register model observers
        Warehouse::observe(WarehouseObserver::class);
        User::observe(UserObserver::class);
        Product::observe(ProductObserver::class);
        Coupon::observe(CouponObserver::class);
        PosSetting::observe(PosSettingObserver::class);
        HirePurchaseInstallment::observe(HirePurchaseInstallmentObserver::class);
        Customer::observe(CustomerObserver::class);
        Adjustment::observe(AdjustmentObserver::class);
        BlackListCustomer::observe(BlackListCustomerObserver::class);
        CashRegister::observe(CashRegisterObserver::class);
        Deposit::observe(DepositObserver::class);
        Discount::observe(DiscountObserver::class);
        DiscountPlanCustomer::observe(DiscountPlanCustomerObserver::class);
        DiscountPlanDiscount::observe(DiscountPlanDiscountObserver::class);
        Expense::observe(ExpenseObserver::class);
        HrmSetting::observe(HrmSettingObserver::class);
        MoneyTransfer::observe(MoneyTransferObserver::class);
        Payment::observe(PaymentObserver::class);

        Product_Sale::observe(ProductSaleObserver::class);
        Product_Warehouse::observe(ProductWarehouseObserver::class);
        ProductAdjustment::observe(ProductAdjustmentObserver::class);
        ProductBatch::observe(ProductBatchObserver::class);
        ProductPurchase::observe(ProductPurchaseObserver::class);
        ProductQuotation::observe(ProductQuotationObserver::class);
        ProductReturn::observe(ProductReturnObserver::class);
        ProductTransfer::observe(ProductTransferObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
        PurchaseProductReturn::observe(PurchaseProductReturnObserver::class);

        Returns::observe(ReturnObserver::class);
        ReturnPurchase::observe(ReturnPurchaseObserver::class);

        Sale::observe(SaleObserver::class);
        SaleAdditionalCost::observe(SaleAdditionalCostObserver::class);
        StockCount::observe(StockCountObserver::class);

        Tax::observe(TaxObserver::class);
    }
}