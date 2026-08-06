<?php

namespace App\Observers;

use App\Models\Returns;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ReturnObserver
{
    public function created(Returns $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return',
            'description' => 'created',
            'subject_type'=> Returns::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function updated(Returns $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return',
            'description' => 'updated',
            'subject_type'=> Returns::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(),
            ],
        ]);
    }

    public function deleted(Returns $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return',
            'description' => 'deleted',
            'subject_type'=> Returns::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function restored(Returns $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return',
            'description' => 'restored',
            'subject_type'=> Returns::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function forceDeleted(Returns $model): void
    {
        ActivityLog::create([
            'log_name'    => 'return',
            'description' => 'force deleted',
            'subject_type'=> Returns::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}