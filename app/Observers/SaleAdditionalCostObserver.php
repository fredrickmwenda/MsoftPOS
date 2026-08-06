<?php

namespace App\Observers;

use App\Models\SaleAdditionalCost;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SaleAdditionalCostObserver
{
    public function created(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'    => 'sale_additional_cost',
            'description' => 'created',
            'subject_type'=> SaleAdditionalCost::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function updated(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'    => 'sale_additional_cost',
            'description' => 'updated',
            'subject_type'=> SaleAdditionalCost::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(),
            ],
        ]);
    }

    public function deleted(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'    => 'sale_additional_cost',
            'description' => 'deleted',
            'subject_type'=> SaleAdditionalCost::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function restored(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'    => 'sale_additional_cost',
            'description' => 'restored',
            'subject_type'=> SaleAdditionalCost::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }

    public function forceDeleted(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'    => 'sale_additional_cost',
            'description' => 'force deleted',
            'subject_type'=> SaleAdditionalCost::class,
            'subject_id'  => $model->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => ['attributes' => $model->getAttributes()],
        ]);
    }
}