<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Active
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    // App\Http\Middleware\Active.php
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isActive()) {
            return $next($request);
        }

        // If logged in but inactive, log out and redirect to login with error
        if (Auth::check()) {
            Auth::logout();
        }

        return redirect('/login')->with('error', 'Your account is inactive. Please contact support.');
    }
}
