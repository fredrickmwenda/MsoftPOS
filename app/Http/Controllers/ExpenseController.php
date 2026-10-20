<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Account;
use App\Models\Warehouse;
use App\Models\CashRegister;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\GeneralSetting;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::user()->hasPermissionTo('expenses-index')){
            $all_permission = Auth::user()->getAllPermissions();
            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            // Default dates
            if($request->has('starting_date') && $request->has('ending_date')) {
                $starting_date = $request->starting_date;
                $ending_date = $request->ending_date;
            } else {
                $starting_date = date('Y-m-d');
                $ending_date = date('Y-m-d');
            }

            $warehouse_id = $request->input('warehouse_id', 0);

            $lims_warehouse_list = Warehouse::select('name', 'id')->where('is_active', true)->get();
            // Fetch expense categories
            $lims_expense_category_list = DB::table('expense_categories')->where('is_active', true)->get();
            $lims_account_list = Account::where('is_active', true)->get();

            return view('backend.expense.index', compact(
                'lims_account_list',
                'lims_warehouse_list',
                'lims_expense_category_list',
                'all_permission',
                'starting_date',
                'ending_date',
                'warehouse_id'
            ));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function expenseData(Request $request)
    {
        // Get permissions safely
        $all_permission = $request->input('all_permission', []);

        // Define columns for ordering
        $columns = [1 => 'created_at', 2 => 'reference_no'];

        // Parameters with fallbacks
        $warehouse_id = $request->input('warehouse_id', 0);
        $starting_date = $request->input('starting_date', date('Y-m-d', strtotime('-30 days')));
        $ending_date = $request->input('ending_date', date('Y-m-d'));

        // Base query
        $baseQuery = Expense::whereDate('created_at', '>=', $starting_date)
                            ->whereDate('created_at', '<=', $ending_date);

        // Staff access
        $isStaff = Auth::user()->roles->contains(fn($role) => $role->id > 2);
        if ($isStaff && config('staff_access', 'all') === 'own') {
            $baseQuery->where('user_id', Auth::id());
        }

        // Warehouse
        if ($warehouse_id) {
            $baseQuery->where('warehouse_id', $warehouse_id);
        }

        // Percentage filter (only if > 0 and < 100)
        $percentage_filter = GeneralSetting::first()->percentage_filter ?? null;
        $filtered_expense_ids = [];
        $filtered_total_expenses = 0;

        if ($percentage_filter !== null && $percentage_filter > 0 && $percentage_filter < 100) {
            $all_expenses = (clone $baseQuery)->orderBy('amount', 'desc')->get(['id', 'amount']);
            $total_value = $all_expenses->sum('amount');
            if ($total_value > 0) {
                $target_value = $total_value * ($percentage_filter / 100);
                $running = 0;
                foreach ($all_expenses as $exp) {
                    $filtered_expense_ids[] = $exp->id;
                    $running += $exp->amount;
                    $filtered_total_expenses = $running;
                    if ($running >= $target_value) break;
                }
                if (!empty($filtered_expense_ids)) {
                    $baseQuery->whereIn('id', $filtered_expense_ids);
                } else {
                    $baseQuery->whereRaw('1 = 0');
                    $filtered_total_expenses = 0;
                }
            }
        }

        // Counts and sum
        $totalData = $baseQuery->count();
        $totalFiltered = $totalData;
        $total_expense_sum = (!empty($filtered_expense_ids))
            ? $filtered_total_expenses
            : (clone $baseQuery)->sum('amount');

        // Pagination & ordering
        $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
        $start = $request->input('start', 0);
        $orderColumn = $columns[$request->input('order.0.column')] ?? 'created_at';
        $dir = $request->input('order.0.dir', 'desc');

        // Fetch with search
        if (empty($request->input('search.value'))) {
            $expenses = (clone $baseQuery)
                            ->with(['warehouse', 'expenseCategory'])
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy('expenses.' . $orderColumn, $dir)
                            ->get();
        } else {
            $search = $request->input('search.value');
            $searchDate = date('Y-m-d', strtotime(str_replace('/', '-', $search)));
            $searchQuery = clone $baseQuery;
            $searchQuery->where(function($q) use ($search, $searchDate) {
                $q->where('reference_no', 'LIKE', "%{$search}%")
                  ->orWhereDate('created_at', '=', $searchDate);
            });
            $totalFiltered = $searchQuery->count();
            $expenses = $searchQuery->with(['warehouse', 'expenseCategory'])
                                    ->offset($start)
                                    ->limit($limit)
                                    ->orderBy('expenses.' . $orderColumn, $dir)
                                    ->get();
        }

        // Build data array using the original button generation
        $data = [];
        $dateFormat = config('date_format', 'd-m-Y');
        $decimalPlaces = config('decimal', 2);

        foreach ($expenses as $key => $expense) {
            $warehouseName = $expense->warehouse ? $expense->warehouse->name : 'N/A';
            $categoryName = $expense->expenseCategory ? $expense->expenseCategory->name : 'N/A';

            // ----- Original button HTML -----
            $options = '<div class="btn-group">
                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                    . trans("file.action") . '
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">';

            if (in_array("expenses-edit", $all_permission)) {
                $options .= '
                    <li>
                        <button type="button" data-id="' . $expense->id . '"
                            class="open-Editexpense_categoryDialog btn btn-link"
                            data-toggle="modal" data-target="#editModal">
                            <i class="dripicons-document-edit"></i> ' . trans('file.edit') . '
                        </button>
                    </li>';
            }

            if (in_array("expenses-delete", $all_permission)) {
                $options .= \Form::open(["route" => ["expenses.destroy", $expense->id], "method" => "DELETE"]) . '
                    <li>
                        <button type="submit" class="btn btn-link" onclick="return confirmDelete()">
                            <i class="dripicons-trash"></i> ' . trans('file.delete') . '
                        </button>
                    </li>' . \Form::close();
            }

            if ($expense->status === 'draft') {
                $options .= '
                    <li>
                        <button type="button" class="btn btn-link authorize-expense"
                            data-id="' . $expense->id . '">
                            <i class="dripicons-checkmark"></i> Authorize
                        </button>
                    </li>';
            }

            if ($expense->status === 'waiting_approval') {
                $options .= '
                    <li>
                        <button type="button" class="btn btn-link approve-expense"
                            data-id="' . $expense->id . '">
                            <i class="dripicons-thumbs-up"></i> Approve
                        </button>
                    </li>';
            }

            $options .= '</ul></div>';

            // Build row
            $data[] = [
                'id' => $expense->id,
                'key' => $key,
                'date' => date($dateFormat, strtotime($expense->created_at->toDateString())),
                'reference_no' => $expense->reference_no,
                'warehouse' => $warehouseName,
                'expenseCategory' => $categoryName,
                'amount' => number_format($expense->amount, $decimalPlaces),
                'note' => $expense->note,
                'options' => $options,
            ];
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "total_expense"   => $total_expense_sum,
            "data"            => $data,
        ]);
    }

    public function create()
    {
        //
    }

    /* ==========================================================
     |  STORE — ledger balance check + auto-sync total_balance
     +========================================================= */
    public function store(Request $request)
    {
        $data   = $request->all();
        $amount = (float) $data['amount'];

        $result = DB::transaction(function () use ($request, $data, $amount) {

            // Lock the account row so concurrent expenses can't overdraw
            $account = Account::where('is_active', true)
                                ->lockForUpdate()
                                ->find($request->input('account_id'));

            if (!$account) {
                return ['error' => 'Please select a valid account!'];
            }

            // 1) Real balance — same calculation as the Accounts page
            $realBalance = $account->calculateBalance();

            // 2) AUTO-SET: if total_balance in the accounts table is stale, fix it
            if (round((float) $account->total_balance, 2) !== $realBalance) {
                $account->total_balance = $realBalance;
                $account->save();
            }

            // 3) Insufficient balance check against the REAL balance
            if ($amount > $realBalance) {
                return ['error' => $this->balanceErrorMessage($account, $amount, $realBalance)];
            }

            $data['account_id'] = $account->id;

            if(isset($data['created_at']))
                $data['created_at'] = date("Y-m-d H:i:s", strtotime($data['created_at']));
            else
                $data['created_at'] = date("Y-m-d H:i:s");

            $data['reference_no'] = 'er-' . date("Ymd") . '-'. date("his");
            $data['user_id'] = Auth::id();

            $cash_register_data = CashRegister::where([
                ['user_id', $data['user_id']],
                ['warehouse_id', $data['warehouse_id']],
                ['status', true]
            ])->first();

            if($cash_register_data)
                $data['cash_register_id'] = $cash_register_data->id;

            Expense::create($data);

            // 4) Keep the accounts table in sync with the ledger (expense now counts)
            $account->syncTotalBalance();

            return ['success' => true];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('not_permitted', $result['error'])->withInput();
        }

        return redirect('expenses')->with('message', 'Data inserted successfully');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        if (Auth::user()->hasPermissionTo('expenses-edit')) {
            $lims_expense_data = Expense::find($id);
            $lims_expense_data->date = date('d-m-Y', strtotime($lims_expense_data->created_at->toDateString()));
            return $lims_expense_data;
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    /* ==========================================================
     |  UPDATE — ledger check (old amount already reserved in it)
     +========================================================= */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $lims_expense_data = Expense::find($data['expense_id'] ?? $id);

        if (!$lims_expense_data) {
            return redirect()->back()->with('not_permitted', 'Expense not found!')->withInput();
        }

        $new_amount = (float) $data['amount'];
        $old_amount = (float) $lims_expense_data->amount;

        $result = DB::transaction(function () use ($request, $data, $lims_expense_data, $new_amount, $old_amount) {

            // Resolve the account: submitted account_id, else the expense's existing one
            $account_id = $request->input('account_id') ?: $lims_expense_data->account_id;
            $account = Account::lockForUpdate()->find($account_id);

            if (!$account) {
                return ['error' => 'Please select a valid account!'];
            }

            // Real ledger balance — it ALREADY subtracts this expense's OLD amount,
            // so add it back before comparing against the new amount.
            $realBalance = $account->calculateBalance();

            // AUTO-SET stale total_balance in the accounts table
            if (round((float) $account->total_balance, 2) !== $realBalance) {
                $account->total_balance = $realBalance;
                $account->save();
            }

            $available_balance = $realBalance + $old_amount;

            if ($new_amount > $available_balance) {
                return ['error' => $this->balanceErrorMessage($account, $new_amount, $available_balance)];
            }

            $data['account_id'] = $account->id;
            $data['created_at'] = date("Y-m-d H:i:s", strtotime($data['created_at']));

            $lims_expense_data->update($data);

            // Ledger changed — re-sync the accounts table (no manual +/- patching)
            $account->syncTotalBalance();

            return ['success' => true];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('not_permitted', $result['error'])->withInput();
        }

        return redirect('expenses')->with('message', 'Data updated successfully');
    }

    /* ==========================================================
     |  DELETE — delete first, then re-sync (ledger refunds it)
     +========================================================= */
    public function deleteBySelection(Request $request)
    {
        $expense_id = $request['expenseIdArray'] ?? [];
        $accountIds = [];

        foreach ($expense_id as $id) {
            $lims_expense_data = Expense::find($id);
            if ($lims_expense_data) {
                $accountIds[] = $lims_expense_data->account_id;
                $lims_expense_data->delete();
            }
        }

        // One sync per affected account — deletion refunds the ledger
        foreach (array_unique(array_filter($accountIds)) as $accountId) {
            Account::find($accountId)?->syncTotalBalance();
        }

        return 'Expense deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_expense_data = Expense::find($id);

        if ($lims_expense_data) {
            DB::transaction(function () use ($lims_expense_data) {
                $accountId = $lims_expense_data->account_id;
                $lims_expense_data->delete();

                if ($accountId) {
                    Account::find($accountId)?->syncTotalBalance();
                }
            });
        }

        return redirect('expenses')->with('message', 'Data deleted successfully');
    }

    public function authorizeExpense($id)
    {
        $expense = Expense::findOrFail($id);

        // ✅ Only draft expenses can be authorized
        if ($expense->status !== 'draft') {
            return response()->json(['error' => 'Only draft expenses can be authorized.'], 400);
        }

        try {
            // 🔹 Mark expense as waiting for approval
            $expense->update(['status' => 'waiting_approval']);

            return response()->json(['success' => 'Expense authorized and sent for approval successfully.']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to authorize expense: ' . $e->getMessage()], 500);
        }
    }

    /* ==========================================================
     |  APPROVE — the waiting expense is already reserved in the
     |  ledger calc; verify solvency then sync total_balance
     +========================================================= */
    public function approveExpense($id)
    {
        $expense = Expense::findOrFail($id);

        // ✅ Only waiting_approval expenses can be approved
        if ($expense->status !== 'waiting_approval') {
            return response()->json(['error' => 'Only expenses waiting for approval can be approved.'], 400);
        }

        // ✅ Find associated account
        $account = Account::find($expense->account_id);

        if (!$account) {
            return response()->json(['error' => 'Associated account not found.'], 404);
        }

        DB::beginTransaction();

        try {
            // Ledger-driven safety net: the calc already includes this expense,
            // so if the real balance went negative we cannot approve.
            $realBalance = $account->calculateBalance();
            if ($realBalance < 0) {
                DB::rollBack();
                return response()->json(['error' => 'Insufficient account balance to approve this expense.'], 400);
            }

            // 🔹 Mark expense as approved
            $expense->update(['status' => 'approved']);

            // 🔹 AUTO-SET total_balance to the real ledger value
            $account->syncTotalBalance();

            DB::commit();

            return response()->json(['success' => 'Expense approved successfully. Account balance synced.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to approve expense: ' . $e->getMessage()], 500);
        }
    }

    /* ==========================================================
     |  PRIVATE HELPERS
     ========================================================== */

    /**
     * Human-readable error message for the insufficient balance case.
     * $available = the REAL (ledger) balance used in the check.
     */
    private function balanceErrorMessage(Account $account, float $amount, ?float $available = null): string
    {
        $available = $available ?? $account->calculateBalance();

        return 'Insufficient balance! Account "' . $account->name . '" has an available balance of '
             . number_format($available, 2)
             . ', but the expense amount is ' . number_format($amount, 2) . '.';
    }
}
