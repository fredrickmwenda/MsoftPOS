<?php



namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Cache;
use DB;


class LoginController extends Controller

{



    use AuthenticatesUsers;



    protected $redirectTo = '/dashboard';

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function __construct()

    {

        $this->middleware('guest')->except('logout');

    }

    public function showLoginForm()
    {
        // return DB::table('general_settings')->latest()->first();

        if(isset($_COOKIE['language']))
            \App::setLocale($_COOKIE['language']);
        else
            \App::setLocale('en');
        //getting theme
        if(isset($_COOKIE['theme']))
            $theme = $_COOKIE['theme'];
        else
            $theme = 'light';
        //get general setting value
        $general_setting =  Cache::remember('general_setting', 60*60*24*365, function () {
            return DB::table('general_settings')->latest()->first();
        });

        if(!$general_setting) {
            \DB::unprepared(file_get_contents('public/tenant_necessary.sql'));
            $general_setting =  Cache::remember('general_setting', 60*60*24*365, function () {
                return DB::table('general_settings')->latest()->first();
            });
        }
        $numberOfUserAccount = \App\Models\User::where('is_active', true)->count();
        return view('backend.auth.login', compact('theme', 'general_setting', 'numberOfUserAccount'));
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'password' => 'required',
        ]);

        $fieldType = filter_var($request->name, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $credentials = [$fieldType => $request->name, 'password' => $request->password];

        // Attempt login with an additional condition
        if (auth()->attempt($credentials) && auth()->user()->is_active) {
            setcookie('login_now', 1, time() + (86400 * 1), "/");
            return redirect('/dashboard');
        } else {
            // Logout if somehow authenticated but inactive (for safety)
            if (auth()->check()) {
                auth()->logout();
            }
            return redirect()->route('login')->with('error', 'Invalid credentials or account inactive.');
        }
    }
}
