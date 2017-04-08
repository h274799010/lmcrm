<?php

namespace App\Helper\PayMaster;

use App\Models\OpenLeads;
use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\TransactionsLeadInfo;
use App\Models\AgentInfo;
use App\Models\Transactions;
use App\Models\TransactionsDetails;
use App\Models\AgentBitmask;




/**
 * Основные расчеты по платежам ценам и т.д.
 *
 *
 */
class PayCalculation
{

    /**
     * Максимальная сумма, которую пользователь может заплатить на данный момент
     *
     * возвращает сумму, которую агент может заплатить
     *
     *
     * @param  Wallet  $wallet
     *
     * @return double
     */
    public static function possibilityPayment( $wallet )
    {

        /**
         * сумма которую агент может заплатить определяется суммой
         * всех его кошельков за вычетом 'wasted'
         */

        // суммируем все типы кошельков включая overdraft
        $possibility  = $wallet['buyed'];
        $possibility += $wallet['earned'];
        $possibility += $wallet['overdraft'];

        // отнимаем wasted
        $possibility -= $wallet['wasted'];

        return $possibility;
    }


    /**
     * Прибыль депозитора лида
     *
     *
     * @param Lead $lead
     *
     * @return double
     */
    public static function depositorProfit( $lead )
    {

        // цена обработки лида оператором
        $callOperator = PayInfo::OperatorPayment( $lead->id ) * (-1);

        // сумма дохода по открытым лидам
        $revenueOpenLead = PayInfo::SystemRevenueFromLeadSum( $lead->id, 'openLead' );


        if( $revenueOpenLead <= 0 ) {

            return $callOperator * (-1);

        }elseif( $lead['status'] == 2 || $lead['status'] == 5 ){

            return $callOperator * (-1);

        }else{

            // находим платежные данные агента
            $agentInfo = AgentInfo::where( 'agent_id', $lead['agent_id'] )->first();

            // процент агента за открытие лида
            $leadRevenueShare = $agentInfo->lead_revenue_share;

            // выручка агента по продажам лида
            // отнимаем от суммы всех продаж по лиду цену за оператора и умножаем на процент от выручки
            $agentRevenueOpenLead = ( $revenueOpenLead - $callOperator ) * $leadRevenueShare;


            return $agentRevenueOpenLead;
        }
    }


    /**
     * Прибыль депозитора лида
     *
     *
     * @param Lead $lead
     *
     * @return double
     */
    public static function depositorLeadRevenueShare( $lead )
    {

        // сумма дохода по открытым лидам
        $revenueOpenLead = PayInfo::SystemRevenueFromLeadSum( $lead->id, 'openLead' );


        /** ----------------- доход агента 0 если:  ----------------- */

        // доход по лиду 0 или меньше 0
        if( $revenueOpenLead <= 0 ){ return 0; }

        // статусы лида "new lead", "operator" и "operator bad"
        if( $lead['status'] < 3 ){ return 0; }

        // статус лида "agent bad"
        if( $lead['status'] == 5 ){ return 0; }

        /** ---------------------------------------------------------- */


        /** ----------------- доход агента по лиду если лид продан если:  ----------------- */

        // цена обработки лида оператором
        $callOperator = PayInfo::OperatorPayment( $lead->id ) * (-1);

        // находим платежные данные агента
        $agentInfo = AgentInfo::where( 'agent_id', $lead['agent_id'] )->first();

        // процент агента за открытые лиды
        $leadRevenueShare = $agentInfo->lead_revenue_share;

        // выручка депозитора по продажам лида
        $agentRevenueOpenLead = ( $revenueOpenLead - $callOperator ) * $leadRevenueShare / 100;

        return $agentRevenueOpenLead;

        /** ------------------------------------------------------- */


    }


    /**
     * Прибыль прибыль системы
     *
     *
     * @param Lead $lead
     *
     * @return double
     */
    public static function systemProfit( $lead )
    {

        // доход системы по открытым лидам
        $systemRevenue = PayInfo::SystemRevenueFromLeadSum( $lead->id, 'openLead' );

        // прибыль депозитора лида
        $depositorProfit = self::depositorProfit( $lead );

        if( $lead['status']==2 || $lead['status']==5 ){

            return 0;
        }else{
            return $systemRevenue - $depositorProfit + PayInfo::OperatorPayment( $lead->id ) ;
        }

    }

}