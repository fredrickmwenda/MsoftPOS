<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SaleObserver
{
    /**
     * Build a structured context array for the sale.
     * This pulls in related data like Customer, Warehouse, Biller, Cashier, and Items.
     */
    protected function buildContext(Sale $sale): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $sale->loadMissing(['customer', 'warehouse', 'biller', 'user', 'currency', 'product_sales.product']);

        $items = $sale->product_sales->map(function ($item) {
            $name = $item->product ? $item->product->name : 'Unknown Product';
            return "{$item->qty} x {$name}";
        })->implode(', ');

        return [
            'reference_no'       => $sale->reference_no,
            'sale_status'        => $sale->sale_status, // 1=Completed, 2=Pending, 3=Draft
            'payment_status'     => $sale->payment_status, // 1=Pending, 2=Due, 3=Partial, 4=Paid
            'grand_total'        => $sale->grand_total,
            'paid_amount'        => $sale->paid_amount,
            'customer'           => $sale->customer ? $sale->customer->name : 'Walk-in Customer',
            'warehouse'          => $sale->warehouse ? $sale->warehouse->name : 'N/A',
            'biller'             => $sale->biller ? $sale->biller->name : 'N/A',
            'cashier'            => $sale->user ? $sale->user->name : 'System',
            'currency'           => $sale->currency ? $sale->currency->code : 'Default',
            'is_hire_purchase'   => (bool) $sale->is_hire_purchase,
            'items_summary'      => $items ?: 'No items',
        ];
    }

    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'     => 'sale',
            'description'  => 'created',
            'subject_type' => Sale::class,
            'subject_id'   => $sale->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($sale),
                'attributes' => $sale->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'     => 'sale',
            'description'  => 'updated',
            'subject_type' => Sale::class,
            'subject_id'   => $sale->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($sale),
                'old'        => $sale->getOriginal(),
                'attributes' => $sale->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'     => 'sale',
            'description'  => 'deleted',
            'subject_type' => Sale::class,
            'subject_id'   => $sale->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($sale),
                'attributes' => $sale->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Sale "restored" event.
     */
    public function restored(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'     => 'sale',
            'description'  => 'restored',
            'subject_type' => Sale::class,
            'subject_id'   => $sale->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($sale),
                'attributes' => $sale->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Sale "force deleted" event.
     */
    public function forceDeleted(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'     => 'sale',
            'description'  => 'force deleted',
            'subject_type' => Sale::class,
            'subject_id'   => $sale->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($sale),
                'attributes' => $sale->getAttributes(),
            ],
        ]);
    }
}