<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductTransfer;
use Illuminate\Support\Facades\Auth;

class ProductTransferObserver
{
    /**
     * Handle the ProductTransfer "created" event.
     */
    public function created(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_transfer',
            'description' => 'created',
            'subject_type'=> ProductTransfer::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductTransfer "updated" event.
     */
    public function updated(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_transfer',
            'description' => 'updated',
            'subject_type'=> ProductTransfer::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductTransfer "deleted" event.
     */
    public function deleted(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_transfer',
            'description' => 'deleted',
            'subject_type'=> ProductTransfer::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductTransfer "restored" event.
     */
    public function restored(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_transfer',
            'description' => 'restored',
            'subject_type'=> ProductTransfer::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductTransfer "force deleted" event.
     */
    public function forceDeleted(ProductTransfer $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_transfer',
            'description' => 'force_deleted',
            'subject_type'=> ProductTransfer::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
