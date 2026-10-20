<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryTemplate;
use App\Models\SalaryTemplateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalaryTemplateController extends Controller
{
    /* =====================================================
     |  MONTHLY TEMPLATES
     +==================================================== */
    public function index()
    {
        // if (!Auth::user()->hasPermissionTo('salary-template')) {
        //     return redirect()->back()->with('not_permitted', 'Access denied');
        // }

        $templates = SalaryTemplate::monthly()->with(['allowances', 'deductions'])->latest()->get();
        $employees = Employee::where('is_active', true)->get(['id', 'name']);

        return view('backend.hrm.salary_template.index', compact('templates', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id'   => 'nullable|exists:salary_templates,id',
            'name'          => ['required','string','max:255',
                                Rule::unique('salary_templates')->ignore($validated['template_id'] ?? null)],
            'basic_salary'  => 'required|numeric|min:0',
            'overtime_rate' => 'required|numeric|min:0',
            'description'   => 'nullable|string|max:500',
            'allowances'    => 'nullable|array',
            'allowances.*.name'   => 'nullable|string|max:255',
            'allowances.*.amount' => 'nullable|numeric|min:0',
            'deductions'    => 'nullable|array',
            'deductions.*.name'   => 'nullable|string|max:255',
            'deductions.*.amount' => 'nullable|numeric|min:0',
        ]);

        $template = DB::transaction(function () use ($validated) {
            $template = !empty($validated['template_id'])
                ? SalaryTemplate::monthly()->findOrFail($validated['template_id'])
                : new SalaryTemplate(['type' => SalaryTemplate::TYPE_MONTHLY]);

            $template->fill([
                'user_id'       => Auth::id(),
                'name'          => $validated['name'],
                'basic_salary'  => $validated['basic_salary'],
                'overtime_rate' => $validated['overtime_rate'],
                'description'   => $validated['description'] ?? null,
            ])->save();

            $template->items()->delete();

            foreach (['allowances', 'deductions'] as $group) {
                $type = $group === 'allowances' ? 'allowance' : 'deduction';
                foreach ($validated[$group] ?? [] as $row) {
                    if (trim((string)($row['name'] ?? '')) === '') continue;
                    SalaryTemplateItem::create([
                        'salary_template_id' => $template->id,
                        'name'   => $row['name'],
                        'type'   => $type,
                        'amount' => $row['amount'] ?? 0,
                    ]);
                }
            }

            return $template;
        });

        // ✅ Fresh payload for the JS table row
        $items       = $template->items()->get();
        $allowances  = $items->where('type', 'allowance');
        $deductions  = $items->where('type', 'deduction');

        return response()->json([
            'success' => true,
            'message' => 'Salary template saved successfully',
            'template' => [
                'id'              => $template->id,
                'name'            => $template->name,
                'basic_salary'    => (float) $template->basic_salary,
                'overtime_rate'   => (float) $template->overtime_rate,
                'total_allowance' => (float) $allowances->sum('amount'),
                'total_deduction' => (float) $deductions->sum('amount'),
                'net_salary'      => (float) $template->basic_salary
                                     + (float) $allowances->sum('amount')
                                     - (float) $deductions->sum('amount'),
                'allowance_count' => $allowances->count(),
                'deduction_count' => $deductions->count(),
                'items'           => $items->map(fn ($i) => [
                    'name' => $i->name, 'type' => $i->type, 'amount' => (float) $i->amount,
                ])->values()->all(),
            ],
        ]);
    }

    /* =====================================================
     |  HOURLY TEMPLATES — paid on a DAILY basis
     +==================================================== */
    public function hourly()
    {
        // if (!Auth::user()->hasPermissionTo('salary-template')) {
        //     return redirect()->back()->with('not_permitted', 'Access denied');
        // }

        $templates = SalaryTemplate::hourly()->latest()->get();
        $employees = Employee::where('is_active', true)->get(['id', 'name']);

        return view('backend.hrm.salary_template.hourly', compact('templates', 'employees'));
    }


    public function storeHourly(Request $request)
    {
        $validated = $request->validate([
            'template_id'   => 'nullable|exists:salary_templates,id',
            'name'          => ['required','string','max:255',
                                Rule::unique('salary_templates')->ignore($validated['template_id'] ?? null)],
            'hourly_rate'   => 'required|numeric|min:0',
            'hours_per_day' => 'required|numeric|min:1|max:24',
            'description'   => 'nullable|string|max:500',
        ]);

        $template = SalaryTemplate::updateOrCreate(
            ['id' => $validated['template_id'] ?? null],
            [
                'user_id'       => Auth::id(),
                'name'          => $validated['name'],
                'type'          => SalaryTemplate::TYPE_HOURLY,
                'hourly_rate'   => $validated['hourly_rate'],
                'hours_per_day' => $validated['hours_per_day'],
                'description'   => $validated['description'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Hourly template saved successfully',
            'template' => [
                'id'            => $template->id,
                'name'          => $template->name,
                'hourly_rate'   => (float) $template->hourly_rate,
                'hours_per_day' => (float) $template->hours_per_day,
                'daily_rate'    => (float) $template->daily_rate,
            ],
        ]);
    }

    /* =====================================================
     |  ASSIGN TEMPLATE TO EMPLOYEES
     +==================================================== */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'salary_template_id' => 'required|exists:salary_templates,id',
            'employee_ids'       => 'required|array|min:1',
            'employee_ids.*'     => 'exists:employees,id',
        ]);

        Employee::whereIn('id', $validated['employee_ids'])
            ->update(['salary_template_id' => $validated['salary_template_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Template assigned to employees successfully',
        ]);
    }

    /* =====================================================
     |  DELETE
     +==================================================== */
    public function destroy($id)
    {
        $template = SalaryTemplate::findOrFail($id);

        DB::transaction(function () use ($template) {
            Employee::where('salary_template_id', $template->id)
                    ->update(['salary_template_id' => null]);
            $template->items()->delete();
            $template->delete();
        });

        return redirect()->back()->with('message', 'Template deleted successfully');
    }
}
