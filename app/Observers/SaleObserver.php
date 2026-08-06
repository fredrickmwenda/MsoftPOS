<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SaleObserver
{
    public function created(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'    => 'sale',
            'description' => 'created',
            'subject_type'=> Sale::class,
            'subject_id'  => $sale->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $sale->getAttributes(),
            ],
        ]);
    }

    public function updated(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'    => 'sale',
            'description' => 'updated',
            'subject_type'=> Sale::class,
            'subject_id'  => $sale->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $sale->getOriginal(),
                'attributes' => $sale->getChanges(),
            ],
        ]);
    }

    public function deleted(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'    => 'sale',
            'description' => 'deleted',
            'subject_type'=> Sale::class,
            'subject_id'  => $sale->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $sale->getAttributes(),
            ],
        ]);
    }

    public function restored(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'    => 'sale',
            'description' => 'restored',
            'subject_type'=> Sale::class,
            'subject_id'  => $sale->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $sale->getAttributes(),
            ],
        ]);
    }

    public function forceDeleted(Sale $sale): void
    {
        ActivityLog::create([
            'log_name'    => 'sale',
            'description' => 'force deleted',
            'subject_type'=> Sale::class,
            'subject_id'  => $sale->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $sale->getAttributes(),
            ],
        ]);
    }
}