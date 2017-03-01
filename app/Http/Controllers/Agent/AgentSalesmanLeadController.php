<?php

namespace App\Http\Controllers\Agent;

use App\Facades\CreateLead;
use App\Lmcrm\Lead;
use App\Models\Agent;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\Customer;
use App\Models\HistoryBadLeads;
use App\Models\LeadBitmask;
use App\Models\OpenLeads;
use App\Models\Organizer;
use App\Models\Salesman;
use App\Models\Sphere;
use App\Models\User;
use App\Transformers\ObtainedLeadsTransformer;
use App\Transformers\OpenedLeadsTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\LeadDepositorData;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;
use Validator;

class AgentSalesmanLeadController extends LeadController
{
    public $salesman;
    public $allSphere;

    public function __construct()
    {
        $this->user = Agent::find(Sentinel::getUser()->id);

        $salesman_id = Route::current()->getParameter('salesman_id');
        $this->salesman = Salesman::find($salesman_id);

        // получаем данные по все именам масок по всем сферам
        $spheres = $this->user->spheresWithMasks($salesman_id)->get();

        $spheres->load('filterAttr', 'leadAttr');

        $wallet = $this->salesman->wallet[0];

        // максимальная цена по маскам
        $maxPrice = 0;

        if(!$this->salesman->inRole('partner')) {
            // добавление статуса и времени
            $spheres->map(function( $item ) use ( $wallet, &$maxPrice ){

                // id сферы
                $sphere_id = $item->id;

                // добавление данных в маску
                $item->masks->map(function($item) use ($sphere_id, &$maxPrice, $wallet){

                    // получение данных фильтра маски
                    $agentMask = new AgentBitmask($sphere_id);
                    $maskItem = $agentMask->find( $item->mask_id );

                    if( $maskItem->status == 0){
                        return false;
                    }

                    // количество лидов, которое агент может купить по этой маске
                    $item->leadsCount = floor($wallet->balance/$maskItem->lead_price);


                    // добавление статуса
                    $item->status = $maskItem->status;
                    // добавление даты
                    $item->updated_at = $maskItem->updated_at;

                    if( $maxPrice < $maskItem->lead_price ){
                        $maxPrice = $maskItem->lead_price;
                    }

                    return $item;
                });

                return $item;
            });
        }

        $this->allSphere = $spheres;

        // данные по забракованным лидам
        $wasted = $wallet->wasted;

        // Данные по сферам для cookies
        $cookieSpheres = array();
        if($spheres) {
            foreach ($spheres as $key => $sphere) {
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
                'allSpheres' => $cookieSpheres
            ];

        $role = $this->salesman->roles()
            ->where('slug', '!=', 'agent')
            ->where('slug', '!=', 'salesman')
            ->first();
        $userData = array(
            'name' => $this->salesman->first_name.' '.$this->salesman->last_name,
            'role' => false,
            'status' => User::isBanned($this->salesman->id)
        );
        if($role->name) {
            $userData['role'] = $role->name;
        }

        $userIds = array($this->salesman->id);

        $badLeads = HistoryBadLeads::whereIn('depositor_id', $userIds)->count();


        // добавляем данные по балансу на страницу
        view()->share([
            'balance' => $balance,
            'salesman_id' => $this->salesman->id,
            'userData' => $userData,
            'badLeads' => $badLeads
        ]);

        // переводим данные по балансу в json
        $balanceJSON = json_encode($balance);

        // добавляем на страницу куки с данными по балансу
        Cookie::queue('salesman_balance', $balanceJSON, null, null, null, false, false);
    }

    /**
     * Выводит таблицу с отфильтрованными лидами
     * (только саму таблицу, строки добавляет метод obtainData)
     *
     * @return object
     */
    public function obtain(){

        // получаем данные по все именам масок по всем сферам
        $spheres = $this->allSphere;

        $view = 'agent.salesman.login.obtain';

        return view($view)->with('spheres', $spheres);
    }

    /**
     * Заполнение строк таблицы на странице obtain
     *
     *
     * @param  Request  $request
     * @param  boolean|integer  $salesman_id
     *
     * @return object
     */
    public function obtainData(Request $request)
    {

        // находим заданную сферу
        $sphere = Sphere::find( $request['sphere_id'] );

        $agent = $this->salesman;

        $user_id = $agent->id;

        $agentOpenedLeads = OpenLeads::where('agent_id', '=', $this->user->id)->select('lead_id')->get();
        $agentOpenedLeads = $agentOpenedLeads->lists('lead_id')->toArray();

        // выборка всех лидов агента
        $auctionData = Auction::where('status', 0)
            ->where( 'user_id', $user_id )
            ->where( 'sphere_id', $sphere->id )
            ->whereNotIn('lead_id', $agentOpenedLeads)
            ->with('lead') /*->with('maskName') */ ->get();

        // маска лида
        $leadBitmask = new LeadBitmask( $sphere->id );


        /** Проверяем наличие фильтра */

        if (count($request->only('filter'))) {
            // если фильтр есть

            // получаем данные фильтра
            $eFilter = $request->only('filter')['filter'];

            if(!empty($eFilter)) {
                // перебираем данные и проверяем на соответствие
                foreach ($eFilter as $eFKey => $eFVal) {

                    // проверяем ключ
                    switch($eFKey) {

                        // если фильтр по дате
                        case 'date':

                            // проверяем значение фильтра

                            if($eFVal=='2d') {
                                // два последних дня

                                // находим время
                                $date = new \DateTime();
                                // выбираем интервал
                                $date->sub(new \DateInterval('P2D'));

                                // отфильтровуем с аукционе только то, что соответсвтует интервалу
                                $auctionData = $auctionData->filter( function( $auction ) use ( $date ){
                                    return $auction['lead']['created_at'] >= $date->format('Y-m-d');
                                });


                            } elseif($eFVal=='1m') {
                                // последний месяц

                                // находим время
                                $date = new \DateTime();
                                // выбираем интервал
                                $date->sub(new \DateInterval('P1M'));

                                // отфильтровуем с аукционе только то, что соответсвтует интервалу
                                $auctionData = $auctionData->filter( function( $auction ) use ( $date ){
                                    return $auction['lead']['created_at'] >= $date->format('Y-m-d');
                                });


                            } else {
                                // если значения фильтра нет

                                // ничего не делаем
                            }

                            break;
                        default: ;
                    }
                }
            }
        }

        $auctionData = $auctionData->filter(function ($auction) use ($agent) {
            $openLead = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', $agent->id )->first();
            $openLeadOther = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', '<>', $agent->id )->first();

            if(!$openLead || !$openLeadOther) {
                return $auction;
            }
        });


        return Datatables::of( $auctionData )
            ->setTransformer(new ObtainedLeadsTransformer($sphere))
            ->make();
    }

    /**
     *
     * Лиды которые агент внес в систему
     *
     * @return object
     */
    public function deposited(){

        // находим данные продавца
        $salesman = $this->salesman;

        // находим все лиды с телефоном и сферой
        $leads = $salesman->leads()->with('phone', 'sphere')->get();

        // задаем имя вьюшки
        $view = 'agent.salesman.login.deposited';

        return view($view)->with('leads', $leads);
    }

    /**
     * Выводит все открытые лиды агента
     *
     * @return object
     */
    public function openedLeads($lead_id = false){

        $user = $this->salesman;

        $agent = $user->agent()->first();
        // Получаем сферы вместе со статусами для фильтра
        $spheres = $agent->onlySpheres()
            ->select('spheres.id', 'spheres.name')
            ->with([
                'statuses' => function($query) {
                    $query->select('id', 'sphere_id', 'stepname');
                }
            ])
            ->get()->toJson();

        $lead_id = Route::current()->getParameter('lead_id');

        if($lead_id) {
            return view('agent.salesman.login.opened', [ 'user' => $user, 'jsonSpheres' => $spheres, 'lead_id' => $lead_id ]);
        } else {
            return view('agent.salesman.login.opened', [ 'user' => $user, 'jsonSpheres' => $spheres ]);
        }

        //return view('agent.salesman.login.opened', [ 'user' => $user, 'jsonSpheres' => $spheres ]);
    }

    public function openedLeadsData(Request $request)
    {
        $user = $this->salesman;

        $openLeads = OpenLeads::select([
            'open_leads.id', 'open_leads.lead_id',
            'open_leads.agent_id','open_leads.mask_id',
            'open_leads.mask_name_id', 'open_leads.status',
            'open_leads.state',
            'open_leads.expiration_time'
        ])->where('open_leads.agent_id', '=', $user->id);

        if (count($request->only('filter'))) {
            // если фильтр есть

            // получаем данные фильтра
            $eFilter = $request->only('filter')['filter'];

            if(!empty($eFilter)) {
                // перебираем данные и проверяем на соответствие
                foreach ($eFilter as $eFKey => $eFVal) {

                    // проверяем ключ
                    switch($eFKey) {

                        // если фильтр по дате
                        case 'sphere':

                            if($eFVal != '') {
                                $openLeads = $openLeads->join('leads', function ($join) use ($eFVal) {
                                    $join->on('open_leads.lead_id', '=', 'leads.id')
                                        ->where('leads.sphere_id', '=', $eFVal);
                                });
                            }

                            break;
                        case 'status':

                            if($eFVal != '') {
                                $openLeads->where('open_leads.status', '=', $eFVal);
                            }

                            break;
                        default: ;
                    }
                }
            }
        }

        $openLeads = $openLeads->with([
            'lead' => function ($query) {
                $query->with('sphereStatuses');
            }
        ])
            ->with('maskName2')
            ->with('statusInfo')
            ->orderBy('open_leads.created_at', 'desc');

        return Datatables::of( $openLeads )
            ->setTransformer(new OpenedLeadsTransformer())
            ->make();
    }

    /**
     * Страница добавления комментария в органайзер
     *
     *
     * @param  integer  $lead_id
     *
     * @return object
     */
    public function addСomment($lead_id)
    {
        return view('agent.salesman.organizer.addComment')
            ->with( ['lead_id' => $lead_id, 'salesman_id' => $this->salesman->id] );
    }

    /**
     * Страница добавления напоминания в органайзер
     *
     *
     * @param  integer  $lead_id
     *
     * @return object
     */
    public function addReminder($lead_id)
    {
        return view('agent.salesman.organizer.addReminder')
            ->with( ['lead_id' => $lead_id, 'salesman_id' => $this->salesman->id] );
    }

    /**
     * Удаление записи из органайзера
     *
     *
     * @param integer $id
     *
     * @return object
     */
    public function deleteReminder($id)
    {
        $user_id = $this->salesman->id;

        $organizer = Organizer::where(['id'=>$id])->first();
        if ($organizer->openLead->agent_id == $user_id){
            $organizer->delete();
        }

        return response()->json(TRUE);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return object
     */
    public function create()
    {
        $data = CreateLead::create($this->salesman->id);

        return view('agent.lead.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @param  Request  $request
     *
     * @return Response
     * @return Redirect
     */
    public function store( Request $request )
    {
        $result = CreateLead::store($request, $this->salesman->id);

        return $result;
    }
}
