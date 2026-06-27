<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Account;
use App\Models\Product;
use App\Models\Role;
use Auth;
use DB;
use Illuminate\Support\Facades\Schema;

class ApprovalController extends Controller
{
    /**
     * Approve an expense from the central Approvals page (redirect response).
     */
    public function approveExpense(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        if ($expense->status !== 'waiting_approval') {
            return redirect()->route('approvals.index')->with('not_permitted', 'Only expenses waiting for approval can be approved.');
        }
        $account = Account::find($expense->account_id);
        if (!$account) {
            return redirect()->route('approvals.index')->with('not_permitted', 'Associated account not found.');
        }
        if ($account->total_balance < $expense->amount) {
            return redirect()->route('approvals.index')->with('not_permitted', 'Insufficient account balance to approve this expense.');
        }
        DB::beginTransaction();
        try {
            $account->total_balance -= $expense->amount;
            $account->save();
            $expense->update(['status' => 'approved']);
            DB::commit();
            return redirect()->route('approvals.index')->with('message', 'Expense approved and deducted from account successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('approvals.index')->with('not_permitted', 'Failed to approve expense: ' . $e->getMessage());
        }
    }

    /**
     * Display the central Approvals page with statistics and pending items
     * for both Purchase Payments and Expenses.
     */
    public function index(Request $request)
    {
        // Permission check using all of the user's roles
        if (!Auth::user()->hasPermissionTo('approvals-index')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access the Approvals module.');
        }

        $canApprovePayments = Auth::user()->hasPermissionTo('approve-payments');
        $canAccessPurchases = Auth::user()->hasPermissionTo('purchases-index');
        $canAccessExpenses  = Auth::user()->hasPermissionTo('expenses-index');

        // Determine if the user is “staff” (has any role with ID > 2)
        $isStaff = Auth::user()->roles->contains(function ($role) {
            return $role->id > 2;
        });

        $basePaymentQuery = Payment::whereNotNull('purchase_id');
        $baseExpenseQuery = Expense::query();

        if ($isStaff && config('staff_access') == 'own') {
            $basePaymentQuery->where('user_id', Auth::id());
            $baseExpenseQuery->where('user_id', Auth::id());
        }

        // Pending statuses
        $pendingPaymentStatuses = ['pending', 'waiting_authorization', 'waiting_approval'];
        $paymentsTableHasStatus = Schema::hasColumn('payments', 'approval_status');

        if ($paymentsTableHasStatus) {
            $pendingPurchasePaymentsCount = (clone $basePaymentQuery)
                ->whereIn('approval_status', $pendingPaymentStatuses)
                ->count();
            $approvedPaymentsCount = (clone $basePaymentQuery)
                ->where('approval_status', 'approved')
                ->count();
            $pendingPurchasePayments = (clone $basePaymentQuery)
                ->with(['purchase.supplier', 'purchase.warehouse'])
                ->whereIn('approval_status', $pendingPaymentStatuses)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();
        } else {
            $pendingPurchasePaymentsCount = 0;
            $approvedPaymentsCount = 0;
            $pendingPurchasePayments = collect();
        }

        $expensesWaitingApprovalCount = (clone $baseExpenseQuery)
            ->where('status', 'waiting_approval')
            ->count();
        $expensesApprovedCount = (clone $baseExpenseQuery)
            ->where('status', 'approved')
            ->count();
        $pendingExpenses = (clone $baseExpenseQuery)
            ->with(['warehouse', 'expenseCategory', 'account'])
            ->where('status', 'waiting_approval')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $stats = [
            'pending_purchase_payments' => $pendingPurchasePaymentsCount,
            'pending_expenses'          => $expensesWaitingApprovalCount,
            'total_pending'             => $pendingPurchasePaymentsCount + $expensesWaitingApprovalCount,
            'approved_payments_count'   => $approvedPaymentsCount,
            'approved_expenses_count'   => $expensesApprovedCount,
        ];

        // Gather all permission names from the user's roles
        $all_permission = Auth::user()->getAllPermissions();
        if (empty($all_permission)) {
            $all_permission = ['dummy text'];
        }

        return view('backend.approval.index', compact(
            'stats',
            'pendingPurchasePayments',
            'pendingExpenses',
            'canApprovePayments',
            'canAccessPurchases',
            'canAccessExpenses',
            'all_permission'
        ));
    }
}