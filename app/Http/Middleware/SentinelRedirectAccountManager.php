<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class SentinelRedirectAccountManager
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
            $accountManager = Sentinel::findRoleBySlug('account_manager');

            if ($user->inRole($accountManager)) {
                return redirect()->intended('accountManager/agent/list');
            }
        }
        return $next($request);
    }
}
