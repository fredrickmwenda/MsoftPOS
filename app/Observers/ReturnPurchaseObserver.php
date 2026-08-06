<?php

namespace App\Observers;

use App\Models\ReturnPurchase;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ReturnPurchaseObserver
{
    public function created(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return_purchase',
            'description' => 'created',
            'subject_type'=> ReturnPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function updated(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return_purchase',
            'description' => 'updated',
            'subject_type'=> ReturnPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(),
            ],
        ]);
    }

    public function deleted(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return_purchase',
            'description' => 'deleted',
            'subject_type'=> ReturnPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function restored(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return_purchase',
            'description' => 'restored',
            'subject_type'=> ReturnPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function forceDeleted(ReturnPurchase $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return_purchase',
            'description' => 'force deleted',
            'subject_type'=> ReturnPurchase::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}