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
                if($user->hasAccess(['salesman.lead.obtain'])) {
                    return redirect()->intended('salesman/lead/obtain');
                } else {
                    return redirect()->intended('salesman/lead/depostited');
                }
            }
        }
        return $next($request);
    }
}
