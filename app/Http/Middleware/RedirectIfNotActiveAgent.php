<?php

namespace App\Http\Middleware;

use App\Models\AgentInfo;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Closure;

class RedirectIfNotActiveAgent
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

        $agentInfo = AgentInfo::where('agent_id', '=', $user->id)->first();
        if($agentInfo->state == 2) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
