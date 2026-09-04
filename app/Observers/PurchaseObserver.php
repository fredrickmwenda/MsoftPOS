<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PurchaseObserver
{
    /**
     * Build a structured context array for the purchase.
     * This pulls in related data like Supplier, Warehouse, Currency, and Items.
     */
    protected function buildContext(Purchase $purchase): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $purchase->loadMissing(['supplier', 'warehouse', 'currency', 'products.product']);

        $items = $purchase->products->map(function ($item) {
            $name = $item->product ? $item->product->name : 'Unknown Product';
            return "{$item->qty} x {$name}";
        })->implode(', ');

        return [
            'reference_no'    => $purchase->reference_no,
            'status'          => $purchase->status,
            'payment_status'  => $purchase->payment_status,
            'grand_total'     => $purchase->grand_total,
            'total_qty'       => $purchase->total_qty,
            'supplier'        => $purchase->supplier ? $purchase->supplier->name : 'N/A',
            'warehouse'       => $purchase->warehouse ? $purchase->warehouse->name : 'N/A',
            'currency'        => $purchase->currency ? $purchase->currency->code : 'Default',
            'items_summary'   => $items ?: 'No items',
        ];
    }

    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase',
            'description'  => 'created',
            'subject_type' => Purchase::class,
            'subject_id'   => $purchase->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($purchase),
                'attributes' => $purchase->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase',
            'description'  => 'updated',
            'subject_type' => Purchase::class,
            'subject_id'   => $purchase->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($purchase),
                'old'        => $purchase->getOriginal(),
                'attributes' => $purchase->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the Purchase "deleted" event.
     */
    public function deleted(Purchase $purchase): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase',
            'description'  => 'deleted',
            'subject_type' => Purchase::class,
            'subject_id'   => $purchase->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($purchase),
                'attributes' => $purchase->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Purchase "restored" event.
     */
    public function restored(Purchase $purchase): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase',
            'description'  => 'restored',
            'subject_type' => Purchase::class,
            'subject_id'   => $purchase->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($purchase),
                'attributes' => $purchase->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Purchase "force deleted" event.
     */
    public function forceDeleted(Purchase $purchase): void
    {
        ActivityLog::create([
            'log_name'     => 'purchase',
            'description'  => 'force deleted',
            'subject_type' => Purchase::class,
            'subject_id'   => $purchase->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($purchase),
                'attributes' => $purchase->getAttributes(),
            ],
        ]);
    }
}