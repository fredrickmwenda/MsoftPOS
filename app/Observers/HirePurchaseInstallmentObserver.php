<?php

namespace App\Observers;

use App\Models\HirePurchaseInstallment;
use Illuminate\Support\Facades\Cache;

class HirePurchaseInstallmentObserver
{
    /**
     * Handle the HirePurchaseInstallment "created" event.
     */
    public function created(HirePurchaseInstallment $installment): void
    {
        $this->invalidateRelatedCache($installment);
    }

    /**
     * Handle the HirePurchaseInstallment "updated" event.
     */
    public function updated(HirePurchaseInstallment $installment): void
    {
        $this->invalidateRelatedCache($installment);
    }

    /**
     * Handle the HirePurchaseInstallment "deleted" event.
     */
    public function deleted(HirePurchaseInstallment $installment): void
    {
        $this->invalidateRelatedCache($installment);
    }

    /**
     * Invalidate cache related to hire purchase installments
     */
    protected function invalidateRelatedCache(HirePurchaseInstallment $installment): void
    {
        // Invalidate sale-related caches
        Cache::forget('sale_' . $installment->sale_id);
        Cache::forget('sale_hire_purchase_' . $installment->sale_id);
        Cache::forget('hire_purchase_installments_sale_' . $installment->sale_id);
        
        // Invalidate general hire purchase cache
        Cache::forget('hire_purchase_list');
        Cache::forget('hire_purchase_pending');
        Cache::forget('hire_purchase_overdue');
    }
}
