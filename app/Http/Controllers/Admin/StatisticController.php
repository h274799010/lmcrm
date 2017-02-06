<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sphere;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use App\Models\Agent;
use App\Models\AccountManager;
use App\Helper\Statistics;

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

        $spheres = Sphere::
                      where('status', 1)
                    ->select('id', 'name', 'created_at')
                    ->get();

//        dd($spheres);

        return view('admin.statistic.sphereList', [
            'spheres' => $spheres,
        ]);

    }


    /**
     * Страница со списком сфер для статистики
     *
     *
     * @param  integer  $agent_id
     *
     * @return View
     */
    public function agentStatistic($agent_id)
    {

        // выбираем агента со сферами
        $agent = Agent::with('spheres')->find($agent_id);

        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $agent->spheres->each(function( $sphere ) use( &$spheres ){
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

            // выбираем статистику
            $statisticData = Statistics::openLeads($agent_id, $sphere['id']);

            // записываем статистику
            $statistic = collect([
                'status' => $statisticData['allOpenLeads'] >= $sphere['minLead'],
                'sphereId' => $sphere['id'],
                'sphereName' => $sphere['name'],
                'minLead' => $sphere['minLead'],
                'data' => $statisticData
            ]);
        }


        return view('admin.statistic.agent', [
            'agent' => $agent,
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

//        dd($sphere_id);

        $sphere = Sphere::find( $sphere_id );

        $statistic = [
            'sphereName' => $sphere['name'],
            'data' => Statistics::sphere( $sphere_id ),
            'status' => true,
        ];

//        dd($statistic['data']);

        return view('admin.statistic.sphere', [
            'sphere' => $sphere,
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
        $user_id = $request->agent_id;
        $sphere_id = $request->sphere_id;
        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;

        // выбираем агента со сферами
        $agent = Agent::with('spheres')->find($user_id);

        // переменная с текущей сферой
        $currentSphere = false;

        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $agent->spheres->each(function( $sphere ) use( &$spheres, &$currentSphere, $sphere_id ){

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
        $statisticData = Statistics::openLeads( $user_id, $currentSphere['id'], $timeFrom, $timeTo );

        // записываем статистику
        $statistic = collect([
            'status' => $statisticData['allOpenLeads'] >= $currentSphere['minLead'],
            'sphereId' => $currentSphere['id'],
            'sphereName' => $currentSphere['name'],
            'minLead' => $currentSphere['minLead'],
            'openLead' => $currentSphere['openLead'],
            'data' => $statisticData
        ]);

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

        $sphere = Sphere::find( $sphere_id );

        // выбираем статистику
        $statisticData = Statistics::sphere( $sphere_id, $timeFrom, $timeTo );

        // записываем статистику
        $statistic = collect([
            'sphereId' => $sphere['id'],
            'sphereName' => $sphere['name'],
            'minLead' => $sphere['minLead'],
            'openLead' => $sphere['openLead'],
            'data' => $statisticData
        ]);

        return response()->json([ 'statistic'=>$statistic ]);
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
