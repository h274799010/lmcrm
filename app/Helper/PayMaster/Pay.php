<?php

namespace App\Helper\PayMaster;

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

        // получаем цену лида
        $price = $lead->price( $mask_id ) * $leadNumber;

        // получение кошелька пользователя
        $wallet = $agent->wallet;

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
                'user_id'       => $agent->id,  // id пользователя, с кошелька которого снимается сумма
                'type'          => 'openLead',  // тип транзакции
                'amount'        => $price,      // снимаемая с пользователя сумма
                'lead_id'       => $lead->id,   // id лида если он учавствует в платеже
                'lead_number'   => $leadNumber  // количество лидов
            ]
        );

        // если возникли ошибки при платеже
        if( !$paymentStatus ){
            // Ошибки при попытке сделать платеж
            return [ 'status' => false, 'description' => trans('lead/lead.openlead.error')];
        }

        return [ 'status' => true ];
    }


    /**
     * Оплата за закрытие сделки агентом по лиду
     *
     *
     * @param  Lead  $lead
     * @param  Agent  $agent
     * @param  integer  $mask_id
     *
     * @return array
     */
    public static function closingDeal( $lead, $agent, $mask_id  )
    {

        // получаем цену за закрытие сделки по лиду
        $price = Price::closeDeal( $agent );

        // проверка, может ли агент оплатить сделку
        if( !$agent->wallet->isPossible( $price ) ){

            // отмена платежа из-за низкого баланса
            return [ 'status' => false, 'description' => trans('lead/lead.closingDeal.low_balance')];
        }

        // оплачиваем закрытие сделки
        $paymentStatus =
            Payment::toSystem(
                [
                    'initiator_id'  => $agent->id,  // id инициатора платежа
                    'user_id'       => $agent->id,  // id пользователя, с кошелька которого снимается сумма
                    'type'          => 'closingDeal',  // тип транзакции
                    'amount'        => $price,      // снимаемая с пользователя сумма
                    'lead_id'       => $lead->id,   // (не обязательно) id лида если он учавствует в платеже
                ]
            );

        // если возникли ошибки при платеже
        if( !$paymentStatus ){
            // Ошибки при попытке сделать платеж
            return [ 'status' => false, 'description' => trans('lead/lead.closingDeal.error')];
        }

        return [ 'status' => true ];
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
     * Возврат "штраф" за обработку оператором автору лида
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
        // сумма, которая была затрачена на обработку лида оператором
        $amount = PayInfo::OperatorPayment( $lead_id ) * (-1);

        // автор лида
        $author_id = Lead::find( $lead_id )->agent_id;

        // снимаем платеж за оператора с депозитора лида, с кошелька wasted
        $paymentStatus =
        Payment::toSystem(
            [
                'initiator_id'  => config('payment.system_id'),  // id инициатора платежа
                'user_id'       => $author_id,          // id депозитора которому будет зачислен wasted
                'wallet_type'   => 'wasted',            // тип кошелька c которого будет снята сумма
                'type'          => 'operatorRepayment', // тип транзакции
                'amount'        => $amount,             // снимаемая с депозитора сумма
                'lead_id'       => $lead_id,            // id лида по которому идет транзакция
            ]
        );

        $paymentStatus['status'] = true;

        return $paymentStatus;
    }


    /** Выплаты агенту за лид за открытия лида */
    public static function rewardForOpenLeads( $lead_id )
    {
        $lead = Lead::find( $lead_id );

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
            $agentInfo = AgentInfo::where( 'agent_id', $agent_id )->first();

            // процент агента за открытие лида
            $leadRevenueShare = $agentInfo->lead_revenue_share;
            // процент агента за закрытие сделки по лиду
            $paymentRevenueShare = $agentInfo->payment_revenue_share;

            // todo вынести расчет в калькуляции
            // выручка агента по продажам лида
            // отнимаем от суммы всех продаж по лиду цену за оператора и умножаем на процент от выручки
            $agentRevenueOpenLead = ( $revenueOpenLead - $callOperator ) * $leadRevenueShare;

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


}