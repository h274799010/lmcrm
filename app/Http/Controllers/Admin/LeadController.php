<?php

namespace App\Http\Controllers\Admin;

use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\OpenLeads;
use App\Models\OperatorSphere;
use App\Models\Sphere;
use App\Transformers\LeadTransformer;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Datatables;
use Illuminate\Support\Facades\Cookie;

class LeadController extends Controller
{
    public function __construct()
    {
        view()->share('type', 'lead');
    }

    public function show() {
        return $this->index();
    }
    /*
   * Display a listing of the resource.
   *
   * @return Response
   */
    public function index()
    {
        $filter = Cookie::get('adminOpenLeadsFilter');
        $filter = json_decode($filter, true);

        $selectedFilters = array(
            'account_manager' => false,
            'agent' => false,
            'operator' => false,
            'period' => false,
            'role' => false,
            'sphere' => false
        );

        if(count($filter) > 0) {
            foreach ($filter as $type => $id) {
                $sphere_id = $filter['sphere'];
                $accountManager_id = $filter['account_manager'];
                $operator_id = $filter['operator'];
                $agent_id = $filter['agent'];

                if($type != '') {
                    $selectedFilters[$type] = $id;
                }

                if($id) {
                    // Ищем данные в зависимости от выбранного фильтра
                    switch ($type) {
                        case 'sphere':

                            $sphere = Sphere::find($id);

                            $agents = $sphere->agentsAll()->select('users.id', \DB::raw('users.email AS name'))->get();
                            $accountManagers = $sphere->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                            $operators = $sphere->operators()->select('users.id', \DB::raw('users.email AS name'))->get();

                            break;
                        case 'account_manager':

                            $accountManager = AccountManager::find($id);

                            $spheres = $accountManager->spheres()->where('status', '=', 1)->select('spheres.id', 'spheres.name')->get();
                            $agents = $accountManager->agentsAll()->select('users.id', \DB::raw('users.email AS name'))->get();
                            $operators = $accountManager->operators()->select('users.id', \DB::raw('users.email AS name'))->get();

                            break;
                        case 'operator':

                            $operator = OperatorSphere::find($id);

                            $accountManagers = $operator->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                            $spheres = $operator->spheres()->where('status', '=', 1)->select('spheres.id', 'spheres.name')->get();

                            $agents = Agent::select('users.id', \DB::raw('users.email AS name'));
                            if(count($spheres)) {
                                $agents = $agents->join('agent_sphere', function ($join) use ($spheres) {
                                    $join->on('agent_sphere.agent_id', '=', 'users.id')
                                        ->whereIn('agent_sphere.sphere_id', $spheres->lists('id')->toArray());
                                });
                            }
                            if(count($accountManagers)) {
                                $agents = $agents->join('account_managers_agents', function ($join) use ($accountManagers) {
                                    $join->on('account_managers_agents.agent_id', '=', 'users.id')
                                        ->whereIn('account_managers_agents.account_manager_id', $accountManagers->lists('id')->toArray());
                                });
                            }

                            $agents = $agents->groupBy('users.id')->get();

                            break;
                        case 'agent':

                            $agent = Agent::find($id);

                            $accountManagers = $agent->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                            $spheres = $agent->spheres()->where('status', '=', 1)->select('spheres.id', 'spheres.name')->get();

                            $operators = OperatorSphere::select('users.id', \DB::raw('users.email AS name'));
                            if(count($spheres)) {
                                $operators = $operators->join('operator_sphere', function ($join) use ($spheres) {
                                    $join->on('operator_sphere.operator_id', '=', 'users.id')
                                        ->whereIn('operator_sphere.sphere_id', $spheres->lists('id')->toArray());
                                });
                            }
                            if(count($accountManagers)) {
                                $operators = $operators->join('account_managers_operators', function ($join) use ($accountManagers) {
                                    $join->on('account_managers_operators.operator_id', '=', 'users.id')
                                        ->whereIn('account_managers_operators.account_manager_id', $accountManagers->lists('id')->toArray());
                                });
                            }

                            $operators = $operators->groupBy('users.id')->get();

                            break;
                        default:
                            break;
                    }
                }
                else {
                    // Если фильтр сбрасывается (выбирается пустое значение)
                    // подгружаем все данные

                    $role = Sentinel::findRoleBySlug('account_manager');
                    $accountManagers = $role->users();

                    $role = Sentinel::findRoleBySlug('agent');
                    $agents = $role->users();

                    $role = Sentinel::findRoleBySlug('operator');
                    $operators = $role->users();

                    $spheres = Sphere::active();

                    if($sphere_id) {
                        $accountManagers = $accountManagers->join('account_manager_sphere', function ($join) use ($sphere_id) {
                            $join->on('account_manager_sphere.account_manager_id', '=', 'users.id')
                                ->where('account_manager_sphere.sphere_id', '=', $sphere_id);
                        });
                        $agents = $agents->join('agent_sphere', function ($join) use ($sphere_id) {
                            $join->on('agent_sphere.agent_id', '=', 'users.id')
                                ->where('agent_sphere.sphere_id', '=', $sphere_id);
                        });
                        $operators = $operators->join('operator_sphere', function ($join) use ($sphere_id) {
                            $join->on('operator_sphere.operator_id', '=', 'users.id')
                                ->where('operator_sphere.sphere_id', '=', $sphere_id);
                        });
                    }

                    if($accountManager_id) {
                        $spheres = $spheres->join('account_manager_sphere', function ($join) use ($accountManager_id) {
                            $join->on('account_manager_sphere.sphere_id', '=', 'spheres.id')
                                ->where('account_manager_sphere.account_manager_id', '=', $accountManager_id);
                        });
                        $agents = $agents->join('account_managers_agents', function ($join) use ($accountManager_id) {
                            $join->on('account_managers_agents.agent_id', '=', 'users.id')
                                ->where('account_managers_agents.account_manager_id', '=', $accountManager_id);
                        });
                        $operators = $operators->join('account_managers_operators', function ($join) use ($accountManager_id) {
                            $join->on('account_managers_operators.operator_id', '=', 'users.id')
                                ->where('account_managers_operators.account_manager_id', '=', $accountManager_id);
                        });
                    }

                    if($operator_id) {
                        $spheres = $spheres->join('operator_sphere', function ($join) use ($operator_id) {
                            $join->on('operator_sphere.sphere_id', '=', 'spheres.id')
                                ->where('operator_sphere.operator_id', '=', $operator_id);
                        });
                        $accountManagers = $accountManagers->join('account_managers_operators', function ($join) use ($operator_id) {
                            $join->on('account_managers_operators.account_manager_id', '=', 'users.id')
                                ->where('account_managers_operators.operator_id', '=', $operator_id);
                        });
                    }

                    if($agent_id) {
                        $spheres = $spheres->join('agent_sphere', function ($join) use ($agent_id) {
                            $join->on('agent_sphere.sphere_id', '=', 'spheres.id')
                                ->where('agent_sphere.agent_id', '=', $agent_id);
                        });
                        $accountManagers = $accountManagers->join('account_managers_agents', function ($join) use ($agent_id) {
                            $join->on('account_managers_agents.account_manager_id', '=', 'users.id')
                                ->where('account_managers_agents.agent_id', '=', $agent_id);
                        });
                    }

                    $accountManagers = $accountManagers->select('users.id', \DB::raw('users.email AS name'))->get();
                    $agents = $agents->select('users.id', \DB::raw('users.email AS name'))->get();
                    $operators = $operators->select('users.id', \DB::raw('users.email AS name'))->get();
                    $spheres = $spheres->select('spheres.id', 'spheres.name')->get();
                }
            }
        } else {
            $role = Sentinel::findRoleBySlug('account_manager');
            $accountManagers = $role->users()->select('id', 'email')->get();

            $role = Sentinel::findRoleBySlug('agent');
            $agents = $role->users()->select('id', 'email')->get();

            $role = Sentinel::findRoleBySlug('operator');
            $operators = $role->users()->select('id', 'email')->get();

            $spheres = Sphere::active()->get();
        }

        // Show the page
        return view('admin.lead.index', [
            'accountManagers' => $accountManagers,
            'agents' => $agents,
            'operators' => $operators,
            'spheres' => $spheres,
            'selectedFilters' => $selectedFilters
        ]);
    }

    public function data(Request $request)
    {
        $leads = OpenLeads::select('open_leads.id', 'open_leads.lead_id', 'open_leads.agent_id', 'open_leads.count', 'open_leads.status', 'open_leads.created_at')
            ->join('leads', 'leads.id', '=', 'open_leads.lead_id');

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // добавляем на страницу куки с данными по фильтру
            Cookie::queue('adminOpenLeadsFilter', json_encode($request->only('filter')['filter']), null, null, null, false, false);
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            // Пробегаемся по параметрам из фильтра
            foreach ($eFilter as $eFKey => $eFVal) {
                if($eFVal != 'empty' && $eFVal != '') {
                    switch ($eFKey) {
                        case 'sphere':
                            $leads = $leads->where('leads.sphere_id', '=', $eFVal);
                            break;
                        case 'account_manager':
                            $accountManager = AccountManager::find($eFVal);
                            $agents = $accountManager->agents()->get()->lists('id')->toArray();

                            $leads = $leads->whereIn('open_leads.agent_id', $agents);
                            break;
                        case 'operator':
                            $leads = $leads->join('operator', function ($join) use ($eFVal) {
                                $join->on('open_leads.lead_id', '=', 'operator.lead_id')
                                    ->where('operator.operator_id', '=', $eFVal);
                            });
                            break;
                        case 'agent':
                            $agent = Agent::find($eFVal);

                            $leads = $leads->where('open_leads.agent_id', '=', $agent->id);
                            break;
                        case 'role':
                            $role = Sentinel::findRoleBySlug($eFVal);
                            $agents = $role->users()->select('id')->get();
                            $agentsIds = $agents->lists('id')->toArray();

                            $leads = $leads->whereIn('open_leads.agent_id', $agentsIds);
                            break;
                        case 'period':
                            $eFVal = explode('/', $eFVal);

                            $start = trim($eFVal[0]);
                            $end = trim($eFVal[1]);

                            $leads = $leads->where('open_leads.created_at', '>=', $start.' 00:00:00')
                                ->where('open_leads.created_at', '<=', $end.' 23:59:59');
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        return Datatables::of($leads)
            ->setTransformer(new LeadTransformer)
            ->make();
    }

    /**
     * Получение данных для связанных фильтров
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
        $operator_id = $request->input('operator_id');
        $agent_id = $request->input('agent_id');

        $res = array();
        if($id) {
            // Ищем данные в зависимости от выбранного фильтра
            switch ($type) {
                case 'sphere':

                    $sphere = Sphere::find($id);

                    $res['agents'] = $sphere->agentsAll()->select('users.id', \DB::raw('users.email AS name'))->get();
                    $res['accountManagers'] = $sphere->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                    $res['operators'] = $sphere->operators()->select('users.id', \DB::raw('users.email AS name'))->get();

                    break;
                case 'account_manager':

                    $accountManager = AccountManager::find($id);

                    $res['spheres'] = $accountManager->spheres()->where('status', '=', 1)->select('spheres.id', 'spheres.name')->get();
                    $res['agents'] = $accountManager->agentsAll()->select('users.id', \DB::raw('users.email AS name'))->get();
                    $res['operators'] = $accountManager->operators()->select('users.id', \DB::raw('users.email AS name'))->get();

                    break;
                case 'operator':

                    $operator = OperatorSphere::find($id);

                    $res['accountManagers'] = $operator->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                    $res['spheres'] = $operator->spheres()->where('status', '=', 1)->select('spheres.id', 'spheres.name')->get();

                    $res['agents'] = Agent::select('users.id', \DB::raw('users.email AS name'));
                    if(count($res['spheres'])) {
                        $res['agents'] = $res['agents']->join('agent_sphere', function ($join) use ($res) {
                            $join->on('agent_sphere.agent_id', '=', 'users.id')
                                ->whereIn('agent_sphere.sphere_id', $res['spheres']->lists('id')->toArray());
                        });
                    }
                    if(count($res['accountManagers'])) {
                        $res['agents'] = $res['agents']->join('account_managers_agents', function ($join) use ($res) {
                            $join->on('account_managers_agents.agent_id', '=', 'users.id')
                                ->whereIn('account_managers_agents.account_manager_id', $res['accountManagers']->lists('id')->toArray());
                        });
                    }

                    $res['agents'] = $res['agents']->groupBy('users.id')->get();

                    break;
                case 'agent':

                    $agent = Agent::find($id);

                    $res['accountManagers'] = $agent->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                    $res['spheres'] = $agent->spheres()->where('status', '=', 1)->select('spheres.id', 'spheres.name')->get();

                    $res['operators'] = OperatorSphere::select('users.id', \DB::raw('users.email AS name'));
                    if(count($res['spheres'])) {
                        $res['operators'] = $res['operators']->join('operator_sphere', function ($join) use ($res) {
                            $join->on('operator_sphere.operator_id', '=', 'users.id')
                                ->whereIn('operator_sphere.sphere_id', $res['spheres']->lists('id')->toArray());
                        });
                    }
                    if(count($res['accountManagers'])) {
                        $res['operators'] = $res['operators']->join('account_managers_operators', function ($join) use ($res) {
                            $join->on('account_managers_operators.operator_id', '=', 'users.id')
                                ->whereIn('account_managers_operators.account_manager_id', $res['accountManagers']->lists('id')->toArray());
                        });
                    }

                    $res['operators'] = $res['operators']->groupBy('users.id')->get();

                    break;
                default:
                    break;
            }
        } else {
            // Если фильтр сбрасывается (выбирается пустое значение)
            // подгружаем все данные

            $role = Sentinel::findRoleBySlug('account_manager');
            $res['accountManagers'] = $role->users();

            $role = Sentinel::findRoleBySlug('agent');
            $res['agents'] = $role->users();

            $role = Sentinel::findRoleBySlug('operator');
            $res['operators'] = $role->users();

            $res['spheres'] = Sphere::active();

            if($sphere_id) {
                $res['accountManagers'] = $res['accountManagers']->join('account_manager_sphere', function ($join) use ($sphere_id) {
                    $join->on('account_manager_sphere.account_manager_id', '=', 'users.id')
                        ->where('account_manager_sphere.sphere_id', '=', $sphere_id);
                });
                $res['agents'] = $res['agents']->join('agent_sphere', function ($join) use ($sphere_id) {
                    $join->on('agent_sphere.agent_id', '=', 'users.id')
                        ->where('agent_sphere.sphere_id', '=', $sphere_id);
                });
                $res['operators'] = $res['operators']->join('operator_sphere', function ($join) use ($sphere_id) {
                    $join->on('operator_sphere.operator_id', '=', 'users.id')
                        ->where('operator_sphere.sphere_id', '=', $sphere_id);
                });
            }

            if($accountManager_id) {
                $res['spheres'] = $res['spheres']->join('account_manager_sphere', function ($join) use ($accountManager_id) {
                    $join->on('account_manager_sphere.sphere_id', '=', 'spheres.id')
                        ->where('account_manager_sphere.account_manager_id', '=', $accountManager_id);
                });
                $res['agents'] = $res['agents']->join('account_managers_agents', function ($join) use ($accountManager_id) {
                    $join->on('account_managers_agents.agent_id', '=', 'users.id')
                        ->where('account_managers_agents.account_manager_id', '=', $accountManager_id);
                });
                $res['operators'] = $res['operators']->join('account_managers_operators', function ($join) use ($accountManager_id) {
                    $join->on('account_managers_operators.operator_id', '=', 'users.id')
                        ->where('account_managers_operators.account_manager_id', '=', $accountManager_id);
                });
            }

            if($operator_id) {
                $res['spheres'] = $res['spheres']->join('operator_sphere', function ($join) use ($operator_id) {
                    $join->on('operator_sphere.sphere_id', '=', 'spheres.id')
                        ->where('operator_sphere.operator_id', '=', $operator_id);
                });
                $res['accountManagers'] = $res['accountManagers']->join('account_managers_operators', function ($join) use ($operator_id) {
                    $join->on('account_managers_operators.account_manager_id', '=', 'users.id')
                        ->where('account_managers_operators.operator_id', '=', $operator_id);
                });
            }

            if($agent_id) {
                $res['spheres'] = $res['spheres']->join('agent_sphere', function ($join) use ($agent_id) {
                    $join->on('agent_sphere.sphere_id', '=', 'spheres.id')
                        ->where('agent_sphere.agent_id', '=', $agent_id);
                });
                $res['accountManagers'] = $res['accountManagers']->join('account_managers_agents', function ($join) use ($agent_id) {
                    $join->on('account_managers_agents.account_manager_id', '=', 'users.id')
                        ->where('account_managers_agents.agent_id', '=', $agent_id);
                });
            }

            $res['accountManagers'] = $res['accountManagers']->select('users.id', \DB::raw('users.email AS name'))->get();
            $res['agents'] = $res['agents']->select('users.id', \DB::raw('users.email AS name'))->get();
            $res['operators'] = $res['operators']->select('users.id', \DB::raw('users.email AS name'))->get();
            $res['spheres'] = $res['spheres']->select('spheres.id', 'spheres.name')->get();
        }

        return response()->json($res);
    }
}
