<?php

namespace App\Http\Middleware;

use Closure;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class SentinelRedirectSalesman
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Sentinel::check()) {
            $user = Sentinel::getUser();
            $salesman = Sentinel::findRoleBySlug('salesman');

            if ($user->inRole($salesman)) {
                return redirect()->intended('agent/lead/obtain');
            }
        }
        return $next($request);
    }
}
