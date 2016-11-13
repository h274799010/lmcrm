<?php

namespace App\Http\Controllers\AccountManager;

use App\Helper\PayMaster;
use App\Http\Controllers\AccountManagerController;
use App\Models\AgentInfo;
use App\Models\AgentSphere;
use App\Models\Wallet;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\Sphere;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Mail;
use Datatables;

class AgentController extends AccountManagerController {

    public function __construct()
    {
        view()->share('type', 'agent');
    }

    public function index()
    {
        return view('accountManager.agent.index');
    }

    public function data()
    {
        $agents = Agent::listAll();

        return Datatables::of($agents)
            ->remove_column('first_name')
            ->edit_column('last_name', function($model) { return $model->last_name.' '.$model->first_name; })
            ->add_column('actions', function($model) { return view('accountManager.agent.datatables.control',['id'=>$model->id]); })
            ->remove_column('id')
            ->make();
    }
    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');
        return view('accountManager.agent.create_edit')->with('spheres', $spheres)->with('role', null);
    }
    public function store(Request $request)
    {
        $user=\Sentinel::registerAndActivate($request->except('password_confirmation','sphere'));
        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = \Sentinel::findRoleBySlug('agent');
        $user->roles()->attach($role);

        // устанавливаем дополнительную роль агенту (leadbayer or dealmaker or partner)
        $role = Sentinel::findRoleBySlug($request->input('role'));
        $user->roles()->attach($role);

        $user = Agent::find($user->id);

        foreach ($request->only('spheres') as $sphere) {
            $user->spheres()->sync($sphere);
        }

        // Заполняем agentInfo
        $agentInfo = new AgentInfo();
        $agentInfo->agent_id = $user->id;
        $agentInfo->lead_revenue_share = $request->input('lead_revenue_share');
        $agentInfo->payment_revenue_share = $request->input('payment_revenue_share');
        $agentInfo->save();

        $agentSpheres = AgentSphere::where('agent_id', '=', $user->id)->get();

        if( count($agentSpheres) > 0 ) {
            foreach ($agentSpheres as $agentSphere) {
                if($agentSphere->lead_revenue_share <= 0) {
                    $agentSphere->lead_revenue_share = $request->input('lead_revenue_share');
                }
                if($agentSphere->payment_revenue_share <= 0) {
                    $agentSphere->payment_revenue_share = $request->input('payment_revenue_share');
                }
                $agentSphere->save();
            }
        }

        // Создаем кошелек
        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->buyed = 0.0;
        $wallet->earned = 0.0;
        $wallet->wasted = 0.0;
        $wallet->overdraft = 0.0;
        $wallet->save();

        return redirect()->route('accountManager.agent.index');
    }
    public function edit($id)
    {
        // данные агента
        $agent = Agent::with('agentInfo')->findOrFail($id);

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
        $userInfo = PayMaster::userInfo($id);

        $agentSpheres = $agent->agentSphere()->with('sphere')->get();

        return view('accountManager.agent.create_edit', ['agent'=>$agent,'spheres'=>$spheres, 'role'=>$role, 'userInfo'=>$userInfo, 'agentSpheres'=>$agentSpheres]);
    }
    public function revenueUpdate(Request $request)
    {
        $agentSphere = AgentSphere::find($request->input('agentSphere_id'));

        if(isset($agentSphere->id)) {
            $agentSphere->lead_revenue_share = $request->input('lead_revenue_share');
            $agentSphere->payment_revenue_share = $request->input('payment_revenue_share');

            $agentSphere->save();
            return response()->json([ 'error'=>false, 'message'=>trans('admin/agent.revenue_update') ]);
        }

        return response()->json([ 'error'=>true, 'message'=>trans('admin/agent.revenue_not_update') ]);
    }
    public function update( Request $request, $id )
    {
        $agent=Agent::findOrFail($id);

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                $agent->password = \Hash::make($request->input('password'));
            }
        }

        $agent->update($request->except('password','password_confirmation', 'spheres','info'));

        $agent->spheres()->sync($request->input('spheres'));

        $agentInfo = AgentInfo::where('agent_id', '=', $agent->id)->first();
        $agentInfo->lead_revenue_share = $request->input('lead_revenue_share');
        $agentInfo->payment_revenue_share = $request->input('payment_revenue_share');
        $agentInfo->save();

        $agentSpheres = AgentSphere::where('agent_id', '=', $agent->id)->get();

        if( count($agentSpheres) > 0 ) {
            foreach ($agentSpheres as $agentSphere) {
                if($agentSphere->lead_revenue_share <= 0) {
                    $agentSphere->lead_revenue_share = $request->input('lead_revenue_share');
                }
                if($agentSphere->payment_revenue_share <= 0) {
                    $agentSphere->payment_revenue_share = $request->input('payment_revenue_share');
                }
                $agentSphere->save();
            }
        }

        return redirect()->route('accountManager.agent.index');
    }
    public function destroy($id)
    {
        Agent::findOrFail($id)->delete();
        return redirect()->route('accountManager.agent.index');
    }

}