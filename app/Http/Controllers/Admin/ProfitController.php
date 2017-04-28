<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Helper\PayMaster\PayInfo;
use App\Http\Controllers\AdminController;
use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\AgentSphere;
use App\Models\ClosedDeals;
use App\Models\RequestPayment;
use App\Models\Sphere;
use App\Models\TransactionsDetails;
use App\Models\TransactionsLeadInfo;
use App\Transformers\Admin\AccManagersProfitTransformer;
use App\Transformers\Admin\AgentProfitTransformer;
use Carbon\Carbon;
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
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail(Request $request, $id)
    {

        $id = (int)$id;

        if (!$id) {
            abort(403, 'Wrong agent id');
        }

        if($request->ajax()) {
            if ($request->input('period')) {
                $period = explode('/', $request->input('period'));

                $period = [
                    'start' => trim($period[0]).' 00:00:00',
                    'end' => trim($period[1]).' 23:59:59'
                ];
            } else {
                $period = null;
            }
        }
        else {
            $date = Carbon::now()->subMonth();

            $period = [
                'start' => date('Y').'-'.$date->month.'-1 00:00:00',
                'end' => date('Y').'-'.$date->month.'-'.$date->format('t').' 23:59:59'
            ];
        }

        $agent = Agent::find($id);

        $spheres = $agent->onlySpheres()->get();

        if($request->ajax()) {
            $sphere_id = $request->input('sphere');
        }
        else {
            $sphere_id = count($spheres) > 0 ? $spheres[0]->id : null;
        }

        $agentSphere = AgentSphere::select('profitability')
            ->where('agent_id', '=', $agent->id)
            ->where('sphere_id', '=', $sphere_id)
            ->first();

        // Профит агента
        $result = $agent->getProfit($sphere_id, $period);

        // Считаем профитабильность агента за выбранный период
        // Если профит меньше или равен 0 - профитабильность выставляем в 0
        if($result['total'] <= 0) {
            $profitability = 0;
        }
        else {
            // Получаем максимальное и минимальное значение профита по сфере
            $sphere = Sphere::find($sphere_id);
            $ratio = $sphere->getAgentsProfitabilityRatio($period);

            // В противном случае считаем профитабильность по формуле
            // ([заработок агента]-MIN) / (MAX-MIN) - старая формула
            // ([заработок агента]-min)/(max-min) * (1-min/max)
            $profitability = ($result['total'] - $ratio['min']) / $ratio['diff'] * (1 - $ratio['min'] / $ratio['max']) * 100;
        }

        if($request->ajax()) {
            return response()->json([
                'result' => $result,
                'profit' => isset($agentSphere->profitability) ? $agentSphere->profitability : 0,
                'profit_period' => $profitability
            ]);
        }
        else {
            return view('admin.profit.detail', [
                'agent' => $agent,
                'result' => $result,
                'spheres' => $spheres,
                'profit' => isset($agentSphere->profitability) ? $agentSphere->profitability : 0,
                'profit_period' => $profitability
            ]);
        }
    }

    /**
     * Страница со списком аккаунт менеджеров
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function accManagers()
    {
        return view('admin.profit.accManagers');
    }

    /**
     * Страница со списком аккаунт менеджеров
     * Datatable
     *
     * @return mixed
     */
    public function accManagersData()
    {
        $role = Sentinel::findRoleBySlug('account_manager');
        $accManagers = $role->users()->select('id')->get()->lists('id')->toArray();
        $accManagers = AccountManager::whereIn('id', $accManagers)->get();

        return Datatables::of($accManagers)
            ->setTransformer(new AccManagersProfitTransformer())
            ->make();
    }

    /**
     * Детальная информация по профитабильности акк менеджера
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function accManagerDetail(Request $request, $id)
    {
        $id = (int)$id;

        if (!$id) {
            abort(403, 'Wrong account manager id');
        }

        if($request->ajax()) {
            if ($request->input('period')) {
                $period = explode('/', $request->input('period'));

                $period = [
                    'start' => trim($period[0]).' 00:00:00',
                    'end' => trim($period[1]).' 23:59:59'
                ];
            } else {
                $period = null;
            }
        }
        else {
            $date = Carbon::now()->subMonth();

            $period = [
                'start' => date('Y').'-'.$date->month.'-1 00:00:00',
                'end' => date('Y').'-'.$date->month.'-'.$date->format('t').' 23:59:59'
            ];
        }

        // Получаем данные акк. менеджера
        $accManager = AccountManager::find($id);

        // Сферы акк. менеджера
        $spheres = $accManager->spheres()->get();

        if($request->ajax()) {
            $sphere_id = $request->input('sphere');
        }
        else {
            // ID первой сферы акк менеджера
            $sphere_id = count($spheres) > 0 ? $spheres[0]->id : null;
        }

        // Список агентов акк. менеджера по одной сфере
        $agents = $accManager->agentsAll()->where('account_managers_agents.sphere_id', '=', $sphere_id)->get();

        // Кол-во агентов у акк. менеджера в сфере
        $agentCount = $agents->count();

        // Общие значения профитабильности и заявок на ввод/вывод денег
        $total = [
            'profits' => [
                'current_coeff' => 0,
                'coeff' => 0,
                'profit' => 0,
                'profitability' => 0,
                'count' => $agentCount,
                'acc_profitability' => 0
            ],
            'payments' => [
                'withdrawal' => 0,
                'replenishment' => 0,
                'profit' => 0,
                'count' => $agentCount,
                'acc_profitability' => 0
            ],
            'count' => $agentCount
        ];

        // Ищем мин. и макс. значения профита в сфере
        $sphere = Sphere::find($sphere_id);
        $ratio = $sphere->getAgentsProfitabilityRatio($period);

        // Проходимся по каждому агенту акк. менеджера
        foreach ($agents as $key => $agent) {
            // Получаем профит агента
            $agentProfit = $agent->getProfit($sphere_id, $period);
            $agents[$key]->profit = $agentProfit;

            // Получаем процент профитабильности агента
            $agentSphere = AgentSphere::select('profitability')
                ->where('agent_id', '=', $agent->id)
                ->where('sphere_id', '=', $sphere_id)
                ->first();

            $agentCoeff = isset($agentSphere->profitability) ? $agentSphere->profitability : 0;
            $agents[$key]->current_coeff = $agentCoeff;

            // Считаем профитабильность агента за выбранный период
            // Если профит меньше или равен 0 - профитабильность выставляем в 0
            if($agentProfit['total'] <= 0) {
                $profitability = 0;
            }
            else {
                // В противном случае считаем профитабильность по формуле
                // ([заработок агента]-MIN) / (MAX-MIN) - старая формула
                // ([заработок агента]-min)/(max-min) * (1-min/max)
                $profitability = ($agentProfit['total'] - $ratio['min']) / $ratio['diff'] * (1 - $ratio['min'] / $ratio['max']) * 100;
            }
            $agents[$key]->coeff = $profitability;

            // Заполняем общие значения профитабильности
            $total['profits']['current_coeff'] += $agentCoeff;
            $total['profits']['coeff'] += $profitability;
            $total['profits']['profit'] += $agentProfit['deposition_total']['total'] + $agentProfit['exposition_total']['total'];
            $total['profits']['profitability'] += $agentProfit['total'];

            // Получаем список заявок на ввод/вывод денег агентом
            // (только тех, которые обработал акк. менеджер)
            $requestsPayments = RequestPayment::where('initiator_id', '=', $agent->id)
                ->where('handler_id', '=', $accManager->id)
                ->where('status', '=', RequestPayment::STATUS_CONFIRMED);

            if($period) {
                $requestsPayments = $requestsPayments->where(function ($query) use ($period) {
                        $query->where('created_at', '>=', $period['start'])
                            ->where('created_at', '<=', $period['end']);
                    });
            }

            $requestsPayments = $requestsPayments->get();

            // Общие суммы введенных/выведеных денег агентом
            $agentPayments = [
                'withdrawal' => 0,
                'replenishment' => 0,
                'profit' => 0
            ];
            if(count($requestsPayments)) {
                foreach ($requestsPayments as $requestsPayment) {
                    if($requestsPayment->type == RequestPayment::TYPE_REPLENISHMENT) {
                        $agentPayments['replenishment'] += $requestsPayment->amount;
                    }
                    else {
                        $agentPayments['withdrawal'] += $requestsPayment->amount;
                    }
                }
                $agentPayments['profit'] = $agentPayments['replenishment'] - $agentPayments['withdrawal'];
            }
            // Заполняем общие значения заявок на ввод/вывод денег агентами акк. менеджера
            foreach ($agentPayments as $field => $val) {
                $total['payments'][$field] += $val;
            }
            $agent->payments = $agentPayments;
        }

        // Если агентов нет - ставим 1
        // Чтоб не было ошибки деления на 0
        $agentCount = $agentCount == 0 ? 1 : $agentCount;

        // Средний процент профитабильности по агентам акк. менеджера
        $total['profits']['current_coeff'] = $total['profits']['current_coeff'] / $agentCount;
        $total['profits']['coeff'] = $total['profits']['coeff'] / $agentCount;

        // Профитабильность акк. менеджера
        $total['profits']['acc_profitability'] = $total['profits']['profitability'] / $agentCount;

        $total['payments']['acc_profitability'] = $total['payments']['profit'] / $agentCount;

        if($request->ajax()) {
            return response()->json([
                'agents' => $agents,
                'total' => $total
            ]);
        }
        else {
            return view('admin.profit.accManagerDetail', [
                'accManager' => $accManager,
                'spheres' => $spheres,
                'agents' => $agents,
                'total' => $total
            ]);
        }
    }
}
