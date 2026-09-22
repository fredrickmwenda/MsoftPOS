<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollTemplate;
use App\Models\PayrollItem;
use App\Models\PayrollTemplateItem;
use App\Models\PayrollPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\PayrollDetails;
use Mail;
use App\Models\MailSetting;
use App\Models\PayrollItemMeta;
use App\Models\Warehouse;

class PayrollController extends Controller
{
    use \App\Traits\MailInfo;

    public function index()
    {
        if (Auth::user()->hasPermissionTo('payroll')) {
            $lims_account_list = Account::where('is_active', true)->get();
            $lims_employee_list = Employee::where('is_active', true)->get();
            $general_setting = DB::table('general_settings')->latest()->first();

            // Determine if the user has a "staff" role (any role ID > 2)
            $isStaff = Auth::user()->roles->contains(function ($role) {
                return $role->id > 2;
            });

            if ($isStaff && $general_setting->staff_access == 'own') {
                $lims_payroll_all = Payroll::with(['items','payments'])->orderBy('id', 'desc')
                    ->where('user_id', Auth::id())
                    ->get();
            } else {
                $lims_payroll_all = Payroll::with(['items','payments'])->orderBy('id', 'desc')->get();
            }

            $payroll_templates = PayrollTemplate::with('items', 'taxes')->get(); // Eager load taxes
            $payroll_items = PayrollTemplateItem::get();
            $lims_tax_list = \App\Models\Tax::all(); // <-- ADD THIS


            $lims_warehouse_list = Warehouse::where('is_active', true)->get();

            return view('backend.hrm.payroll.index', compact(
                'lims_account_list',
                'lims_employee_list',
                'lims_payroll_all',
                'payroll_templates',
                'payroll_items',
                'lims_warehouse_list',
                'lims_tax_list'
            ));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function create()
    {
        $payroll_templates = PayrollTemplate::with('items')->get();
        $payroll_items = PayrollTemplateItem::get();
        $employees = Employee::where('is_active', true)->get();

        return view('backend.payroll.create', compact('payroll_templates', 'payroll_items', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if(isset($data['created_at']))
            $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at'])));
        else
            $data['created_at'] = date("Y-m-d");
        $data['reference_no'] = 'payroll-' . date("Ymd") . '-'. date("his");
        $data['user_id'] = Auth::id();
        $payroll = Payroll::create($data);
        $message = 'Payroll created successfully';

        // Store payroll items if provided
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $itemData = [
                    'payroll_id' => $payroll->id,
                    'payroll_template_item_id' => $item['payroll_template_item_id'] ?? null,
                    'name' => $item['name'] ?? ($item['label'] ?? null),
                    'type' => $item['type'] ?? null,
                    'amount_type' => $item['amount_type'] ?? ($item['amountType'] ?? null),
                    'amount' => $item['amount'] ?? 0,
                    'taxable' => isset($item['taxable']) ? (bool)$item['taxable'] : false,
                    'description' => $item['description'] ?? null,
                    'meta' => $item['meta'] ?? null,
                ];
                $payrollItem = PayrollItem::create($itemData);

                // If meta rows exist for this item, create them
                if (!empty($item['meta']) && is_array($item['meta'])) {
                    foreach ($item['meta'] as $meta) {
                        $metaRow = [
                            'payroll_id' => $payroll->id,
                            'payroll_item_id' => $payrollItem->id,
                            'percentage' => $meta['percentage'] ?? null,
                            'name' => $meta['name'] ?? null,
                            'type' => $meta['type'] ?? null,
                            'amount_type' => $meta['amount_type'] ?? null,
                            'amount' => $meta['amount'] ?? null,
                        ];
                        \App\Models\PayrollItemMeta::create($metaRow);
                    }
                }
            }
        }
        //collecting mail data
        $lims_employee_data = Employee::find($data['employee_id']);
        $mail_data['reference_no'] = $data['reference_no'];
        $mail_data['amount'] = $data['amount'];
        $mail_data['name'] = $lims_employee_data->name;
        $mail_data['email'] = $lims_employee_data->email;
        $mail_data['currency'] = config('currency');
        $mail_setting = MailSetting::latest()->first();
        if($mail_setting) {
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new PayrollDetails($mail_data));
            }
            catch(\Exception $e){
                $message = ' Payroll created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('payroll')->with('message', $message);
    }

    public function edit($id)
    {
        if (Auth::user()->hasPermissionTo('payroll-edit')) {
            $lims_payroll_data = Payroll::with(['items','payments'])->findOrFail($id);
            $payroll_templates = PayrollTemplate::with('items')->get();
            $payroll_items = PayrollTemplateItem::get();
            $employees = Employee::where('is_active', true)->get();
            return view('backend.payroll.edit', compact('lims_payroll_data','payroll_templates','payroll_items','employees'));
        }
        return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        if(isset($data['created_at']))
            $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at'])));
        else
            $data['created_at'] = date("Y-m-d");
        $lims_payroll_data = Payroll::find($data['payroll_id']);
        $lims_payroll_data->update($data);

        // Remove existing items and meta, then recreate from payload
        $lims_payroll_data->items()->delete();
        \App\Models\PayrollItemMeta::where('payroll_id', $lims_payroll_data->id)->delete();

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $itemData = [
                    'payroll_id' => $lims_payroll_data->id,
                    'payroll_template_item_id' => $item['payroll_template_item_id'] ?? null,
                    'name' => $item['name'] ?? ($item['label'] ?? null),
                    'type' => $item['type'] ?? null,
                    'amount_type' => $item['amount_type'] ?? ($item['amountType'] ?? null),
                    'amount' => $item['amount'] ?? 0,
                    'taxable' => isset($item['taxable']) ? (bool)$item['taxable'] : false,
                    'description' => $item['description'] ?? null,
                    'meta' => $item['meta'] ?? null,
                ];
                $payrollItem = PayrollItem::create($itemData);

                if (!empty($item['meta']) && is_array($item['meta'])) {
                    foreach ($item['meta'] as $meta) {
                        $metaRow = [
                            'payroll_id' => $lims_payroll_data->id,
                            'payroll_item_id' => $payrollItem->id,
                            'percentage' => $meta['percentage'] ?? null,
                            'name' => $meta['name'] ?? null,
                            'type' => $meta['type'] ?? null,
                            'amount_type' => $meta['amount_type'] ?? null,
                            'amount' => $meta['amount'] ?? null,
                        ];
                        \App\Models\PayrollItemMeta::create($metaRow);
                    }
                }
            }
        }

        return redirect('payroll')->with('message', 'Payroll updated succesfully');
    }

    public function deleteBySelection(Request $request)
    {
        $payroll_id = $request['payrollIdArray'];
        foreach ($payroll_id as $id) {
            $lims_payroll_data = Payroll::find($id);
            if ($lims_payroll_data) {
                $lims_payroll_data->items()->delete();
                \App\Models\PayrollItemMeta::where('payroll_id', $id)->delete();
                PayrollPayment::where('payroll_id', $id)->delete();
                $lims_payroll_data->delete();
            }
        }
        return 'Payroll deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_payroll_data = Payroll::find($id);
        if ($lims_payroll_data) {
            $lims_payroll_data->items()->delete();
            PayrollItemMeta::where('payroll_id', $id)->delete();
            PayrollPayment::where('payroll_id', $id)->delete();
            $lims_payroll_data->delete();
        }
        return redirect('payroll')->with('not_permitted', 'Payroll deleted succesfully');
    }

        public function generateCards(Request $request)
    {
        $data = $request->all();
        $employee_ids = $data['employee_ids'];
        $month = $data['month'];
        $warehouse_id = $data['warehouse_id'];

        // Get default account
        $default_account = Account::where('is_default', true)->first();
        $account_id = $default_account ? $default_account->id : null;

        foreach ($employee_ids as $employee_id) {
            $payroll_data = [
                'reference_no' => 'payroll-' . date("Ymd") . '-' . date("his") . '-' . $employee_id,
                'user_id' => Auth::id(),
                'employee_id' => $employee_id,
                'account_id' => $account_id,
                'month' => $month,
                'amount' => 0, // Default amount, can be updated later
                'paying_method' => 1, // Default to Cash (1)
                'created_at' => date("Y-m-d"),
            ];

            Payroll::create($payroll_data);
        }

        return redirect('payroll')->with('message', 'Payroll cards generated successfully!');
    }


        public function getEmployeesByWarehouse(Request $request)
    {
        $warehouse_id = $request->input('warehouse_id');
        
        // If warehouse_id is 0 or null, fetch all active employees
        if (!$warehouse_id || $warehouse_id == 0) {
            $employees = Employee::where('is_active', true)->get(['id', 'name']);
        } else {
            // Fetch employees belonging to the specific warehouse
            $employees = Employee::where('is_active', true)
                ->where('warehouse_id', $warehouse_id)
                ->get(['id', 'name']);
        }

        return response()->json($employees);
    }


        public function monthlyData(Request $request)
    {
        $employee_id = $request->input('employee_id');
        $month = $request->input('month'); // Format: YYYY-MM

        $employee = Employee::find($employee_id);
        
        if (!$employee) {
            return response()->json(['salary' => 0, 'transactions' => 0, 'commission' => 0]);
        }

        // Get the employee's basic salary. 
        // (Assuming your Employee model has a 'salary' or 'basic_salary' column)
        $salary = $employee->salary ?? $employee->basic_salary ?? 0;

        // Calculate previous transactions (loans/advances) for this employee
        // You can expand this logic to query your Expense/Loan models if needed
        $transactions = 0; 

        // Calculate Sale Commission for the selected month
        // You can expand this logic to calculate commission based on the employee's sales
        $commission = 0; 

        return response()->json([
            'salary' => $salary,
            'transactions' => $transactions,
            'commission' => $commission
        ]);
    }


    public function storeTemplate(Request $request)
    {
        $data = $request->all();
        
        // Create the Template
        $template = PayrollTemplate::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        // Create the Items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                PayrollTemplateItem::create([
                    'payroll_template_id' => $template->id,
                    'name' => $item['name'],
                    'type' => $item['type'] ?? 'allowance',
                    'amount_type' => $item['amount_type'] ?? 'fixed',
                    'amount' => $item['amount'] ?? 0,
                    'taxable' => isset($item['taxable']) ? 1 : 0,
                    'description' => $item['description'] ?? null,
                ]);
            }
        }

        // SYNC MULTI-TAXES <-- ADD THIS BLOCK
        if (isset($data['tax_ids']) && is_array($data['tax_ids'])) {
            $template->taxes()->sync($data['tax_ids']);
        }

        // Load the taxes relationship to return to the frontend
        $template->load('taxes');

        return response()->json([
            'success' => true,
            'id' => $template->id,
            'name' => $template->name
        ]);
    }



}