<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class SentinelOperatorUser
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
        $user = Sentinel::getUser();
        $admin = Sentinel::findRoleBySlug('operator');

        if (!$user->inRole($admin)) {
            return redirect()->route('home');
        }
        return $next($request);
    }
}
