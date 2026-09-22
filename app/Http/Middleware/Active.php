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

        if (Auth::check()) {
            Auth::logout();
        }

        return redirect()->route('login')
            ->with('error', 'Your account is inactive. Please contact support.');
    }
}
