<?php

namespace App\Http\Controllers\AccountManager;

use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\OpenLeads;
use App\Models\Operator;
use App\Models\Sphere;
use App\Transformers\LeadTransformer;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Datatables;

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
        $accountManager = AccountManager::find(Sentinel::getUser()->id);

        $agents = $accountManager->agents()->select('users.id', 'users.email')->get();

        $operators = $accountManager->operators()->select('users.id', 'users.email')->get();

        $spheres = $accountManager->spheres()->where('status', '=', 1)->get();

        // Show the page
        return view('accountManager.lead.index', [
            'agents' => $agents,
            'operators' => $operators,
            'spheres' => $spheres
        ]);
    }

    public function data(Request $request)
    {
        $accountManager = AccountManager::find(Sentinel::getUser()->id);

        $accountManagerAgents = $accountManager->agents()->select('users.id')->get()->lists('id')->toArray();

        $leads = OpenLeads::whereIn('open_leads.agent_id', $accountManagerAgents)->select('open_leads.id', 'open_leads.lead_id', 'open_leads.agent_id', 'open_leads.count', 'open_leads.status', 'open_leads.created_at')
            ->join('leads', 'leads.id', '=', 'open_leads.lead_id');

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            // Пробегаемся по параметрам из фильтра
            foreach ($eFilter as $eFKey => $eFVal) {
                if($eFVal != 'empty' && $eFVal != '') {
                    switch ($eFKey) {
                        case 'sphere':
                            $leads = $leads->where('leads.sphere_id', '=', $eFVal);
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
        $accountManager = AccountManager::find(Sentinel::getUser()->id);

        $accountManagerAgents = $accountManager->agents()->select('users.id')->get()->lists('id')->toArray();
        $accountManagerOperators = $accountManager->operators()->select('users.id')->get()->lists('id')->toArray();
        $accountManagerSpheres = $accountManager->spheres()->where('spheres.status', '=', 1)->select('spheres.id')->get()->lists('id')->toArray();

        $type = $request->input('type');
        $id = $request->input('id');

        $sphere_id = $request->input('sphere_id');
        $operator_id = $request->input('operator_id');
        $agent_id = $request->input('agent_id');

        $res = array();
        if($id) {
            // Ищем данные в зависимости от выбранного фильтра
            switch ($type) {
                case 'sphere':

                    $sphere = Sphere::find($id);

                    $res['agents'] = $sphere->agentsAll()->whereIn('users.id', $accountManagerAgents)->select('users.id', \DB::raw('users.email AS name'))->get();
                    $res['operators'] = $sphere->operators()->whereIn('users.id', $accountManagerOperators)->select('users.id', \DB::raw('users.email AS name'))->get();

                    break;
                case 'operator':

                    $operator = Operator::find($id);

                    $res['spheres'] = $operator->spheres()->whereIn('spheres.id', $accountManagerSpheres)->where('spheres.status', '=', 1)->select('spheres.id', 'spheres.name')->get();

                    $res['agents'] = Agent::whereIn('users.id', $accountManagerAgents)->select('users.id', \DB::raw('users.email AS name'));
                    if(count($res['spheres'])) {
                        $res['agents'] = $res['agents']->join('agent_sphere', function ($join) use ($res) {
                            $join->on('agent_sphere.agent_id', '=', 'users.id')
                                ->whereIn('agent_sphere.sphere_id', $res['spheres']->lists('id')->toArray());
                        });
                    }

                    $res['agents'] = $res['agents']->get();

                    break;
                case 'agent':

                    $agent = Agent::find($id);

                    $res['spheres'] = $agent->spheres()->whereIn('spheres.id', $accountManagerSpheres)->where('spheres.status', '=', 1)->select('spheres.id', 'spheres.name')->get();

                    $res['operators'] = Operator::whereIn('users.id', $accountManagerOperators)->select('users.id', \DB::raw('users.email AS name'));
                    if(count($res['spheres'])) {
                        $res['operators'] = $res['operators']->join('operator_sphere', function ($join) use ($res) {
                            $join->on('operator_sphere.operator_id', '=', 'users.id')
                                ->whereIn('operator_sphere.sphere_id', $res['spheres']->lists('id')->toArray());
                        });
                    }

                    $res['operators'] = $res['operators']->get();

                    break;
                default:
                    break;
            }
        } else {
            // Если фильтр сбрасывается (выбирается пустое значение)
            // подгружаем все данные

            $role = Sentinel::findRoleBySlug('agent');
            $res['agents'] = $role->users()->whereIn('users.id', $accountManagerAgents);

            $role = Sentinel::findRoleBySlug('operator');
            $res['operators'] = $role->users()->whereIn('users.id', $accountManagerOperators);

            $res['spheres'] = Sphere::active()->whereIn('spheres.id', $accountManagerSpheres);

            if($sphere_id) {
                $res['agents'] = $res['agents']->join('agent_sphere', function ($join) use ($sphere_id) {
                    $join->on('agent_sphere.agent_id', '=', 'users.id')
                        ->where('agent_sphere.sphere_id', '=', $sphere_id);
                });
                $res['operators'] = $res['operators']->join('operator_sphere', function ($join) use ($sphere_id) {
                    $join->on('operator_sphere.operator_id', '=', 'users.id')
                        ->where('operator_sphere.sphere_id', '=', $sphere_id);
                });
            }

            if($operator_id) {
                $res['spheres'] = $res['spheres']->join('operator_sphere', function ($join) use ($operator_id) {
                    $join->on('operator_sphere.sphere_id', '=', 'spheres.id')
                        ->where('operator_sphere.operator_id', '=', $operator_id);
                });
            }

            if($agent_id) {
                $res['spheres'] = $res['spheres']->join('agent_sphere', function ($join) use ($agent_id) {
                    $join->on('agent_sphere.sphere_id', '=', 'spheres.id')
                        ->where('agent_sphere.agent_id', '=', $agent_id);
                });
            }

            $res['agents'] = $res['agents']->select('users.id', \DB::raw('users.email AS name'))->get();
            $res['operators'] = $res['operators']->select('users.id', \DB::raw('users.email AS name'))->get();
            $res['spheres'] = $res['spheres']->select('spheres.id', 'spheres.name')->get();
        }

        return response()->json($res);
    }
}
