<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Lead;
use App\Models\AccountManagersAgents;
use App\Models\AccountManagerSphere;
use App\Models\AgentSphere;
use App\Models\OpenLeads;
use App\Models\OpenLeadsStatusDetails;
use App\Models\Operator;
use App\Models\OperatorSphere;
use App\Models\Salesman;
use App\Models\Sphere;
use App\Models\SphereStatuses;
use App\Models\SphereStatusTransitions;
use App\Models\User;
use App\Models\UserMasks;
use App\Transformers\Admin\StatisticSpheresTransformer;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Psy\Util\Json;
use Yajra\Datatables\Facades\Datatables;
use App\Models\Agent;
use App\Models\AccountManager;
use Statistic;
use Carbon\Carbon;


class StatisticController extends Controller
{

    public function __construct()
    {
        view()->share('type', 'agent');
    }


    /**
     * Страница со списком всех агентов
     *
     * @return View
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
     * @return View
     */
    public function spheresList()
    {
        return view('admin.statistic.sphereList');

    }

    /**
     * Получение списка сфер
     * Datatables
     *
     * @return mixed
     */
    public function spheresData()
    {
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

        return Datatables::of( $spheres )
            ->setTransformer(new StatisticSpheresTransformer())
            ->make();
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
//            $statistic = Statistic::agentBySphere( $user['id'], $sphere['id'], false );
        }


//        dd( $statistic );

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
//        $statistic = Statistic::agentBySphere( $user['id'], $currentSphere['id'], false, $timeFrom, $timeTo );


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
    public function getFilterAgent(Request $request)
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
     * Список операторов для статистики
     *
     * @return View
     */
    public function operatorsList()
    {

        // находим роль
        $role = Sentinel::findRoleBySlug('operator');
        // находим id всех пользователей по роли
        $operatorsId = $role->users()->lists('id');

        // находим всех операторов
        $operators = OperatorSphere::
              whereIn('id', $operatorsId)
            ->select('id', 'email', 'first_name', 'last_name', 'updated_at', 'created_at')
            ->get();


        // перебираем всех операторов и добавляем ему нужные данные
        $operators->map(function( $operator ){

            // количество лидов которые добавил оператор
            $operator->leadsAddedCount = Lead::where('agent_id', $operator->id)->count();

            // сферы оператора
            $operator->sphere = $operator->spheres;

            // все лиды которые еще нужно отредактировать
            $operator->leadsToEdit = Lead::
                  whereIn('sphere_id', $operator->sphere->pluck('id'))
                ->where('status', 0)
                ->count();

            // лиды которые оператор уже отредактировал
            $operator->leadsEdited = Operator::where('operator_id', $operator->id)->count();

            // лиды которые обработал оператор
            $operatorLeadsId = Operator::where('operator_id', $operator->id)->lists('lead_id');
            // лиды которые забанил оператор
            $operator->marked_bad = Lead::
                  whereIn('id', $operatorLeadsId)
                ->where('status', 2)
                ->count();


        });


//        dd( $operators );

        return view('admin.statistic.operatorsList', ['operators' => $operators]);
    }


    /**
     * Страница со статистикой одного оператора
     *
     *
     * @param  integer  $operator_id
     *
     * @return View
     */
    public function operatorStatistic( $operator_id )
    {

        // проверка id пользователя
        $operatorId = (int)$operator_id;

        // если id пользователя равен нулю - выходим
        if( !$operatorId ){ abort(403, 'Wrong user id'); }

        $operator = OperatorSphere::with('spheres')->find($operatorId);

//        dd($operator);

        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $operator->spheres->each(function( $sphere ) use( &$spheres ){
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

            // лиды к обработке
            $for_processing = Lead::
                  where('sphere_id', $sphere['id'])
                ->where('status', 0)
                ->count();

            // обработанные лиды
            $processed_all = Operator::where('operator_id', $operator->id)->count();

            // добавленные лиды
            $added_all = Lead::
                  where('sphere_id', $sphere['id'])
                ->where('agent_id', $operator->id)
                ->count();


            // лиды которые обработал оператор
            $operatorLeadsId = Operator::where('operator_id', $operator->id)->lists('lead_id');
            // лиды которые забанил оператор
            $marked_bad = Lead::
                  whereIn('id', $operatorLeadsId)
                ->with('phone')
                ->where('sphere_id', $sphere['id'])
                ->select('id', 'email', 'customer_id', 'name', 'created_at')
                ->where('status', 2)
                ->get();

            // количество лидов которые забанил оператор
            $marked_bad_all = $marked_bad->count();

            // количество лидов добавленные оператором, которые забанили пользователи
            $users_banned_all = Lead::
                  where('agent_id', $operator->id)
                ->where('sphere_id', $sphere['id'])
                ->where('status', 5)
                ->count();


            $statistic =
            [
                'leads' =>
                [
                    'for_processing' => $for_processing,

                    'processed_all' => $processed_all,
                    'processed_period' => 0,

                    'added_all' => $added_all,
                    'added_period' => 0,

                    'marked_bad_all' => $marked_bad_all,
                    'marked_bad_period' => 0,

                    'users_banned_all' => $users_banned_all,
                    'users_banned_period' => 0,
                ],
            ];

        }


        return view('admin.statistic.operator', [
            'operator' => $operator,
            'spheres' => $spheres,
            'currentSphere' => $sphere,
            'statistic' => $statistic,
            'marked_bad' => $marked_bad,
        ]);
    }


    /**
     * Подгрузка данных для фильтра в списке агентов
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function operatorStatisticData( Request $request )
    {

        // проверка id пользователя
        $operatorId = (int)$request->agent_id;

        // если id пользователя равен нулю - выходим
        if( !$operatorId ){ abort(403, 'Wrong user id'); }

        $operator = OperatorSphere::with('spheres')->find($operatorId);

//        dd($operator);

        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $operator->spheres->each(function( $sphere ) use( &$spheres ){
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
            $sphere = Sphere::find($request->sphere_id);

            // лиды к обработке
            $for_processing = Lead::
                  where('sphere_id', $sphere['id'])
                ->where('status', 0)
                ->count();

            // приводим начальное время к нужному формату
            $dateFrom = Carbon::createFromFormat('Y-m-d', $request->timeFrom)->format('Y-m-d 00:00:00');
            // запоминаем дату в глобальной переменной
            $this->openLeads['dateFrom'] = $dateFrom;

            // приводи конечное время к нужному формату
            $dateTo = Carbon::createFromFormat('Y-m-d', $request->timeTo)->format('Y-m-d 23:59:59');
            // запоминаем дату в глобальной переменной
            $this->openLeads['dateTo'] = $dateTo;


            // обработанные лиды
            $operators_leads_all = Operator::where('operator_id', $operator->id)->lists('id');

            $processed_all = Lead::whereIn('id', $operators_leads_all)->where('sphere_id', $sphere['id'])->count();


            // обработанные лиды за период
            $operators_leads_period = Operator::
                  where('operator_id', $operator->id)
                ->where( 'created_at', '>=', $dateFrom )
                ->where( 'created_at', '<=', $dateTo )
                ->lists('id');

            $processed_period = Lead::whereIn('id', $operators_leads_period)->where('sphere_id', $sphere['id'])->count();



            // добавленные лиды
            $added_all = Lead::
                  where('sphere_id', $sphere['id'])
                ->where('agent_id', $operator->id)
                ->count();

            // добавленные лиды за период
            $added_period = Lead::
                  where('sphere_id', $sphere['id'])
                ->where('agent_id', $operator->id)
                ->where( 'created_at', '>=', $dateFrom )
                ->where( 'created_at', '<=', $dateTo )
                ->count();

            // лиды которые обработал оператор
            $operatorLeadsId = Operator::where('operator_id', $operator->id)->lists('lead_id');
            // количество лидов которые забанил оператор
            $marked_bad_all = Lead::
                  whereIn('id', $operatorLeadsId)
                ->where('sphere_id', $sphere['id'])
                ->where('status', 2)
                ->count();

            // количество лидов которые забанил оператор за период
            $marked_bad_period = Lead::
                  whereIn('id', $operatorLeadsId)
                ->where('sphere_id', $sphere['id'])
                ->where('status', 2)
                ->where( 'created_at', '>=', $dateFrom )
                ->where( 'created_at', '<=', $dateTo )
                ->count();


            // количество лидов добавленные оператором, которые забанили пользователи
            $users_banned_all = Lead::
                  where('agent_id', $operator->id)
                ->where('sphere_id', $sphere['id'])
                ->where('status', 5)
                ->count();

            // количество лидов добавленные оператором, которые забанили пользователи
            $users_banned_period = Lead::
                  where('agent_id', $operator->id)
                ->where('sphere_id', $sphere['id'])
                ->where('status', 5)
                ->where( 'created_at', '>=', $dateFrom )
                ->where( 'created_at', '<=', $dateTo )
                ->count();


            $statistic =
                [
                    'leads' =>
                    [
                        'for_processing' => $for_processing,

                        'processed_all' => $processed_all,
                        'processed_period' => $processed_period,

                        'added_all' => $added_all,
                        'added_period' => $added_period,

                        'marked_bad_all' => $marked_bad_all,
                        'marked_bad_period' => $marked_bad_period,

                        'users_banned_all' => $users_banned_all,
                        'users_banned_period' => $users_banned_period,
                    ],

                    'sphere' => $sphere,

                ];

        }


//        dd($statistic);

        return response()->json([ 'spheres' => $spheres, 'statistic' => $statistic ]);
    }


    /**
     * Подробности по транзиту
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function transitionDetails( Request $request )
    {

        /** Проверка полученных данных */

        // id транзита
        $transitId = (int)$request->transitId;

        // id текущего пользователя
        $currentUser = (int)$request->userId;


        /** Проверка роли пользователя */

        // если роль "агент",
        // то в общий массив пользователей добавляются id пользователя
        // и id салесманов этого пользователя

        // если роль "салесман",
        // то в общий массив пользователей добавляется только id салесмана

        // выбираем пользователя с ролями
        $userSystemData = User::with('roles')->find( $currentUser );

        // массив c id всех пользователей
        $allUsers = [];
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
            $currentUserData = Agent::with('salesmen')->find( $currentUser );

            // добавляем к общим пользователям id агента
            $allUsers[] = $currentUserData['id'];

            // перебираем всех салесманов пользователя
            $currentUserData['salesmen']->each(function( $salesman ) use( &$allUsers ){
                // добавляем id салесмана в общий массив
                $allUsers[] = $salesman['id'];
            });

        }elseif( $userRole == 'salesman' ){
            // если салесман
            // выбираем модель salesman
            $currentUserData = Salesman::find( $currentUser );

            // добавляем к общим пользователям id салесмана
            $allUsers[] = $currentUserData['id'];

        }else{
            // если нет совпадений по роли
            // выходим c ошибкой
            abort( 403, 'Wrong user slug' );
        }


        /** Выборка данных лидов которые учавствуют в транзите */

        // находим транзит по сфере
        $sphereTransition = SphereStatusTransitions::find($transitId);

        // находим данные по транзитам в истории статусов
        $statusDetails = OpenLeadsStatusDetails::
              where('previous_status_id', $sphereTransition['previous_status_id'])
            ->where('status_id', $sphereTransition['status_id'])
            ->get();

        // данные по статусу сферы на который перешли с транзита
        $sphereStatus = SphereStatuses::find($sphereTransition['status_id']);

        // проверка статуса на тип
        if( $sphereStatus['type'] == 1 ){
            // если это процессный статус, то делаем выборку без предыдущего статуса

            // находим транзиты конкретный пользователей (агента и его салесманов)
            $userStatusDetails = OpenLeadsStatusDetails::
                  whereIn('user_id', $allUsers)
                ->where('status_id', $sphereTransition['status_id'])
                ->lists('open_lead_id');

        }else{
            // если любой другой статус, делаем выборку от предыдущего к последующему статусу

            // находим транзиты конкретный пользователей (агента и его салесманов)
            $userStatusDetails = OpenLeadsStatusDetails::
                  whereIn('user_id', $allUsers)
                ->where('previous_status_id', $sphereTransition['previous_status_id'])
                ->where('status_id', $sphereTransition['status_id'])
                ->lists('open_lead_id');
        }

        // id лидов которые были открыты пользователями
        $leadsId = OpenLeads::whereIn('id', $userStatusDetails)
            ->lists('lead_id');

        // данные лидов
        $leads = Lead::
              whereIn('id', $leadsId)
            ->select('id', 'email', 'customer_id', 'name')
            ->with('phone')
            ->get();


        /** Расчет общего процента транзита по системе */

        // находим маски сферы (чтобы узнать какой открытый лид к какой сфере относится)
        $userMasks = UserMasks::where('sphere_id', $sphereTransition['sphere_id'])->lists('id');

        // считаем количество открытых лидов
        $allOpenLeads = OpenLeads::whereIn('mask_name_id', $userMasks)->count();

        // считаем количество транзитов
        $allTransitions = $statusDetails->count();

        // вычисление процента за весь период
        $overallPercent = $allOpenLeads != 0 ? round($allTransitions * 100 / $allOpenLeads, 2) : 0;



        return response()->json([ 'leads' => $leads, 'overallPercent' => $overallPercent ]);
    }
}
