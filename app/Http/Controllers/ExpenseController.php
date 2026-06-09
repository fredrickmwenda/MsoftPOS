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
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('expenses-index')){
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';

            // Default: today's date for both start and end
            if($request->has('starting_date') && $request->has('ending_date')) {
                $starting_date = $request->starting_date;
                $ending_date = $request->ending_date;
            } else {
                $starting_date = date('Y-m-d');
                $ending_date = date('Y-m-d');
            }

            if($request->input('warehouse_id'))
                $warehouse_id = $request->input('warehouse_id');
            else
                $warehouse_id = 0;

            $lims_warehouse_list = Warehouse::select('name', 'id')->where('is_active', true)->get();
            $lims_account_list = Account::where('is_active', true)->get();
            return view('backend.expense.index', compact('lims_account_list', 'lims_warehouse_list', 'all_permission', 'starting_date', 'ending_date', 'warehouse_id'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

   

    public function expenseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
        );

        $warehouse_id = $request->input('warehouse_id');

        // Get percentage filter from General Setting
        $percentage_filter = GeneralSetting::first()->percentage_filter;
        info('Expense Percentage Filter: ' . $percentage_filter);

        $filtered_expense_ids = [];
        $filtered_total_expenses = 0;

        // ---------- Base query (common filters) ----------
        $baseQuery = Expense::whereDate('created_at', '>=', $request->input('starting_date'))
                            ->whereDate('created_at', '<=', $request->input('ending_date'));

        if (Auth::user()->role_id > 2 && config('staff_access') == 'own')
            $baseQuery = $baseQuery->where('user_id', Auth::id());
        if ($warehouse_id)
            $baseQuery = $baseQuery->where('warehouse_id', $warehouse_id);

        // ---------- Apply percentage filter (top X% by amount) ----------
        if ($percentage_filter !== null && $percentage_filter !== '' && $percentage_filter < 100) {
            $all_expenses = (clone $baseQuery)->orderBy('amount', 'desc')->get(['id', 'amount']);
            $total_value = $all_expenses->sum('amount');
            info('Total Expense Value: ' . $total_value);
            $target_value = $total_value * ($percentage_filter / 100);
            info('Target Value for Top ' . $percentage_filter . '%: ' . $target_value);

            $running = 0;
            foreach ($all_expenses as $expense) {
                $filtered_expense_ids[] = $expense->id;
                $running += $expense->amount;
                $filtered_total_expenses = $running;
                if ($running >= $target_value) break;
            }
            $baseQuery = $baseQuery->whereIn('id', $filtered_expense_ids);
        }

        // ---------- Counts & total expense sum ----------
        $totalData = $baseQuery->count();
        $totalFiltered = $totalData;

        if ($percentage_filter !== null && $percentage_filter !== '' && $percentage_filter < 100) {
            $total_expense_sum = $filtered_total_expenses;
        } else {
            $total_expense_sum = (clone $baseQuery)->sum('amount');
        }

        // ---------- Pagination & ordering ----------
        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'expenses.' . $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        // ---------- Fetch data (two branches) ----------
        if (empty($request->input('search.value'))) {
            $expenses = (clone $baseQuery)
                            ->with('warehouse', 'expenseCategory')
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
        } else {
            $search = $request->input('search.value');
            $searchDate = date('Y-m-d', strtotime(str_replace('/', '-', $search)));

            $searchQuery = Expense::whereDate('expenses.created_at', '=', $searchDate);

            // Re‑apply percentage filter if active
            if (!empty($filtered_expense_ids)) {
                $searchQuery->whereIn('expenses.id', $filtered_expense_ids);
            }

            if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $searchQuery = $searchQuery->where('expenses.user_id', Auth::id())
                    ->orWhere([
                        ['reference_no', 'LIKE', "%{$search}%"],
                        ['user_id', Auth::id()]
                    ]);
                $totalFiltered = (clone $searchQuery)->count();
                $expenses = $searchQuery->select('expenses.*')
                                        ->with('warehouse', 'expenseCategory')
                                        ->offset($start)
                                        ->limit($limit)
                                        ->orderBy($order, $dir)
                                        ->get();
            } else {
                $searchQuery = $searchQuery->orWhere('reference_no', 'LIKE', "%{$search}%");
                $totalFiltered = (clone $searchQuery)->count();
                $expenses = $searchQuery->select('expenses.*')
                                        ->with('warehouse', 'expenseCategory')
                                        ->offset($start)
                                        ->limit($limit)
                                        ->orderBy($order, $dir)
                                        ->get();
            }
        }

        // ---------- Build DataTable response ----------
        $data = [];
        if (!empty($expenses)) {
            foreach ($expenses as $key => $expense) {
                $nestedData = [];
                $nestedData['id'] = $expense->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($expense->created_at->toDateString()));
                $nestedData['reference_no'] = $expense->reference_no;
                $nestedData['warehouse'] = $expense->warehouse->name;
                $nestedData['expenseCategory'] = $expense->expenseCategory->name;
                $nestedData['amount'] = number_format($expense->amount, config('decimal'));
                $nestedData['note'] = $expense->note;
                $nestedData['options'] = '<div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                        . trans("file.action") . '
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">';

                if (in_array("expenses-edit", $request['all_permission'])) {
                    $nestedData['options'] .= '
                        <li>
                            <button type="button" data-id="' . $expense->id . '" 
                                class="open-Editexpense_categoryDialog btn btn-link" 
                                data-toggle="modal" data-target="#editModal">
                                <i class="dripicons-document-edit"></i> ' . trans('file.edit') . '
                            </button>
                        </li>';
                }

                if (in_array("expenses-delete", $request['all_permission'])) {
                    $nestedData['options'] .= \Form::open(["route" => ["expenses.destroy", $expense->id], "method" => "DELETE"]) . '
                        <li>
                            <button type="submit" class="btn btn-link" onclick="return confirmDelete()">
                                <i class="dripicons-trash"></i> ' . trans("file.delete") . '
                            </button>
                        </li>' . \Form::close();
                }

                if ($expense->status === 'draft') {
                    $nestedData['options'] .= '
                        <li>
                            <button type="button" class="btn btn-link authorize-expense" 
                                data-id="' . $expense->id . '">
                                <i class="dripicons-checkmark"></i> Authorize
                            </button>
                        </li>';
                }

                if ($expense->status === 'waiting_approval') {
                    $nestedData['options'] .= '
                        <li>
                            <button type="button" class="btn btn-link approve-expense" 
                                data-id="' . $expense->id . '">
                                <i class="dripicons-thumbs-up"></i> Approve
                            </button>
                        </li>';
                }

                $nestedData['options'] .= '</ul></div>';
                $data[] = $nestedData;
            }
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

   public function store(Request $request)
    {
        $data = $request->all();
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
        return redirect('expenses')->with('message', 'Data inserted successfully');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if ($role->hasPermissionTo('expenses-edit')) {
            $lims_expense_data = Expense::find($id);
            $lims_expense_data->date = date('d-m-Y', strtotime($lims_expense_data->created_at->toDateString()));
            return $lims_expense_data;
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $lims_expense_data = Expense::find($data['expense_id']);
        $data['created_at'] = date("Y-m-d H:i:s", strtotime($data['created_at']));
        $lims_expense_data->update($data);
        return redirect('expenses')->with('message', 'Data updated successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $expense_id = $request['expenseIdArray'];
        foreach ($expense_id as $id) {
            $lims_expense_data = Expense::find($id);
            $lims_expense_data->delete();
        }
        return 'Expense deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_expense_data = Expense::find($id);
        $lims_expense_data->delete();
        return redirect('expenses')->with('not_permitted', 'Data deleted successfully');
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

        // ✅ Check available balance
        if ($account->total_balance < $expense->amount) {
            return response()->json(['error' => 'Insufficient account balance to approve this expense.'], 400);
        }

        // ✅ Start database transaction
        DB::beginTransaction();

        try {
            // 🔹 Deduct the expense amount from the account
            $account->total_balance -= $expense->amount;
            $account->save();

            // 🔹 Mark expense as approved
            $expense->update(['status' => 'approved']);

            DB::commit();

            return response()->json(['success' => 'Expense approved and deducted from account successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to approve expense: ' . $e->getMessage()], 500);
        }
    }

}
