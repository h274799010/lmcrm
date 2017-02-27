<?php namespace App\Http\Controllers\Admin;

use App\Helper\CreditHelper;
use App\Http\Controllers\AdminController;
use App\Http\Requests\AgentFormRequest;
use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\Transactions;
use App\Models\AgentInfo;
use App\Models\AgentSphere;
use App\Models\TransactionsDetails;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Sphere;
//use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\AdminUsersEditFormRequest;
use Carbon\Carbon;
use Cartalyst\Sentinel\Roles\EloquentRole;
use Validator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
//use App\Repositories\UserRepositoryInterface;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Helper\PayMaster;

use Datatables;
use Cookie;


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
        $filter = Cookie::get('adminAgentsFilter');
        $filter = json_decode($filter, true);

        $selectedFilters = array(
            'sphere' => false,
            'accountManager' => false,
            'role' => false
        );
        if (count($filter) > 0) {
            $sphere_id = $filter['sphere'];
            $accountManager_id = $filter['accountManager'];

            if($filter['role'] != '') {
                $selectedFilters['role'] = $filter['role'];
            }

            if(!$sphere_id) {
                $role = Sentinel::findRoleBySlug('account_manager');
                $accountManagers = $role->users()->get();
            } else {
                $selectedFilters['sphere'] = $sphere_id;
                $sphere = Sphere::find($sphere_id);
                $accountManagers = $sphere->accountManagers()->select('users.id', 'users.email')->get();
            }

            if(!$accountManager_id) {
                $spheres = Sphere::active()->get();
            } else {
                $selectedFilters['accountManager'] = $accountManager_id;
                $accountManager = AccountManager::find($accountManager_id);
                $spheres = $accountManager->spheres()->select('spheres.id', 'spheres.name')->get();
            }
        } else {
            $spheres = Sphere::active()->get();

            $role = Sentinel::findRoleBySlug('account_manager');
            $accountManagers = $role->users()->get();
        }
        $permissions = User::$bannedPermissions;

        // Show the page
        return view('admin.agent.index', [
            'spheres' => $spheres,
            'accountManagers' => $accountManagers,
            'selectedFilters' => $selectedFilters,
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');

        $accountManagers = Sentinel::findRoleBySlug('account_manager')->getUsers();

        return view('admin.agent.create_edit')->with(['spheres'=>$spheres, 'accountManagers'=>$accountManagers])->with('role', null);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AgentFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AgentFormRequest $request)
    {
        $user=\Sentinel::registerAndActivate($request->except('password_confirmation','sphere'));
        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = \Sentinel::findRoleBySlug('agent');
        $user->roles()->attach($role);

        // устанавливаем дополнительную роль агенту (leadbayer or dealmaker or partner)
        $role = Sentinel::findRoleBySlug($request->input('role'));
        $user->roles()->attach($role);

        $user = Agent::find($user->id);

        $user->spheres()->sync($request->input('spheres'));

        //$user->accountManagers()->sync($request->input('accountManagers'));

        // Заполняем agentInfo
        $agentInfo = new AgentInfo();
        $agentInfo->agent_id = $user->id;
        $agentInfo->lead_revenue_share = $request->input('lead_revenue_share');
        $agentInfo->payment_revenue_share = $request->input('payment_revenue_share');
        $agentInfo->company = $request->input('company');
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

        // Получаем дополнительную роль (тип) продавцов
        foreach ($agent->salesmen as $key => $salesman) {
            foreach ($salesman->roles as $val) {
                if($val->slug != 'salesman') {
                    $salesman->role = $val->name;
                }
            }

            $salesmanSpheres = $salesman->spheres()->get();
            foreach ($salesmanSpheres as $k => $salesmanSphere) {
                $masks = $salesman->bitmaskAllWithNames($salesmanSphere->id);
                $salesmanSpheres[$k]['masks'] = $masks;
            }
            $agent->salesmen[$key]->spheres = $salesmanSpheres;
        }

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

        $agentSpheres = $agent->agentSphere()->with(['sphere' => function($query) {
            $query->with('statuses');
        }])->get();

        $openLeadsStatistic = $agent->openLeadsStatistic();

        $accountManagers = Sentinel::findRoleBySlug('account_manager')->getUsers();

        $agentMasks = $agent->spheres()->get();

        foreach ($agentMasks as $key => $agentMask) {
            $agentMasks[$key]['masks'] = $agent->bitmaskAllWithNames($agentMask->id);
        }
        $permissions = User::$bannedPermissions;

        return view('admin.agent.create_edit', [
            'agent'=>$agent,
            'spheres'=>$spheres,
            'role'=>$role,
            'userInfo'=>$userInfo,
            'agentSpheres'=>$agentSpheres,
            'accountManagers'=>$accountManagers,
            'agentMasks' => $agentMasks,
            'statistic' => $openLeadsStatistic,
            'permissions' => $permissions
        ]);
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

        if(!$agent->inRole($request->input('role'))) {
            $roles = EloquentRole::whereIn('slug', ['agent', $request->input('role')])->get();
            $agent->roles()->sync($roles);
        }

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                //$user->password = bcrypt($password);
                $agent->password = \Hash::make($request->input('password'));
            }
        }

        $agent->update($request->except('password','password_confirmation', 'spheres','info'));

        //$agent->spheres()->sync($request->input('spheres'));
        if( count($request->input('spheres')) > 0 ) {
            AgentSphere::where('agent_id', '=', $agent->id)
                ->whereNotIn('sphere_id', $request->input('spheres'))
                ->delete();
            foreach ($request->input('spheres') as $sphere_id) {
                $agentSphere = AgentSphere::withTrashed()
                    ->where('agent_id', '=', $agent->id)
                    ->where('sphere_id', '=', $sphere_id)
                    ->first();
                if(isset($agentSphere->id) && $agentSphere->trashed()) {
                    $agentSphere->restore();
                }
                elseif(!isset($agentSphere->id)) {
                    $agentSphere = new AgentSphere();
                    $agentSphere->agent_id = $agent->id;
                    $agentSphere->sphere_id = $sphere_id;
                    $agentSphere->save();
                }
            }
        }

        //$agent->accountManagers()->sync($request->input('accountManagers'));

        $agentInfo = AgentInfo::where('agent_id', '=', $agent->id)->first();
        $agentInfo->lead_revenue_share = $request->input('lead_revenue_share');
        $agentInfo->payment_revenue_share = $request->input('payment_revenue_share');
        $agentInfo->company = $request->input('company');
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
            return response()->json([ 'error'=>false, 'message'=>trans('admin/agent.revenue_update') ]);
        }

        return response()->json([ 'error'=>true, 'message'=>trans('admin/agent.revenue_not_update') ]);
    }

    /**
     * Метод обновляет agent_range агента в табл. agent_sphere
     *
     * @param Request $request
     * @return mixed
     */
    public function rankUpdate(Request $request)
    {
        $agentSphere = AgentSphere::with('sphere')->find($request->input('agentSphere_id'));

        if(isset($agentSphere->id) && isset($agentSphere->sphere->max_range)) {
            $max_rank = $agentSphere->sphere->max_range;
            $rank = (int)$request->input('rank');

            if($rank <= 0) {
                $rank = 1;
            }
            if($rank > $max_rank) {
                $rank = $max_rank;
            }

            $agentSphere->agent_range = $rank;

            $agentSphere->save();
            return response()->json([ 'error'=>false, 'message'=>trans('admin/agent.rank_update') ]);
        }

        return response()->json([ 'error'=>true, 'message'=>trans('admin/agent.rank_not_update') ]);
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
    public function data(Request $request)
    {
        $agents = Agent::listAll();

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // добавляем на страницу куки с данными по фильтру
            Cookie::queue('adminAgentsFilter', json_encode($request->only('filter')['filter']), null, null, null, false, false);
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            $filteredIds = array();

            $agentsSphereIds = array();
            $agentsAccIds = array();
            $agentsRoleIds = array();

            // Пробегаемся по параметрам из фильтра
            //
            foreach ($eFilter as $eFKey => $eFVal) {
                switch($eFKey) {
                    case 'sphere':
                        $agentsSphereIds = array();
                        if($eFVal) {
                            $sphere = Sphere::find($eFVal);
                            $agentsSphereIds = $sphere->agentsAll()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    case 'accountManager':
                        $agentsAccIds = array();
                        if($eFVal) {
                            $accountManager = AccountManager::find($eFVal);
                            $agentsAccIds = $accountManager->agentsAll()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    case 'role':
                        $agentsRoleIds = array();
                        if($eFVal) {
                            $role = Sentinel::findRoleBySlug($eFVal);
                            $agentsRoleIds = $role->users()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    default:
                        break;
                }
            }

            // Обьеденяем id агентов по всем фильтрам
            $tmp = array_merge($agentsSphereIds, $agentsAccIds, $agentsRoleIds);
            // Убираем повторяющиеся записи (оставляем только уникальные)
            $tmp = array_unique($tmp);

            // Ишем обшие id по всем фильтрам
            foreach ($tmp as $val) {
                $flag = 0;
                if(empty($eFilter['sphere']) || in_array($val, $agentsSphereIds)) {
                    $flag++;
                }
                if(empty($eFilter['accountManager']) || in_array($val, $agentsAccIds)) {
                    $flag++;
                }
                if(empty($eFilter['role']) || in_array($val, $agentsRoleIds)) {
                    $flag++;
                }
                if( $flag == 3 ) {
                    $filteredIds[] = $val;
                }
            }
            // Если фильтры не пустые - то применяем их
            if( !empty($eFilter['sphere']) || !empty($eFilter['accountManager']) || !empty($eFilter['role']) ) {
                $agents->whereIn('id', $filteredIds);
            }
        }

        return Datatables::of($agents)
            ->remove_column('first_name', 'created_at', 'email')
            ->edit_column('last_name', function($model) { return $model->last_name.' '.$model->first_name; })
            ->add_column('role', function($model) {
                // Дополнительная роль (тип) агента
                $role = '';
                foreach ($model->roles as $val) {
                    if($val->slug != 'agent') {
                        $role = $val->name;
                    }
                }
                return $role;
            })
            ->add_column('spheres', function($model) {
                $spheres = $model->spheres()->get()->lists('name')->toArray();
                if(count($spheres)) {
                    $spheres = implode(', ', $spheres);
                }
                return $spheres;
            })
            ->add_column('accountManagers', function($model) {
                $accountManagers = $model->accountManagers()->get()->lists('email')->toArray();
                if(count($accountManagers)) {
                    $accountManagers = implode(', ', $accountManagers);
                }
                return $accountManagers;
            })
            ->add_column('actions', function($model) { return view('admin.agent.datatables.control',['user'=>$model]); })
            ->remove_column('id')
            ->remove_column('banned_at')
            ->make();
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

        if($user->inRole('agent')) {
            $agent = Agent::findOrFail($user->id);
            $agent->banned_at = Carbon::now();
            $agent->permissions = $permissions;
            $agent->save();

            $salesmans = $agent->salesmen()->get();

            if(count($salesmans)) {
                foreach ($salesmans as $salesman) {
                    $salesman->banned_at = Carbon::now();
                    $salesman->permissions = $permissions;
                    $salesman->save();
                }
            }
        } else {
            $user->banned_at = Carbon::now();
            $user->permissions = $permissions;
            $user->save();
        }

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

        if($user->inRole('agent')) {
            $agent = Agent::findOrFail($user->id);
            if(count($request->input('permissions')) == 0) {
                $agent->banned_at = null;
            }
            $agent->permissions = $permissions;
            $agent->save();

            $salesmans = $agent->salesmen()->get();

            if(count($salesmans)) {
                foreach ($salesmans as $salesman) {
                    if(count($request->input('permissions')) == 0) {
                        $salesman->banned_at = null;
                    }
                    $salesman->permissions = $permissions;
                    $salesman->save();
                }
            }
        } else {
            if(count($request->input('permissions')) == 0) {
                $user->banned_at = null;
            }
            $user->permissions = $permissions;
            $user->save();
        }

        return response()->json(array(
            'errors' => array(),
            'status'=>'success'
        ));
    }

    /**
     * Список самостоятельно зарегестрированных (новых, не активированых) агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function newAgents()
    {
        $agentsInfo = AgentInfo::where('state', '=', 2)->get()->lists('agent_id')->toArray();

        $agents = Agent::whereIn('id', $agentsInfo)->with(['roles' => function ($query) {
            $query->where('slug', '!=', 'agent');
        }])->get();

        return view('admin.agent.new')->with([ 'agents' => $agents ]);
    }
    public function agentActivatedPage($id)
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

        $accountManagers = Sentinel::findRoleBySlug('account_manager')->getUsers();

        $agentMasks = $agent->spheres()->get();

        foreach ($agentMasks as $key => $agentMask) {
            $agentMasks[$key]['masks'] = $agent->bitmaskAllWithNames($agentMask->id);
        }

        return view('admin.agent.activated', [
            'agent'=>$agent,
            'spheres'=>$spheres,
            'role'=>$role,
            'userInfo'=>$userInfo,
            'agentSpheres'=>$agentSpheres,
            'accountManagers'=>$accountManagers,
            'agentMasks' => $agentMasks
        ]);
    }

    /**
     * Активация агента
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function agentActivate(Request $request, $id)
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

        $agent->accountManagers()->sync($request->input('accountManagers'));

        $agentInfo = AgentInfo::where('agent_id', '=', $agent->id)->first();
        $agentInfo->lead_revenue_share = $request->input('lead_revenue_share');
        $agentInfo->payment_revenue_share = $request->input('payment_revenue_share');
        $agentInfo->company = $request->input('company');
        $agentInfo->state = 3;
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

        return redirect()->route('admin.agent.index');
    }

    public function attachAccountManagers(Request $request)
    {
        $agent = Agent::findOrFail($request->input('agent_id'));

        $accountManagers = ( $request->input('accountManagers') ?: [] );

        $agent->accountManagers()->sync( $accountManagers );

        return redirect()->back();
    }

    function getFilter(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        $sphere_id = $request->input('sphere_id');
        $accountManager_id = $request->input('accountManager_id');

        $result = array();
        if($id) {
            switch ($type) {
                case 'sphere':
                    $sphere = Sphere::find($id);
                    $result['accountManagers'] = $sphere->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                    break;
                case 'accountManager':
                    $accountManager = AccountManager::find($id);
                    $result['spheres'] = $accountManager->spheres()->select('spheres.id', 'spheres.name')->get();
                    break;
                default:
                    break;
            }
        } else {
            if(!$sphere_id) {
                $role = Sentinel::findRoleBySlug('account_manager');
                $result['accountManagers'] = $role->users()->select('users.id', \DB::raw('users.email AS name'))->get();
            }

            if(!$accountManager_id) {
                $result['spheres'] = Sphere::active()->get();
            }
        }

        return response()->json($result);
    }


    /**
     * Переключение одного разрешения пользователя
     *
     * если был false станет true и на оборот
     *
     *
     * @param  Request  $request
     *
     * @return json
     */
    public function switchPermission( Request $request )
    {

        $userId = $request['agent_id'];
        $permission = $request['permission'];

        // получение всех правил системы по пользователям
        $systemPermissions = User::$bannedPermissions;

        // пользователь которому нужно поменять права
        $user = Sentinel::findById($userId);

        // получаем права пользователя
        $permissions = $user->permissions;

        // проверка на наличие прав у пользователя
        if( $permissions == []){
            // если прав нет

            // записываем их
            $permissions = $systemPermissions;
        }

        // проверяем заданное правило на существование
        if( !isset($permissions[$permission]) ){
            // если не существует
            // выходим из метода
            return response()->json([ 'status' => false ]);
        }

        // меняем значение правила
        $permissions[$permission] = $permissions[$permission] == false;

        // заносим правила в модель
        $user->permissions = $permissions;
        // сохраняем
        $user->save();

        // отправляем ответ на фронтенд
        return response()->json([ 'status' => true, 'permissions' => $permissions ]);
    }

}
