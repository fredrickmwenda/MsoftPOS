<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ProductQuotation;
use Illuminate\Support\Facades\Auth;

class ProductQuotationObserver
{
    /**
     * Handle the ProductQuotation "created" event.
     */
    public function created(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_quotation',
            'description' => 'created',
            'subject_type'=> ProductQuotation::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductQuotation "updated" event.
     */
    public function updated(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_quotation',
            'description' => 'updated',
            'subject_type'=> ProductQuotation::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductQuotation "deleted" event.
     */
    public function deleted(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_quotation',
            'description' => 'deleted',
            'subject_type'=> ProductQuotation::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductQuotation "restored" event.
     */
    public function restored(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_quotation',
            'description' => 'restored',
            'subject_type'=> ProductQuotation::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    /**
     * Handle the ProductQuotation "force deleted" event.
     */
    public function forceDeleted(ProductQuotation $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_quotation',
            'description' => 'force deleted',
            'subject_type'=> ProductQuotation::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}
