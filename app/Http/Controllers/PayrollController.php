<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SalaryTemplate;
use App\Models\Warehouse;
use App\Models\MailSetting;
use App\Mail\PayrollDetails;
use App\Traits\MailInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PayrollController extends Controller
{
    use MailInfo;

    /* =====================================================
     |  MANAGE SALARY — list
     +==================================================== */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('payroll')) {
            return redirect()->back()->with('not_permitted', 'Access denied');
        }

        $general_setting = DB::table('general_settings')->latest()->first();
        $isStaff = Auth::user()->roles->contains(fn($r) => $r->id > 2);

        $payrolls = Payroll::with(['employee', 'salaryTemplate', 'payments'])
            ->when($isStaff && $general_setting->staff_access === 'own',
                fn($q) => $q->where('user_id', Auth::id()))
            ->when($request->filled('month'), function ($q) use ($request) {
                [$y, $m] = explode('-', $request->month);
                $q->where('year', $y)->where('month', $m);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.hrm.payroll.index', [
            'lims_payroll_all'   => $payrolls,
            'general_setting'    => $general_setting,
            'lims_account_list'  => Account::where('is_active', true)->get(),
            'lims_warehouse_list'=> Warehouse::where('is_active', true)->get(),
        ]);
    }

    /* =====================================================
     |  GENERATE PAGE — employees with their assigned templates
     +==================================================== */
    public function generate(Request $request)
    {
        $month       = $request->input('month', now()->format('Y-m'));
        [$year, $mon] = array_map('intval', explode('-', $month));
        $warehouseId = (int) $request->input('warehouse_id', 0);

        $employees = Employee::with(['salaryTemplate.items'])
            ->where('is_active', true)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->orderBy('name')
            ->get();

        $existing = Payroll::where('year', $year)->where('month', $mon)
            ->get()->keyBy('employee_id');

        // Overtime recorded during the month (auto-pulled into monthly payroll)
        $overtimes = Overtime::where('year', $year)->where('month', $mon)
            ->get()->groupBy('employee_id');

        return view('backend.hrm.payroll.generate', [
            'employees'    => $employees,
            'existing'     => $existing,
            'overtimes'    => $overtimes,
            'month'        => $month,
            'year'         => $year,
            'mon'          => $mon,
            'warehouseId'  => $warehouseId,
            'lims_warehouse_list' => Warehouse::where('is_active', true)->get(),
            'lims_account_list'   => Account::where('is_active', true)->get(),
        ]);
    }

    /* =====================================================
     |  BULK GENERATE — server recalculates everything
     +==================================================== */
    public function generateStore(Request $request)
    {
        $request->validate([
            'month'    => 'required|date_format:Y-m',
            'payrolls' => 'required|array|min:1',
        ]);

        [$year, $mon] = array_map('intval', explode('-', $request->month));
        $accountId = $request->input('account_id') ?: Account::where('is_default', true)->value('id');

        $created = 0; $updated = 0;

        DB::transaction(function () use ($request, $year, $mon, $accountId, &$created, &$updated) {
            foreach ($request->input('payrolls', []) as $employeeId => $row) {
                if (empty($row['include'])) continue;

                $employee = Employee::with('salaryTemplate.items')->find($employeeId);
                if (!$employee) continue;

                $template = $employee->salaryTemplate;
                $payload  = $this->buildPayrollPayload($employee, $template, $row, $year, $mon, $accountId);

                $payroll = Payroll::updateOrCreate(
                    ['employee_id' => $employeeId, 'year' => $year, 'month' => $mon],
                    $payload
                );

                if (empty($payroll->reference_no)) {
                    $payroll->reference_no = $this->generateUniqueReferenceNo($year, $mon);
                    $payroll->save();
                }

                $this->syncItems($payroll, $template, $row);
                $payroll->recalculate();
                $payroll->refreshPaymentStatus();

                $payroll->wasRecentlyCreated ? $created++ : $updated++;
            }
        });

        return redirect()->route('payroll.index')
            ->with('message', "Payroll generated — Created: {$created}, Updated: {$updated}");
    }

    /* =====================================================
     |  SINGLE MANUAL PAYROLL (edge cases, one-off payments)
     +==================================================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'month'         => 'required|date_format:Y-m',
            'amount'        => 'required|numeric|min:0',
            'paying_method' => 'required|in:0,1,2',
            'account_id'    => 'nullable|exists:accounts,id',
            'note'          => 'nullable|string',
        ]);

        [$year, $mon] = array_map('intval', explode('-', $validated['month']));

        $payroll = DB::transaction(function () use ($validated, $year, $mon) {
            $employee = Employee::with('salaryTemplate')->findOrFail($validated['employee_id']);

            $payroll = Payroll::updateOrCreate(
                ['employee_id' => $employee->id, 'year' => $year, 'month' => $mon],
                [
                    'user_id'             => Auth::id(),
                    'account_id'          => $validated['account_id'] ?? Account::where('is_default', true)->value('id'),
                    'employee_name'       => $employee->name,
                    'salary_template_id'  => $employee->salary_template_id,
                    'template_type'       => $employee->salaryTemplate?->type ?? SalaryTemplate::TYPE_MONTHLY,
                    'basic_salary'        => $validated['amount'],
                    'date'                => now()->toDateString(),
                    'paying_method'       => $validated['paying_method'],
                    'note'                => $validated['note'] ?? null,
                ]
            );

            if (empty($payroll->reference_no)) {
                $payroll->reference_no = $this->generateUniqueReferenceNo($year, $mon);
                $payroll->save();
            }

            $payroll->recalculate();
            $payroll->refreshPaymentStatus();

            return $payroll;
        });

        $this->sendPayrollMail($payroll);

        return redirect()->route('payroll.index')->with('message', 'Payroll created successfully');
    }

    /* =====================================================
     |  AJAX DETAILS (view modal)
     +==================================================== */
     /* =====================================================
      |  AJAX DETAILS (view modal) — employee, user role,
      |  template snapshot + full payroll breakdown
      +==================================================== */
     public function details($id)
     {
         $payroll = Payroll::with([
                 'items',
                 'payments',
                 'employee.department',
                 'employee.user.roles',   // ← the employee's login account + its roles
                 'salaryTemplate',
             ])->findOrFail($id);

         $employee = $payroll->employee;
         $user     = $employee?->user;

         return response()->json([
             'id'             => $payroll->id,
             'reference_no'   => $payroll->reference_no,
             'template_type'  => $payroll->template_type,
             'year'           => (int) $payroll->year,
             'month'          => (int) $payroll->month,
             'payment_status' => $payroll->payment_status,
             'paying_method'  => (int) $payroll->paying_method,
             'note'           => $payroll->note,

             /* ---- Employee + user role ---- */
             'employee' => $employee ? [
                 'name'       => $employee->name,
                 'email'      => $employee->email,
                 'phone'      => $employee->phone_number,
                 'department' => $employee->department?->name,
                 'roles'      => $user?->roles->pluck('name')->values()->all() ?? [],
             ] : null,

             /* ---- Assigned template ---- */
             'salary_template' => $payroll->salaryTemplate ? [
                 'id'   => $payroll->salaryTemplate->id,
                 'name' => $payroll->salaryTemplate->name,
                 'type' => $payroll->salaryTemplate->type,
             ] : null,

             /* ---- Snapshot breakdown ---- */
             'basic_salary'    => (float) $payroll->basic_salary,
             'overtime_hours'  => (float) $payroll->overtime_hours,
             'overtime_rate'   => (float) $payroll->overtime_rate,
             'overtime_amount' => (float) $payroll->overtime_amount,
             'hourly_rate'     => (float) $payroll->hourly_rate,
             'hours_per_day'   => (float) $payroll->hours_per_day,
             'daily_rate'      => (float) $payroll->daily_rate,
             'days_worked'     => (int)   $payroll->days_worked,
             'gross_amount'    => (float) $payroll->gross_amount,
             'total_allowance' => (float) $payroll->total_allowance,
             'total_deduction' => (float) $payroll->total_deduction,
             'amount'          => (float) $payroll->amount,   // net
             'paid_amount'     => (float) $payroll->paid_amount,

             /* ---- Line items & payments ---- */
             'items'    => $payroll->items->map(fn ($i) => [
                 'name' => $i->name, 'type' => $i->type,
                 'amount' => (float) $i->amount, 'source' => $i->source,
             ]),
             'payments' => $payroll->payments->map(fn ($p) => [
                 'payment_date'  => $p->payment_date?->format('d-m-Y'),
                 'amount'        => (float) $p->amount,
                 'paying_method' => (int) $p->paying_method,
             ]),
         ]);
     }

    /* =====================================================
     |  UPDATE (period / method / note)
     +==================================================== */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'month'         => 'required|date_format:Y-m',
            'paying_method' => 'required|in:0,1,2',
            'note'          => 'nullable|string',
        ]);

        [$year, $mon] = array_map('intval', explode('-', $validated['month']));

        $clash = Payroll::where('employee_id', Payroll::find($id)->employee_id)
            ->where('year', $year)->where('month', $mon)->where('id', '!=', $id)->exists();

        if ($clash) {
            return redirect()->back()->with('not_permitted', 'A payroll already exists for that month.');
        }

        Payroll::findOrFail($id)->update([
            'year' => $year, 'month' => $mon,
            'paying_method' => $validated['paying_method'],
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('payroll.index')->with('message', 'Payroll updated successfully');
    }

    /* =====================================================
     |  DELETE
     +==================================================== */
    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);

        DB::transaction(function () use ($payroll) {
            $payroll->items()->delete();
            $payroll->payments()->delete();
            $payroll->delete();
        });

        return redirect()->back()->with('message', 'Payroll deleted successfully');
    }

    /* =====================================================
     |  PRIVATE HELPERS
     +==================================================== */
    private function buildPayrollPayload($employee, $template, array $row, int $year, int $mon, $accountId): array
    {
        $payload = [
            'user_id'            => Auth::id(),
            'account_id'         => $accountId,
            'employee_name'      => $employee->name,
            'salary_template_id' => $template?->id,
            'template_type'      => $template?->type ?? SalaryTemplate::TYPE_MONTHLY,
            'date'               => now()->toDateString(),
            'year'               => $year,
            'month'              => $mon,
            'paying_method'      => $row['paying_method'] ?? 0,
            'note'               => $row['note'] ?? null,
            'basic_salary' => 0, 'overtime_hours' => 0, 'overtime_rate' => 0, 'overtime_amount' => 0,
            'hourly_rate'  => 0, 'hours_per_day'  => 0, 'daily_rate'    => 0, 'days_worked'    => 0,
        ];

        if ($template && $template->type === SalaryTemplate::TYPE_HOURLY) {
            // HOURLY — paid on a daily basis
            $days      = max(0, (int) ($row['days_worked'] ?? 0));
            $dailyRate = $template->daily_rate; // hourly_rate × hours_per_day

            $payload += [
                'hourly_rate'   => $template->hourly_rate,
                'hours_per_day' => $template->hours_per_day,
                'daily_rate'    => $dailyRate,
                'days_worked'   => $days,
                'gross_amount'  => $dailyRate * $days,
            ];
        } else {
            // MONTHLY
            $payload['basic_salary']   = $template
                ? (float) $template->basic_salary
                : (float) ($row['basic_salary'] ?? $employee->salary ?? 0);
            $payload['overtime_hours'] = max(0, (float) ($row['overtime_hours'] ?? 0));
            $payload['overtime_rate']  = $template
                ? (float) $template->overtime_rate
                : (float) ($row['overtime_rate'] ?? 0);
        }

        return $payload;
    }

    private function syncItems(Payroll $payroll, $template, array $row): void
    {
        $payroll->items()->delete();

        // Snapshot template allowances/deductions (monthly only — hourly has none)
        if ($template && $template->type === SalaryTemplate::TYPE_MONTHLY) {
            foreach ($template->items as $item) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'name'       => $item->name,
                    'type'       => $item->type,
                    'amount'     => $item->amount, // fixed
                    'source'     => 'template',
                ]);
            }

            // Auto-attach recorded overtime (manual input overrides)
            $otHours = isset($row['overtime_hours'])
                ? max(0, (float) $row['overtime_hours'])
                : (float) Overtime::where('employee_id', $payroll->employee_id)
                    ->where('year', $payroll->year)->where('month', $payroll->month)->sum('hours');

            if ($otHours > 0 && (float) $payroll->overtime_rate > 0) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'name'       => 'Overtime',
                    'type'       => 'overtime',
                    'amount'     => $otHours * (float) $payroll->overtime_rate,
                    'source'     => 'overtime',
                ]);
            }
        }

        // Manual ad-hoc items from the form
        foreach ($row['items'] ?? [] as $item) {
            if (trim((string)($item['name'] ?? '')) === '' || !isset($item['amount'])) continue;
            PayrollItem::create([
                'payroll_id' => $payroll->id,
                'name'       => $item['name'],
                'type'       => in_array($item['type'] ?? '', ['allowance', 'deduction']) ? $item['type'] : 'allowance',
                'amount'     => (float) $item['amount'],
                'source'     => 'manual',
            ]);
        }
    }

    private function sendPayrollMail(Payroll $payroll): void
    {
        try {
            $employee = $payroll->employee;
            if (!$employee || !$employee->email) return;

            $mailSetting = MailSetting::latest()->first();
            if (!$mailSetting) return;

            $this->setMailInfo($mailSetting);
            Mail::to($employee->email)->send(new PayrollDetails([
                'reference_no' => $payroll->reference_no,
                'amount'       => $payroll->amount,
                'name'         => $employee->name,
                'email'        => $employee->email,
                'currency'     => config('currency'),
            ]));
        } catch (\Exception $e) {
            // best-effort
        }
    }

    private function generateUniqueReferenceNo(int $year, int $month): string
    {
        $prefix = 'payr-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT);

        do {
            $refNo = $prefix . '-' . strtoupper(Str::random(6));
        } while (Payroll::where('reference_no', $refNo)->exists());

        return $refNo;
    }
}
