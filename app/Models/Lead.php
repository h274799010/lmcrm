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
     * todo переделать, сделать статусы не по таблице, а по массиву
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
     * Получение данных о статусе лида
     *
     * @return LeadStatus
     */
    public function statusName(){
        return $this->hasOne('App\Models\LeadStatus', 'id', 'status');
    }


    // todo доделать
    public function user(){

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
        $badOPenLeads = $this->openLeads()->where('bad','=',1)->count();
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
        // находим цену за открытие лида
        return Price::openLead( $this, $agent_mask_id );
    }


    /**
     * Открыть лид
     *
     *
     * Метод делает лид открытым для агента
     *
     * todo доработать пендингТайм
     *
     * todo добавить маску
     * todo добавить плату за открытие
     *
     * @param  Agent  $agent
     * @param  integer  $mask_id
     *
     * @return OpenLeads
     */
    public function open( $agent, $mask_id )
    {

        // снимаем оплату за открытие лида
        $payment = Pay::openLead( $this, $agent, $mask_id );

        // выход если платеж не произведен
        if( !$payment['status'] ){
            return $payment['description'];
        }

        // заносим лид в таблицу открытых лидов
        OpenLeads::makeOrIncrement( $this, $agent->id, $mask_id );

        // если лид открыт максимальное количество раз
        if( $this->opened >= $this->MaxOpenNumber() ){
            // добавляем лиду статус "открыт максимальное количество раз"
            // чем убираем с аункцона
            $this->setStatus(5);
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
            $this->setStatus(1);

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

            // меняем статус на "закрытый лид"
            $this->setStatus(6);

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
            ->where( 'status', '<>', 2)
            ->where( 'expired', '=', 0)
            ->where( 'finished', '=', 0)
            ->where( 'expiry_time', '<', date("Y-m-d H:i:s") );
    }


    /**
     * Ставит пометку об ситекшем сроке
     *
     * @return Lead
     */
    public function markExpired()
    {
        $this->expired = 1;
        $this->save();

        return $this;
    }


    /**
     * Возвращает все просроченные к текущему времени лиды
     *
     *
     * @param Query $query
     *
     * @return Builder
     */
    public function scopeOpenLeadExpired( $query )
    {
        return $query
            ->where( 'status', '<>', 2)
            ->where( 'expired', '=', 1)
            ->where( 'finished', '=', 0)
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
        return PayCalculation::systemProfit( $this );
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
        return PayCalculation::depositorProfit( $this );
    }

    /**
     * Помечает лид как завершенный
     */
    public function finish()
    {

        // проверить хороший/плохой
        if( $this->status == 1 ){
            // если плохой

            // полный расчет по лиду как по плохомму
            $payStatus =
            Pay::forBadLead( $this->id );

        }else{
            // если хороший

            // полный расчет по лиду как по хорошему
            $payStatus =
            Pay::forGoodLead( $this->id );
        }


        if( $payStatus ) {
            $this->finished = 1;
            $this->save();
        }

        return $this;
    }

}
