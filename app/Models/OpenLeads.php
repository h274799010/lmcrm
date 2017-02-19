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

    public function closeDealInfo() {
        return $this->hasOne('App\Models\ClosedDeals', 'open_lead_id', 'id');
    }

    public function uploadedCheques() {
        return $this->hasMany('App\Models\CheckClosedDeals', 'open_lead_id', 'id');
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
            if($mask_id == 0){

                $maskNameId = 0;

            }else{

                $maskName = UserMasks::where('sphere_id', '=', $lead->sphere_id)->where('mask_id', '=', $mask_id)->first();
                $maskNameId = $maskName->id;
            }

            $openLead = new OpenLeads();
            $openLead->lead_id = $lead->id;                 // id лида
            $openLead->agent_id = $agent_id;                // id агента, который его открыл
            $openLead->mask_id = $mask_id;                  // комментарий (не обазательно)
            $openLead->expiration_time = $expiration_time;  // время истечения лида
            $openLead->mask_name_id = $maskNameId;          // имя маски
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
     * @param  integer  $price
     * @param  integer|boolean  $senderId
     *
     * @return boolean
     */
    public function closeDeal( $price, $comments, $senderId=false )
    {

        // если у агента уже заключена сделка
        // выходим
        if( $this->state == 2 ){ return false; }

        // выбираем лид
        $lead = Lead::find( $this['lead_id'] );
        // выбираем агента который открыл этот лид
        $agent = Agent::find( $this['agent_id'] );

        // проверяем лид,
        // если сделок по нему нет - расчитываем и закрываем
        // если были - игнорим этот шаг
        $lead->checkCloseDeal();

        /** Проверка, это сделка по группе, или сделка в системе */
        if( $this['mask_id']==0 ) {
            // лид закрывается по группе

            //$owner = Agent::find( $lead['agent_id'] );

            // todo дописать логику
            // снимаем деньги за сделку по лиду по группе
            //$paymentStatus =
            //    Pay::closingDealInGroup(
            //        $lead,
            //        $agent,
            //        $owner,
            //        $price
            //    );
            $lead_source = 2;
        }
        else {
            // лид закрывается в системе

            // проверяем лид,
            // если сделок по нему нет - расчитываем и закрываем
            // если были - игнорим этот шаг
            $lead->checkCloseDeal();

            // снимаем деньги за сделку по лиду в системе
            //$paymentStatus =
            //    Pay::closingDeal(
            //        $lead,
            //        $agent,
            //        $this['mask_id'],
            //        $price
            //    );
            $lead_source = 1;
        }


        // если сделка не проплаченна
        //if(!$paymentStatus){
        //    // выходим из метода
        //    return false;
        //}

        // получаем id отправителя
        $sender_id = $senderId ? $senderId : $agent->id;

        // получаем данные пользователя с ролями
        //$sender = User::with('roles')->find( $sender_id );

        // помечаем что по открытому лиду была закрыта сделка
        //if( $paymentStatus ){
        //}
        $this->state = 2;
        $this->save();

        $closedDeal = new ClosedDeals();
        $closedDeal->open_lead_id = $this['id'];       // id открытого лида, по которому закрывается сделка
        $closedDeal->agent_id = $agent->id;                 // id агента который закрывает сделку
        $closedDeal->sender = $sender_id;                   // id пользователя который отдал лид агенту (оператор или партнер)
        $closedDeal->lead_source = $lead_source;            // лид получен с аукциона или передан напрямую по группе (1-auction, 2-group)
        $closedDeal->comments = $comments;                  // описание
        $closedDeal->status = 0;                            // закрыта/не закрыта (подтверждает админ или акк. менеджер)
        $closedDeal->price = $price;                        // цена за сделку. добавляет агент при закрытии сделки
        //$closedDeal->percent = '';                          // процент от сделки
        //$closedDeal->purchase_transaction_id = '';          // id транзакции платежа
        //$closedDeal->purchase_date = '';                    // дата когда был совершен платеж
        $closedDeal->save();

        return true;
    }

}
