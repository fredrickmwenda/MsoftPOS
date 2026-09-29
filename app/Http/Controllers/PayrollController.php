<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollTemplate;
use App\Models\PayrollItem;
use App\Models\PayrollItemMeta;
use App\Models\PayrollPayment;
use App\Models\Warehouse;
use App\Mail\PayrollDetails;
use App\Models\MailSetting;
use App\Traits\MailInfo;

class PayrollController extends Controller
{
    use MailInfo; 

    /* =========================================================
     |  INDEX — List all payrolls
     +======================================================== */
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('payroll')) {
            return redirect()->back()->with('not_permitted', 'Access denied');
        }

        $general_setting = DB::table('general_settings')->latest()->first();
        $isStaff = Auth::user()->roles->contains(fn($r) => $r->id > 2);

        $lims_payroll_all = Payroll::with(['employee', 'account', 'items', 'payments'])
            ->when($isStaff && $general_setting->staff_access === 'own', fn($q) => $q->where('user_id', Auth::id()))
            ->orderBy('id', 'desc')
            ->get();

        $data = [
            'lims_account_list'    => Account::where('is_active', true)->get(),
            'lims_employee_list'   => Employee::where('is_active', true)->get(),
            'lims_warehouse_list'  => Warehouse::where('is_active', true)->get(),
            'payroll_templates'    => PayrollTemplate::with('taxes')->get(),
            'lims_tax_list'        => \App\Models\Tax::all(),
            'lims_payroll_all'     => $lims_payroll_all,
            'general_setting'      => $general_setting,
        ];

        return view('backend.hrm.payroll.index', $data);
    }

    /* =========================================================
     |  STORE — Create single Payroll (with items + meta + payment)
     +======================================================== */
    public function store(Request $request)
    {
        $data = $this->normalizeRequest($request);

        return DB::transaction(function () use ($data, $request) {
            // 1) Create payroll
            $payroll = Payroll::create($data);

            // 2) Snapshot items from template OR manual items
            $this->syncItems($payroll, $request);

            // 3) Recalculate totals
            $payroll->recalculate();

            // 4) Record initial payment (if amount > 0)
            if ((float) $payroll->amount > 0) {
                PayrollPayment::create([
                    'payroll_id'     => $payroll->id,
                    'user_id'        => Auth::id(),
                    'account_id'     => $data['account_id'] ?? null,
                    'amount'         => $payroll->amount,
                    'payment_method' => $data['paying_method'] ?? 0,
                    'payment_date'   => $data['date'] ?? now(),
                    'note'           => 'Initial payroll payment',
                ]);
            }

            // 5) Mail (non-blocking)
            $this->sendPayrollMail($payroll, $data);

            return redirect('payroll')->with('message', 'Payroll created successfully');
        });
    }

    /* =========================================================
     |  UPDATE
     +======================================================== */
    public function update(Request $request, $id)
    {
        $data = $this->normalizeRequest($request);

        return DB::transaction(function () use ($id, $data, $request) {
            $payroll = Payroll::findOrFail($data['payroll_id']);
            $payroll->update($data);

            $this->syncItems($payroll, $request);
            $payroll->recalculate();

            return redirect('payroll')->with('message', 'Payroll updated successfully');
        });
    }

    /* =========================================================
     |  GENERATE CARDS — Bulk generate from template
     +======================================================== */
    public function generateCards(Request $request)
    {
        $data       = $request->all();
        $employeeIds = $data['employee_ids'] ?? [];
        $templateId = $data['payroll_template_id'] ?? null;

        // Parse period
        $rawMonth  = $data['month'];
        $timestamp = strtotime($rawMonth . '-01');
        $date      = date('Y-m-d', $timestamp);
        $year      = (int) date('Y', $timestamp);
        $month     = (int) date('n', $timestamp);

        $account = Account::where('is_default', true)->first();
        $accountId = $account?->id;

        $template = $templateId ? PayrollTemplate::find($templateId) : null;
        $templateItems = $template ? $template->getItemsCollection() : collect();

        $created = 0;
        $updated = 0;

        DB::transaction(function () use (
            $employeeIds, $templateId, $template, $templateItems,
            $date, $year, $month, $accountId, &$created, &$updated
        ) {
            foreach ($employeeIds as $employeeId) {
                $employee = Employee::find($employeeId);
                if (!$employee) continue;

                // Calculate from template
                $amount = 0;
                foreach ($templateItems as $item) {
                    $amt = (float) ($item['amount'] ?? 0);
                    $amount += (($item['type'] ?? 'allowance') === 'allowance') ? $amt : -$amt;
                }
                $amount += $template ? (float) $template->amount_per_duration * (float) $template->work_duration : 0;

                $payload = [
                    'user_id'             => Auth::id(),
                    'account_id'          => $accountId,
                    'payroll_template_id' => $templateId,
                    'employee_name'       => $employee->name,
                    'work_duration'       => $template?->work_duration ?? 1,
                    'duration_unit'       => $template?->duration_unit ?? 'day',
                    'amount_per_duration' => $template?->amount_per_duration ?? 0,
                    'total_duration_amount' => $template ? (float) $template->amount_per_duration * (float) $template->work_duration : 0,
                    'gross_amount'        => $amount,
                    'amount'              => $amount,
                    'date'                => $date,
                    'year'                => $year,
                    'month'               => $month,
                    'paying_method'       => 0,
                    'created_at'          => now(),
                ];

                // updateOrCreate prevents duplicates for same employee+month+year
                $payroll = Payroll::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'year'        => $year,
                        'month'       => $month,
                    ],
                    $payload
                );

                // New — assign pr-{unique} only on first creation
                if ($payroll->wasRecentlyCreated || empty($payroll->reference_no)) {
                    $payroll->reference_no = $this->generateUniqueReferenceNo($year, $month);
                    $payroll->save();
                }

                // Sync template items into payroll_items (snapshot pattern)
                $payroll->items()->delete();
                PayrollItemMeta::where('payroll_id', $payroll->id)->delete();

                foreach ($templateItems as $itemArray) {
                    $payrollItem = PayrollItem::create([
                        'payroll_id'   => $payroll->id,
                        'name'         => $itemArray['name'] ?? 'Unnamed',
                        'type'         => $itemArray['type'] ?? 'allowance',
                        'amount_type'   => $itemArray['amount_type'] ?? 'fixed',
                        'amount'       => $itemArray['amount'] ?? 0,
                        'taxable'      => $itemArray['taxable'] ?? false,
                        'description'  => $itemArray['description'] ?? null,
                        'source'       => 'template',
                    ]);
                }

                $payroll->wasRecentlyCreated ? $created++ : $updated++;
            }
        });

        return redirect('payroll')->with(
            'message',
            "Payroll cards generated — Created: {$created}, Updated: {$updated}"
        );
    }

    /* =========================================================
     |  STORE TEMPLATE — Save template with JSON items + taxes
     +======================================================== */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'work_duration'        => 'nullable|numeric',
            'duration_unit'        => 'nullable|in:hour,day,week,month',
            'amount_per_duration'  => 'nullable|numeric',
            'is_default'           => 'nullable|boolean',
            'items'                => 'nullable|array',
            'items.*.name'        => 'required_with:items|string',
            'items.*.type'        => 'nullable|in:allowance,deduction',
            'items.*.amount_type'  => 'nullable|in:fixed,percentage',
            'items.*.amount'      => 'nullable|numeric',
            'items.*.taxable'     => 'nullable|boolean',
            'items.*.description' => 'nullable|string',
            'tax_ids'              => 'nullable|array',
            'tax_ids.*'            => 'exists:taxes,id',
        ]);

        return DB::transaction(function () use ($validated) {
            // Handle is_default — only one default allowed
            if (!empty($validated['is_default'])) {
                PayrollTemplate::where('is_default', true)->update(['is_default' => false]);
            }

            $template = PayrollTemplate::create([
                'name'                 => $validated['name'],
                'description'          => $validated['description'] ?? null,
                'work_duration'        => $validated['work_duration'] ?? 1,
                'duration_unit'        => $validated['duration_unit'] ?? 'day',
                'amount_per_duration'  => $validated['amount_per_duration'] ?? 0,
                'items'                => $validated['items'] ?? [],
                'is_default'           => $validated['is_default'] ?? false,
            ]);

            if (!empty($validated['tax_ids'])) {
                $template->taxes()->sync($validated['tax_ids']);
            }

            $template->load('taxes');

            return response()->json([
                'success' => true,
                'id'      => $template->id,
                'name'    => $template->name,
            ]);
        });
    }

    /* =========================================================
     |  DELETE
     +======================================================== */
    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);
        DB::transaction(function () use ($payroll) {
            $payroll->items()->delete();
            $payroll->metas()->delete();
            $payroll->payments()->delete();
            $payroll->delete();
        });

        return redirect('payroll')->with('message', 'Payroll deleted successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $ids = $request['payrollIdArray'] ?? [];
        foreach ($ids as $id) {
            $this->destroy($id);
        }
        return 'Payrolls deleted successfully';
    }

    /* =========================================================
     |  AJAX — Get employees by warehouse
     +======================================================== */
    public function getEmployeesByWarehouse(Request $request)
    {
        $wid = $request->input('warehouse_id');
        $employees = Employee::where('is_active', true)
            ->when($wid && $wid != 0, fn($q) => $q->where('warehouse_id', $wid))
            ->get(['id', 'name']);

        return response()->json($employees);
    }

    /* =========================================================
     |  AJAX — Get employee salary data
     +======================================================== */
    public function monthlyData(Request $request)
    {
        $employee = Employee::find($request->input('employee_id'));
        if (!$employee) {
            return response()->json(['salary' => 0, 'transactions' => 0, 'commission' => 0]);
        }

        return response()->json([
            'salary'       => $employee->salary ?? $employee->basic_salary ?? 0,
            'transactions' => 0,    // TODO: query previous loans/advances
            'commission'   => 0,    // TODO: calculate from sales
        ]);
    }

    /* =========================================================
     |  PRIVATE HELPERS
     +======================================================== */

    /**
     * Normalize incoming request: dates, period splitting, defaults.
     */
    private function normalizeRequest(Request $request): array
    {
        $data = $request->all();

        // Date
        $data['date'] = isset($data['created_at'])
            ? date('Y-m-d', strtotime(str_replace('/', '-', $data['created_at'])))
            : date('Y-m-d');

        // Split month input "YYYY-MM" into year/month/date
        if (!empty($data['month'])) {
            $ts = strtotime($data['month'] . '-01');
            $data['date']  = date('Y-m-d', $ts);
            $data['year']  = (int) date('Y', $ts);
            $data['month'] = (int) date('n', $ts);
        }

        // Reference number
        $data['reference_no'] ??= 'payroll-' . date('Ymd') . '-' . date('his');
        $data['user_id']      ??= Auth::id();

        return $data;
    }

    /**
     * Sync payroll items + meta from request payload.
     * If a template is selected, optionally clone template items too.
     */
    private function syncItems(Payroll $payroll, Request $request): void
    {
        $data = $request->all();

        // Wipe existing items + meta (full sync)
        $payroll->items()->delete();
        PayrollItemMeta::where('payroll_id', $payroll->id)->delete();

        // 1) If a template was selected, snapshot template items first
        if (!empty($data['payroll_template_id'])) {
            $template = PayrollTemplate::find($data['payroll_template_id']);
            if ($template) {
                foreach ($template->getItemsCollection() as $tplItem) {
                    $payrollItem = PayrollItem::create([
                        'payroll_id'   => $payroll->id,
                        'name'         => $tplItem['name'] ?? 'Template item',
                        'type'         => $tplItem['type'] ?? 'allowance',
                        'amount_type'   => $tplItem['amount_type'] ?? 'fixed',
                        'amount'       => $tplItem['amount'] ?? 0,
                        'taxable'      => $tplItem['taxable'] ?? false,
                        'description'  => $tplItem['description'] ?? null,
                        'source'       => 'template',
                    ]);
                }
            }
        }

        // 2) Add manual items from the form (overtime, bonus, etc.)
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $payrollItem = PayrollItem::create([
                    'payroll_id'   => $payroll->id,
                    'name'         => $item['name'] ?? ($item['label'] ?? null),
                    'type'         => $item['type'] ?? 'allowance',
                    'amount_type'   => $item['amount_type'] ?? ($item['amountType'] ?? 'fixed'),
                    'amount'       => $item['amount'] ?? 0,
                    'taxable'      => !empty($item['taxable']),
                    'description'  => $item['description'] ?? null,
                    'meta'         => $item['meta'] ?? null,
                    'source'       => 'manual',
                ]);

                // Sub-meta (e.g., tax bracket tiers)
                if (!empty($item['meta']) && is_array($item['meta'])) {
                    foreach ($item['meta'] as $meta) {
                        PayrollItemMeta::create([
                            'payroll_id'      => $payroll->id,
                            'payroll_item_id' => $payrollItem->id,
                            'name'            => $meta['name'] ?? null,
                            'type'            => $meta['type'] ?? 'tax',
                            'amount_type'     => $meta['amount_type'] ?? 'percentage',
                            'percentage'      => $meta['percentage'] ?? null,
                            'amount'          => $meta['amount'] ?? 0,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Send payroll email (silently fails if mail not configured).
     */
    private function sendPayrollMail(Payroll $payroll, array $data): void
    {
        try {
            $employee = Employee::find($payroll->employee_id);
            if (!$employee) return;

            $mailData = [
                'reference_no' => $payroll->reference_no,
                'amount'       => $payroll->amount,
                'name'          => $employee->name,
                'email'         => $employee->email,
                'currency'      => config('currency'),
            ];

            $mailSetting = MailSetting::latest()->first();
            if ($mailSetting) {
                $this->setMailInfo($mailSetting);
                Mail::to($mailData['email'])->send(new PayrollDetails($mailData));
            }
        } catch (\Exception $e) {
            // Silent fail — payroll is saved, mail is best-effort
        }
    }


    private function generateUniqueReferenceNo(int $year, int $month): string
    {
        $prefix = 'pr-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT);

        do {
            $code   = strtoupper(Str::random(6));
            $refNo  = $prefix . '-' . $code;
            $exists = Payroll::where('reference_no', $refNo)->exists();
        } while ($exists);

        return $refNo;
    }
}