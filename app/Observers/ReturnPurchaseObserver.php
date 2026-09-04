<?php

namespace App\Observers;

use App\Models\ReturnPurchase;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ReturnPurchaseObserver
{
    /**
     * Build a structured context array for the purchase return.
     * This pulls in related data like Original Purchase, Supplier, Warehouse, and Currency.
     */
    protected function buildContext(ReturnPurchase $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['purchase', 'supplier', 'warehouse', 'user', 'currency']);

        return [
            'return_reference'         => $model->reference_no,
            'original_purchase_reference' => $model->purchase ? $model->purchase->reference_no : 'N/A',
            'grand_total'              => $model->grand_total,
            'total_qty'                => $model->total_qty,
            'supplier'                 => $model->supplier ? $model->supplier->name : 'N/A',
            'warehouse'                => $model->warehouse ? $model->warehouse->name : 'N/A',
            'processed_by'             => $model->user ? $model->user->name : 'System',
            'currency'                 => $model->currency ? $model->currency->code : 'Default',
        ];
    }

    public function created(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return_purchase',
            'description'  => 'created',
            'subject_type' => ReturnPurchase::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function updated(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return_purchase',
            'description'  => 'updated',
            'subject_type' => ReturnPurchase::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(),
            ],
        ]);
    }

    public function deleted(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return_purchase',
            'description'  => 'deleted',
            'subject_type' => ReturnPurchase::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function restored(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return_purchase',
            'description'  => 'restored',
            'subject_type' => ReturnPurchase::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function forceDeleted(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return_purchase',
            'description'  => 'force deleted',
            'subject_type' => ReturnPurchase::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }
}