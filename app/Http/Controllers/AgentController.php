<?php

namespace App\Http\Controllers;

use App\Models\Agent;
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

        // добавляем данные по балансу на страницу
        view()->share([
            'balance' => $balance,
            'userData' => $userData
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
        $agents = $agent->agentsPrivetGroups()->select('users.id', 'users.email')->get()->pluck('email', 'id');

        return response()->json($agents);
    }
}
