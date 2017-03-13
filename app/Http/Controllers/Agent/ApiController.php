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

                $auctionData[] = $auction;

                return true;
            }

            return false;
        });


//        $data =
//        [
//            'id' => $this->user->id,
//            'email' => $this->user->email,
//            'wallet' => $this->wallet->earned + $this->wallet->buyed,
//            'wasted' => $this->wallet->wasted,
//            'auctionData' => $auctionData,
//
//        ];

        $this->userData['auctionData'] = $auctionData;

//        return response()->json($data);
        return response()->json( $this->userData );

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


    // todo пока что тестовая
    // страница открытых лидов
    public function openedLeads()
    {

        // Выбираем все открытые лиды агента с дополнительными данными
        $openLeads = OpenLeads::
        where( 'agent_id', $this->user->id )
            ->with('maskName2')
            ->with( ['lead' => function( $query ){
                $query
                    ->with('sphereStatuses')
                    ->with('phone');
            }])
            ->get();


        $data =
            [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'wallet' => $this->wallet->earned + $this->wallet->buyed,
                'wasted' => $this->wallet->wasted,
                'openLeads' => $openLeads,
                'statuses' => $openLeads[0]['lead']['sphereStatuses']['statuses'],
            ];

        return response()->json($data);
    }


}