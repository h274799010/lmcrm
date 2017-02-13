<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Lead;
use App\Models\AccountManagersAgents;
use App\Models\AccountManagerSphere;
use App\Models\AgentSphere;
use App\Models\Salesman;
use App\Models\Sphere;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use App\Models\Agent;
use App\Models\AccountManager;
use Statistic;


class StatisticController extends Controller
{

    public function __construct()
    {
        view()->share('type', 'agent');
    }


    /**
     * Страница со списком всех агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentsList()
    {
        $spheres = Sphere::active()->get();

        $role = Sentinel::findRoleBySlug('account_manager');
        $accountManagers = $role->users()->get();

        return view('admin.statistic.agentsList', [
            'spheres' => $spheres,
            'accountManagers' => $accountManagers
        ]);
    }


    /**
     * Получение списка агентов
     * Datatables
     *
     * @param Request $request
     * @return mixed
     */
    public function agentsData(Request $request)
    {
        $agents = Agent::listAll();

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
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
            ->add_column('actions', function($model) { return view('admin.statistic.datatables.agentControls',['user'=>$model]); })
            ->remove_column('id')
            ->remove_column('banned_at')
            ->make();
    }


    /**
     * Страница со списком всех агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function spheresList(){

        // выбираем все сферы
        $spheres = Sphere::
                      where('status', 1)
                    ->select('id', 'name', 'created_at')
                    ->get();

        // перебираем все сферы и добавляем дополнительные данные
        $spheres = $spheres->map(function( $sphere ){

            // выбираем количество всех лидов по сфере
            $sphere->leads = Lead::where( 'sphere_id', $sphere['id'] )->count();

            // считаем количество всех агентов
            $users = User::lists('id');
            $sphere->agents = AgentSphere::whereIn( 'agent_id', $users )->where( 'sphere_id', $sphere['id'] )->count();

            // выбираем количество агентов которые незабаненные
            $users = User::where( 'banned_at', NULL )->lists('id');
            $sphere->activeAgents = AgentSphere::whereIn( 'agent_id', $users )->where( 'sphere_id', $sphere['id'] )->count();

            return $sphere;

        });

        return view('admin.statistic.sphereList', [
            'spheres' => $spheres,
        ]);

    }


    /**
     * Страница со списком сфер для статистики
     *
     *
     * @param  integer  $user_id
     *
     * @return View
     */
    public function agentStatistic( $user_id )
    {

        // проверка id пользователя
        $userId = (int)$user_id;

        // если id пользователя равен нулю - выходим
        if( !$userId ){ abort(403, 'Wrong user id'); }

        // выбираем пользователя с ролями
        $userSystemData = User::with('roles')->find( $userId );

        // определяем роль, agent или salesman
        $userRole = false;
        // перебираем все роли пользователя и выбираем нужную роль
        $userSystemData->roles->each(function( $role ) use ( &$userRole ){
            // выбираем нужную роль
            if( $role->slug == 'agent' ){
                // если роль пользователя "agent"

                // выставляем роль пользователя как 'agent'
                $userRole = 'agent';

            }elseif( $role->slug == 'salesman' ){
                // если роль пользователя "salesman"

                // выставляем роль пользователя как 'salesman'
                $userRole = 'salesman';
            }
        });

        // выбор пользователя в зависимости от его роли
        if( $userRole == 'agent' ){
            // если агент
            // выбираем модель агента
            $user = Agent::with('spheres')->find( $userId );

        }elseif( $userRole == 'salesman' ){
            // если салесман
            // выбираем модель salesman
            $user = Salesman::with('spheres')->find( $userId );

        }else{
            // если нет совпадений по роли
            // выходим c ошибкой
            abort( 403, 'Wrong user slug' );
        }


        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $user->spheres->each(function( $sphere ) use( &$spheres ){
            // добавляем данные по сфере в $spheres
            $spheres->push(
                collect(
                    [
                        'id' => $sphere->id,
                        'name' => $sphere->name,
                        'openLead' => $sphere->openLead,
                        'minLead' => $sphere->minLead,
                    ]
                )
            );
        });

        // проверка на количество сфер
        if( $spheres->count() == 0){
            // если у агента нет сфер

            // указываем что статистики нет
            $statistic = false;

        }else {
            // если у агента есть сферы

            // выбираем первую сферу из списка
            $sphere = $spheres->first();

            $statistic = Statistic::agentBySphere( $user['id'], $sphere['id'], true );

        }


        return view('admin.statistic.agent', [
            'user' => $user,
            'spheres' => $spheres,
            'statistic' => $statistic,
        ]);
    }


    /**
     * Страница со статистикой сферы
     *
     *
     * @param  integer  $sphere_id
     *
     * @return View
     */
    public function sphereStatistic( $sphere_id )
    {

        // получение данных по статистике
        $statistic = Statistic::bySphere( $sphere_id );

//        dd( $statistic );

        return view('admin.statistic.sphere', [
            'statistic' => $statistic,
        ]);
    }


    /**
     * Получение данных по статистике агента
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function agentStatisticData(Request $request)
    {

        // данные из реквеста
        $user_id = (int)$request->agent_id;
        $sphere_id = (int)$request->sphere_id;
        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;

        // если id пользователя равен нулю - выходим
        if( !$user_id ){ abort(403, 'Wrong user id'); }

        // если id сферы равен нулю - выходим
        if( !$sphere_id ){ abort(403, 'Wrong sphere id'); }

        // выбираем пользователя с ролями
        $userSystemData = User::with('roles')->find( $user_id );

        // определяем роль, agent или salesman
        $userRole = false;
        // перебираем все роли пользователя и выбираем нужную роль
        $userSystemData->roles->each(function( $role ) use ( &$userRole ){
            // выбираем нужную роль
            if( $role->slug == 'agent' ){
                // если роль пользователя "agent"

                // выставляем роль пользователя как 'agent'
                $userRole = 'agent';

            }elseif( $role->slug == 'salesman' ){
                // если роль пользователя "salesman"

                // выставляем роль пользователя как 'salesman'
                $userRole = 'salesman';
            }
        });

        // выбор пользователя в зависимости от его роли
        if( $userRole == 'agent' ){
            // если агент
            // выбираем модель агента
            $user = Agent::with('spheres')->find( $user_id );

        }elseif( $userRole == 'salesman' ){
            // если салесман
            // выбираем модель salesman
            $user = Salesman::with('spheres')->find( $user_id );

        }else{
            // если нет совпадений по роли
            // выходим c ошибкой
            abort( 403, 'Wrong user slug' );
        }

        // переменная с текущей сферой
        $currentSphere = false;

        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $user->spheres->each(function( $sphere ) use( &$spheres, &$currentSphere, $sphere_id ){

            // ищем среди сфер заданную сферу
            if( $sphere->id == $sphere_id ){
                // если id сферы такое же как id заданной сферы

                // добавляем в текущую сферу коллекцию
                $currentSphere = collect(
                    [
                        'id' => $sphere->id,
                        'name' => $sphere->name,
                        'openLead' => $sphere->openLead,
                        'minLead' => $sphere->minLead,
                    ]
                );
            }

            // добавляем данные по сфере в $spheres
            $spheres->push(
                collect(
                    [
                        'id' => $sphere->id,
                        'name' => $sphere->name,
                        'openLead' => $sphere->openLead,
                        'minLead' => $sphere->minLead,
                    ]
                )
            );
        });

        // если заданной сферы нет в списке пользователя - возвращаем на фронтенд что сфера отсутствует
        if( !$currentSphere ){ return 'false'; }

        // выбираем статистику
        $statistic = Statistic::agentBySphere( $user['id'], $currentSphere['id'], true, $timeFrom, $timeTo );

        return response()->json([ 'spheres'=>$spheres, 'statistic'=>$statistic ]);
    }


    /**
     * Получение данных по статистике сферы
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function sphereStatisticData(Request $request)
    {

        // данные из реквеста
        $sphere_id = $request->sphere_id;
        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;

        // выбираем статистику
        $statistic = Statistic::bySphere( $sphere_id, $timeFrom, $timeTo );

        return response()->json( $statistic );
    }


    /**
     * Страница со списком всех аккаунт менеджеров
     *
     * @return View
     */
    public function accManagerList()
    {

        $accManagersData = [];

        $accManagersId = \Sentinel::findRoleBySlug('account_manager')->users()->lists('id');

        $accManagers = User::whereIn( 'id', $accManagersId )->with(['accManagerSpheres' => function( $query ){
            $query->with('sphere');
        }])->get();

        $accManagers->each(function( $manager ) use( &$accManagersData ){

            $agents = AccountManagersAgents::
                  where( 'account_manager_id', $manager['id'] )
                ->lists('agent_id');

            $agents = $agents->unique()->count();

            $accManagersData[] =
            [
                'id' => $manager['id'],
                'email' => $manager['email'],
                'created_at' => $manager['created_at'],
                'spheres' => $manager['accManagerSpheres'],
                'agents' => $agents,

            ];

        });

        return view('admin.statistic.accManagerList', [
            'accountManagers' => $accManagersData
        ]);
    }


    /**
     * Страница со статистикой по акк. менеджеру
     *
     *
     * @param  integer  $accManager_id
     *
     * @return View
     */
    public function accManagerStatistic( $accManager_id )
    {

        // проверка id пользователя
        $accManagerId = (int)$accManager_id;

        // если id пользователя равен нулю - выходим
        if( !$accManagerId ){ abort(403, 'Wrong user id'); }

        // выбрать акк. менеджера со сферами
        $accManager = AccountManagerSphere::
              where( 'account_manager_id', $accManagerId )
            ->with('spheres', 'accManager')
            ->first();


        $accManagers = User::where( 'id', $accManager_id )->with(['accManagerSpheres' => function( $query ){
            $query->with('sphere');
        }])->first();


        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $accManagers->accManagerSpheres->each(function( $sphere ) use( &$spheres ){
            // добавляем данные по сфере в $spheres
            $spheres->push(
                collect(
                    [
                        'id' => $sphere['sphere']->id,
                        'name' => $sphere['sphere']->name,
                        'openLead' => $sphere['sphere']->openLead,
                        'minLead' => $sphere['sphere']->minLead,
                    ]
                )
            );
        });


        // проверка на количество сфер
        if( $spheres->count() == 0){
            // если у агента нет сфер

            // указываем что статистики нет
            $statistic = false;

        }else {
            // если у агента есть сферы

            // выбираем первую сферу из списка
            $sphere = $spheres->first();

            // получаем по ней статистику
            $statistic = Statistic::accManagerBySphere( $accManagerId, $sphere['id'] );
        }

        return view('admin.statistic.accManager', [
            'user' => $accManager['accManager'],
            'spheres' => $spheres,
            'statistic' => $statistic,
        ]);
    }


    /**
     * Получение данных по статистике агента
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function accManagerStatisticData(Request $request)
    {

        // данные из реквеста
        $user_id = (int)$request->agent_id;
        $sphere_id = (int)$request->sphere_id;
        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;

        // если id пользователя равен нулю - выходим
        if( !$user_id ){ abort(403, 'Wrong user id'); }

        // если id сферы равен нулю - выходим
        if( !$sphere_id ){ abort(403, 'Wrong sphere id'); }


        // выбрать акк. менеджера со сферами
        $accManager = AccountManagerSphere::
        where( 'account_manager_id', $user_id )
            ->with('spheres', 'accManager')
            ->first();


        $accManagers = User::where( 'id', $user_id )->with(['accManagerSpheres' => function( $query ){
            $query->with('sphere');
        }])->first();


        $currentSphere = false;

        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $accManagers->accManagerSpheres->each(function( $sphere ) use( &$spheres, &$currentSphere, $sphere_id ){

            if($sphere_id == $sphere['sphere']->id){
                $currentSphere['id'] = $sphere_id;
            }

            // добавляем данные по сфере в $spheres
            $spheres->push(
                collect(
                    [
                        'id' => $sphere['sphere']->id,
                        'name' => $sphere['sphere']->name,
                        'openLead' => $sphere['sphere']->openLead,
                        'minLead' => $sphere['sphere']->minLead,
                    ]
                )
            );
        });


        // если заданной сферы нет в списке пользователя - возвращаем на фронтенд что сфера отсутствует
        if( !$currentSphere ){ return 'false'; }

        // выбираем статистику
        $statistic = Statistic::accManagerBySphere( $user_id, $currentSphere['id'], $timeFrom, $timeTo );

        return response()->json([ 'spheres'=>$spheres, 'statistic'=>$statistic ]);
    }





    /**
     * Подгрузка данных для фильтра в списке агентов
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function getFilterAgent(Request $request)
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
}
