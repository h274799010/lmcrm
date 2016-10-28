<?php namespace App\Http\Controllers\Admin;

use App\Helper\CreditHelper;
use App\Http\Controllers\AdminController;
use App\Models\Agent;
use App\Models\Transactions;
use App\Models\AgentInfo;
use App\Models\AgentSphere;
use App\Models\TransactionsDetails;
use App\Models\Wallet;
use App\Models\Sphere;
//use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\AdminUsersEditFormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
//use App\Repositories\UserRepositoryInterface;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\PayMaster;

use Datatables;


class AgentController extends AdminController
{


    public function __construct()
    {
        view()->share('type', 'agent');
    }

    /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        return view('admin.agent.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');
        return view('admin.agent.create_edit')->with('spheres', $spheres)->with('role', null);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(AdminUsersEditFormRequest $request)
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

        // Создаем кошелек
        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->buyed = 0.0;
        $wallet->earned = 0.0;
        $wallet->wasted = 0.0;
        $wallet->overdraft = 0.0;
        $wallet->save();

        return redirect()->route('admin.agent.index');
    }


    /**
     * Форма редактирования админом данных агента и его кошелька
     *
     *
     * @param  integer  $id
     *
     * @return object
     */
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

        return view('admin.agent.create_edit', ['agent'=>$agent,'spheres'=>$spheres, 'role'=>$role, 'userInfo'=>$userInfo, 'agentSpheres'=>$agentSpheres]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function update( Request $request, $id )
    {
        $agent=Agent::findOrFail($id);
        //var_dump($request->info['agent']['bill']);exit;
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
        return redirect()->route('admin.agent.index');
    }

    /**
     * Метод обновляет revenue_share агента в табл. agent_sphere
     *
     * @param Request $request
     * @return mixed
     */
    public function revenueUpdate(Request $request)
    {
        $agentSphere = AgentSphere::find($request->input('agentSphere_id'));

        if(isset($agentSphere->id)) {
            $agentSphere->lead_revenue_share = $request->input('lead_revenue_share');
            $agentSphere->payment_revenue_share = $request->input('payment_revenue_share');

            $agentSphere->save();
            return response()->json([ 'error'=>false, 'message'=>'revenue share updated success' ]);
        }

        return response()->json([ 'error'=>true, 'message'=>'revenue share not updated!' ]);
    }





    /**
     * Remove the specified resource from storage.
     *
     * @param integer $id
     * @return Response
     */
    public function destroy($id)
    {
        Agent::findOrFail($id)->delete();
        return redirect()->route('admin.agent.index');
    }

    /**
     * Show a list of all the languages posts formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function data()
    {
        $agents = Agent::listAll();

        return Datatables::of($agents)
            ->remove_column('first_name')
            ->remove_column('last_name')
            ->add_column('name', function($model) { return view('admin.agent.datatables.username',['user'=>$model]); })
            ->add_column('actions', function($model) { return view('admin.agent.datatables.control',['id'=>$model->id]); })
            ->remove_column('id')
            ->make();
    }

}
