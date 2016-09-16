<?php

namespace App\Helper\PayMaster;

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
     * todo доработать
     *
     */
    public static function ReturnsToAgentsForLead( $lead_id ){

        // находим всех покупателей лида
        $buyers = PayInfo::LeadBuyers( $lead_id );

        // возврат средств обратно
        $buyers->each(function( $buyer ){

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

        return $buyers;
    }


    /**
     * Платеж за плохой лид
     *
     * todo
     *
     */
    public static function forBadLead( $lead_id )
    {

        // todo вернуть платежи всем агентам которые купили лид
        self::ReturnsToAgentsForLead( $lead_id );

        // todo залепить wasted автору лида

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