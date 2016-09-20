<?php

namespace App\Models;

use App\Helper\PayMaster\PayCalculation;
use App\Helper\PayMaster\PayInfo;
use Cartalyst\Sentinel\Users\EloquentUser;

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

        // todo думаю что ненужно:
        5 => 'agent bad',      // агент пометил лид как bad
        6 => 'closed_deal',    // закрытие сделки по лиду
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

        $lead = $this;

        // снимаем оплату за открытие лида
        $payment = Pay::openLead( $lead, $agent, $mask_id );

        // выход если платеж не произведен
        if( !$payment['status'] ){
            return $payment['description'];
        }

        // заносим лид в таблицу открытых лидов
        OpenLeads::makeOrIncrement( $lead, $agent->id, $mask_id );

        // если лид открыт максимальное количество раз
        if( $lead->opened >= $lead->MaxOpenNumber() ){
            // убираем лид с аукциона
            $lead->setStatus(4);
            // помечаем что он буран по причине максимального открытия
            $lead->setAuctionStatus(2);
        }

        return trans('lead/lead.openlead.successfully_opened');
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

            // меняем статус на "bad_lead"
            $this->setStatus(5);
            $this->setAuctionStatus(4);

            // финишируем лид (полный финансовый расчет по лиду)
            $this->finish();

            return true;
        }

        // если плохих не больше половины максимального открытия - возвращается false
        return false;
    }


    /**
     * Закрытие сделки по лиду
     *
     * Если в открытых лидах по этому лиду
     * больше половины закрытых (из максимально возможных)
     * лид помечается как закрытый, убирается с аукциона
     * и по нему делается полный расчет
     * как по "хорошему" лиду
     *
     * todo доработать, закрытие идет после первой сделки
     *
     * @return boolean
     */
    public function checkOnCloseDeal()
    {
        // проверяем количество закрытых сделок в открытых лидах по лиду
        $BadLeadCount = OpenLeads::
              where( 'lead_id', $this['id'] )
            ->where( 'state', 2)
            ->count();

        // если закрытых лидов нет, возвращаем false
        if( $BadLeadCount==0 ){ return false; }

        // находим количество макс. открытия лидов
        $MaxOpenNumber = $this->MaxOpenNumber();

        // сравниваем количество закрытых и макс/2
        if( ($MaxOpenNumber/2) < $BadLeadCount ){
            // если закрытых больше половины от максимально возможного

            // убираем лид с аукциона
            $this->setStatus(4);

            // фиксируем что он убран по причине закрытия сделки
            $this->setAuctionStatus(5);

            // финишируем лид (полный финансовый расчет по лиду)
            $this->finish();

            return true;
        }

        // если лидов с закрытыми сделками не больше половины максимального открытия - возвращается false
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
        // убираем с аукциона
        $this->setStatus(4);
        // помечаем что убрана по причине истекшего времени лида
        $this->setAuctionStatus(3);

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


    public function depositorProfit()
    {

        $lead = $this;

        return PayCalculation::depositorProfit( $lead );
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


        if( $payStatus ) {
            $this->finished = 1;
            $this->save();
        }

        return $this;
    }

}
