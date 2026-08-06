<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\Auth;

class ProductBatchObserver
{
    /**
     * Handle the ProductBatch "created" event.
     */
    public function created(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_batch',
            'description' => 'created',
            'subject_type'=> ProductBatch::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductBatch "updated" event.
     */
    public function updated(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_batch',
            'description' => 'updated',
            'subject_type'=> ProductBatch::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductBatch "deleted" event.
     */
    public function deleted(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_batch',
            'description' => 'deleted',
            'subject_type'=> ProductBatch::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductBatch "restored" event.
     */
    public function restored(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_batch',
            'description' => 'restored',
            'subject_type'=> ProductBatch::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductBatch "force deleted" event.
     */
    public function forceDeleted(ProductBatch $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_batch',
            'description' => 'force deleted',
            'subject_type'=> ProductBatch::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
