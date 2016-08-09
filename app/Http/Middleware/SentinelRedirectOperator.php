<?php

namespace App\Http\Middleware;

use Closure;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;


class SentinelRedirectOperator
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
            $salesman = Sentinel::findRoleBySlug('operator');

            if ($user->inRole($salesman)) {
                return redirect()->intended('callcenter/sphere');
            }
        }
        return $next($request);
    }
}
