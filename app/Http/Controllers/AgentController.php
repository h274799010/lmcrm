<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Salesman;
use App\Models\AgentBitmask;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
//use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Sentinel;


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
        // получение данных по сфере
        $sphere = $this->user->sphere();
        // id сферы
        $sphere_id = $sphere->id;

        // получение строки маски агента
        $this->mask = new AgentBitmask($sphere_id,$this->uid);

        $maxPrice = 0;

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


        $wasted = $wallet->wasted;



        /**
         * Данные агента по средствам
         *
         * количество лидов, которое может купить агент
         *
         */
//        $balance = ( $price && $price->lead_price && $wallet )?floor($wallet->balance/$price->lead_price):0;
        $minLeadsToBuy = ( $maxPrice && $wallet )?floor($wallet->balance/$maxPrice):0;


        $balance =
        [
            'wasted' => $wasted,
            'minLeadsToBuy' => $minLeadsToBuy,
            'allSpheres' => $allSpheres
        ];

//        dd($balance);

        view()->share('balance', $balance);

//        view()->share('balance', [ 'wasted' => $wasted, 'minLeadsToBuy' $minLeadsToBuy, $allSpheres]);

        return true;
    }
}
