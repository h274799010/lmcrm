<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\AgentController;
use App\Http\Requests\SalesmanCreateFormRequest;
use App\Models\SphereMask;
use App\Models\User;
use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Validator;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\SalesmanInfo;
use App\Models\OpenLeads;
use App\Models\Lead;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\AdminUsersEditFormRequest;
use Datatables;

class SalesmanController extends AgentController {
     /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        $salesmen = Agent::find($this->uid)->salesmen()->get();
        $permissions = User::$bannedPermissions;
        return view('agent.salesman.index')
            ->with('salesmen',$salesmen)
            ->with('salesman_id', false)
            ->with('permissions', $permissions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('agent.salesman.create')->with('salesman',NULL);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SalesmanCreateFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SalesmanCreateFormRequest $request)
    {
        $agent = Agent::with('sphereLink','wallet')->findOrFail($this->uid);

//        dd($agent);

        $salesman=\Sentinel::registerAndActivate($request->except('password_confirmation','sphere'));
        $salesman->update(['password'=>\Hash::make($request->input('password'))]);

        $role = \Sentinel::findRoleBySlug('salesman');
        $salesman->roles()->attach($role);

        $agentType = $agent->roles()->whereNotIn('slug', ['agent'])->first();
        $salesman->roles()->attach($agentType);

        $salesman = Salesman::find($salesman->id);

        $salesman->info()->save(new SalesmanInfo([
            'agent_id'=>$agent->id,
            'sphere_id'=>$agent->sphereLink->sphere_id,
            'wallet_id'=>$agent->wallet->id
        ]));

        return redirect()->route('agent.salesman.edit',[$salesman->id]);
    }

    public function edit($id)
    {
        $salesman = Salesman::findOrFail($id);
        return view('agent.salesman.create')->with('salesman',$salesman);
    }

    public function update(AdminUsersEditFormRequest $request, $id) {
        $salesman = Salesman::find($id);

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                $salesman->password = \Hash::make($request->input('password'));
            }
        }

        $salesman->update($request->except('password','password_confirmation'));

        return redirect()->route('agent.salesman.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        Agent::findOrFail($this->uid)->leads()->whereIn([$id])->delete();
        return response()->route('agent.salesman.index');
    }

    public function ban(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'permissions' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json(array(
                'errors' => $validator->errors()
            ));
        }

        $user = Sentinel::findById($request->input('user_id'));
        $permissions = User::$bannedPermissions;

        foreach ($request->input('permissions') as $permission) {
            $permissions[$permission] = false;
        }

        $user->banned_at = Carbon::now();
        $user->permissions = $permissions;
        $user->save();

        return response()->json(array(
            'errors' => array(),
            'status'=>'success'
        ));
    }

    public function unbanData(Request $request)
    {
        $user = Sentinel::findById($request->input('user_id'));

        $permissions = User::$bannedPermissions;

        foreach ($user->permissions as $permission => $value) {
            if(isset($permissions[$permission])) {
                $permissions[$permission] = array(
                    'value' => $value,
                    'name' => trans('admin/users.permissions.'.$permission)
                );
            }
        }

        return response()->json($permissions);
    }

    public function unban(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json(array(
                'errors' => $validator->errors()
            ));
        }

        $user = Sentinel::findById($request->input('user_id'));
        $permissions = User::$bannedPermissions;

        if(count($request->input('permissions')) > 0) {
            foreach ($request->input('permissions') as $permission) {
                if(isset($permissions[$permission])) {
                    $permissions[$permission] = false;
                }
            }
        }

        if(count($request->input('permissions')) == 0) {
            $user->banned_at = null;
        }
        $user->permissions = $permissions;
        $user->save();

        return response()->json(array(
            'errors' => array(),
            'status'=>'success'
        ));
    }

}
