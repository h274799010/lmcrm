<?php

namespace App\Http\Controllers\Admin;

use App\Models\AccountManager;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\Customer;
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
        $role = Sentinel::findRoleBySlug('account_manager');
        $accountManagers = $role->users()->get();

        $role = Sentinel::findRoleBySlug('agent');
        $agents = $role->users()->get();

        // Show the page
        return view('admin.lead.index', [
            'accountManagers' => $accountManagers,
            'agents' => $agents
        ]);
    }

    public function data(Request $request)
    {
        $leads = Lead::all();

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            // Пробегаемся по параметрам из фильтра
            foreach ($eFilter as $eFKey => $eFVal) {
                if($eFVal != 'empty' && $eFVal != '') {
                    switch ($eFKey) {
                        case 'lead_status':
                            $leads = $leads->filter(function ($lead) use ($eFVal) {
                                return $lead->status == $eFVal;
                            });
                            break;
                        case 'auction_status':
                            $leads = $leads->filter(function ($lead) use ($eFVal) {
                                return $lead->auction_status == $eFVal;
                            });
                            break;
                        case 'payment_status':
                            $leads = $leads->filter(function ($lead) use ($eFVal) {
                                return $lead->payment_status == $eFVal;
                            });
                            break;
                        case 'account_manager':
                            $accountManager = AccountManager::find($eFVal);
                            $agents = $accountManager->agents()->get()->lists('id')->toArray();

                            $leads = $leads->whereIn('agent_id', $agents);
                            break;
                        case 'agent':
                            $agent = Agent::find($eFVal);

                            $leads = $leads->filter(function ($lead) use ($agent) {
                                return $lead->agent_id == $agent->id;
                            });
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

    public function getFilter(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        if($id) {
            if($type == 'agent') {
                $agent = Agent::find($id);
                $accountManagers = $agent->accountManagers()->get();
                $result = $accountManagers->lists('email', 'id')->toArray();
            } else {
                $accountManager = AccountManager::find($id);
                $agents = $accountManager->agents()->get();
                $result = $agents->lists('email', 'id');
            }
        } else {
            $result = array();
        }

        return response()->json(['result' => $result]);
    }
}
