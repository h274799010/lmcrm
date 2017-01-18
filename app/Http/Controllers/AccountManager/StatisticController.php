<?php

namespace App\Http\Controllers\AccountManager;


use App\Models\Sphere;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use App\Models\Agent;
use App\Models\AccountManager;

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

        return view('accountManager.statistic.agentsList', [
            'spheres' => $spheres
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
            ->remove_column('first_name', 'email', 'created_at')
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
                $agent = Agent::find($model->id);
                $spheres = $agent->spheres()->get()->lists('name')->toArray();
                if(count($spheres)) {
                    $spheres = implode(', ', $spheres);
                }
                return $spheres;
            })
            ->add_column('actions', function($model) { return view('accountManager.statistic.datatables.agentControls',['user'=>$model]); })
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
        $agent = Agent::find($agent_id);

        return view('accountManager.statistic.agent', [
            'agent' => $agent
        ]);
    }

    public function agentStatisticData(Request $request)
    {
        $agent = Agent::find($request->input('agent_id'));
        $spheres = $agent->openLeadsStatistic2($request->input('period'));

        return view('accountManager.statistic.partials.agentStatistic', [
            'spheres' => $spheres
        ]);
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
