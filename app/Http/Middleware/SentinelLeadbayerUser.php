<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class SentinelLeadbayerUser
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
        $admin = Sentinel::findRoleBySlug('leadbayer');

        if (!$user->inRole($admin)) {
            return redirect()->route('home');
        }
        return $next($request);
    }
}
