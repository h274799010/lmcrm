<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class SentinelRedirectAgent
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
            $agent = Sentinel::findRoleBySlug('agent');

            if ($user->inRole($agent)) {
                if($user->hasAccess(['agent.lead.obtain'])) {
                    return redirect()->intended('agent/lead/obtain');
                } else {
                    return redirect()->intended('agent/lead/depostited');
                }
            }
        }
        return $next($request);
    }
}
