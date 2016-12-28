<?php

namespace App\Http\Controllers\AccountManager;

use App\Helper\PayMaster;
use App\Http\Controllers\AccountManagerController;
use App\Http\Requests\AgentFormRequest;
use App\Models\AccountManager;
use App\Models\AccountManagersAgents;
use App\Models\AgentBitmask;
use App\Models\AgentInfo;
use App\Models\AgentSphere;
use App\Models\Salesman;
use App\Models\User;
use App\Models\UserMasks;
use App\Models\Wallet;
use Carbon\Carbon;
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
        $accountManager = AccountManager::find(Sentinel::getUser()->id);
        $spheres = $accountManager->spheres()->get();
        return view('accountManager.agent.index', [
            'spheres' => $spheres
        ]);
    }

    /**
     * Получения списка агентов
     *
     * @return mixed
     */
    public function data(Request $request)
    {
        $accountManager = AccountManager::find(Sentinel::getUser()->id);
        $agents = $accountManager->agents();


        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            $filteredIds = array();

            $agentsSphereIds = array();
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
            $tmp = array_merge($agentsSphereIds, $agentsRoleIds);
            // Убираем повторяющиеся записи (оставляем только уникальные)
            $tmp = array_unique($tmp);

            // Ишем обшие id по всем фильтрам
            foreach ($tmp as $val) {
                $flag = 0;
                if(empty($eFilter['sphere']) || in_array($val, $agentsSphereIds)) {
                    $flag++;
                }
                if(empty($eFilter['role']) || in_array($val, $agentsRoleIds)) {
                    $flag++;
                }
                if( $flag == 2 ) {
                    $filteredIds[] = $val;
                }
            }
            // Если фильтры не пустые - то применяем их
            if( !empty($eFilter['sphere']) || !empty($eFilter['role']) ) {
                $agents->whereIn('users.id', $filteredIds);
            }
        }

        return Datatables::of($agents)
            ->remove_column('first_name')
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
            ->add_column('actions', function($model) { return view('accountManager.agent.datatables.control',['user'=>$model]); })
            ->remove_column('id')
            ->remove_column('banned_at')
            ->make();
    }

    /**
     * Страница создания агента
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $accountManager = AccountManager::find(Sentinel::getUser()->id);
        // список сфер акк. менеджера
        $spheres = $accountManager->spheres()->get();
        return view('accountManager.agent.create_edit')->with('spheres', $spheres)->with('role', null);
    }

    /**
     * Сохранение данных агента в БД
     *
     * @param Request $request
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

        $accountManagerAgent = new AccountManagersAgents();
        $accountManagerAgent->agent_id = $user->id;
        $accountManagerAgent->account_manager_id = Sentinel::getUser()->id;
        $accountManagerAgent->save();

        $user = Agent::find($user->id);

        // привязываем агента к сферам
        foreach ($request->only('spheres') as $sphere) {
            $user->spheres()->sync($sphere);
        }

        // Заполняем agentInfo
        $agentInfo = new AgentInfo();
        $agentInfo->agent_id = $user->id;
        $agentInfo->lead_revenue_share = $request->input('lead_revenue_share');
        $agentInfo->payment_revenue_share = $request->input('payment_revenue_share');
        $agentInfo->company = $request->input('company');
        $agentInfo->save();

        $agentSpheres = AgentSphere::where('agent_id', '=', $user->id)->get();

        // Заполняем поля *_revenue_share значениями по умолчанию
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

    /**
     * Страница редактирования агента
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $accountManager = AccountManager::find(Sentinel::getUser()->id);

        // данные сферы
        $accountManagerSpheresIds = $accountManager->spheres()->get()->lists('id')->toArray();

        // данные агента
        $agent = Agent::with('agentInfo')
            ->with(['salesmen' => function ($query) use ($accountManagerSpheresIds) {
                return $query->whereIn('sphere_id', $accountManagerSpheresIds);
            }])
            ->findOrFail($id);

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
        $agentSpheres = $agent->spheres()->whereIn('sphere_id', $accountManagerSpheresIds)->get();

        foreach ($agentSpheres as $key => $agentSphere) {
            $agentSpheres[$key]['masks'] = $agent->bitmaskAllWithNames($agentSphere->id);
        }

        // Маски агента
        $spheres = $agentSpheres;

        // Дополнительные роли (тип) агента
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

        // Сферы в которых работает агент
        $agentSpheres = $agent->agentSphere()->whereIn('sphere_id', $spheres->lists('id')->toArray())->with('sphere')->get();

        $agentSelectedSpheres = $agent->spheres()->get()->lists('id')->toArray();

        return view('accountManager.agent.create_edit', ['agent'=>$agent,'spheres'=>$spheres, 'role'=>$role, 'userInfo'=>$userInfo, 'agentSpheres'=>$agentSpheres, 'agentSelectedSpheres'=>$agentSelectedSpheres ]);
    }

    /**
     * Обновление полей *_revenue_share по сферам
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * Обновление аккаунта агента
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
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

        // Присоединение/отсоиденение агента к сферам
        // работает только с теми сферами, к которым подключен акк. менеджер
        $accountManager = AccountManager::find(Sentinel::getUser()->id);
        $accountManagerSpheres = $accountManager->spheres()->get()->lists('id')->toArray();

        $agentSelectedSpheres = $agent->spheres()->whereNotIn('sphere_id', $accountManagerSpheres)->get()->lists('id')->toArray();

        $spheres = $request->input('spheres');
        if(count($agentSelectedSpheres)) {
            $spheres = array_merge($spheres, $agentSelectedSpheres);
        }

        $agent->spheres()->sync($spheres);

        // Заполняем таблицу agent_info
        $agentInfo = AgentInfo::where('agent_id', '=', $agent->id)->first();
        $agentInfo->lead_revenue_share = $request->input('lead_revenue_share');
        $agentInfo->payment_revenue_share = $request->input('payment_revenue_share');
        $agentInfo->company = $request->input('company');
        $agentInfo->save();

        // Устанавливаем значение полей *_revenue_share по умолчанию
        // для сфер у которых это поле не указано
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

    /**
     * Удаление агента
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        Agent::findOrFail($id)->delete();
        return redirect()->route('accountManager.agent.index');
    }

    /**
     * Список самостоятельно зарегестрированных (новых, не активированых) агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function newAgents()
    {
        $accountManager = AccountManager::with('spheres')->find(Sentinel::getUser()->id);
        $agentsIds = AgentSphere::whereIn( 'sphere_id', $accountManager->spheres->lists('id')->toArray() )->get()->lists('agent_id')->toArray();

        $agentsInfo = AgentInfo::whereIn('agent_id', $agentsIds)->where('state', '=', 2)->get()->lists('agent_id')->toArray();

        $agents = Agent::whereIn('id', $agentsInfo)->with(['roles' => function ($query) {
            $query->where('slug', '!=', 'agent');
        }])->get();

        return view('accountManager.agent.new')->with([ 'agents' => $agents ]);
    }

    /**
     * Страница активации агента (аналогична странице редактирования)
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentActivatedPage($id)
    {
        // данные агента
        $agent = Agent::with(['agentInfo'])->findOrFail($id);

        $accountManager = AccountManager::find(Sentinel::getUser()->id);

        $accountManager->agentsAll()->attach($agent->id);

        // данные сферы
        $spheres = $accountManager->spheres()->get();

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

        $agentSpheres = $agent->agentSphere()->whereIn('sphere_id', $spheres->lists('id')->toArray())->with('sphere')->get();

        $agentSelectedSpheres = $agent->spheres()->get()->lists('id')->toArray();

        return view('accountManager.agent.activated', ['agent'=>$agent,'spheres'=>$spheres, 'role'=>$role, 'userInfo'=>$userInfo, 'agentSpheres'=>$agentSpheres, 'agentSelectedSpheres'=>$agentSelectedSpheres ]);
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
        if($request->input('lead_revenue_share') <= 0 || $request->input('payment_revenue_share') <= 0) {
            return redirect()->back()->withErrors(['success'=>false, 'message' => 'Lead revenue share or payment revenue share must be greater than zero']);
        }
        $agent=Agent::findOrFail($id);

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                $agent->password = \Hash::make($request->input('password'));
            }
        }

        $agent->update($request->except('password','password_confirmation', 'spheres','info'));

        $accountManager = AccountManager::find(Sentinel::getUser()->id);
        $accountManagerSpheres = $accountManager->spheres()->get()->lists('id')->toArray();

        $agentSelectedSpheres = $agent->spheres()->whereNotIn('sphere_id', $accountManagerSpheres)->get()->lists('id')->toArray();

        $spheres = $request->input('spheres');
        if(count($agentSelectedSpheres)) {
            $spheres = array_merge($spheres, $agentSelectedSpheres);
        }

        $agent->spheres()->sync($spheres);

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

        // Отправка сообшения об успешной активации
        Mail::send('emails.activation', [ 'user'=>$agent ], function ($message) use ($agent) {
            $message->from('us@example.com', 'Laravel');

            $message->to($agent->email)->subject('Your account activated!');
        });

        return redirect()->route('accountManager.agent.index');
    }

    public function ban($user_id)
    {
        $user = Sentinel::findById($user_id);

        $user->banned_at = Carbon::now();
        $user->save();

        if($user->inRole('agent')) {
            $agent = Agent::findOrFail($user->id);
            $salesmans = $agent->salesmen()->get();

            if(count($salesmans)) {
                foreach ($salesmans as $salesman) {
                    $salesman->banned_at = Carbon::now();
                    $salesman->save();
                }
            }
        }

        return redirect()->back();
    }

    public function unban($user_id)
    {
        $user = Sentinel::findById($user_id);

        if($user->inRole('agent')) {
            $agent = Agent::findOrFail($user->id);
            $salesmans = $agent->salesmen()->get();

            if(count($salesmans)) {
                foreach ($salesmans as $salesman) {
                    $salesman->banned_at = null;
                    $salesman->save();
                }
            }
        } elseif ($user->inRole('salesman')) {
            $salesman = Salesman::findOrFail($user->id);
            $agent = $salesman->agent()->first();
            if($agent->banned_at != null) {
                return redirect()->back()->withErrors(['success'=>false, 'message' => 'The seller can not be unlocked, as his agent blocked.']);
            }
        }

        $user->banned_at = null;
        $user->save();

        return redirect()->back();
    }

}