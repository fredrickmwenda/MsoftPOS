<?php

namespace App\Observers;

use App\Models\StockCount;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class StockCountObserver
{
    /**
     * Build a structured context array for the stock count.
     * This pulls in related data like Warehouse, User, and Items counted.
     */
    protected function buildContext(StockCount $model): array
    {
        // Load relationships safely. Using loadMissing in case they aren't defined on the model.
        // We use inline closures for hasMany to avoid errors if the relationship method isn't named 'items'
        $model->loadMissing(['warehouse', 'user']);

        $itemsSummary = 'No items';
        
        // Dynamically check if the items relationship exists and map it
        if (method_exists($model, 'items')) {
            $model->loadMissing(['items.product']);
            if ($model->items && $model->items->isNotEmpty()) {
                $itemsSummary = $model->items->map(function ($item) {
                    $name = $item->product ? $item->product->name : 'Unknown Product';
                    return "{$name} (System: {$item->system_qty}, Counted: {$item->physical_qty})";
                })->implode(', ');
            }
        }

        return [
            'reference_no'   => $model->reference_no ?? 'N/A',
            'status'         => $model->status ?? 'N/A', // e.g., draft, completed
            'warehouse'      => $model->warehouse ? $model->warehouse->name : 'N/A',
            'initiated_by'   => $model->user ? $model->user->name : 'System',
            'items_summary'  => $itemsSummary,
        ];
    }

    /**
     * Handle the StockCount "created" event.
     */
    public function created(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'     => 'stock_count',
            'description'  => 'created',
            'subject_type' => StockCount::class,
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
     * Handle the StockCount "updated" event.
     */
    public function updated(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'     => 'stock_count',
            'description'  => 'updated',
            'subject_type' => StockCount::class,
            'subject_id'   => $model->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($model),
                'old'        => $model->getOriginal(),
                'attributes' => $model->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the StockCount "deleted" event.
     */
    public function deleted(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'     => 'stock_count',
            'description'  => 'deleted',
            'subject_type' => StockCount::class,
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
     * Handle the StockCount "restored" event.
     */
    public function restored(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'     => 'stock_count',
            'description'  => 'restored',
            'subject_type' => StockCount::class,
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
     * Handle the StockCount "force deleted" event.
     */
    public function forceDeleted(StockCount $model): void
    {
        ActivityLog::create([
            'log_name'     => 'stock_count',
            'description'  => 'force deleted',
            'subject_type' => StockCount::class,
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