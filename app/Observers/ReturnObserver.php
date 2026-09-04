<?php

namespace App\Observers;

use App\Models\Returns;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ReturnObserver
{
    /**
     * Build a structured context array for the sale return.
     * This pulls in related data like Original Sale, Customer, Warehouse, and Biller.
     */
    protected function buildContext(Returns $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['sale', 'customer', 'warehouse', 'biller', 'user', 'currency']);

        return [
            'return_reference'      => $model->reference_no,
            'original_sale_reference' => $model->sale ? $model->sale->reference_no : 'N/A',
            'grand_total'           => $model->grand_total,
            'total_qty'             => $model->total_qty,
            'customer'              => $model->customer ? $model->customer->name : 'Walk-in Customer',
            'warehouse'             => $model->warehouse ? $model->warehouse->name : 'N/A',
            'biller'                => $model->biller ? $model->biller->name : 'N/A',
            'processed_by'          => $model->user ? $model->user->name : 'System',
            'currency'              => $model->currency ? $model->currency->code : 'Default',
        ];
    }

    public function created(Returns $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return',
            'description'  => 'created',
            'subject_type' => Returns::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function updated(Returns $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return',
            'description'  => 'updated',
            'subject_type' => Returns::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    public function deleted(Returns $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return',
            'description'  => 'deleted',
            'subject_type' => Returns::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function restored(Returns $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return',
            'description'  => 'restored',
            'subject_type' => Returns::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function forceDeleted(Returns $model): void
    {
        ActivityLog::create([
            'log_name'     => 'return',
            'description'  => 'force deleted',
            'subject_type' => Returns::class,
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