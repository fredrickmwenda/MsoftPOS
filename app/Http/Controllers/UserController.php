<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;
use App\Models\Biller;
use App\Models\Warehouse;
use App\Models\CustomerGroup;
use App\Models\Customer;
use DB;
use Auth;
use Hash;
use Keygen;
use Illuminate\Validation\Rule;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Mail\UserDetails;
use Mail;
use App\Models\MailSetting;

class UserController extends Controller
{
    use \App\Traits\MailInfo;

    public function index()
    {
        
        if(Auth::user()->hasPermissionTo('users-index')){
            // Get all permission names from all assigned roles
            $all_permission = Auth::user()->getAllPermissions();

            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            $lims_user_list = User::where('is_deleted', false)->get();
            $numberOfUserAccount = User::where('is_active', true)->count();
            return view('backend.user.index', compact('lims_user_list', 'all_permission', 'numberOfUserAccount'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        if(Auth::user()->hasPermissionTo('users-add')){
            $lims_role_list = Roles::where('is_active', true)->get();
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_customer_group_list = CustomerGroup::where('is_active', true)->get();
            $numberOfUserAccount = User::where('is_active', true)->count();
            return view('backend.user.create', compact('lims_role_list', 'lims_biller_list', 'lims_warehouse_list', 'lims_customer_group_list', 'numberOfUserAccount'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function generatePassword()
    {
        $id = Keygen::numeric(6)->generate();
        return $id;
    }

   
    public function store(Request $request)
    {
        //dd($request->all());
        // 1. Basic validation
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'email' => [
                'email',
                'max:255',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'roles' => 'required|array|min:1',               // <-- multi-role
            'roles.*' => 'exists:roles,id',                  // adjust table name if needed
        ]);

        // 2. If customer role (id=5) is selected, validate customer fields
        if (in_array(5, $request->roles)) {
            $this->validate($request, [
                'phone_number' => [
                    'max:255',
                    Rule::unique('customers')->where(function ($query) {
                        return $query->where('is_active', 1);
                    }),
                ],
            ]);
        }

        $data = $request->all();
        $message = 'User created successfully';

        // Mail logic (unchanged)
        $mail_setting = MailSetting::latest()->first();
        if ($mail_setting) {
            $this->setMailInfo($mail_setting);
            try {
                Mail::to($data['email'])->send(new UserDetails($data));
            } catch (\Exception $e) {
                $message = 'User created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }

        // Build user data (no role_id)
        $userData = $request->only(['name', 'email', 'phone_number']);
        $userData['is_active'] = $request->has('is_active') ? true : false;
        $userData['is_deleted'] = false;
        $userData['password'] = bcrypt($request->password);
        $userData['phone'] = $request->phone_number;
        if($request->has('company_name')) {
            $userData['company_name'] = $request->company_name;
        }

        $user = User::create($userData);

        // Attach the selected roles
        $user->roles()->attach($request->roles);

        // Handle customer creation if role 5 is present
        if (in_array(5, $request->roles)) {
            Customer::create([
                'name'         => $request->customer_name ?? $user->name,
                'phone_number' => $user->phone,
                'email'        => $user->email,
                'user_id'      => $user->id,
                'is_active'    => true,
            ]);
        }

        return redirect('user')->with('message1', $message);
    }

    public function edit($id)
    {
        if(Auth::user()->hasPermissionTo('users-edit')){
            $lims_user_data = User::find($id);
            $lims_role_list = Roles::where('is_active', true)->get();
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            return view('backend.user.edit', compact('lims_user_data', 'lims_role_list', 'lims_biller_list', 'lims_warehouse_list'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

 

    public function update(Request $request, $id)
    {
        if (!env('USER_VERIFIED'))
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');

        // Validate
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('users')->ignore($id)->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'email' => [
                'email',
                'max:255',
                Rule::unique('users')->ignore($id)->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::findOrFail($id);
        $input = $request->except('password', 'roles'); // roles handled separately
        if (!isset($input['is_active']))
            $input['is_active'] = false;
        if (!empty($request->password))
            $input['password'] = bcrypt($request->password);

        $user->update($input);

        // Sync roles
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);

            // Customer logic
            if (in_array(5, $request->roles)) {
                Customer::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name'         => $request->customer_name ?? $user->name,
                        'phone_number' => $request->phone_number ?? $user->phone,
                        'email'        => $user->email,
                        'is_active'    => $input['is_active'] ?? true,
                    ]
                );
            } else {
                // Optionally delete or deactivate customer if role removed
                Customer::where('user_id', $user->id)->delete();
            }
        }

        // If you had cached something related to role, you can forget it
        // cache()->forget('user_role'); // adjust if needed

        return redirect('user')->with('message2', 'Data updated successfully');
    }

    public function superadminProfile($id)
    {
        $lims_user_data = User::find($id);
        return view('landlord.profile', compact('lims_user_data'));
    }

    public function profile($id)
    {
        $lims_user_data = User::find($id);
        return view('backend.user.profile', compact('lims_user_data'));
    }

    public function profileUpdate(Request $request, $id)
    {
        if(!env('USER_VERIFIED'))
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');

        $input = $request->all();
        $lims_user_data = User::find($id);
        $lims_user_data->update($input);
        return redirect()->back()->with('message3', 'Data updated successfullly');
    }

    public function changePassword(Request $request, $id)
    {
        if(!env('USER_VERIFIED'))
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');

        $input = $request->all();
        $lims_user_data = User::find($id);
        if($input['new_pass'] != $input['confirm_pass'])
            return redirect("user/" .  "profile/" . $id )->with('message2', "Please Confirm your new password");

        if (Hash::check($input['current_pass'], $lims_user_data->password)) {
            $lims_user_data->password = bcrypt($input['new_pass']);
            $lims_user_data->save();
        }
        else {
            return redirect("user/" .  "profile/" . $id )->with('message1', "Current Password doesn't match");
        }
        auth()->logout();
        return redirect('/');
    }

    public function deleteBySelection(Request $request)
    {
        $user_id = $request['userIdArray'];
        foreach ($user_id as $id) {
            $lims_user_data = User::find($id);
            $lims_user_data->is_deleted = true;
            $lims_user_data->is_active = false;
            $lims_user_data->save();
        }
        return 'User deleted successfully!';
    }

    public function destroy($id)
    {
        if(!env('USER_VERIFIED'))
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');

        $lims_user_data = User::find($id);
        $lims_user_data->is_deleted = true;
        $lims_user_data->is_active = false;
        $lims_user_data->save();
        if(Auth::id() == $id){
            auth()->logout();
            return redirect('/login');
        }
        else
            return redirect('user')->with('message3', 'Data deleted successfullly');
    }

    public function notificationUsers()
    {
        $notification_users = User::where('is_active', true)
            ->where('id', '!=', \Auth::user()->id)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('id', 5);
            })->get();
        
        $html = '';
        foreach($notification_users as $user){
            $html .='<option value="'.$user->id.'">'.$user->name . ' (' . $user->email. ')'.'</option>';
        }

        return response()->json($html);
    }

    public function allUsers()
    {
        $lims_user_list = DB::table('users')->where('is_active', true)->get();
        
        $html = '';
        foreach($lims_user_list as $user){
            $html .='<option value="'.$user->id.'">'.$user->name . ' (' . $user->phone. ')'.'</option>';
        }

        return response()->json($html);
    }
}
