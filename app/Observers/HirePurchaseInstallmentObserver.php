<?php

namespace App\Observers;

use App\Models\HirePurchaseInstallment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HirePurchaseInstallmentObserver
{
    /**
     * Handle the HirePurchaseInstallment "created" event.
     */
    public function created(HirePurchaseInstallment $installment): void
    {
        $this->invalidateRelatedCache($installment);

        ActivityLog::create([
            'log_name'    => 'hire_purchase_installment',
            'description' => 'created',
            'subject_type'=> HirePurchaseInstallment::class,
            'subject_id'  => $installment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $installment->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the HirePurchaseInstallment "updated" event.
     */
    public function updated(HirePurchaseInstallment $installment): void
    {
        $this->invalidateRelatedCache($installment);

        ActivityLog::create([
            'log_name'    => 'hire_purchase_installment',
            'description' => 'updated',
            'subject_type'=> HirePurchaseInstallment::class,
            'subject_id'  => $installment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $installment->getOriginal(),
                'attributes' => $installment->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the HirePurchaseInstallment "deleted" event.
     */
    public function deleted(HirePurchaseInstallment $installment): void
    {
        $this->invalidateRelatedCache($installment);

        ActivityLog::create([
            'log_name'    => 'hire_purchase_installment',
            'description' => 'deleted',
            'subject_type'=> HirePurchaseInstallment::class,
            'subject_id'  => $installment->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $installment->getAttributes(),
            ],
        ]);
    }

    /**
     * Invalidate cache related to hire purchase installments
     */
    protected function invalidateRelatedCache(HirePurchaseInstallment $installment): void
    {
        Cache::forget('sale_' . $installment->sale_id);
        Cache::forget('sale_hire_purchase_' . $installment->sale_id);
        Cache::forget('hire_purchase_installments_sale_' . $installment->sale_id);
        Cache::forget('hire_purchase_list');
        Cache::forget('hire_purchase_pending');
        Cache::forget('hire_purchase_overdue');
    }
}