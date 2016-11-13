<?php

namespace App\Models;

use App\Helper\PayMaster\PayCalculation;
use App\Helper\PayMaster\PayInfo;
use Cartalyst\Sentinel\Users\EloquentUser;

use App\Models\Customer;

use App\Models\Sphere;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Query;
use PhpParser\Builder;
use App\Helper\PayMaster\Pay;
use App\Helper\PayMaster\Price;

#class Lead extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
#    use Authenticatable, CanResetPassword;
class Lead extends EloquentUser {

    protected $table="leads";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agent_id','sphere_id','name', 'customer_id', 'comment', 'bad'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    #protected $hidden = [
    #    'password', 'remember_token',
    #];


    /**
     * Статус лида на аукционе
     *
     * @var array
     */
    public static $status =
    [
        0 => 'new lead',       // новый лид в системе
        1 => 'operator',       // лид обрабатывается оператором todo подумать над этим
        2 => 'operator bad',   // оператор отметил лид как bad
        3 => 'auction',        // лид на аукционе
        4 => 'close auction',  // лид снят с аукциона
        5 => 'agent bad',      // агент пометил лид как bad
        6 => 'closed deal',    // закрытие сделки по лиду
    ];


    /**
     * Статус лида снятого с аукциона
     *
     * @var array
     */
    public static $auctionStatus =
    [
        // todo вместо этих двух достаточно просто "no status"
        0 => 'not at auction',          // не на аукциоа
        1 => 'auction',                 // на аукционе

        2 => 'closed by max open',      // снят с аукциона по причине максимального открытия лидов
        3 => 'closed by time expired',  // снят с аукциона по причине истечения времени по лиду
        4 => 'closed by agent bad',     // снят с аукциона, большая часть агентов пометили его как bad
        5 => 'closed by close deal',    // снят с аукциона по закрытию сделки по лиду
    ];


    /**
     * Статус платежа по лиду
     *
     * @var array
     */
    public static $paymentStatus =
    [
        0 => 'expects payment',          // ожидание платежа по лиду
        1 => 'payment to depositor',     // оплата депозитору его доли по лиду
        2 => 'payment for unsold lead',  // "штраф" депозитору за непроданный лид
        3 => 'payment for bad lead',     // оплата агентам по плохому лиду (возврат покупателям, штраф депозитору)
    ];


    /**
     * Метод создания нового лида
     *
     * для удобства, просто передаются параметры
     * и весь процесс создания проходит сам
     *
     *
     * @param  integer  $user_id
     * @param  string  $name
     * @param  string  $phone
     * @param  string  $comment
     *
     * @return Lead
     */
    public static function createNew( $user_id, $name, $phone, $comment='' )
    {
        // выбираем модель агента по id
        $agent = Agent::find( $user_id );

        // записываем телефон и получаем id записи
        $customer = Customer::firstOrCreate( ['phone'=>preg_replace('/[^\d]/', '', $phone )] );

        // создаем новый лид
        $lead = new Lead();
        // записываем id телефона (связь с таблицей customer)
        $lead->customer_id = $customer->id;
        // записываем id сферы
        $lead->sphere_id = $agent->sphere()->id;
        // статус лида выставляем в 0
        $lead->status = 0;
        // записываем имя
        $lead->name = $name;
        // заносим комментарии
        $lead->comment = $comment;
        // записываем id агента который занес лид
        $lead->agent_id = $user_id;

        // сохранение лид с новыми данными
        $lead->save();

        return $lead;
    }

    public function SphereFormFilters($sphere_id=NULL){
        $relation = $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'sphere_id');

        return ($sphere_id)? $relation->where('sphere_id','=',$sphere_id) : $relation;
    }

    public function sphereAttrByType($type=NULL, $sphere_id=NULL){

        $relation = $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'sphere_id');

        return ($sphere_id and $type)? $relation->where('sphere_id','=',$sphere_id)->where('_type', '=', $type) : $relation;
    }

    public function SphereAdditionForms($sphere_id=NULL){
        $relation = $this->hasMany('App\Models\SphereAdditionForms', 'sphere_id', 'sphere_id');

        return ($sphere_id)? $relation->where('sphere_id','=',$sphere_id) : $relation;
    }


    // возвращает все поля SphereFromFilters со значением поля label=radio
    public function sAttrRadio($sphere_id=NULL){
        $relation = $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'sphere_id');

        return ($sphere_id)? $relation->where('sphere_id','=',$sphere_id)->where('_type', '=', 'radio') : $relation;
    }

    // возвращает все поля SphereFromFilters со значением поля label=checkbox
    public function sAttrCheckbox($sphere_id=NULL){
        $relation = $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'sphere_id');

        return ($sphere_id)? $relation->where('sphere_id','=',$sphere_id)->where('_type', '=', 'checkbox') : $relation;
    }

    public function openLeads($agent_id=NULL){
        $relation = $this->hasMany('App\Models\OpenLeads', 'lead_id', 'id');

        return ($agent_id)? $relation->where('agent_id','=',$agent_id) : $relation;
    }

    public function sphere(){
        return $this->hasOne('App\Models\Sphere', 'id', 'sphere_id');
    }

    /**
     * Возвращает все статусы сферы лида
     *
     * todo доработать
     *
     */
    public function sphereStatuses(){

        $rel = $this->sphere()->with('statuses');

        return $rel;
    }


    public function openLeadStatus(){

        $openLead = $this->hasOne('App\Models\OpenLeads', 'lead_id', 'id');

        return $openLead;

    }

    /**
     * Телефон клиента
     *
     * Связь с таблицей customer
     *
     *
     * @return Query
     */
    public function phone(){
        return $this->hasOne('App\Models\Customer','id','customer_id');
    }


    public function obtainedBy($agent_id=NULL){
        $relation=$this->belongsToMany('App\Models\Agent','open_leads','lead_id','agent_id');
        return ($agent_id)? $relation->where('agent_id','=',$agent_id) : $relation;
    }


    /**
     * Установка статуса лида
     *
     *
     * @param integer $status
     *
     * @return Lead
     */
    public function setStatus( $status )
    {
        // устанавливаем статус
        $this->status = $status;
        $this->save();

        return $this;
    }


    /**
     * Установка причины по которой лид убран с аукциона
     *
     *
     * @param integer $auctionStatus
     *
     * @return Lead
     */
    public function setAuctionStatus( $auctionStatus )
    {
        // устанавливаем статус
        $this->auction_status = $auctionStatus;
        $this->save();

        return $this;
    }


    /**
     * Установка причины по которой лид убран с аукциона
     *
     *
     * @param integer $paymentStatus
     *
     * @return Lead
     */
    public function setPaymentStatus( $paymentStatus )
    {
        // устанавливаем статус
        $this->payment_status = $paymentStatus;
        $this->save();

        return $this;
    }


    /**
     * Состояние лида
     *
     * установка всех трех статусов
     *
     * Структура данных
     * [
     *    'status'  => 0,
     *    'auction' => 0,
     *    'payment' => 0
     * ]
     *
     * @param  array  $statuses
     *
     * @return Lead
     */
    public function state( $statuses )
    {

        /**
         $statuses =
            [
                'status'  => 0,
                'auction' => 0,
                'payment' => 0
            ]
        */

        // если задан статус
        if( isset($statuses['status']) ){

            // если теперешний статус лида "3" и задаваемый не "3"
            // т.е. он снимается с аукциона и не выставляется на аукцион
            if( $this->status == 3 && $statuses['status'] != 3){
                // полностью удаляем этот лид с аукциона
                Auction::removeByLead( $this['id'] );
            }

            // устанавливаем статус
            $this->status = $statuses['status'];
        }

        // если задан статус по аукциону
        if( isset($statuses['auction']) ) {
            // устанавливаем статус по аукциону
            $this->auction_status = $statuses['auction'];
        }

        // если задан статус по платежу
        if( isset($statuses['payment']) ) {
            // устанавливаем статус по платежу
            $this->payment_status = $statuses['payment'];
        }

        $this->save();

    }


    /**
     * Получение имя статуса лида
     *
     * todo проверить в работе
     *
     * @return string
     */
    public function statusName()
    {
        // выбираем значение из переменной статуса лида
        return self::$status[ $this['status'] ];
    }


    /**
     * Название индекса по которому лид был убран с аукциона
     *
     * todo проверить в работе
     *
     * @return string
     */
    public function auctionStatusName()
    {
        // выбираем значение из переменной статуса лида на аукционе
        return self::$auctionStatus[ $this['auction_status'] ];
    }


    /**
     * Название индекса статуса по оплате
     *
     * todo проверить в работе
     *
     * @return string
     */
    public function paymentStatusName()
    {
        // выбираем значение из переменной статуса лида по оплате
        return self::$paymentStatus[ $this['payment_status'] ];
    }


    /**
     * Данные автора лида
     *
     * @return Builder
     */
    public function user()
    {

        return $this->hasOne('App\Models\Agent', 'id', 'agent_id')->select('id','first_name');
    }


    /**
     * Маска лида
     *
     * todo доработать
     *
     *
     * @return object
     */
    public function bitmask()
    {

        $tableName = 'lead_bitmask_' .$this->sphere_id;

        $mask = DB::table($tableName)->where('user_id', '=', $this->id)->first();

        return $mask;
    }


    public function getIsBadAttribute(){
        $outOfPending = $this->openLeads()->where('pending_time','>',date('Y-m-d H:i:s'))->count();
        $badOPenLeads = $this->openLeads()->where('status','=', 5)->count();
        //$goodOPenLeads = $this->openLeads()->where('bad','=',0)->count();
        //if ($badOPenLeads > $goodOPenLeads)
        if ($this->opened && !$outOfPending) {
            if ($badOPenLeads > $this->opened/2)
            {
                return true;
            }
        }
        return false;
    }


    /**
     * Цена за открытие лида по маске агента
     *
     *
     * @param integer $agent_mask_id
     *
     * @return double
     */
    public function price( $agent_mask_id )
    {
        $lead = $this;

        // находим цену за открытие лида
        return Price::openLead( $lead, $agent_mask_id );
    }


    /**
     * Открыть лид для агента
     *
     *
     * Метод делает лид открытым для агента
     *
     *
     * @param  Agent  $agent
     * @param  integer  $mask_id
     *
     * @return OpenLeads
     */
    public function open( $agent, $mask_id )
    {

        // лид
        $lead = $this;


        // если сфера лида удалена
        if( !$lead->sphere ){
            return trans('lead/lead.lead.sphere_deleted');
        }


        // если сфера лида отключена
        if( $lead->sphere->status == 0 ){
            return trans('lead/lead.lead.sphere_off');
        }


        // если лид уже снят с аукциона, сообщаем об этом и выходим
        if( $lead->status != 3 ){
            return trans('lead/lead.Lead.not_at_auction');
        }



        // снимаем оплату за открытие лида
        $payment = Pay::openLead($lead, $agent, $mask_id);

        // выход если платеж не произведен
        if (!$payment['status']) {
            return trans('lead/lead.openlead.low_balance');
        }

        // заносим лид в таблицу открытых лидов
        $openLead =
        OpenLeads::makeOpen( $lead, $agent->id, $mask_id );

        if( $openLead ) {


            // если лид открыт максимальное количество раз
            if ($lead->opened >= $lead->MaxOpenNumber()) {

                // устанавливаем статусы
                $lead->state(
                    [
                        'status'  => 4,  // close auction
                        'auction' => 2,  // closed by max open
                    ]
                );
            }

            // сообщаем что лид открыт нормально
            return trans('lead/lead.openlead.successfully_opened');

        }else{

            // сообщаем что лид уже открыт (нельзя открыть больше одного раза)
            return trans('lead/lead.openlead.already_open');
        }
    }


    /**
     * Открыть лид максимальное количество раз
     *
     *
     * @param  Agent  $agent
     * @param  integer  $mask_id
     *
     * @return OpenLeads
     */
    public function openAll( $agent, $mask_id )
    {

        // лид
        $lead = $this;


        // если сфера лида удалена
        if( !$lead->sphere ){
            return trans('lead/lead.lead.sphere_deleted');
        }


        // если сфера лида отключена
        if( $lead->sphere->status == 0 ){
            return trans('lead/lead.lead.sphere_off');
        }


        // если лид уже снят с аукциона, сообщаем об этом и выходим
        if( $lead->status != 3 ){
            return trans('lead/lead.Lead.not_at_auction');
        }

        // Ищем этот лид у других агентов
        $openLead =
        OpenLeads::
              where( 'lead_id', $lead->id )
            ->where( 'agent_id', '<>', $agent->id )
            ->first();

        // если другие агенты уже открывали этот лид - сообщаем об этом и выходим
        if( $openLead ){
            return trans('lead/lead.openAllLead.already_open_other_agents');
        }

        // проверяем, открывал ли агент этот лид
        $agentOpenLead =
            OpenLeads::
            where( 'lead_id', $lead->id )
                ->where( 'agent_id', $agent->id )
                ->first();

        // узнаем количество максимального открытия
        $leadMaxOpen = $lead->MaxOpenNumber();

        // количество покупаемых лидов
        $amountLeads = $agentOpenLead ? $leadMaxOpen - 1 : $leadMaxOpen ;

        // снимаем плату за покупаемые лиды
        $payment = Pay::openLead($lead, $agent, $mask_id, $amountLeads);

        // проверяем прошел ли платеж
        if( $payment['status'] ){
            // если платеж прошел нормально

            // заносим лид в таблицу открытых лидов
            OpenLeads::maxOpen( $lead, $agent->id, $mask_id, $amountLeads );

            $lead->state(
                [
                    'status'  => 4,  // close auction
                    'auction' => 2,  // closed by max open
                ]
            );

            // сообщаем что лид открыт нормально
            return trans('lead/lead.openlead.AllLead_successfully_opened');

        }else{
            // если платеж не прошел

            // возвращаем причину по которой платеж не прошел
            return $payment['description'];
        }

    }


    /**
     * Максимальное количество открытия лида
     *
     * @return integer
     */
    public function MaxOpenNumber ()
    {
        return $this->sphere->openLead;
    }


    /**
     * Проверка на плохих лидов
     *
     * Если в открытых лидах по этому лиду
     * больше половины плохих (из максимально возможных)
     * лид помечается как плохой
     * и по нему делается полный расчет
     * как по плохому лиду
     *
     *
     * @return boolean
     */
    public function checkOnBad()
    {
        // проверяем количество плохих открытых лидов по лиду
        $BadLeadCount = OpenLeads::
              where( 'lead_id', $this['id'] )
            ->where( 'state', 1)
            ->count();

        // если плохих лидов нет, возвращаем false
        if( $BadLeadCount==0 ){ return false; }

        // находим количество макс. открытия лидов
        $MaxOpenNumber = $this->MaxOpenNumber();

        // сравниваем количество плохих и макс/2
        if( ($MaxOpenNumber/2) < $BadLeadCount ){
            // если плохих больше половины от максимально возможного

            // возврат средств агентам, которые купили лид
            Pay::ReturnsToAgentsForLead( $this->id );

            // зачисление денег за оператора депозитору лида
            Pay::OperatorRepayment( $this->id );


            if( $this->status == 3 ){
                // если лид на аукционе

                $this->state(
                    [
                        'status'  => 5,  // agent bad
                        'auction' => 4,  // closed by agent bad
                        'payment' => 3   // payment for bad lead
                    ]
                );

            }else{
                // если лид уже снят с аукциона

                $this->state(
                    [
                        'status'  => 5,  // agent bad
                        'payment' => 3   // payment for bad lead
                    ]
                );
            }

            return true;
        }

        // если плохих не больше половины максимального открытия - возвращается false
        return false;
    }


    /**
     * Закрытие сделки по лиду
     *
     * если лид еще на аукционе
     *    делается полный расчет по лиду
     *    и смена статусов
     *
     * если лида нет на аукционе
     *    ничего не делаем, просто выходим
     *
     *
     * @return boolean|array
     */
    public function checkCloseDeal()
    {

        /** проверяем статус лида */
        if( $this['status'] == 3 || $this['status'] == 4 ){
            // если лид еще на аукционе

            // расчет с депозитором лида
            $paymentStatus =
            Pay::rewardForOpenLeads( $this->id );

            // простовляем состояние лида
            if( $paymentStatus ) {
                $this->state(
                    [
                        'status'  => 6,  // closed deal
                        'auction' => 5,  // closed by close deal
                        'payment' => 1   // payment for bad lead
                    ]
                );
            }

            return $paymentStatus;

        }elseif( $this['status'] == 4 ){
            $this->state(
                [
                    'status'  => 6,  // closed deal
                ]
            );

            return true;
        }

        return false;
    }


    /**
     * Время, после которого лид снимается с аукциона
     *
     *
     * @return string
     */
    public function expiredTime()
    {

        // интервал после которого лид снимается с аукциона
        $interval = $this->sphere->lead_auction_expiration_interval;

        // время (дата) после которой лид снимается с аукциона
        $expiredTime = date('Y-m-d H:i:s', time()+$interval);

        return $expiredTime;
    }


    /**
     * Возвращает все просроченные к текущему времени лиды
     *
     *
     * @param Query $query
     *
     * @return Builder
     */
    public function scopeExpired( $query )
    {
        return $query
            ->where( 'status', 3)
            ->where( 'expiry_time', '<', date("Y-m-d H:i:s") );
    }


    /**
     * Ставит пометку об истекшем сроке
     *
     * @return Lead
     */
    public function markExpired()
    {

        /** проверяем, открывался лид или нет */
        if( $this->opened == 0 ){
            // если лид ни разу не открывался

            // делаем расчет по лиду
            Pay::OperatorRepayment( $this->id );

            // проставляем статусы
            $this->state(
                [
                    'status'  => 4,  // close auction
                    'auction' => 3,  // closed by time expired
                    'payment' => 2   // payment for unsold lead
                ]
            );


        }else{
            // если лид открывался хотя бы один раз

            // проставляем статусы
            $this->state(
                [
                    'status'  => 4,  // close auction
                    'auction' => 3,  // closed by time expired
                ]
            );
        }

        return $this;
    }


    /**
     * Возвращает все просроченные к текущему времени лиды по открытым лидам
     *
     *
     * @param Query $query
     *
     * @return Builder
     */
    public function scopeOpenLeadExpired( $query )
    {
        return $query
            ->where( 'status', 4)
            ->where( 'payment_status', 0)
            ->where( 'open_lead_expired', '<', date("Y-m-d H:i:s") );
    }


    /**
     * Цена за обработку лида оператором
     *
     *
     * @return double
     */
    public function operatorSpend()
    {
        // цена за обработку лида оператором
        return PayInfo::OperatorPayment( $this->id );
    }


    /**
     * Цена обработки лида оператором
     *
     *
     * @return double
     */
    public function systemSpend()
    {
       return PayInfo::LeadSpend( $this->id );
    }


    /**
     * Доход с лида за его открытия
     *
     */
    public function revenueForOpen()
    {
        return PayInfo::SystemRevenueFromLeadSum( $this->id, ['openLead', 'repaymentForLead'] );
    }


    /**
     * Доход со сделок
     *
     */
    public function ClosingDealCount()
    {

        $deals = PayInfo::SystemRevenueFromLeadDetails( $this->id, 'closingDeal' );

        return $deals->count();
    }


    /**
     * Доход со сделок
     *
     */
    public function revenueForClosingDeal()
    {
        return PayInfo::SystemRevenueFromLeadSum( $this->id, 'closingDeal' );
    }


    /**
     * Доходы системы по лиду
     *
     *
     * @return double
     */
    public function systemRevenue()
    {
        return PayInfo::SystemRevenueFromLeadSum( $this->id );
    }


    /**
     * Прибыль системы по лиду
     *
     *
     * @return double
     */
    public function systemProfit()
    {
        $lead = $this;

        return PayCalculation::systemProfit( $lead );
    }


    /**
     * Процент выручки агента
     *
     * процент который агент получает с продажи лидов
     * которые он внес в систему
     *
     *
     * @return double
     */
    public function paymentRevenueShare()
    {
        $agentInfo = $this    // данные агента в таблице AgentInfo
            ->hasOne( 'App\Models\AgentInfo', 'agent_id', 'agent_id')
            ->first();

        // возвращает только саму выручку
        return $agentInfo->payment_revenue_share;
    }


    /**
     * доход депозитора лида
     *
     */
    public function depositorProfit()
    {

        $lead = $this;

        return PayCalculation::depositorProfit( $lead );
    }


    /**
     * Обработка лида когда оператор отметил его как bad
     *
     * @return Lead
     */
    public function operatorBad()
    {

        // возврат денег за обработку оператора
        $paymentStatus =
        Pay::OperatorRepayment( $this->id );

        // если платеж нормальный, выставляем статусы лида
        if( isset( $paymentStatus['status'] ) ){

            $this->state(
                [
                    'status'  => 2,  // operator bad
                    'payment' => 3,  // payment for bad lead
                ]
            );
        }

        return $this;
    }


    /**
     * Выплата депозитору за открытия его лида
     *
     */
    public function rewardForOpenLeads()
    {

        Pay::rewardForOpenLeads( $this->id );

        // проставляем статусы
        $this->state(
            [
                'payment' => 1,  //  payment to depositor
            ]
        );
    }


    /**
     * Получение имени маски по которой был открыт лид
     *
     *
     * @param  integer  $mask_id
     *
     * @return string
     */
    public function maskName( $mask_id )
    {
        /*$mask = new AgentBitmask( $this->sphere['id'] );
        return $mask->find( $mask_id )->name;*/
        $maskName = UserMasks::where('mask_id', '=', $mask_id)->where('sphere_id', '=', $this->sphere['id'])->first();

        return $maskName->name;
    }


    /**
     * Помечает лид как завершенный
     */
    public function finish()
    {

        // проверить хороший/плохой
        if( $this->status == 2 || $this->status == 5 ){
            // если плохой

            // полный расчет по лиду как по плохомму
            $payStatus =
            Pay::forBadLead( $this->id );

            if( $payStatus ) {
                $this->setPaymentStatus(3);
            }

        }else{
            // если хороший

            // полный расчет по лиду как по хорошему
            $payStatus =
            Pay::forGoodLead( $this->id );

            if( $payStatus ) {
                $this->setPaymentStatus( $payStatus['status'] );
            }
        }

        return $this;
    }

}
