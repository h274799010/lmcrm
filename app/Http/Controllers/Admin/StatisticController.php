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
     * Страница со статистикой агента
     *
     * @param $agent_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentStatistic($agent_id)
    {

        // выбираем агента со сферами
        $agent = Agent::with('spheres')->find($agent_id);

        // переменная со статистикой по сферам
        $statistics = collect();

        // получаем статистику агента по каждой сфере
        $agent->spheres->each(function( $sphere ) use ( &$statistics, $agent_id ){

            $statistic = Statistics::openLeads( $agent_id, $sphere['id'] );

            if( $sphere['openLead'] >= $sphere['minLead'] ){

                $statistics->push( collect([
                    'status' => true,
                    'sphereId' => $sphere['id'],
                    'sphereName' => $sphere['name'],
                    'data' => $statistic
                ]) );

            }else{

                $statistics->push( collect([
                    'status' => false,
                    'sphereId' => $sphere['id'],
                    'sphereName' => $sphere['name'],
                    'minLead' => $sphere['minLead'],
                    'openLead' => $sphere['openLead'],
                    'data' => $statistic
                ]) );
            }
        });

        return view('admin.statistic.agent', [
            'agent' => $agent,
            'statistics' => $statistics,
        ]);
    }


    public function agentStatisticData(Request $request)
    {
//        dd($request->all());

//        return $request->all();
//        return $request->agent_id;
//        return $request->timeFrom;
//        return $request->timeTo;

        $user_id = $request->agent_id;
        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;


        // выбираем агента со сферами
        $agent = Agent::with('spheres')->find($user_id);

        // переменная со статистикой по сферам
        $statistics = collect();

        // получаем статистику агента по каждой сфере
        $agent->spheres->each(function( $sphere ) use ( &$statistics, $user_id, $timeFrom, $timeTo ){

            // получаем полную статистику агента по сфере
            $statistic = Statistics::openLeads( $user_id, $sphere['id'], $timeFrom, $timeTo );

            /**
             * Проверяем достаточное ли количество открытых лидов для ститистики
             *
             */
            if( $sphere['openLead'] >= $sphere['minLead'] ){
                // если открытых лидов достаточно для статистики

                // добавляем данные в коллекцию по статистики
                $statistics->push( collect([
                    'status' => true,
                    'sphereId' => $sphere['id'],
                    'sphereName' => $sphere['name'],
                    'data' => $statistic
                ]) );

            }else{
                // если открытых лидов меньше чем нужно для статистики

                // добавляем данные в коллекцию по статистики
                $statistics->push( collect([
                    'status' => false,
                    'sphereId' => $sphere['id'],
                    'sphereName' => $sphere['name'],
                    'minLead' => $sphere['minLead'],
                    'openLead' => $sphere['openLead'],
                    'data' => $statistic
                ]) );
            }
        });

        return $statistics;
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
