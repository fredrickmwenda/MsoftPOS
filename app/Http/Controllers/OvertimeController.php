<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('payroll')) {
            return redirect()->back()->with('not_permitted', 'Access denied');
        }

        // All active employees — rate comes from their assigned (monthly) template
        $employees = Employee::where('is_active', true)
            ->with('salaryTemplate')
            ->orderBy('name')
            ->get();

        $overtimes = Overtime::with('employee')->orderBy('date', 'desc')->get();

        return view('backend.hrm.payroll.overtime', compact('employees', 'overtimes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required',
            'hours'       => 'required|numeric|min:0.25',
            'note'        => 'nullable|string|max:500',
        ]);

        $employee = Employee::with('salaryTemplate')->findOrFail($validated['employee_id']);

        // Rate = employee's assigned monthly template overtime_rate
        $rate = (float) ($employee->salaryTemplate?->overtime_rate ?? 0);
        if ($rate <= 0) {
            return redirect()->back()
                ->with('not_permitted', 'This employee has no overtime rate. Assign a monthly salary template first.')
                ->withInput();
        }

        // Parse datetime — supports "d-m-Y h:i A" (picker) and "Y-m-d\TH:i" (native fallback)
        $date = $this->parseDateTime($validated['date']);
        if (!$date) {
            return redirect()->back()->with('not_permitted', 'Invalid date format.')->withInput();
        }

        $hours = (float) $validated['hours'];

        Overtime::create([
            'employee_id'        => $employee->id,
            'salary_template_id' => $employee->salary_template_id,
            'year'               => $date->year,   // derived from date
            'month'              => $date->month,  // derived from date
            'hours'              => $hours,
            'rate'               => $rate,
            'amount'             => round($hours * $rate, 2),
            'date'               => $date->format('Y-m-d H:i:s'),
            'note'               => $validated['note'] ?? null,
        ]);

        return redirect()->route('overtime.index')->with('message', 'Overtime record added successfully');
    }

    public function destroy($id)
    {
        Overtime::findOrFail($id)->delete();
        return redirect()->back()->with('message', 'Overtime deleted successfully');
    }

    /* ---------------- Helper ---------------- */
    private function parseDateTime(string $value): ?Carbon
    {
        $formats = ['d-m-Y h:i A', 'd-m-Y H:i', 'Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);
                if ($parsed !== false) return $parsed;
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
