<?php

namespace App\Http\Middleware;

use App\Models\AgentInfo;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Closure;
use Sentinel;

class SentinelAgentOrSalesmanUser
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
        if (!Sentinel::inRole('salesman') && !Sentinel::inRole('agent')) {
            return redirect()->route('home');
        }
        if(Sentinel::inRole('agent')) {
            $user = Sentinel::getUser();

            $agentInfo = AgentInfo::where('agent_id', '=', $user->id)->first();
            if(!Activation::completed($user)) {
                return view('auth.activationPage', [ 'user' => $user ]);
            }elseif($agentInfo->state == 1) {
                return redirect()->route('agent.registerStepTwo');
            } elseif($agentInfo->state == 2) {
                Sentinel::logout();

                return redirect()->route('home')->withErrors(['Expect to activate your account administrator. After activation you will be notified by e-mail.']);
            }
        }
        return $next($request);
    }
}
