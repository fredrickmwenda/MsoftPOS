<?php

namespace App\Observers;

use App\Models\Tax;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TaxObserver
{
    /**
     * Build a structured context array for the tax.
     * This pulls in related data like the count of products using this tax.
     */
    protected function buildContext(Tax $tax): array
    {
        // Load relationship count safely
        $tax->loadCount(['products']);

        return [
            'tax_name'         => $tax->name,
            'rate'             => $tax->rate,
            'is_active'        => (bool) $tax->is_active,
            'products_count'   => $tax->products_count ?? 0,
        ];
    }

    /**
     * Handle the Tax "created" event.
     */
    public function created(Tax $tax): void
    {
        Cache::forget('tax_list');

        ActivityLog::create([
            'log_name'     => 'tax',
            'description'  => 'created',
            'subject_type' => Tax::class,
            'subject_id'   => $tax->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($tax),
                'attributes' => $tax->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Tax "updated" event.
     */
    public function updated(Tax $tax): void
    {
        Cache::forget('tax_list');

        ActivityLog::create([
            'log_name'     => 'tax',
            'description'  => 'updated',
            'subject_type' => Tax::class,
            'subject_id'   => $tax->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($tax),
                'old'        => $tax->getOriginal(),
                'attributes' => $tax->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the Tax "deleted" event.
     */
    public function deleted(Tax $tax): void
    {
        Cache::forget('tax_list');

        ActivityLog::create([
            'log_name'     => 'tax',
            'description'  => 'deleted',
            'subject_type' => Tax::class,
            'subject_id'   => $tax->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($tax),
                'attributes' => $tax->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Tax "restored" event.
     */
    public function restored(Tax $tax): void
    {
        Cache::forget('tax_list');

        ActivityLog::create([
            'log_name'     => 'tax',
            'description'  => 'restored',
            'subject_type' => Tax::class,
            'subject_id'   => $tax->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($tax),
                'attributes' => $tax->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Tax "force deleted" event.
     */
    public function forceDeleted(Tax $tax): void
    {
        Cache::forget('tax_list');

        ActivityLog::create([
            'log_name'     => 'tax',
            'description'  => 'force deleted',
            'subject_type' => Tax::class,
            'subject_id'   => $tax->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($tax),
                'attributes' => $tax->getAttributes(),
            ],
        ]);
    }
}