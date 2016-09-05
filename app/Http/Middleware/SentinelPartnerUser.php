<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class SentinelPartnerUser
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
        $admin = Sentinel::findRoleBySlug('partner');

        if (!$user->inRole($admin)) {
            return redirect()->route('home');
        }
        return $next($request);
    }
}
