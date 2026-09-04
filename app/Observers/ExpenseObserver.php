<?php

namespace App\Observers;
 
use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ExpenseObserver
{
    /**
     * Build a structured context array for the expense.
     * This pulls in related data like Warehouse, Category, Account, and Cashier.
     */
    protected function buildContext(Expense $expense): array
    {
        // Load relationships safely to prevent N+1 issues or null errors
        $expense->loadMissing(['warehouse', 'expenseCategory', 'account', 'user', 'cashRegister']);

        return [
            'reference_no'      => $expense->reference_no,
            'expense_name'      => $expense->name,
            'amount'             => $expense->amount,
            'status'             => $expense->status, // e.g., pending, approved
            'expense_category'   => $expense->expenseCategory ? $expense->expenseCategory->name : 'Uncategorized',
            'account'            => $expense->account ? $expense->account->name : 'N/A',
            'warehouse'          => $expense->warehouse ? $expense->warehouse->name : 'N/A',
            'created_by'         => $expense->user ? $expense->user->name : 'System',
            'cash_register_id'   => $expense->cashRegister ? $expense->cashRegister->id : 'N/A',
        ];
    }

    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        ActivityLog::create([
            'log_name'     => 'expense',
            'description'  => 'created',
            'subject_type' => Expense::class,
            'subject_id'   => $expense->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($expense),
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
            'log_name'     => 'expense',
            'description'  => 'updated',
            'subject_type' => Expense::class,
            'subject_id'   => $expense->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($expense),
                'old'        => $expense->getOriginal(),
                'attributes' => $expense->getChanges(), // Only the fields that changed
            ],
        ]);
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        ActivityLog::create([
            'log_name'     => 'expense',
            'description'  => 'deleted',
            'subject_type' => Expense::class,
            'subject_id'   => $expense->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($expense),
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
            'log_name'     => 'expense',
            'description'  => 'restored',
            'subject_type' => Expense::class,
            'subject_id'   => $expense->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($expense),
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
            'log_name'     => 'expense',
            'description'  => 'force deleted',
            'subject_type' => Expense::class,
            'subject_id'   => $expense->id,
            'causer_type'  => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id'    => Auth::id(),
            'properties'   => [
                'context'    => $this->buildContext($expense),
                'attributes' => $expense->getAttributes(),
            ],
        ]);
    }
}