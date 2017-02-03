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

    public function __construct()
    {
        // получаем пользователя
        $this->user = JWTAuth::parseToken()->authenticate();
        $this->wallet = Wallet::where( 'user_id', $this->user->id )->first();

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


    // todo пока что тестовая
    // страница фильтра лидов
    public function obtain()
    {

//        $auctionData = Auction::
//                              where('status', 0)
//                            ->where( 'user_id', $this->user->id )
//                            ->with('lead')
//                            /*->with('maskName') */
//                            ->get();


        $auctionData = Auction::
            where('status', 0)
                ->where( 'user_id', 6 )
                ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id')
                ->with(
                    [
                        'lead' => function($query)
                        {
                            $query
                                ->select('id', 'opened', 'email', 'name', 'created_at')
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
                ->get()
                ->toArray()
            ;


        $data =
        [
            'id' => $this->user->id,
            'email' => $this->user->email,
            'wallet' => $this->wallet->earned + $this->wallet->buyed,
            'wasted' => $this->wallet->wasted,
            'auctionData' => $auctionData,

        ];

        return response()->json($data);
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