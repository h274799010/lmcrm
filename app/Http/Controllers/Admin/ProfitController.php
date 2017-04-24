<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Helper\PayMaster\PayInfo;
use App\Http\Controllers\AdminController;
use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\AgentSphere;
use App\Models\ClosedDeals;
use App\Models\Sphere;
use App\Models\TransactionsDetails;
use App\Models\TransactionsLeadInfo;
use App\Transformers\Admin\AgentProfitTransformer;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;

class ProfitController extends AdminController
{
    public function __construct()
    {
        view()->share('type', '');
    }

    /**
     * Страница со списком агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $filter = Cookie::get('adminCreditReportsFilter');
        $filter = json_decode($filter, true);

        $selectedFilters = array(
            'sphere' => false,
            'accountManager' => false
        );
        if (count($filter) > 0) {
            $sphere_id = $filter['sphere'];
            $accountManager_id = $filter['accountManager'];

            if (!$sphere_id) {
                $role = Sentinel::findRoleBySlug('account_manager');
                $accountManagers = $role->users()->get();
            } else {
                $selectedFilters['sphere'] = $sphere_id;
                $sphere = Sphere::find($sphere_id);
                $accountManagers = $sphere->accountManagers()->select('users.id', 'users.email')->get();
            }

            if (!$accountManager_id) {
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

        return view('admin.profit.index', [
            'spheres' => $spheres,
            'accManagers' => $accountManagers,
            'selectedFilters' => $selectedFilters
        ]);
    }

    /**
     * Список агентов
     * Datatable
     *
     * @param Request $request
     * @return mixed
     */
    public function data(Request $request)
    {
        $agents = Agent::listAll();

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // добавляем на страницу куки с данными по фильтру
            Cookie::queue('adminCreditReportsFilter', json_encode($request->only('filter')['filter']), null, null, null, false, false);
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            $filteredIds = array();

            $agentsSphereIds = array();
            $agentsAccIds = array();

            // Пробегаемся по параметрам из фильтра
            foreach ($eFilter as $eFKey => $eFVal) {
                switch ($eFKey) {
                    case 'sphere':
                        $agentsSphereIds = array();
                        if ($eFVal) {
                            $sphere = Sphere::find($eFVal);
                            $agentsSphereIds = $sphere->agentsAll()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    case 'accountManager':
                        $agentsAccIds = array();
                        if ($eFVal) {
                            $accountManager = AccountManager::find($eFVal);
                            $agentsAccIds = $accountManager->agentsAll()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    default:
                        break;
                }
            }

            // Обьеденяем id агентов по всем фильтрам
            $tmp = array_merge($agentsSphereIds, $agentsAccIds);
            // Убираем повторяющиеся записи (оставляем только уникальные)
            $tmp = array_unique($tmp);

            // Ишем обшие id по всем фильтрам
            foreach ($tmp as $val) {
                $flag = 0;
                if (empty($eFilter['sphere']) || in_array($val, $agentsSphereIds)) {
                    $flag++;
                }
                if (empty($eFilter['accountManager']) || in_array($val, $agentsAccIds)) {
                    $flag++;
                }
                if ($flag == 2) {
                    $filteredIds[] = $val;
                }
            }
            // Если фильтры не пустые - то применяем их
            if (!empty($eFilter['sphere']) || !empty($eFilter['accountManager']) || !empty($eFilter['role'])) {
                $agents->whereIn('id', $filteredIds);
            }
        }

        return Datatables::of($agents)
            ->setTransformer(new AgentProfitTransformer())
            ->make();
    }

    /**
     * Параметры фильтра
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilter(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        $sphere_id = $request->input('sphere_id');
        $accountManager_id = $request->input('accountManager_id');

        $result = array();
        if ($id) {
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
            if (!$sphere_id) {
                $role = Sentinel::findRoleBySlug('account_manager');
                $result['accountManagers'] = $role->users()->select('users.id', \DB::raw('users.email AS name'))->get();
            }

            if (!$accountManager_id) {
                $result['spheres'] = Sphere::active()->get();
            }
        }

        return response()->json($result);
    }

    /**
     * Получаем профит из агента
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail($id)
    {
        $id = (int)$id;

        if (!$id) {
            abort(403, 'Wrong agent id');
        }

        $agent = Agent::find($id);

        $result = $agent->getProfit();

        // TEST

        $role = Sentinel::findRoleBySlug('agent');
        $agents = $role->users()->get()->lists('id')->toArray();
        $agents = Agent::whereIn('id', $agents)->get();
        $maxCoeff = false;
        $minCoeff = false;

        foreach ($agents as $agent2) {
            $profit = $agent2->getProfit();

            $leads = $profit['leads'] + $profit['openLeads'];
            if($leads == 0) {
                $leads = 1;
            }

            $profit = ($profit['profit']['profit']['total'] + $profit['profit_bayed']['profit']['total']) / $leads;

            if($maxCoeff === false || $maxCoeff < $profit) {
                $maxCoeff = $profit;
            }
            if($minCoeff === false || $minCoeff > $profit) {
                $minCoeff = $profit;
            }
        }
        $agentProfit = ($result['profit']['profit']['total'] + $result['profit_bayed']['profit']['total']) / ($result['leads'] + $result['openLeads'] == 0 ? 1 : $result['leads'] + $result['openLeads']);
        $agentCoeff = $agentProfit / ( $maxCoeff - $minCoeff );


        // TEST

        return view('admin.profit.detail', [
            'agent' => $agent,
            'result' => $result,
            'depositedProfit' => [
                'count' => $result['leads'],
                'profit' => $result['profit']['profit']['total'] / ($result['leads'] > 0 ? $result['leads'] : 1)
            ],
            'bayedProfit' => [
                'count' => $result['openLeads'],
                'profit' => $result['profit_bayed']['profit']['total'] / ($result['openLeads'] > 0 ? $result['openLeads'] : 1)
            ],
            'profit' => $agentCoeff
        ]);
    }
}
