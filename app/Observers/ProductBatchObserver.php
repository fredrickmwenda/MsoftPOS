<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\Auth;

class ProductBatchObserver
{
    /**
     * Build a structured context array for the product batch.
     * This pulls in related data like Product Name and Code.
     */
    protected function buildContext(ProductBatch $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['product']);

        return [
            'batch_no'      => $model->batch_no,
            'product_name'  => $model->product ? $model->product->name : 'Unknown Product',
            'product_code'  => $model->product ? $model->product->code : 'N/A',
            'qty'           => $model->qty,
            'expired_date'  => $model->expired_date ? $model->expired_date->format('Y-m-d') : 'N/A',
        ];
    }

    /**
     * Handle the ProductBatch "created" event.
     */
    public function created(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_batch',
            'description'  => 'created',
            'subject_type' => ProductBatch::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the ProductBatch "updated" event.
     */
    public function updated(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_batch',
            'description'  => 'updated',
            'subject_type' => ProductBatch::class,
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

    /**
     * Handle the ProductBatch "deleted" event.
     */
    public function deleted(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_batch',
            'description'  => 'deleted',
            'subject_type' => ProductBatch::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the ProductBatch "restored" event.
     */
    public function restored(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_batch',
            'description'  => 'restored',
            'subject_type' => ProductBatch::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the ProductBatch "force deleted" event.
     */
    public function forceDeleted(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'     => 'product_batch',
            'description'  => 'force deleted',
            'subject_type' => ProductBatch::class,
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