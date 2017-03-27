<?php


namespace App\Http\Controllers\Agent;

use App\Helper\PayMaster;
use App\Helper\PayMaster\PayInfo;
use App\Helper\PayMaster\Pay;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Controller;
use App\Models\AgentBitmask;
use App\Models\LeadBitmask;
use App\Models\Organizer;
use App\Models\SphereStatuses;
use App\Models\Wallet;
//use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Psy\Util\Json;
use Validator;
use App\Models\Agent;
use App\Models\Salesman;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Sphere;
use App\Models\OpenLeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;
use App\Facades\Notice;
use App\Models\Auction;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Models\AgentsPrivateGroups;


class ApiController extends Controller
{

    /**
     * Данные пользователя
     *
     */
    private $user;


    /**
     * Данные по кошельку пользователя
     *
     */
    private $wallet;


    /**
     * Данные пользователя для отправки на апилкацию
     *
     */
    private $userData;


    /**
     * Конструктор
     *
     * выбирается пользователь и его основные данные
     */
    public function __construct()
    {
        // получаем пользователя
        $this->user = JWTAuth::parseToken()->authenticate();
        // кошелек пользователя
        $this->wallet = Wallet::where( 'user_id', $this->user->id )->first();

        // переменная с ролями пользователя
        $roles = [];
        // перебираем все роли апользователя
        $this->user->roles->each(function( $role ) use( &$roles ){
            // заносим роль в массив

            // проверка типа роли
            if( $role['slug'] == 'agent' || $role['slug'] == 'salesman' ){
                // если главная роль

                // добавляем по ключу role
                $roles['role'] = $role['slug'];

            }else{
                // если дополнительная роль

                // добавляем с ключом subRole
                $roles['subRole'] = $role['slug'];
            }
        });

        // формирование переменной с данными пользователя
        $this->userData =
        [
            'id' => $this->user->id,
            'email' => $this->user->email,
            'roles' => $roles,
            'wallet' => $this->wallet->earned + $this->wallet->buyed,
            'wasted' => $this->wallet->wasted,
        ];
    }


    /**
     * Создание нового лида
     *
     *
     * @param  Request  $request
     *
     * @return boolean
     */
    public function createLead( Request $request )
    {
        // выбираем данные для удобства
        $depositor_id = $this->user->id; // id депозитора
        $name = $request->name;          // имя клиента
        $phone = $request->phone;        // телефон клиента
        $comment = $request->comment;    // комментарий

        // создание нового лида
        $newLead = Lead::createNew( $depositor_id, $name, $phone, $comment );

        // если все нормально возвращается "Ок"
        if( $newLead  ){ return response()->json('Ok'); }

        // Если что-то пошло не так, возвращается 'Error'
        return response()->json('Error');
    }


    /**
     * Страница фильтра лидов
     *
     *
     * @param  Request  $request
     *
     * $return JSON
     */
    public function obtain( Request $request )
    {

        // лиды которые нужно пропустить
        $offset = (int)$request->offset;

        // id пользователя
        $userId = $this->user->id;

        // выборка лидов и данных для аукциона
        $auctionList = Auction::
        where( 'status', 0 )
            ->where( 'user_id', $userId )
            ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id', 'created_at')
            ->with(
                [
                    'lead' => function($query)
                    {
                        $query
                            ->with('phone')
                            ->select('id', 'opened', 'customer_id', 'email', 'sphere_id', 'name', 'operator_processing_time', 'created_at')
                        ;
                    },
                    'sphere' => function($query){
                        $query
                            ->select('id', 'name')
                        ;
                    },
                    'maskName' => function($query){
                        $query
                            ->select('id', 'name')
                        ;
                    }
                ])
            ->orderBy('created_at', 'desc')
            ->orderBy('id')
            ->skip( $offset )
            ->take(10)
            ->get()
        ;


        // добавляем лиду данные по маскам
        $auctionItems = [];
        $auctionList->each(function( $auction ) use(&$auctionItems, $userId){

            // проверка, открыт ли этот лид у агента
            $openLead = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', $userId )->first();
            // поверка, открыт ли этот лид у других агентов
            $openLeadOther = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', '<>', $userId )->first();

            if(!$openLead || !$openLeadOther) {

                // добавляем лиду атрибуты фильтра
                $auction->lead->getFilter();

                // добавляем лиду атрибуты фильтра
                $auction->lead->getAdditional();

                // обработка номера телефона (только первые 4 символа, остальные закрываются звездочками)
                $phone = str_pad(substr($auction->lead->phone->phone, 0, 4), strlen($auction->lead->phone->phone), '*', STR_PAD_RIGHT);

                // подготовка имени лида (имя частично прячется звездочками)
                $name = substr($auction->lead['name'], 0, 1) .'***' .mb_substr($auction->lead['name'], -1, 1);

                // собираются данные итема лида
                $auctionItems[] =
                [

                    // id лида
                    'id' => $auction->lead['id'],

                    // имя лида
                    'name' => $name,

                    // телефон
                    'phone' => $phone,

                    // количество открытий лида
                    'opened' => $auction->lead['opened'],

                    // открыт ли этот лид у агента, или нет
                    'openLead' => $openLead ? 'true' : 'false',

                    // открыт ли этот лид у других агентов
                    'openLeadOther' => $openLeadOther ? 'true' : 'false',

                    // время когда лид был обработан оператором и добавлен в систему
                    'system_added' => $auction->lead['operator_processing_time'] ? $auction->lead['operator_processing_time']->format('H:i d M Y') : NULL,

                    // время когда лид был добавлен непосредственно на аукцион агента
                    'auction_added' => $auction['created_at']->format('H:i d M Y'),

                    // данные сферы
                    'sphere' =>
                    [
                        'id' => $auction->lead['sphere']['id'],
                        'name' => $auction->lead['sphere']['name'],
                    ],

                    // данные маски
                    'mask' =>
                    [
                        'id' => $auction['maskName']['id'] ? $auction['maskName']['id'] : 0,
                        'name' => $auction['maskName']['name'] ? $auction['maskName']['name'] : 'deleted',
                    ],

                    // данные фильтра лида
                    'filter' => $auction->lead['filter'],

                    // дополнительные данные по лиду
                    'additional' => $auction->lead['additional'],
                ];

                return true;
            }

            return false;
        });

        // добавление в общие данные итемов аукциона
        $this->userData['auctionItems'] = $auctionItems;

        return response()->json( $this->userData );
    }


    /**
     * Страница фильтра лидов
     *
     *
     * @param  Request  $request
     *
     * $return JSON
     */
    public function obtainNew( Request $request )
    {
        // id последнего итема на апликации
        $lastItemId = (int)$request->lastItemId;

        // id пользователя
        $userId = $this->user->id;

        $lastItem = Auction::find( $lastItemId );

        Log::info($lastItem);


        $auction = Auction::
              where( 'status', 0 )
            ->where( 'user_id', $userId )
            ->where( 'created_at', '>',  $lastItem->created_at )
            ->count();


//        Log::info($auction);

        // добавляем лиду данные по маскам



        return response()->json( $auction );
    }


    /**
     * Страница отданных лидов пользователя
     *
     * @param  Request  $request
     *
     * $return JSON
     */
    public function deposited( Request $request )
    {

        // лиды которые нужно пропустить
        $offset = (int)$request->offset;

        // выбираем лиды
        $leads = Lead::
              where('agent_id', $this->user->id)
            ->with('phone', 'sphere')
            ->orderBy('created_at', 'desc')
            ->skip( $offset )
            ->take(10)
            ->get();

        // добавляем имя статуса в лид
        $leads = $leads->map(function( $lead ){

            // формат времени
            $lead->date = $lead->created_at->format('Y/m/d');

            // имя статуса лида
            $lead->sName = $lead->statusName();

            // вознаграждение агента за лид,
            // обработка в зависимости от того где лид, на аукционе или в приватной группе
            if( $lead->status != 8 ){

                // если лид еще не расчитан - возвращается 0, если расчитан - выбирается вся сумма
                $lead->earnings = $lead->payment_status == 0 ? 0 : PayInfo::getAgentsOpenedLeadsData( $lead->id, true );

            }else{

                // заработки по сделкам
                $lead->earnings = PayInfo::getClosedDealInGroupData( $lead->id, true );
            }

            return $lead;
        });

        // добавляем в общие данные отданых лидов
        $this->userData['leads'] = $leads;


        return response()->json( $this->userData );
    }


    /**
     * Данные открытых лидов
     *
     *
     * @param  Request  $request
     *
     * $return JSON
     */
    public function openedLeads( Request $request )
    {

        // лиды которые нужно пропустить
        $offset = (int)$request->offset;

        // id последнего итема на апликации
        $lastItemId = (int)$request->lastItemId;

        // id пользователя
        $userId = $this->user->id;

//        Log::info($offset);

        // Выбираем все открытые лиды агента с дополнительными данными
        $openLeads = OpenLeads::
              where( 'agent_id', $userId )
            ->with('maskName2')
            ->with(
                [
                    'lead' => function( $query ){
                        $query
                            ->with('sphereStatuses', 'sphere', 'phone');
                    },
                    'statusInfo',
                    'closeDealInfo'
                ]
            )
            ->orderBy('created_at', 'desc')
            ->skip( $offset )
            ->take(10)
            ->get();

//        Log::info($openLeads[3]);


        // добавляем лиду данные по маскам
        $openLeadsData = [];
        $openLeads->each(function( $openLead ) use(&$openLeadsData, $userId){

            // добавляем лиду атрибуты фильтра
            $openLead->lead->getFilter();

            // добавляем лиду атрибуты фильтра
            $openLead->lead->getAdditional();

            $openLead->date = $openLead->created_at->format('Y/m/d H:i');

//            Log::info( $openLead->lead->sphereStatuses );


            $statusInfo = $openLead->statusInfo;

            // данные по статусу
            $statusesData =
                [
                    1 => [],
                    2 => [],
                    3 => [],
                    4 => [],
                    5 => [],
                ];

            // обработка статусов
            $openLead->lead->sphereStatuses->statuses->each( function($status) use( &$statusesData, $statusInfo ){

                $statusData =
                    [
                        'id' => $status->id,
                        'name' => $status->stepname,
                        'comment' => $status->comment,
                        'additional_type' => $status->additional_type,
//                        'lock' => 'true',
                    ];





                // проверка текущего статуса
                if( $statusInfo && $status->id == $statusInfo->id){
                    // если текущий статус

                    // ставим true
                    $statusData['lock'] = 'true';

                }else{
                    // если это не текущий статус

                    // ставим false
                    $statusData['lock'] = 'false';
                }


                if( $status->type == 1){

                    if( $statusInfo && $statusInfo->position > $status->position ){
                        // ставим true
                        $statusData['lock'] = 'true';
                    }
                }


                $statusesData[ $status->type ][ $status->position - 1 ] = $statusData;

            });

            $openLead->statuses = $statusesData;

            $openLeadsData[] = $openLead;

            return true;
        });


        Log::info($openLeadsData[3]->statuses);


        $this->userData['openedLeads'] = $openLeadsData;
        $this->userData['lastItemId'] = $lastItemId;
        $this->userData['statuses'] = $openLeads->count() ? $openLeads[0]['lead']['sphereStatuses']['statuses'] : 'false';

        return response()->json( $this->userData );
    }


    /**
     * Открытие лида
     *
     *
     * @param  Request  $request
     *
     * @return JSON
     */
    public function openLead( Request $request )
    {

//        Log::info('open start');

        $lead_id = (int)$request->lead_id;

        $mask_id = (int)$request->mask_id;

        $amount = $request->amount;


        // находим лид
        $lead = Lead::find( $lead_id );

        // проверка типа агента

        $salesman_id = false;
        if( $salesman_id ){
            // если это salesman
            // выбираем модель salesman
            $user = Salesman::find($salesman_id);

        }else{
            // если это пользователь
            // достаем уже существующие данные
            $user = Agent::find( $this->user->id );
        }


        Log::info($amount);


        if( $amount == 'One' ){
            // пробуем открыть лид, статус записываем в переменную
            $openResult = $lead->open( $user, $mask_id );

            Log::info('make One');


        }else{
            // пробуем открыть лид, статус записываем в переменную
            $openResult = $lead->openAll( $user, $mask_id );

            Log::info('make All');
        }

//        Log::info($openResult);


        if(isset($openResult['error'])) {
            return response()->json( [ 'status'=>'false', 'data' => $openResult['error'] ] );
        }


        $openLead = OpenLeads::
                          where( 'agent_id', $user->id )
                        ->where( 'lead_id', $lead_id )
                        ->with('maskName2')
                        ->with( ['lead' => function( $query ){
                                    $query
                                        ->with('sphereStatuses')
                                        ->with('phone');
                        }])
                        ->first();

//        return response()->json( $openResult );
//        if($salesman_id) {
//            return redirect()->route('agent.salesman.openedLeads', [
//                'salesman_id' => $salesman_id,
//                'lead_id' => $lead->id
//            ]);
//        } else {
//            return redirect()->route('agent.lead.opened', [
//                'lead_id' => $lead->id
//            ]);
//        }


        return response()->json([ 'status' => 'true', 'openLead' => $openLead ]);
    }


    /**
     * Вывод детализации по передаче лида агентом другим агентам в группе
     *
     *
     * @param  Request  $request
     *
     * @return Json
     */
    public function privateGroup( Request $request ){

        $leadId = (int)$request->lead_id;

        // получаем лид
        $lead = Lead::find( $leadId );

        // выбираем статусы сферы
        $sphereStatuses = $lead->sphereStatuses->statuses;

        // массив со статусами ( status_id => stepname )
        $statuses[0] = 'No status';

        // перебираем все статусы и формируем массив со статусами
        $sphereStatuses->each(function( $status ) use (&$statuses){
            // добавление статуса в массив статусов
            $statuses[$status->id] = $status->stepname;
        });

        // получаем всех участников группы агента
        $members = AgentsPrivateGroups::
        where( 'agent_owner_id', $lead['agent_id'] )
            ->with(
                [
                    'memberData',
                    'openLead'=>function($query) use ($leadId){
                        // получаем только текущий лид
                        $query->where('lead_id', $leadId);
                    }
                ]
            )
            ->get();

        // коллекция с агентами для которых лид был открыт
        $membersOpen = collect();
        // коллекция с агентами для которых лид небыл открыт
        $membersNotOpen = collect();

        // перебор всех участников группы и выборка нужных данных
        $members->each(function($item) use (&$membersOpen, &$membersNotOpen, $statuses){

            // проверка открытых лидов у участника
            if( $item['openLead']->count()==0 ){
                // если нет открытых лидов

                $data =
                    [
                        'id' => $item['memberData']['id'],
                        'email' => $item['memberData']['email'],
                    ];


                // todo добавляем данные в массив с агентами, которым лид не добавлен
                $membersNotOpen->push($data);

            }else{
                // если лид открыт для участника

                $data =
                    [
                        'id' => $item['memberData']['id'],
                        'email' => $item['memberData']['email'],
                        'status' => $statuses[ $item['openLead'][0]['status'] ]
                    ];

                // todo добавляем данные в массив с агентами, которым лид был добавлен
                $membersOpen->push($data);
            }
        });


        return response()->json(
            [
                'membersOpen' => $membersOpen,
                'membersNotOpen' => $membersNotOpen
            ]
        );
    }

}