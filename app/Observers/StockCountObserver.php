<?php

namespace App\Observers;

use App\Models\StockCount;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class StockCountObserver
{
    public function created(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'    => 'stock_count',
            'description' => 'created',
            'subject_type'=> StockCount::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function updated(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'    => 'stock_count',
            'description' => 'updated',
            'subject_type'=> StockCount::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(),
            ],
        ]);
    }

    public function deleted(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'    => 'stock_count',
            'description' => 'deleted',
            'subject_type'=> StockCount::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function restored(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'    => 'stock_count',
            'description' => 'restored',
            'subject_type'=> StockCount::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function forceDeleted(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'    => 'stock_count',
            'description' => 'force deleted',
            'subject_type'=> StockCount::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}