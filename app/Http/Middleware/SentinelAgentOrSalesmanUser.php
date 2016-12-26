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
        $user = Sentinel::getUser();
        if(Sentinel::inRole('agent')) {
            $agentInfo = AgentInfo::where('agent_id', '=', $user->id)->first();
            if(!Activation::completed($user)) {
                return view('auth.activationPage', [ 'user' => $user ]);
            }elseif($agentInfo->state == 1) {
                return redirect()->route('agent.registerStepTwo');
            } elseif($agentInfo->state == 2) {
                view()->share('userNotActive', true);
                /*Sentinel::logout();

                return redirect()->route('home')->withErrors(['success'=>true, 'message' => 'Expect to activate your account administrator. After activation you will be notified by e-mail.']);*/
            }
        } else {
            view()->share('userNotActive', false);
        }
        if($user->banned_at) {
            view()->share('userBanned', true);
        } else {
            view()->share('userBanned', false);
        }
        return $next($request);
    }
}
