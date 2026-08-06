<?php

namespace App\Observers;

use App\Models\Discount;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DiscountObserver
{
    public function created(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'    => 'discount',
            'description' => 'created',
            'subject_type'=> Discount::class,
            'subject_id'  => $discount->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $discount->getAttributes(),
            ],
        ]);
    }

    public function updated(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'    => 'discount',
            'description' => 'updated',
            'subject_type'=> Discount::class,
            'subject_id'  => $discount->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $discount->getOriginal(),
                'attributes' => $discount->getChanges(),
            ],
        ]);
    }

    public function deleted(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'    => 'discount',
            'description' => 'deleted',
            'subject_type'=> Discount::class,
            'subject_id'  => $discount->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $discount->getAttributes(),
            ],
        ]);
    }

    public function restored(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'    => 'discount',
            'description' => 'restored',
            'subject_type'=> Discount::class,
            'subject_id'  => $discount->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $discount->getAttributes(),
            ],
        ]);
    }

    public function forceDeleted(Discount $discount): void
    {
        ActivityLog::create([
            'log_name'    => 'discount',
            'description' => 'force deleted',
            'subject_type'=> Discount::class,
            'subject_id'  => $discount->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $discount->getAttributes(),
            ],
        ]);
    }
}