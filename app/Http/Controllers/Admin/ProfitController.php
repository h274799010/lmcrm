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

        $leads = $agent->leads()->with('sphere', 'openLeads')->get();

        /**
         * Структура массива деталей по лиду
         *
         * [
         *      'type' => '', // Тип строки: "Deposition" или "Deposition + Deal"
         *      'revenue_share' => [
         *          'from_deals' => '', // Профит системы со сделки
         *          'from_leads' => ''  // Профит системы с открытия лида
         *      ],
         *          'max_opened' => '', // Максимальное кол-во открытий лида в сфере
         *      'opened' => [ // Открытия лида: Номер открытия => Цена по которой открыли
         *          1 => 'price',
         *          2 => 'price'
         *      ],
         *      'deals' => [ // Профит системы с закрытой сделки
         *          'total' => '', // сумма на которую закрыли сделку
         *          'our' => ''    // процент от сделки, который пологается системе: $deal_price * $profit_from_deals / 100%
         *      ],
         *      'auction' => [ // Профит системы с аукциона
         *          'leads' => '', // Общий профит системы за открытия лида
         *          'deals' => '', // Общий профит системы за закрытые сделки
         *          'total' => '' // Общий профит системы: $sum_leads_auction + $deals
         *      ],
         *      'operator' => '', // Цена по которой лид был обработан оператором
         *      'profit' => [ // Окончательный профит системы
         *          'leads' => '', // Профит за открытия лидов
         *          'deals' => '', // Профит за закрыьтия сделок
         *          'total' => ''  // Общий профит системы: $leads + $deals
         *      ]
         * ]
         */
        $result = [
            'details' => [],
            'bayed' => [],
            'profit' => [
                'revenue_share' => [
                    'from_deals' => 0,
                    'from_leads' => 0,
                    'from_dealmaker' => 0,
                ],
                'max_opened' => 0,
                'opened' => 0,
                'deals' => [
                    'total' => 0,
                    'our' => 0,
                ],
                'auction' => [
                    'leads' => 0,
                    'deals' => 0,
                    'total' => 0,
                ],
                'operator' => 0,
                'profit' => [
                    'leads' => 0,
                    'deals' => 0,
                    'total' => 0
                ]
            ],
            'profit_bayed' => [
                'revenue_share' => [
                    'from_deals' => 0,
                    'from_leads' => 0,
                    'from_dealmaker' => 0,
                ],
                'max_opened' => 0,
                'opened' => 0,
                'deals' => [
                    'total' => 0,
                    'our' => 0,
                ],
                'auction' => [
                    'leads' => 0,
                    'deals' => 0,
                    'total' => 0,
                ],
                'operator' => 0,
                'profit' => [
                    'leads' => 0,
                    'deals' => 0,
                    'total' => 0
                ]
            ],
        ];
        // Профит по внесенным лидам
        foreach ($leads as $lead) {
            $details = $lead->getDepositionsProfit();

            foreach ($details as $key => $val) {
                if($key == 'type') {
                    continue;
                }
                if($key == 'opened') {
                    foreach ($val as $val2) {
                        $result['profit'][$key] += $val2;
                    }
                    continue;
                }
                if(is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $result['profit'][$key][$key2] += (float)$val2;
                    }
                }
                else {
                    $result['profit'][$key] += (float)$val;
                }
            }

            $result['details'][] = $details;
            //$result = $tmp;
        }

        // Профит по открытым лидам
        $openLeads = $agent->openLeads()->get();
        foreach ($openLeads as $openLead) {
            $details = $openLead->getBayedProfit();

            foreach ($details as $key => $val) {
                if($key == 'type') {
                    continue;
                }
                if($key == 'opened') {
                    foreach ($val as $val2) {
                        $result['profit_bayed'][$key] += $val2;
                    }
                    continue;
                }
                if(is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $result['profit_bayed'][$key][$key2] += (float)$val2;
                    }
                }
                else {
                    $result['profit_bayed'][$key] += (float)$val;
                }
            }

            $result['bayed'][] = $details;
        }

        //dd($result);

        return view('admin.profit.detail', [
            'agent' => $agent,
            'result' => $result,
            'depositedProfit' => [
                'count' => count($leads),
                'profit' => $result['profit']['profit']['total'] / (count($leads) > 0 ? count($leads) : 1)
            ],
            'bayedProfit' => [
                'count' => count($openLeads),
                'profit' => $result['profit_bayed']['profit']['total'] / (count($openLeads) > 0 ? count($openLeads) : 1)
            ],
        ]);
    }
}
