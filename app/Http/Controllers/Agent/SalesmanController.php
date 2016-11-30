<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\AgentController;
use App\Http\Requests\SalesmanCreateFormRequest;
use App\Models\SphereMask;
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
        return view('agent.salesman.index')
            ->with('salesmen',$salesmen)
            ->with('salesman_id', false);
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

    public function ban($user_id)
    {
        $user = Sentinel::findById($user_id);
        $user->banned_at = Carbon::now();
        $user->save();

        return redirect()->back();
    }

    public function unban($user_id)
    {
        $user = Sentinel::findById($user_id);
        $user->banned_at = null;
        $user->save();

        return redirect()->back();
    }

}
