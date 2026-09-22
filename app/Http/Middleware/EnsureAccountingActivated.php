<?php

namespace App\Http\Middleware;

use App\Models\AccountingConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountingActivated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Handle an incoming request.
     * 
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = AccountingConfig::firstOrCreate(['id' => 1]);
        
        if (!$config->enabled) {
            return redirect()->route('accounting.activation.index');
        }
        
        return $next($request);
    }
}
