<?php

namespace App\Models;

use App\Helper\PayMaster\Pay;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lead;
use App\Models\AgentBitmask;

class OpenLeads extends Model {


    /**
     * Название таблицы
     *
     *
     * @var string
     */
    protected $table="open_leads";


    /**
     * Состояния открытого лида
     *
     * На странице открытых лидов отображаются как
     * дополнительные статусы
     *
     * по этим параметрам, в итоге будет проходить расчет за лид
     *
     *
     * @var array
     */
    public static $state =
    [
        0 => 'default',      // значение по умолчанию, означает good_lead
        1 => 'bad_lead',     // плохой лид
        2 => 'deal_closed',  // сделка закрыта
    ];


    /**
     * Связь с таблицей лидов
     *
     */
    public function lead(){
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }


    /**
     * Связь с таблицей пользователей
     *
     */
    public function agent(){
        return $this->hasMany('App\Models\Agent', 'id', 'agent_id');
    }

    public function maskName2() {
        return $this->hasOne('App\Models\UserMasks', 'id', 'mask_name_id');
    }


    /**
     * Связь с таблицей органайзера
     *
     */
    public function organizer(){
        return $this->hasMany('App\Models\Organizer','open_lead_id', 'id')->orderBy('time','desc');
    }


    /**
     * Связь с таблицей статусов сферы
     *
     */
    public function statusInfo() {
        return $this->hasOne('App\Models\SphereStatuses', 'id', 'status')->orderBy('position');
    }


    /**
     * Получение имени маски по которой был открыт лид
     *
     * @return string
     */
    public function maskName()
    {
        $lead = Lead::find( $this['lead_id'] );
        $maskName = UserMasks::where('mask_id', '=', $this['mask_id'])->where('sphere_id', '=', $lead->sphere['id'])->first();

        return $maskName->name;
        //$mask = new AgentBitmask( $lead->sphere['id'] );
        //return $mask->find( $this['mask_id'] )->name;
    }


    /**
     * Создает открытый лид для агента в таблице открытых лидов
     *
     *
     * @param  Lead  $lead
     * @param  integer  $agent_id
     * @param  integer  $mask_id
     *
     * @return OpenLeads
     */
    public static function makeOpen( $lead, $agent_id, $mask_id )
    {

        // интервал гарантированный агентом на работу с лидом, который он октрыл
        // после этого интервала агент не сможет ставить bad_lead
        $interval = $lead->sphere->lead_bad_status_interval;

        // время (дата) после которого bad_lead будет блокирован
        $expiration_time = date('Y-m-d H:i:s', time()+$interval);

        // проверяем есть ли такой лид
        $openLead = OpenLeads::
              where( 'lead_id', $lead->id )
            ->where( 'agent_id', $agent_id )
            ->first();

        if( $openLead ){
            // если ЕСТЬ открытый лид с такими параметрами
            // обновляем счетчики и время гарантированное на bad_lead

            return false;

        }else{
            // если НЕТ открытого лида с такими параметрами
            // создаем его

            // получаем имя маски
            $maskName = UserMasks::where('sphere_id', '=', $lead->sphere_id)->where('mask_id', '=', $mask_id)->first();

            $openLead = new OpenLeads();
            $openLead->lead_id = $lead->id;                 // id лида
            $openLead->agent_id = $agent_id;                // id агента, который его открыл
            $openLead->mask_id = $mask_id;                  // комментарий (не обазательно)
            $openLead->expiration_time = $expiration_time;  // время истечения лида
            $openLead->mask_name_id = $maskName->id;        // имя маски
            $openLead->count = 1;                           // количество открытий (при первом открытии = "1")

            $openLead->save();

            // инкрементим opened у лида, (количество открытия лида)
            $lead->opened++;
            // время истечения открытых лидов
            $lead->open_lead_expired  = $expiration_time;
            $lead->save();
        }

        return $openLead;
    }


    /**
     * Открытие лида максимальное количество раз
     *
     *
     * @param  Lead  $lead
     * @param  integer  $agent_id
     * @param  integer  $mask_id
     * @param  integer  $count
     *
     * @return OpenLeads
     */
    public static function maxOpen( $lead, $agent_id, $mask_id, $count )
    {

        // интервал гарантированный агентом на работу с лидом, который он октрыл
        // после этого интервала агент не сможет ставить bad_lead
        $interval = $lead->sphere->lead_bad_status_interval;

        // время (дата) после которого bad_lead будет блокирован
        $expiration_time = date( 'Y-m-d H:i:s', time()+$interval );

        // проверяем есть ли такой лид
        $openLead = OpenLeads::
        where( 'lead_id', $lead->id )
            ->where( 'agent_id', $agent_id )
            ->first();

        if( $openLead ){
            // если ЕСТЬ открытый лид с такими параметрами
            // обновляем счетчики и время гарантированное на bad_lead

            $openLead->expiration_time = $expiration_time;  // время истечения лида
            $openLead->count = $count;                      // количество открытий (при первом открытии = "1")

            $openLead->save();

            // устанавливаем количество открытых лидов у лида
            $lead->opened = $count + 1;


        }else{
            // если НЕТ открытого лида с такими параметрами
            // создаем его

            // получаем имя маски
            $maskName = UserMasks::where('sphere_id', '=', $lead->sphere_id)->where('mask_id', '=', $mask_id)->first();

            $openLead = new OpenLeads();
            $openLead->lead_id = $lead->id;                 // id лида
            $openLead->agent_id = $agent_id;                // id агента, который его открыл
            $openLead->mask_id = $mask_id;                  // комментарий (не обазательно)
            $openLead->expiration_time = $expiration_time;  // время истечения лида
            $openLead->mask_name_id = $maskName->id;        // имя маски
            $openLead->count = $count;                      // количество открытий (при первом открытии = "1")

            $openLead->save();

            // устанавливаем количество открытых лидов у лида
            $lead->opened = $count;
        }

        // время истечения открытых лидов
        $lead->open_lead_expired  = $expiration_time;
        $lead->save();


        return $openLead;
    }


    /**
     * Установка отметки что лид плохой
     *
     * в том числе идет проверка лида
     * если больше половины (от максимального открытия) открытых лидов
     * помечены как bad
     * лид помечается как bad  и идет полный расчет по лиду
     *
     *
     * @return boolean
     */
    public function setBadLead()
    {
        // помечаем открытый лид как как bad
        $this->state = 1;
        $this->save();

        // поверяем лид, помечать его как плохой или нет
        $lead = Lead::find( $this['lead_id'] );
        $lead->checkOnBad();

        return true;
    }


    /**
     * Закрытие сделки по открытому лиду
     *
     * @param integer $price
     *
     * @return boolean
     */
    public function closeDeal( $price )
    {

        // если у агента уже заключена сделка
        // выходим
        if( $this->state == 2 ){ return false; }

        // выбираем лид
        $lead = Lead::find( $this['lead_id'] );
        // выбираем агента
        $agent = Agent::find( $this['agent_id'] );

        // проверяем лид,
        // если сделок по нему нет - расчитываем и закрываем
        // если были - игнорим этот шаг
        $lead->checkCloseDeal();

        // снимаем деньги за сделку по лиду
        $paymentStatus =
        Pay::closingDeal(
            $lead,
            $agent,
            $this['mask_id']
        );

        // помечаем что по открытому лиду была закрыта сделка
        if( $paymentStatus ){
            $this->state = 2;
            $this->save();

            $closedDeal = new ClosedDeals();
            $closedDeal->open_lead_id = $this['lead_id'];
            $closedDeal->agent_id = $agent->id;
            $closedDeal->sender = $agent->id;
            $closedDeal->source = $agent->id;
            $closedDeal->comments = '';
            $closedDeal->price = $price;
            $closedDeal->created_at = new \DateTime();
            $closedDeal->save();
        }

        return true;
    }

}
