<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Models\Biller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use App\Models\Warehouse;

class SaleAgentController extends Controller
{
    // Exclude Super-Admin (id=5) from being assignable
    const EXCLUDED_ROLE_IDS = [5];

    /**
     * Centralized permission gate for "sale-agents" module.
     */
    private function canAccessSaleAgents(): bool
    {
        return Auth::user()->hasPermissionTo('sale-agents');
    }

    public function index()
    {
        if (! $this->canAccessSaleAgents()) {
            return redirect()->back()
                ->with('not_permitted', __('file.Sorry! You are not allowed to access this module'));
        }

        // Aggregate permissions across ALL roles (multi-role aware)
        $all_permission = Auth::user()->getAllPermissionNames();
        if (empty($all_permission)) {
            $all_permission = ['dummy text'];
        }

        $lims_sale_agent_all = Employee::with('user')
            ->where('is_active', true)
            ->where('is_sale_agent', 1)
            ->get();

        $lims_department_list  = Department::where('is_active', true)->get();
        $lims_role_list       = Role::where('is_active', true)
            ->whereNotIn('id', self::EXCLUDED_ROLE_IDS)
            ->get();
        $lims_warehouse_list  = Warehouse::where('is_active', true)->get();
        $lims_biller_list     = Biller::where('is_active', true)->get();
        $lims_shift_list      = Shift::where('is_active', true)->get();
        $lims_designation_list= Designation::where('is_active', true)->get();

        $numberOfEmployee = Employee::where('is_active', true)->count();

        return view('backend.hrm.sale_agent.index', compact(
            'lims_role_list',
            'lims_sale_agent_all',
            'lims_department_list',
            'lims_warehouse_list',
            'lims_biller_list',
            'lims_shift_list',
            'lims_designation_list',
            'all_permission',
            'numberOfEmployee'
        ));
    }

    public function create()
    {
        if (! Auth::user()->hasPermissionTo('sale-agents-add')) {
            return redirect()->back()
                ->with('not_permitted', __('file.Sorry! You are not allowed to access this module'));
        }

        $lims_role_list       = Role::where('is_active', true)
            ->whereNotIn('id', self::EXCLUDED_ROLE_IDS)
            ->get();
        $lims_warehouse_list   = Warehouse::where('is_active', true)->get();
        $lims_biller_list     = Biller::where('is_active', true)->get();
        $lims_department_list = Department::where('is_active', true)->get();

        $numberOfEmployee     = Employee::where('is_active', true)->count();
        $numberOfUserAccount  = User::where('is_active', true)->count();
        $companies            = [];

        return view('backend.hrm.sale_agent.create', compact(
            'lims_role_list',
            'lims_warehouse_list',
            'lims_biller_list',
            'lims_department_list',
            'numberOfEmployee',
            'numberOfUserAccount',
            'companies'
        ));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->except('image');
            $message = 'Sale Agent created successfully';

            $createUser = ! empty($data['user']);

            if ($createUser) {
                $request->validate([
                    'name'     => ['required', 'max:255',
                        Rule::unique('users')->where(fn ($q) => $q->where('is_deleted', false)),
                    ],
                    'email'    => ['required', 'email', 'max:255',
                        Rule::unique('users')->where(fn ($q) => $q->where('is_deleted', false)),
                    ],
                    'password' => ['required', 'min:6'],
                    'role_id'  => ['required', 'exists:roles,id'],
                ]);

                $userData = [
                    'name'         => $data['name'],
                    'email'        => $data['email'],
                    'password'     => bcrypt($data['password']),
                    'phone'        => $data['phone_number'] ?? null,
                    'company_name' => $data['company'] ?? null,
                    'biller_id'     => $data['biller_id'] ?? null,
                    'warehouse_id'  => $data['warehouse_id'] ?? null,
                    'is_active'    => true,
                    'is_deleted'   => false,
                ];

                $user = User::create($userData);

                // Multi-role: attach role(s). Allow array or single id.
                $roleIds = is_array($data['role_id'] ?? null) ? $data['role_id'] : [$data['role_id']];
                $user->roles()->sync($roleIds);

                $data['user_id'] = $user->id;
                $message = 'Employee created successfully and added to user list';
            }

            $request->validate([
                'email' => ['email', 'max:255',
                    Rule::unique('employees')->where(fn ($q) => $q->where('is_active', true)),
                ],
                'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:100000',
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'), 'sale_agent');
            }

            $data['is_active']      = true;
            $data['is_sale_agent']  = 1;

            Employee::create($data);

            return redirect('sale-agents')->with('message', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Sale Agent Store Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('error', 'Something went wrong: '.$e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($request['employee_id']);

        if ($employee->user_id) {
            $request->validate([
                'name'  => ['max:255',
                    Rule::unique('users')->ignore($employee->user_id)
                        ->where(fn ($q) => $q->where('is_deleted', false)),
                ],
                'email' => ['email', 'max:255',
                    Rule::unique('users')->ignore($employee->user_id)
                        ->where(fn ($q) => $q->where('is_deleted', false)),
                ],
            ]);
        }

        $request->validate([
            'email' => ['email', 'max:255',
                Rule::unique('employees')->ignore($employee->id)
                    ->where(fn ($q) => $q->where('is_active', true)),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:100000',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $this->fileDelete(public_path('images/sale_agent/'), $employee->image); // match store path
            $data['image'] = $this->uploadImage($request->file('image'), 'sale_agent');
        }

        $employee->is_sale_agent = 1;
        $employee->update($data);

        // Sync linked user too (multi-role aware)
        if ($employee->user_id && $user = User::find($employee->user_id)) {
            $user->update([
                'name'  => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone_number'] ?? $user->phone,
            ]);

            if (! empty($data['role_id'])) {
                $roleIds = is_array($data['role_id']) ? $data['role_id'] : [$data['role_id']];
                $user->roles()->sync($roleIds);
            }
        }

        return redirect('sale-agents')->with('message', __('file.Employee updated successfully'));
    }

    public function deleteBySelection(Request $request)
    {
        foreach ((array) $request['employeeIdArray'] as $id) {
            $this->deactivateEmployee((int) $id);
        }
        return 'Employee deleted successfully!';
    }

    public function destroy($id)
    {
        $this->deactivateEmployee($id);
        return redirect('sale-agents')->with('not_permitted', __('file.Employee deleted successfully'));
    }

    /**
     * Shared delete/deactivate logic.
     */
    private function deactivateEmployee(int $id): void
    {
        $employee = Employee::find($id);
        if (! $employee) return;

        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                $user->is_deleted = true;
                $user->save();
            }
        }

        $this->fileDelete(public_path('images/sale_agent/'), $employee->image);

        $employee->is_active = false;
        $employee->save();
    }

    /**
     * Image upload helper — tenant-aware.
     */
    private function uploadImage($file, string $folder): string
    {
        $ext       = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $imageName = date("Ymdhis");

        if (! config('database.connections.saleprosaas_landlord')) {
            $imageName = $imageName . '.' . $ext;
        } else {
            $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
        }

        $file->move(public_path("images/{$folder}"), $imageName);
        return $imageName;
    }
}