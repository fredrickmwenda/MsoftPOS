<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        ActivityLog::create([
            'log_name'    => 'expense',
            'description' => 'created',
            'subject_type'=> Expense::class,
            'subject_id'  => $expense->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $expense->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        ActivityLog::create([
            'log_name'    => 'expense',
            'description' => 'updated',
            'subject_type'=> Expense::class,
            'subject_id'  => $expense->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'old'        => $expense->getOriginal(),
                'attributes' => $expense->getChanges(),
            ],
        ]);
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        ActivityLog::create([
            'log_name'    => 'expense',
            'description' => 'deleted',
            'subject_type'=> Expense::class,
            'subject_id'  => $expense->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $expense->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Expense "restored" event (if SoftDeletes is used).
     */
    public function restored(Expense $expense): void
    {
        ActivityLog::create([
            'log_name'    => 'expense',
            'description' => 'restored',
            'subject_type'=> Expense::class,
            'subject_id'  => $expense->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $expense->getAttributes(),
            ],
        ]);
    }

    /**
     * Handle the Expense "force deleted" event.
     */
    public function forceDeleted(Expense $expense): void
    {
        ActivityLog::create([
            'log_name'    => 'expense',
            'description' => 'force deleted',
            'subject_type'=> Expense::class,
            'subject_id'  => $expense->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'   => Auth::id(),
            'properties'  => [
                'attributes' => $expense->getAttributes(),
            ],
        ]);
    }
}