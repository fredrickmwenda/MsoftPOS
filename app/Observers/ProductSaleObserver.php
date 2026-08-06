<?php

namespace App\Observers;

use App\Models\Product_Sale as ProductSale;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ProductSaleObserver
{
    public function created(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_sale',
            'description' => 'created',
            'subject_type'=> ProductSale::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function updated(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_sale',
            'description' => 'updated',
            'subject_type'=> ProductSale::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(),
            ],
        ]);
    }

    public function deleted(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_sale',
            'description' => 'deleted',
            'subject_type'=> ProductSale::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function restored(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_sale',
            'description' => 'restored',
            'subject_type'=> ProductSale::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function forceDeleted(ProductSale $model): void
    {
        ActivityLog::create([
            'log_name'    => 'product_sale',
            'description' => 'force deleted',
            'subject_type'=> ProductSale::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}