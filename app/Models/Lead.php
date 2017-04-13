<?php

namespace App\Models;

use App\Facades\Settings;
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
use App\Models\LeadBitmask;

#class Lead extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
#    use Authenticatable, CanResetPassword;
class Lead extends EloquentUser {


    /**
     * Таблица модели
     *
     */
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
     * Поля БД с датой
     *
     */
    protected $dates = ['operator_processing_time'];


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
    private static $status =
    [
        0 => 'new lead',          // новый лид в системе
        1 => 'operator',          // лид обрабатывается оператором
        2 => 'operator bad',      // оператор отметил лид как bad
        3 => 'auction',           // лид на аукционе
        4 => 'close auction',     // лид снят с аукциона
        5 => 'agent bad',         // агент пометил лид как bad
        6 => 'closed deal',       // закрытие сделки по лиду
        7 => 'selective auction', // добавление лида оператором на аукционы выборочных агентов
        8 => 'private group',      // лид для приватной группы
    ];


    /**
     * Статус лида снятого с аукциона
     *
     * @var array
     */
    private static $auctionStatus =
    [
        // todo вместо этих двух достаточно просто "no status"
        0 => 'not at auction',          // не на аукциоа
        1 => 'auction',                 // на аукционе

        2 => 'closed by max open',      // снят с аукциона по причине максимального открытия лидов
        3 => 'closed by time expired',  // снят с аукциона по причине истечения времени по лиду
        4 => 'closed by agent bad',     // снят с аукциона, большая часть агентов пометили его как bad
        5 => 'closed by close deal',    // снят с аукциона по закрытию сделки по лиду
        6 => 'private group',           // лид для приватной группы в аукционе не учавствует
    ];


    /**
     * Статус платежа по лиду
     *
     * @var array
     */
    private static $paymentStatus =
    [
        0 => 'expects payment',          // ожидание платежа по лиду
        1 => 'payment to depositor',     // оплата депозитору его доли по лиду
        2 => 'payment for unsold lead',  // "штраф" депозитору за непроданный лид
        3 => 'payment for bad lead',     // оплата агентам по плохому лиду (возврат покупателям, штраф депозитору)
        4 => 'private group',            // лид для приватной группы в денежной системе не учавствует
    ];

    /**
     * "Спецификация" лида
     */
    const SPECIFICATION_FOR_DEALMAKER = 1; // Лид отправляется на аукцион только дилмейкерам



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


    /**
     * Выбор маски лида
     *
     * Возвращает лид
     * с добавленной переменной с маской лида
     *
     * @return Lead
     */
    public function getMask()
    {

        // id сферы
        $sphere_id = $this->sphere_id;

        // id лида
        $lead_id = $this->id;

        // маска лида
        $mask = new LeadBitmask( $sphere_id );
        $this->mask = $mask->where( 'user_id', $lead_id )->first();

        return $this->mask;
    }


    /**
     * Выбор фильтра
     *
     * Возвращает лид
     * с добавленной переменной с атрибутами фильтра
     */
    public function getFilter()
    {
        // проверка маски на существование
        if( !$this->mask ){
            // если маски нет
            // создаем ее
            $this->getMask();
        }

        $mask = $this->mask;

        $formFilter = [];

        // перебирание атрибутов фильтра
        $this->SphereFormFilters->each(function( $attr, $key ) use( &$lead, &$formFilter, $mask  ){

            // данные опций фильтра
            $options = '';

            // перебирание опций фильтра
            $attr->options->each(function( $opt ) use( &$options, $attr, $mask ){

                // переменная с адресом фильтра
                $fb_attr_opt = 'fb_' .$attr->id .'_' .$opt->id;

                // если поле равняется 1
                if( $mask[$fb_attr_opt] == 1 ){
                    // заносим опцию в данные

                    // проверка содержания поля
                    if( $options == '' ){
                        // если в поле уже есть данные

                        // заносим имя в переменную
                        $options = $opt->name;

                    }else{
                        // если в поле уже есть данные

                        // добавляем имя в переменную через запятую
                        $options .= ', ' .$opt->name;
                    }
                }
            });

            // добавляем данные имени поля и его значения в массив
            $formFilter[] =
                [
                    'label' => $attr->label,
                    'value' => $options
                ];
        });

        // сохраняем данные фильтра в лиде акциона
        $this->filter = $formFilter;
    }


    /**
     * Выбор фильтра
     *
     * Возвращает лид
     * с добавленной переменной с атрибутами фильтра
     */
    public function getAdditional()
    {
        // проверка маски на существование
        if( !$this->mask ){
            // если маски нет
            // создаем ее
            $this->getMask();
        }

        $mask = $this->mask;

        $additionalData = [];

        // перебирание атрибутов фильтра
        $this->SphereAdditionForms->each(function( $attr, $key ) use( &$lead, &$additionalData, $mask  ){

            // данные опций фильтра
            $options = '';

            // обработка атрибута в зависимости от типа
            if( $attr->_type=='radio' || $attr->_type=='checkbox' || $attr->_type=='select' ){
                // если тип 'radio', 'checkbox' и 'select'

                // перебирание опций фильтра
                $attr->options->each(function( $opt ) use( &$options, $attr, $mask ){

                    // переменная с адресом фильтра
                    $ad_attr_opt = 'ad_' .$attr->id .'_' .$opt->id;

                    // если поле равняется 1
                    if( $mask[$ad_attr_opt] == 1 ){
                        // заносим опцию в данные

                        // проверка содержания поля
                        if( $options == '' ){
                            // если в поле уже есть данные

                            // заносим имя в переменную
                            $options = $opt->name;

                        }else{
                            // если в поле уже есть данные

                            // добавляем имя в переменную через запятую
                            $options .= ', ' .$opt->name;
                        }
                    }
                });

            }else{
                // если другой тип (email, date...)

                // переменная с адресом фильтра
                $ad_attr_opt = 'ad_' .$attr->id .'_0';
                // просто присваиваем опции значение поля
                $options = $mask[$ad_attr_opt];
            }


            // добавляем данные имени поля и его значения в массив
            $additionalData[] =
                [
                    'label' => $attr->label,
                    'value' => $options
                ];
        });

        // сохраняем данные дополнительных данных в лиде акциона
        $this->additional = $additionalData;
    }


    /**
     * Связь лида с таблицей органайзера операторов
     *
     */
    public function operatorOrganizer()
    {
        return $this->hasOne('App\Models\OperatorOrganizer', 'lead_id', 'id');
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

    /**
     * Связь с таблицей сфер
     *
     */
    public function sphere(){
        return $this->hasOne('App\Models\Sphere', 'id', 'sphere_id');
    }

    /**
     * Связь с таблицей данных депозитора лида
     *
     */
    public function leadDepositorData(){
        return $this->hasOne('App\Models\LeadDepositorData', 'lead_id', 'id');
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

            if( $this->status == 7 && $statuses['status'] != 7){
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
        //return self::$status[ $this['status'] ];
        return trans('lead/statuses.status.'.$this['status']);
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
        return trans('lead/statuses.auctionStatus.'.$this['auction_status']);
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
        return trans('lead/statuses.paymentStatus.'.$this['payment_status']);
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
     * Данные автора лида (выбираются из таблицы users, чтоб правильно выбрать
     * agent_info в зависимости от роли пользователя - Агент/Продавец)
     *
     * @return mixed
     */
    public function user2()
    {
        return $this->hasOne('App\Models\User', 'id', 'agent_id')->select('id','first_name');
    }

    /**
     * Данные пользователя добавившего лида
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function depositor()
    {
        return $this->hasOne('App\Models\User', 'id', 'agent_id');
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
     * @param $agent
     * @param $mask_id
     * @param bool $operator
     * @return array|string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function open( $agent, $mask_id, $operator=false )
    {

        // лид
        $lead = $this;

        if(($agent->banned_at != NULL && $agent->banned_at != '0000-00-00 00:00:00') && !$agent->hasAccess('opening_leads')) {
            return array(
                'error' => trans('lead/lead.open.error.banned')
            );
        }

        // если сфера лида удалена
        if( !$lead->sphere ){
            return array(
                'error' => trans('lead/lead.lead.sphere_deleted')
            );
        }


        // если сфера лида отключена
        if( $lead->sphere->status == 0 ){
            return array(
                'error' => trans('lead/lead.lead.sphere_off')
            );
        }


        // если лид уже снят с аукциона, сообщаем об этом и выходим
        if( $lead->status != 3 && $lead->status != 7 && $operator ){
            return array(
                'error' => trans('lead/lead.Lead.not_at_auction')
            );
        }

        // Если у агента есть открытые лиды без проставленного статуса
        // возвращаем ошибку
        $countOpenLeadNoStatus = OpenLeads::where('agent_id', '=', $agent->id)
            ->where('status', '=', 0)->count();
        if($countOpenLeadNoStatus > 0) {
            return array(
                'error' => trans('lead/lead.there_is_open_leads_no_status')
            );
        }



        // снимаем оплату за открытие лида
        $payment = Pay::openLead($lead, $agent, $mask_id);

        // выход если платеж не произведен
        if (!$payment['status']) {
            return array(
                'error' => trans('lead/lead.openlead.low_balance')
            );
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
            } elseif ($agent->inRole('dealmaker')) {
                // Если тип пользователя Deal maker
                // Ищим все его аукционы по текущему лиду
                // и удаляем их
                Auction::where('lead_id', '=', $lead->id)
                    ->where('user_id', '=', $agent->id)
                    ->delete();
            }

            // сообщаем что лид открыт нормально
            return array(
                'message' => trans('lead/lead.openlead.successfully_opened')
            );

        }else{

            // сообщаем что лид уже открыт (нельзя открыть больше одного раза)
            return array(
                'error' => trans('lead/lead.openlead.already_open')
            );
        }
    }

    /**
     * Открытие лида агентом для агента
     *
     *
     * @param  Agent  $user
     *
     * @return OpenLeads
     */
    public function openForMember($user){

        $lead = $this;

        // заносим лид в таблицу открытых лидов
        $openLead =
            OpenLeads::makeOpen( $lead, $user->id, 0 );

        return $openLead;
    }


    /**
     * Открыть лид максимальное количество раз
     *
     * @param $agent
     * @param $mask_id
     * @return array|string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function openAll( $agent, $mask_id )
    {

        // лид
        $lead = $this;

        if($agent->banned_at != NULL && $agent->banned_at != '0000-00-00 00:00:00' && !$agent->hasAccess('opening_leads')) {
            return array(
                'error' => trans('lead/lead.open.error.banned')
            );
        }


        // если сфера лида удалена
        if( !$lead->sphere ){
            return array(
                'error' => trans('lead/lead.lead.sphere_deleted')
            );
        }


        // если сфера лида отключена
        if( $lead->sphere->status == 0 ){
            return array(
                'error' => trans('lead/lead.lead.sphere_off')
            );
        }


        // если лид уже снят с аукциона, сообщаем об этом и выходим
        if( $lead->status != 3 ){
            return array(
                'error' => trans('lead/lead.Lead.not_at_auction')
            );
        }

        // Если у агента есть открытые лиды без проставленного статуса
        // возвращаем ошибку
        $countOpenLeadNoStatus = OpenLeads::where('agent_id', '=', $agent->id)
            ->where('status', '=', 0)->count();
        if($countOpenLeadNoStatus > 0) {
            return array(
                'error' => trans('lead/lead.there_is_open_leads_no_status')
            );
        }

        // Ищем этот лид у других агентов
        $openLead =
        OpenLeads::
              where( 'lead_id', $lead->id )
            ->where( 'agent_id', '<>', $agent->id )
            ->first();

        // если другие агенты уже открывали этот лид - сообщаем об этом и выходим
        if( $openLead ){
            return array(
                'error' => trans('lead/lead.openAllLead.already_open_other_agents')
            );
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
            return array(
                'message' => trans('lead/lead.openlead.AllLead_successfully_opened')
            );

        }else{
            // если платеж не прошел

            // возвращаем причину по которой платеж не прошел
            return array(
                'error' => $payment['description']
            );
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
//            Pay::OperatorRepayment( $this->id );

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

    public function revenueForAuction()
    {
        return PayInfo::SystemRevenueFromLeadSum( $this->id, [
            'openLead',
            'repaymentForLead',
            'operatorPayment',
            'rewardForOpenLead'
        ] );
    }

    public function revenueForSystem()
    {
        return PayInfo::SystemRevenueFromLeadSum( $this->id, [
            'openLead',
            'repaymentForLead',
            'operatorPayment',
            'rewardForOpenLead',
            'closingDeal',
            'closeDealLeadForDealmakers'
        ] );
    }

    /**
     * Профит системы по лиду
     *
     * @return array
     */
    public function getDepositionsProfit() {
        $agent = $this->depositor()->first();

        $maxOpened = $this->sphere->openLead;

        $agentSphere = AgentSphere::select('lead_revenue_share', 'payment_revenue_share', 'dealmaker_revenue_share')
            ->where('agent_id', '=', $agent->id)
            ->where('sphere_id', '=', $this->sphere_id)
            ->first();

        $paymentRevenueShare = isset($agentSphere->payment_revenue_share) ? $agentSphere->payment_revenue_share : Settings::get_setting('system.agents.payment_revenue_share');
        $leadRevenueShare = isset($agentSphere->lead_revenue_share) ? $agentSphere->lead_revenue_share : Settings::get_setting('system.agents.lead_revenue_share');

        $dealmakerRevenueShare = isset($agentSphere->dealmaker_revenue_share) ? $agentSphere->dealmaker_revenue_share : Settings::get_setting('system.agents.dealmaker_revenue_share');

        $paymentRevenueShare = 100 - $paymentRevenueShare;
        $leadRevenueShare = 100 - $leadRevenueShare;
        $dealmakerRevenueShare = 100 - $dealmakerRevenueShare;

        // все транзакции в которых учавствовал лид
        $transactions = TransactionsLeadInfo::where( 'lead_id', '=', $this->id )
            ->lists( 'transaction_id' );

        $transactionsDetails = TransactionsDetails::whereIn( 'transaction_id', $transactions )
            ->where( 'user_id', config('payment.system_id') )
            ->whereIn( 'type', ['openLead'] )
            ->select('amount')
            ->get();

        $operatorPayment = TransactionsDetails::whereIn( 'transaction_id', $transactions ) // получение деталей по найденным транзакциям
            ->where( 'type', 'operatorPayment' )
            ->where('user_id', '=', config('payment.system_id'))
            ->first();
        //dd($operatorPayment);

        $openedArr = array();
        if(count($transactionsDetails) > 0) {
            $openedArr = $transactionsDetails->lists('amount')->toArray();

            if(count($openedArr) < $maxOpened) {
                for ($i = count($openedArr); $i < $maxOpened; $i++) {
                    $openedArr[] = '-';
                }
            }
        }
        else {
            for ($i = 1; $i <= $maxOpened; $i++) {
                $openedArr[] = '-';
            }
        }

        $closedDeals = ClosedDeals::whereIn('open_lead_id', $this->openLeads->lists('id')->toArray())->get();

        $totalDeals = 0;
        $percentDeals = 0;
        if(count($closedDeals) > 0) {
            foreach ($closedDeals as $closedDeal) {
                $totalDeals += $closedDeal->price;
                $percentDeals += $closedDeal->percent;
            }
        }

        $result = [
            'type' => $this->ClosingDealCount() ? 'Deposition + Deal' : 'Deposition', // Тип строки: "Deposition" или "Deposition + Deal"
            'revenue_share' => [
                'from_deals' => $paymentRevenueShare, // Профит системы со сделки
                'from_leads' => $leadRevenueShare,  // Профит системы с открытия лида
                'from_dealmaker' => $this->specification == self::SPECIFICATION_FOR_DEALMAKER ? $dealmakerRevenueShare : '-'  // Профит системы с лида "Только для дилмейкеров"
            ],
            'max_opened' => $maxOpened, // Максимальное кол-во открытий лида в сфере
            'opened' => $openedArr, // Открытия лида: Номер открытия => Цена по которой открыли
            'deals' => [ // Профит системы с закрытой сделки
                'total' => $totalDeals, // сумма на которую закрыли сделку
                'our' => $this->revenueForClosingDeal()    // процент от сделки, который пологается системе: $deal_price * $profit_from_deals / 100%
            ],
            'auction' => [ // Профит системы с аукциона
                'leads' => PayInfo::SystemRevenueFromLeadSum( $this->id, ['openLead'] ), // Общий профит системы за открытия лида
                'deals' => $this->revenueForClosingDeal(), // Общий профит системы за закрытые сделки
                'total' => $this->systemRevenue() // Общий профит системы: $sum_leads_auction + $deals
            ],
            'operator' => isset($operatorPayment->amount) ? $operatorPayment->amount : 0, // Цена по которой лид был обработан оператором
            'profit' => [ // Окончательный профит системы
                'leads' => $this->revenueForAuction(), // Профит за открытия лидов
                'deals' => $this->revenueForClosingDeal(), // Профит за закрыьтия сделок
                'total' => $this->revenueForSystem()  // Общий профит системы: $leads + $deals
            ]
        ];

        return $result;
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
     * Общая сумма по доходам со сделок
     *
     */
    public function revenueForClosingDeal()
    {
        return PayInfo::SystemRevenueFromLeadSum( $this->id, ['closingDeal', 'closeDealLeadForDealmakers'] );
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
        /*$agentInfo = $this    // данные агента в таблице AgentInfo
            ->hasOne( 'App\Models\AgentInfo', 'agent_id', 'agent_id')
            ->first();*/
        $agentSphere = AgentSphere::where('sphere_id', '=', $this->sphere_id)
            ->where('agent_id', '=', $this->agent_id)
            ->first();

        // возвращает только саму выручку
        return isset($agentSphere->payment_revenue_share) ? $agentSphere->payment_revenue_share : Settings::get_setting('system.agents.payment_revenue_share');
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

    public function getStatuses($status)
    {
        switch ($status) {
            case 'status':
                $res = self::$status;
                break;
            case 'auctionStatus':
                $res = self::$auctionStatus;
                break;
            case 'paymentStatus':
                $res = self::$paymentStatus;
                break;
            default:
                $res = false;
                break;
        }

        if($res) {
            foreach ($res as $key => $name) {
                $res[$key] = trans('lead/statuses.'.$status.'.'.$key);
            }
        }

        return $res;
    }

    /**
     * Возвращает название "спецификации" лида
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getSpecifications() {
        return collect(
            [
                self::SPECIFICATION_FOR_DEALMAKER => trans('lead/statuses.specifications.'.self::SPECIFICATION_FOR_DEALMAKER),
            ]
        );
    }

}
