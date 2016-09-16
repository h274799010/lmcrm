<?php

namespace App\Helper\PayMaster;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\TransactionsLeadInfo;

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
     *
     * @return array
     */
    public static function openLead( $lead, $agent, $mask_id )
    {

        // получаем цену лида
        $price = $lead->price( $mask_id );

        // проверка, может ли агент оплатить открытие лида
        if( !$agent->wallet->isPossible($price) ){

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
                'lead_id'       => $lead->id,   // (не обязательно) id лида если он учавствует в платеже
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
     * Возвращает агенту все его платежи по лиду
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
                    'initiator_id'  => PayData::SYSTEM_ID,         // id инициатора платежа
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

        // todo залепить прямой платеж на wasted в сумме платежа за лид
        $paymentStatus =
        Payment::single(
            [
                'initiator_id'  => PayData::SYSTEM_ID,  // id инициатора платежа
                'user_id'       => $author_id,          // id пользователя, на кошелек которого будет зачисленна сумма
                'wallet_type'   => 'wasted',            // тип кошелька с которого снимается сумма
                'type'          => 'operatorRepayment', // тип транзакции
                'amount'        => $amount,             // снимаемая с пользователя сумма
                'lead_id'       => $lead_id,            // (не обязательно) id лида если он учавствует в платеже
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
     * todo
     *
     */
    public static function forGoodLead()
    {

    }


}