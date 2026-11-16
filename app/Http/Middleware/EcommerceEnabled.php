<?php

namespace App\Http\Middleware;

use App\Models\GeneralSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EcommerceEnabled
{
    public function handle(Request $request, Closure $next)
       {
           // Pull from the same cache key your CacheForget trait uses
           $setting = cache()->rememberForever('general_setting', function () {
               return GeneralSetting::latest()->first();
           });

           // Ecommerce off → nobody browses the shop front; go to admin login
           if ($setting && !($setting->ecommerce_enabled ?? false)) {
               // Don't redirect if already heading to the login page (avoid redirect loop)
               if (!$request->routeIs('login')) {
                   return redirect()->route('login');
               }
           }

           return $next($request);
       }
}
