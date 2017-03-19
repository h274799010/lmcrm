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

        // id последнего итема на апликации
        $lastItemId = (int)$request->lastItemId;

        // id пользователя
        $userId = $this->user->id;


        if( $lastItemId == 0 ){

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
                                ->select('id', 'opened', 'email', 'sphere_id', 'name', 'operator_processing_time', 'created_at')
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


            $lastAuction = Auction::
                  where( 'status', 0 )
                ->where( 'user_id', $userId )
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
            ;

            $lastItemId = $lastAuction[ count($lastAuction)-1 ]->id;

        }else{

            $lastItem = Auction::find( $lastItemId );

            // выборка лидов и данных для аукциона
            $auctionList = Auction::
                  where( 'status', 0 )
                ->where( 'user_id', $userId )
                ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id', 'created_at')
                ->where( 'created_at', '<=', $lastItem->created_at )
                ->with(
                    [
                        'lead' => function($query)
                        {
                            $query
                                ->select('id', 'opened', 'email', 'sphere_id', 'name', 'created_at')
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

        }



        // добавляем лиду данные по маскам
        $auctionData = [];
        $auctionList->each(function( $auction ) use(&$auctionData, $userId){

            $openLead = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', $userId )->first();
            $openLeadOther = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', '<>', $userId )->first();

            if(!$openLead || !$openLeadOther) {

                // добавляем лиду атрибуты фильтра
                $auction->lead->getFilter();

                // добавляем лиду атрибуты фильтра
                $auction->lead->getAdditional();

                $auction->openLead = $openLead ? 'true' : 'false';

                $auction->openLeadOther = $openLeadOther ? 'true' : 'false';

                $auctionData[] = $auction;

                return true;
            }

            return false;
        });


        $this->userData['auctionData'] = $auctionData;
        $this->userData['lastItemId'] = $lastItemId;


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
     */
    public function deposited()
    {

//        $leads = $this->user->leads()->with('phone')->get();
        $leads = Lead::where('agent_id', $this->user->id)->with('phone')->get();

        if( !$leads->count() ){
            $leads = 'Нет лидов';
        }else{
//            $leads = $leads->toArray();
        }

        // добавляем маску в лид
        $leads = $leads->map(function( $lead ){

            $lead->sName = $lead->statusName();

            return $lead;

        });

        $data =
            [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'wallet' => $this->wallet->earned + $this->wallet->buyed,
                'wasted' => $this->wallet->wasted,
                'leads' => $leads,
            ];

        return response()->json($data);
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



}