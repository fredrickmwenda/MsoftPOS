<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class CustomerAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('backend.auth.customer-login');
    }

public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

   
    if (Auth::guard('customer')->attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('customer.dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
}
 public function showRegistrationForm()
    {
        return view('backend.auth.customer-register');
    }

   public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
            'phone_number' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'customer_group_id' => 1, // Default group, adjust as necessary
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }   

public function logout(Request $request)
{
    // Logout from the customer guard
    Auth::guard('customer')->logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('customer.login');
}
}
