<?php

namespace App\Helper\PayMaster;

use App\Facades\Settings;
use App\Models\AgentSphere;
use App\Models\HistoryBadLeads;
use App\Models\SalesmanInfo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\TransactionsLeadInfo;
use App\Models\AgentInfo;

use App\Models\Transactions;
use App\Models\TransactionsDetails;
use App\Models\AgentBitmask;
use App\Helper\PayMaster\PayInfo;
use Log;


/**
 * Класс отвечающий за все платежи
 *
 *
 *
 * Дополнительные классы
 *
 * calculation - расчеты
 * price - цена
 *
 *
 */
class Pay
{

    /**
     * Оплата за открытие лида
     *
     *
     * @param  Lead  $lead
     * @param  Agent  $agent
     * @param  integer  $mask_id
     * @param  integer  $leadNumber
     *
     * @return array
     */
    public static function openLead( $lead, $agent, $mask_id, $leadNumber=1 )
    {

        // начало оплаты по лиду
        self::log(
            'payOpenLeadLog_lead_payment_start',
            [
                'msg' => 'старт оплаты по лиду',
                'lead_id' => $lead->id,
                'agent_id' => $agent->id,
                'mask_id' => $mask_id,
                'lead_number' => $leadNumber
            ]
        );

        // получаем цену лида
        $price = $lead->price( $mask_id ) * $leadNumber;

        // получение цены за открытия лида
        self::log(
            'payOpenLeadLog_get_lead_price_completed',
            [
                'msg' => 'получение цены за открытия лида лид',
                'lead_id' => $lead->id,
                'agent_id' => $agent->id,
                'mask_id' => $mask_id,
                'lead_number' => $leadNumber,
                'price' => $price
            ]
        );

        // получение кошелька пользователя
        if($agent->inRole('salesman')) {
            $salesmanAgent = $agent->agent()->first();
            $wallet = $salesmanAgent->wallet()->first();
        } else {
            $wallet = $agent->wallet;
        }

        // получение кошелька агента
        self::log(
            'payOpenLeadLog_get_agent_wallet',
            [
                'msg' => 'получение кошелька агента',
                'lead_id' => $lead->id,
                'agent_id' => $agent->id,
                'mask_id' => $mask_id,
                'lead_number' => $leadNumber,
                'wallet_id' => $wallet->id
            ]
        );

        // агента можно легко связать с таблицей кошельков, а salesman пользуется кошельком своего агента,
        // поэтому получить его можно только через дополнительную таблицу salesman_info, данные, которые
        // в итоге получаются, относятся к объекту Collection, а сам объект Wallet - единственный элемент в
        // этой коллекции.
        // Чтобы это исправить стоит проверка, если полученный объект Wallet, то возвращается он, если
        // полученный объект Collection - возвращается элемент с нулевым индексом.
        $wallet = (get_class($wallet) === 'Illuminate\Database\Eloquent\Collection') ? $wallet[0] : $wallet;

        // проверка, может ли агент оплатить открытие лида
        if( !$wallet->isPossible( $price ) ){

            // отмена платежа из-за низкого баланса
            return [ 'status' => false, 'description' => trans('lead/lead.openlead.low_balance')];
        }

        // оплачиваем лид
        $paymentStatus =
        Payment::toSystem(
            [
                'initiator_id'  => $agent->id,  // id инициатора платежа
                'user_id'       => $wallet->user_id,  // id пользователя, с кошелька которого снимается сумма
                'type'          => 'openLead',  // тип транзакции
                'amount'        => $price,      // снимаемая с пользователя сумма
                'lead_id'       => $lead->id,   // id лида если он учавствует в платеже
                'lead_number'   => $leadNumber  // количество лидов
            ]
        );

        // если возникли ошибки при платеже
        if( !$paymentStatus ){
            // оплата за открытый лид прошла с ошибкой
            self::log('payOpenLeadLog_payment_error');
            // Ошибки при попытке сделать платеж
            return [ 'status' => false, 'description' => trans('lead/lead.openlead.error')];
        }

        // оплата за открытый лид прошла успешно
        self::log('payOpenLeadLog_payment_completed', ['msg'=>'успешная оплата открытого лида', 'lead_id'=>$lead->id]);

        return [ 'status' => true ];
    }


    /**
     * Оплата за закрытие сделки агентом по лиду
     *
     *
     * @param  Lead  $lead
     * @param  Agent  $agent
     * @param  integer  $mask_id
     * @param  float  $price
     *
     * @return array
     */
    public static function closingDeal( $lead, $agent, $mask_id, $price )
    {

        // определяем роль пользователя
        if($agent->inRole('salesman')) {
            $salesmanInfo = SalesmanInfo::where('salesman_id', '=', $agent->id)->first();
            $agentParent = Agent::find($salesmanInfo->agent_id);

            // раньше расчитывался процент от сделки
//            $amount = Price::closeDeal( $agentParent->id, $lead->sphere_id, $price );
            // теперь он передается на прямую
            $amount = $price;
            $user_id = $agentParent->id;

            // проверка, может ли агент оплатить сделку
            if( !$agentParent->wallet->isPossible( $amount ) ){

                // отмена платежа из-за низкого баланса
                return [ 'status' => false, 'description' => trans('lead/lead.closingDeal.low_balance')];
            }

        } else {
            // раньше расчитывался процент от сделки 
//            $amount = Price::closeDeal( $agent->id, $lead->sphere_id, $price );
            // теперь он передается на прямую
            $amount = $price;
            $user_id = $agent->id;

            // проверка, может ли агент оплатить сделку
            if( !$agent->wallet->isPossible( $amount ) ){

                // отмена платежа из-за низкого баланса
                return [ 'status' => false, 'description' => trans('lead/lead.closingDeal.low_balance')];
            }
        }

        // оплачиваем закрытие сделки
        $paymentStatus =
            Payment::toSystem(
                [
                    'initiator_id'  => $agent->id,  // id инициатора платежа
                    'user_id'       => $user_id,  // id пользователя, с кошелька которого снимается сумма
                    'type'          => 'closingDeal',  // тип транзакции
                    'amount'        => $price,      // снимаемая с пользователя сумма
                    'lead_id'       => $lead->id,   // (не обязательно) id лида если он учавствует в платеже
                ]
            );

        // Если лид был добавлен как "Только дилмейкеру" - отдаем депозитору процент от сделки
        if($lead->specification == Lead::SPECIFICATION_FOR_DEALMAKER) {
            $depositor = Agent::find($lead->agent_id);
            $depositorSphere = AgentSphere::where('agent_id', '=', $depositor->id)
                ->where('sphere_id', '=', $lead->sphere_id)
                ->first();
            if(!isset($depositorSphere->id)) {
                $depositorSphere = AgentInfo::where('agent_id', '=', $depositor->id)->first();
            }

            $depositorPrice = ($price / 100) * $depositorSphere->dealmaker_revenue_share;

            Payment::fromSystem(
                [
                    'initiator_id'  => config('payment.system_id'),         // id инициатора платежа
                    'user_id'       => $depositor->id,          // id пользователя, на кошелек которого будет зачисленна сумма
                    'wallet_type'   => 'earned',      // тип кошелька с которого снимается сумма
                    'type'          => 'closeDealLeadForDealmakers',         // тип транзакции
                    'amount'        => $depositorPrice,           // снимаемая с пользователя сумма
                    'lead_id'       => $lead->id,  // (не обязательно) id лида если он учавствует в платеже
                ]
            );
        }

        return $paymentStatus;
        // если возникли ошибки при платеже
        /*if( !$paymentStatus ){
            // Ошибки при попытке сделать платеж
            return [ 'status' => false, 'description' => trans('lead/lead.closingDeal.error')];
        }

        return [ 'status' => true ];*/
    }


    /**
     * Оплата за закрытие сделки агентом по лиду
     *
     *
     * @param  Lead  $lead
     * @param  Agent  $member
     * @param  Agent  $owner
     * @param  float  $price
     *
     * @return array
     */
    public static function closingDealInGroup( $lead, $member, $owner, $price  )
    {

        // получаем процент от суммы по сделке
        $amount = Price::closeDealInGroup($member['id'], $owner['id'], $price);

        // проверка, может ли агент оплатить сделку
        if( !$member->wallet->isPossible( $amount ) ){

            // отмена платежа из-за низкого баланса
            return [ 'status' => false, 'description' => trans('lead/lead.closingDeal.low_balance')];
        }


        // оплачиваем закрытие сделки
        $paymentStatus =
            Payment::userToUser(
                [
                    'initiator_id'  => $member->id,  // id инициатора платежа
                    'donor_id'      => $member->id,  // id пользователя, с кошелька которого снимается сумма
                    'recipient_id'  => $owner->id,  // id пользователя, на кошелек которого зачисляется сумма
                    'type'          => 'closingDealInGroup',  // тип транзакции
                    'amount'        => $amount,      // снимаемая с пользователя сумма
                    'lead_id'       => $lead->id,   // (не обязательно) id лида если он учавствует в платеже
                ]
            );

        return $paymentStatus;
        // если возникли ошибки при платеже
        /*if( !$paymentStatus ){
            // Ошибки при попытке сделать платеж
            return [ 'status' => false, 'description' => trans('lead/lead.closingDeal.error')];
        }

        return [ 'status' => true ];*/
    }


    /**
     * Возвращает платежи по лиду всем агентам, которые его купили
     *
     * Находит все транзакции по лиду с типом openLeads
     * которые принадлежат агентам
     * Делает возврат на те же суммы и на те же кошельки
     *
     *
     * @param  integer  $lead_id
     *
     * @return Collection
     */
    public static function ReturnsToAgentsForLead( $lead_id ){

        // находим всех покупателей лида
        $buyers = PayInfo::LeadBuyers( $lead_id );

        $buyersStatus = [];

        // возврат средств обратно
        $buyers->each(function( $buyer ) use ( &$buyersStatus){

            $buyersStatus[ $buyer['id'] ] =
            Payment::fromSystem(
                [
                    'initiator_id'  => config('payment.system_id'),         // id инициатора платежа
                    'user_id'       => $buyer['user_id'],          // id пользователя, на кошелек которого будет зачисленна сумма
                    'wallet_type'   => $buyer['wallet_type'],      // тип кошелька с которого снимается сумма
                    'type'          => 'repaymentForLead',         // тип транзакции
                    'amount'        => $buyer['amount'],           // снимаемая с пользователя сумма
                    'lead_id'       => $buyer['lead']['lead_id'],  // (не обязательно) id лида если он учавствует в платеже
                    'lead_number'   => $buyer['lead']['number'],   // (не обязательно) количество лидов, если их несколько
                ]
            );
        });

        return $buyersStatus;
    }


    /**
     * Оплата за обработку лида обератором
     *
     * Прибавление суммы, которую система затратила на обработку лида,
     * кошельку с типом wasted агента
     *
     *
     * @param  Lead  $lead
     * @param  integer  $initiator_id
     *
     * @return array
     */
    public static function OperatorPayment( $lead, $initiator_id )
    {

        // получаем роли пользователя
        $roles = $lead->leadDepositorData->roles();

        // если лид добавлен оператором, выходим
        if($roles[0] == 'operator'){
            return $paymentStatus['status'] = false;
        }

        // проверяем оплаченна обработка оператором или нет
        $isPaid = PayInfo::IsOperatorPayment( $lead['id'] );

        if( !$isPaid ){
            // если обработка еще не оплаченна

            // получаем сумму оплаты за обработку оператором
            $amount = Price::processingOperator( $lead['id'] );

            // зачисляем сумму за обработку лида на кошелек системы
            $paymentStatus =
                Payment::single(
                    [
                        'initiator_id'  => $initiator_id,                // id инициатора, в данном случае - оператор
                        'user_id'       => config('payment.system_id'),  // id системы, сумма снимается с системы
                        'wallet_type'   => 'earned',                     // тип кошелька с которого снимется сумма
                        'type'          => 'operatorPayment',            // тип транзакции
                        'amount'        => $amount,                      // сумма оплаты за лид
                        'lead_id'       => $lead['id'],                  // id лида, который проверяет оператор
                    ]
                );

            // помечаем что все прошло нормально
            $paymentStatus['status'] = true;

            return $paymentStatus;
        }

        return $paymentStatus['status'] = false;
    }


    /**
     * Возврат "штраф" за обработку оператором по лиду
     *
     * todo
     *
     *
     * Прибавление суммы, которую система затратила на обработку лида,
     * кошельку с типом wasted агента
     *
     *
     * @param  integer  $lead_id
     *
     * @return array
     */
    public static function OperatorRepayment( $lead_id )
    {
        // выбираем лид
        $lead = Lead::find( $lead_id );

        // получаем роли пользователя
        $roles = $lead->leadDepositorData->roles();

        // если лид добавлен оператором, выходим
        if($roles[0] == 'operator'){
            return $paymentStatus['status'] = false;
        }

        // сумма, которая была затрачена на обработку лида оператором
        $amount = PayInfo::OperatorPayment( $lead_id ) * (-1);

        // снимаем платеж за оператора с депозитора лида, с кошелька wasted
        $paymentStatus =
        Payment::toSystem(
            [
                'initiator_id'  => config('payment.system_id'),  // id инициатора платежа
                'user_id'       => $lead->agent_id,          // id депозитора которому будет зачислен wasted
                'wallet_type'   => 'wasted',            // тип кошелька c которого будет снята сумма
                'type'          => 'operatorRepayment', // тип транзакции
                'amount'        => $amount,             // снимаемая с депозитора сумма
                'lead_id'       => $lead_id,            // id лида по которому идет транзакция
            ]
        );

        $historyBadLead = new HistoryBadLeads();
        $historyBadLead->sphere_id = $lead->sphere_id;
        $historyBadLead->lead_id = $lead->id;
        $historyBadLead->depositor_id = $lead->agent_id;
        $historyBadLead->price = $amount;
        $historyBadLead->save();

        $paymentStatus['status'] = true;

        return $paymentStatus;
    }


    /** Выплаты агенту за лид за открытия лида */
    public static function rewardForOpenLeads( $lead_id )
    {
        $lead = Lead::find( $lead_id );

        // получаем роли пользователя
        $roles = $lead->leadDepositorData->roles();

        // если лид добавлен оператором, выходим
        if($roles[0] == 'operator'){
            return $paymentStatus['status'] = false;
        }

        // находим депозитора лида
        $agent_id = $lead['agent_id'];

        // выручка агента по продажам лида
        // отнимаем от суммы всех продаж по лиду цену за оператора и умножаем на процент от выручки
        $agentRevenueOpenLead = PayCalculation::depositorLeadRevenueShare( $lead );

        // записываем для отчетности
        $paymentStatus['openLeads'] = $agentRevenueOpenLead;

        // todo обработать инициатора, сделать что если его нет, то инициатор - система
        // зачисляем на счет автора и снимаем с системы
        $paymentStatus['openLeadsDetails'] =
            Payment::fromSystem(
                [
                    'initiator_id'  => config('payment.system_id'),    // id инициатора платежа
                    'user_id'       => $agent_id,             // id пользователя, на кошелек которого будет зачисленна сумма
                    'wallet_type'   => 'earned',              // тип кошелька агента на который будет зачисленна сумма
                    'type'          => 'rewardForOpenLead',   // тип транзакции
                    'amount'        => $agentRevenueOpenLead, // снимаемая сумма с системы
                    'lead_id'       => $lead_id,              // id лида по которому идет транзакция
                ]
            );

        return $paymentStatus;
    }





    /**
     * Платеж за плохой лид
     *
     * Возврат денег агентам которые его купили
     * Зачисление "штрафа" на wasted агенту который занес этот лид в систему
     *
     *
     * @param  integer  $lead_id
     *
     * @return array
     */
    public static function forBadLead( $lead_id )
    {

        // выбираем лид из БД
        $lead = Lead::find( $lead_id );

        // если лид уже финиширован - выходим
        if( $lead['payment_status'] != 0 ){ return false; }

        // возвращаем всем агентам, которые купили лид, их платежи
        $transactionStatus['buyers'] =
        self::ReturnsToAgentsForLead( $lead_id );

        // зачисляем цену обработки оператором на wasted автору лида
        $transactionStatus['author'] =
        self::OperatorRepayment( $lead_id );

        return $transactionStatus;
    }


    /**
     * Платеж за хороший лид
     *
     *
     * @param  integer  $lead_id
     *
     * @return array
     */
    public static function forGoodLead( $lead_id )
    {

        // выбираем лид из БД
        $lead = Lead::find( $lead_id );

        // если лид уже финиширован - выходим
        if( $lead['payment_status'] != 0 ){ return false; }

        // деньги за обработку лида оператором
        $callOperator = PayInfo::OperatorPayment( $lead_id )  * (-1);

        // сумма дохода по открытым лидам
        $revenueOpenLead = PayInfo::SystemRevenueFromLeadSum( $lead_id, 'openLead' );

        // сумма доходов по заключенным сделкам
        $revenueClosingDeal = PayInfo::SystemRevenueFromLeadSum( $lead_id, 'closingDeal' );

        // результат расчета по платежу
        $paymentStatus = [];

        // обработка лида в зависимости от доходов по нему
        if( $revenueOpenLead <= 0 || ( $revenueOpenLead - $callOperator) <= 0 ){
            // если доход отрицательный, либо 0

            // расчитываем его как плохой лид
            $paymentStatus['paymentDetails'] =
            self::forBadLead( $lead_id );

            // помечаем что лид ниразу не продан
            $paymentStatus['status'] = 2;

            return $paymentStatus;

        }else{
            // если доход положительный

            // находим депозитора лида
            $agent_id = Lead::find( $lead['agent_id'] )->agent_id;

            // находим платежные данные агента
            //$agentInfo = AgentInfo::where( 'agent_id', $agent_id )->first();

            $agentSphere = AgentSphere::where('sphere_id', '=', $lead_id)
                ->where('agent_id', '=', $agent_id)
                ->first();

            // процент агента за открытие лида
            $leadRevenueShare = isset($agentSphere->lead_revenue_share) && $agentSphere->lead_revenue_share > 0 ? $agentSphere->lead_revenue_share : Settings::get_setting('system.agents.lead_revenue_share');
            // процент агента за закрытие сделки по лиду
            $paymentRevenueShare = isset($agentSphere->payment_revenue_share) && $agentSphere->payment_revenue_share > 0 ? $agentSphere->payment_revenue_share : Settings::get_setting('system.agents.payment_revenue_share');

            // todo вынести расчет в калькуляции
            // выручка агента по продажам лида
            // отнимаем от суммы всех продаж по лиду цену за оператора и умножаем на процент от выручки
            //$agentRevenueOpenLead = ( $revenueOpenLead - $callOperator ) * $leadRevenueShare;
            $agentRevenueOpenLead = ( $revenueOpenLead - $callOperator ) * $leadRevenueShare / 100;

            $paymentStatus['openLeads'] = $agentRevenueOpenLead;

            // todo обработать инициатора, сделать что если его нет, то инициатор - система
            // todo зачисляем на счет автора и снимаем с системы
            $paymentStatus['openLeadsDetails'] =
            Payment::fromSystem(
                [
                    'initiator_id'  => config('payment.system_id'),  // id инициатора платежа
                    'user_id'       => $agent_id,  // id пользователя, на кошелек которого будет зачисленна сумма
                    'wallet_type'   => 'earned',  // тип кошелька с которого снимается сумма
                    'type'          => 'rewardForOpenLead',  // тип транзакции
                    'amount'        => $agentRevenueOpenLead,  // снимаемая с пользователя сумма
                    'lead_id'       => $lead_id,  // (не обязательно) id лида если он учавствует в платеже
                ]
            );

            // если есть доходы по закрытым сделкам
            if( $revenueClosingDeal != 0 ){

                // todo вынести расчет в калькуляции
                // выручка агента по закрытию сделок
                $agentRevenueClosingDeal = $revenueClosingDeal * $paymentRevenueShare;

                $paymentStatus['closingDeal'] = $agentRevenueClosingDeal;

                $paymentStatus['Summ'] = $agentRevenueOpenLead + $agentRevenueClosingDeal;

                // todo переводим на счет автора и снимаем с системы
                $paymentStatus['closingDealDetails'] =
                Payment::fromSystem(
                    [
                        'initiator_id'  => config('payment.system_id'),        // id инициатора платежа
                        'user_id'       => $agent_id,                 // id пользователя, на кошелек которого будет зачисленна сумма
                        'wallet_type'   => 'earned',                  // тип кошелька с которого снимается сумма
                        'type'          => 'rewardForClosingDeal',    // тип транзакции
                        'amount'        => $agentRevenueClosingDeal,  // снимаемая с пользователя сумма
                        'lead_id'       => $lead_id,                  // (не обязательно) id лида если он учавствует в платеже
                    ]
                );
            }

            // помечаем что лид ниразу не продан
            $paymentStatus['status'] = 1;

            return $paymentStatus;
        }

    }


    /**
     * Процесс открытия лида
     *
     */
    public static $payLog =
        [

            /** Процесс открытия лида */

            // начало оплаты по лиду с начальными данными
            'payOpenLeadLog_lead_payment_start' => 'Начало оплаты по лиду',
            // начало получения цены за лид
            'payOpenLeadLog_get_lead_price_start' => 'Начало получения цены за лид',
            // успешное получение цены за лид
            'payOpenLeadLog_get_lead_price_completed' => 'успешное получение цены за лид',
            // получение кошелька агента
            'payOpenLeadLog_get_agent_wallet' => 'получение кошелька агента',
            // кошелек принадлежит агенту
            'payOpenLeadLog_select_agent_wallet' => 'кошелек принадлежит агенту',
            // кошелек принадлежит продавцу, выбрат кошелек его агента
            'payOpenLeadLog_select_salesman_agent_wallet' => 'кошелек принадлежит продавцу, выбрат кошелек его агента',
            // открытие отмененно из-за недостаточного баланса
            'payOpenLeadLog_cancelled_due_to_low_balance' => 'открытие отмененно из-за недостаточного баланса',
            // баланс достаточен
            'payOpenLeadLog_balance_is_sufficient' => 'баланс достаточен',
            // старт оплаты за открытие лида
            'payOpenLeadLog_payment_start' => 'старт оплаты за открытие лида',
            // оплата за открытый лид прошла успешно
            'payOpenLeadLog_payment_completed' => 'оплата за открытый лид прошла успешно',
            // оплата за открытый лид прошла с ошибкой
            'payOpenLeadLog_payment_error' => 'оплата за открытый лид прошла с ошибкой',

        ];



    public static $logLevel = 2;


    public static $logLevelSets =
        [
            0 =>
            [],

            1 =>
            [
                // начало оплаты по лиду с начальными данными
                'payOpenLeadLog_lead_payment_start',
                // оплата за открытый лид прошла успешно
                'payOpenLeadLog_payment_completed',
                // оплата за открытый лид прошла с ошибкой
                'payOpenLeadLog_payment_error',

            ],

            2 =>
            [
                // начало оплаты по лиду с начальными данными
                'payOpenLeadLog_lead_payment_start',
                // начало получения цены за лид
                'payOpenLeadLog_get_lead_price_start',
                // успешное получение цены за лид
                'payOpenLeadLog_get_lead_price_completed',
                // получение кошелька агента
                'payOpenLeadLog_get_agent_wallet',
                // кошелек принадлежит агенту
                'payOpenLeadLog_select_agent_wallet',
                // кошелек принадлежит продавцу, выбрат кошелек его агента
                'payOpenLeadLog_select_salesman_agent_wallet',
                // открытие отмененно из-за недостаточного баланса
                'payOpenLeadLog_cancelled_due_to_low_balance',
                // баланс достаточен
                'payOpenLeadLog_balance_is_sufficient',
                // старт оплаты за открытие лида
                'payOpenLeadLog_payment_start',
                // оплата за открытый лид прошла успешно
                'payOpenLeadLog_payment_completed',
                // оплата за открытый лид прошла с ошибкой
                'payOpenLeadLog_payment_error',

            ],

        ];

    /**
     * Отписывает лог
     *
     *
     * @param  string  $subject
     * @param  array  $details
     */
    public static function log( $subject, $details=[] ){

        $logSets = array_flip( self::$logLevelSets[self::$logLevel] );

        if(isset($logSets[$subject])){
            Log::info($subject, $details);
        }

    }

}