<?php

namespace App\Observers;

use App\Models\SaleAdditionalCost;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SaleAdditionalCostObserver
{
    /**
     * Build a structured context array for the sale additional cost.
     * This pulls in related data like the Parent Sale Reference.
     */
    protected function buildContext(SaleAdditionalCost $model): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $model->loadMissing(['sale']);

        return [
            'cost_name'      => $model->name,
            'amount'          => $model->amount,
            'sale_reference'  => $model->sale ? $model->sale->reference_no : 'N/A',
            'sale_id'         => $model->sale_id,
        ];
    }

    public function created(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'     => 'sale_additional_cost',
            'description'  => 'created',
            'subject_type' => SaleAdditionalCost::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function updated(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'     => 'sale_additional_cost',
            'description'  => 'updated',
            'subject_type' => SaleAdditionalCost::class,
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

    public function deleted(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'     => 'sale_additional_cost',
            'description'  => 'deleted',
            'subject_type' => SaleAdditionalCost::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function restored(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'     => 'sale_additional_cost',
            'description'  => 'restored',
            'subject_type' => SaleAdditionalCost::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }

    public function forceDeleted(SaleAdditionalCost $model): void
    {
        ActivityLog::create([
            'log_name'     => 'sale_additional_cost',
            'description'  => 'force deleted',
            'subject_type' => SaleAdditionalCost::class,
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