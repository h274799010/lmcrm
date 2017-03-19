<?php

namespace App\Http\Controllers;

use App\Models\AgentsPrivateGroups;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\HistoryBadLeads;
use App\Models\Salesman;
use App\Models\AgentBitmask;
use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
//use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Sentinel;
use Cookie;
use Illuminate\Http\Response;
use Validator;

class AgentController extends BaseController
{
//    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use DispatchesJobs, ValidatesRequests;


    /** основные данные */

    // переменная с объектом пользователя
    public $user;

    // класс пользователя (agent или salesman)
    public $userClass;

    // кошелек пользователя
    public $wallet;


    // конструктор класса
    public function __construct()
    {

        $user = Sentinel::getUser();

        if( !$user ){ return false; }

        // получение id пользователя
        $this->uid = Sentinel::getUser()->id;


        /** Проверка роли пользователя (salesman или agent) */

        if(Sentinel::inRole('agent')) {
            // если агент

            // получаем объект агента
            $this->user = Agent::findOrFail($this->uid);
            // устанавливаем класс пользователя
            $this->userClass = 'Agent';

        } elseif(Sentinel::inRole('salesman')) {
            // если продавец (salesman)

            // получаем объект продавца
            $this->user  = Salesman::findOrFail($this->uid);
            // устанавливаем класс продавца
            $this->userClass = 'Salesman';

        } else {
            // если ни одна роль не подходит

            // переходим на страницу логина
            return redirect()->route('login');
        }

        // получение данных по кошельку
        $wallet = $this->user->wallet()->first();

//        dd($wallet);

        // получение данных по сфере
        $this->spheres = $this->user->spheres;

        // todo удалить, вместе со всем что от него зависит, перейти на spheres
        // получение данных по сфере
        $this->sphere = $this->user->sphere();

        // если сферы нет
        if( $this->sphere ){

            // id сферы
            $sphere_id = $this->sphere->id;

            // получение строки маски агента
            $this->mask = new AgentBitmask($sphere_id,$this->uid);

            // максимальная цена по маскам
            $maxPrice = 0;

            // todo выбор сфер сделать полностью на этом методе
            // todo когда будет нормальный битмаск и тблица с именами масок
            // получение всех сфер вместе с масками
            $allSpheres = $this->user->spheresWithMasks;

            // добавление статуса, времени и прайс
            $allSpheres->map(function( $item ) use ( &$maxPrice, $wallet ){

                // id сферы
                $sphere_id = $item->id;

                // добавление данных в маску
                $item->masks->map(function($item) use ( $sphere_id, &$maxPrice, $wallet ){

                    // получение данных фильтра маски
                    $agentMask = new AgentBitmask($sphere_id);
                    $maskItem = $agentMask->find( $item->mask_id );
                    if(!$maskItem) {
                        return false;
                    }

                    if( $maskItem->status == 0){
                        return false;
                    }

                    // количество лидов, которое агент может купить по этой маске
                    $item->leadsCount = floor($wallet->balance/$maskItem->lead_price);


                    // добавление статуса
                    $item->status = $maskItem->status;
                    // добавление даты
                    $item->lead_price = $maskItem->lead_price;
                    // добавление даты
                    $item->updated_at = $maskItem->updated_at;

                    if( $maxPrice < $maskItem->lead_price ){
                        $maxPrice = $maskItem->lead_price;
                    }

                    return $item;
                });

                return $item;
            });

            $this->allSphere = $allSpheres;

            // минимальное количество лидо которое может купить агент
            // сколько агент может купить лидов по маске с максимальным прайсом
            //$minLeadsToBuy = ( $maxPrice && $wallet )?floor($wallet->balance/$maxPrice):0;

        } else{

            $allSpheres = false;
            //$minLeadsToBuy = 0;

        }

//        dd($wallet);

        // данные по забракованным лидам
        $wasted = $wallet->wasted;

        // Данные по сферам для cookies
        $cookieSpheres = array();
        if($allSpheres) {
            foreach ($allSpheres as $key => $sphere) {
                // Имя сферы
                $cookieSpheres[$key]['name'] = $sphere->name;

                // Данные по маскам в сфере
                $cookieSpheres[$key]['masks'] = array();
                foreach ($sphere->masks as $k => $mask) {
                    //$cookieSpheres[$key]['masks'][$k]['status'] = $mask->status;
                    $cookieSpheres[$key]['masks'][$k]['name'] = $mask->name;
                    $cookieSpheres[$key]['masks'][$k]['leadsCount'] = $mask->leadsCount;
                }
            }
        }

        // данные по балансу в шапке
        $balance =
        [
            'wasted' => $wasted,
            //'minLeadsToBuy' => $minLeadsToBuy,
            'allSpheres' => $cookieSpheres
        ];

        $role = $this->user->roles()
            ->where('slug', '!=', 'agent')
            ->where('slug', '!=', 'salesman')
            ->first();
        $userData = array(
            'name' => $this->user->first_name.' '.$this->user->last_name,
            'role' => false,
            'status' => User::isBanned($this->user->id)
        );
        if($role->name) {
            $userData['role'] = $role->name;
        }

        $userIds = array($this->user->id);

        if($this->user->inRole('agent')) {
            $salesmans = $this->user->salesmen()->get()->lists('id')->toArray();

            $userIds = array_merge($userIds, $salesmans);
        }

        $badLeads = HistoryBadLeads::whereIn('depositor_id', $userIds)->count();

        $permissions = $this->user->permissions;

        // добавляем данные по балансу на страницу
        view()->share([
            'balance' => $balance,
            'userData' => $userData,
            'badLeads' => $badLeads,
            'permissions' => $permissions
        ]);

        // переводим данные по балансу в json
        $balanceJSON = json_encode($balance);

        // добавляем на страницу куки с данными по балансу
        Cookie::queue('balance', $balanceJSON, null, null, null, false, false);

        return true;
    }

    public function getAgentPrivateGroup()
    {
        $agent = Agent::find(\Cartalyst\Sentinel\Laravel\Facades\Sentinel::getUser()->id);
        $agents = $agent->agentsPrivetGroups()->where('agents_private_groups.status', '=', AgentsPrivateGroups::AGENT_ACTIVE)->select('users.id', 'users.email')->get()->pluck('email', 'id');

        return response()->json($agents);
    }

    /**
     * Получение списка агентов в группе на странице управления приватной группой
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentPrivateGroup()
    {
        $agent = Agent::find(\Cartalyst\Sentinel\Laravel\Facades\Sentinel::getUser()->id);
        $agents = $agent->agentsPrivetGroups()->select('users.id', 'users.email', 'agents_private_groups.status', 'agents_private_groups.revenue_share')
            ->orderBy('agents_private_groups.status', 'desc')->get();

        $statuses = AgentsPrivateGroups::getStatusTypeName();

        return view('agent.privateGroup.index', [
            'agents' => $agents,
            'statuses' => $statuses
        ]);
    }

    /**
     * Поиск агентов для группы
     *
     * @param Request $request
     * @return mixed
     */
    public function searchPrivateGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json(array(
                'errors' => $validator->errors()
            ));
        }
        $keyword = $request->input('keyword');

        $agent = Agent::find(\Cartalyst\Sentinel\Laravel\Facades\Sentinel::getUser()->id);
        $agentsInPrivateGroup = $agent->agentsPrivetGroups()->select('users.id')->get()->lists('id')->toArray();

        $agentsInPrivateGroup[] = $agent->id;

        $agents = Agent::SearchByKeyword($keyword)->whereNotIn('id', $agentsInPrivateGroup)->get();

        return response()->json($agents);
    }

    /**
     * Добавление агента в группу
     *
     * @param Request $request
     * @return mixed
     */
    public function addAgentInPrivateGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'revenue_share' => 'required|numeric|min:1|max:100'
        ]);

        if($validator->fails()) {
            return response()->json(array(
                'status' => 'fail',
                'errors' => $validator->errors()
            ));
        }

        $agent_id = (int)$request['id'];

        if( !$agent_id ) {
            abort(403, 'Wrong agent id');
        }

        $agent = Sentinel::getUser();
        $attachAgent = Agent::find($agent_id);
        $revenue_share = (float)$request->input('revenue_share');

        $privateGroup = new AgentsPrivateGroups();
        $privateGroup->agent_owner_id = $agent->id;
        $privateGroup->agent_member_id = $attachAgent->id;
        $privateGroup->status = 0;
        $privateGroup->revenue_share = $revenue_share;
        $privateGroup->save();

        return response()->json([
            'status' => 'success',
            'agent' => $attachAgent
        ]);
    }

    /**
     * Удаление агента из группы
     *
     * @param Request $request
     * @return mixed
     */
    public function deleteAgentInPrivateGroup(Request $request)
    {
        $agent_id = (int)$request['id'];

        if( !$agent_id ) {
            abort(403, 'Wrong agent id');
        }

        $agent = Sentinel::getUser();
        $attachedAgent = Agent::find($agent_id);

        AgentsPrivateGroups::where('agent_owner_id', '=', $agent->id)
            ->where('agent_member_id', '=', $attachedAgent->id)->delete();

        return response()->json(true);
    }
}
