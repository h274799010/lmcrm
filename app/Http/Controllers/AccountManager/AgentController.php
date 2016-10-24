<?php

namespace App\Http\Controllers\AccountManager;

use App\Helper\PayMaster;
use App\Http\Controllers\AccountManagerController;
use App\Models\AgentInfo;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\Sphere;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Mail;

class AgentController extends AccountManagerController {

    /**
     * Список всех агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentList()
    {
        $agentRole = Sentinel::findRoleBySlug('agent');
        $agents = $agentRole->users()->get();

        return view('accountManager.agent.index', [ 'agents' => $agents ]);
    }

    /**
     * Подробная информация о агенте
     *
     * @param $agent_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentInfo($agent_id)
    {
        $agent = Sentinel::findById($agent_id);

        return view('accountManager.agent.info', [ 'agent' => $agent ]);
    }

    public function agentEdit($agent_id)
    {
        $agent = Agent::with('agentInfo')->findOrFail($agent_id);

        // данные сферы
        $spheres = Sphere::active()->lists('name','id');

        $user = Sentinel::findById($agent->id);
        $roles = array('leadbayer', 'partner', 'dealmaker');
        $role = '';
        foreach ($roles as $v) {
            if($user->inRole($v)) {
                $role = $v;
            }
        }
        if(!$role) {
            $role = null;
        }

        // все данные агента по кредитам (кошелек, история, транзакции)
        $userInfo = PayMaster::userInfo($agent_id);

        return view('accountManager.agent.create_edit', [ 'agent'=>$agent,'spheres'=>$spheres, 'role'=>$role, 'userInfo'=>$userInfo ]);
    }

    public function update(Request $request)
    {
        $agent=Agent::findOrFail($request->input('agent_id'));

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                //$user->password = bcrypt($password);
                $agent->password = \Hash::make($request->input('password'));
            }
        }

        $agent->update($request->except('password','password_confirmation', 'spheres','info'));

        $agent->spheres()->sync($request->input('spheres'));

        // Заполняем agentInfo
        $agentInfo = AgentInfo::where('agent_id', '=', $agent->id)->first();
        $agentInfo->lead_revenue_share = $request->input('lead_revenue_share');
        $agentInfo->payment_revenue_share = $request->input('payment_revenue_share');
        $agentInfo->save();

        if (Activation::exists($agent) && !Activation::completed($agent))
        {
            $activation = Activation::exists($agent);
            Activation::complete($agent, $activation->code);

            Mail::send('emails.activation', [ 'user'=>$agent ], function ($message) use ($agent) {
                $message->from('us@example.com', 'Laravel');

                $message->to($agent->email, $agent->name)->subject('Account activated!');
            });
        }

        return redirect()->route('admin.agent.index');
    }

}