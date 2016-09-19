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

        // сумма доходов по заключенным сделкам
        $revenueClosingDeal = PayInfo::SystemRevenueFromLeadSum( $lead->id, 'closingDeal' );

        if( $revenueOpenLead <= 0 ) {

            return 0;

        }elseif( $lead['status'] == 1 ){

            return 0;

        }else{

            // находим платежные данные агента
            $agentInfo = AgentInfo::where( 'agent_id', $lead['agent_id'] )->first();

            // процент агента за открытие лида
            $leadRevenueShare = $agentInfo->lead_revenue_share;
            // процент агента за закрытие сделки по лиду
            $paymentRevenueShare = $agentInfo->payment_revenue_share;

            // выручка агента по продажам лида
            // отнимаем от суммы всех продаж по лиду цену за оператора и умножаем на процент от выручки
            $agentRevenueOpenLead = ( $revenueOpenLead - $callOperator ) * $leadRevenueShare;

            // если есть доходы по закрытым сделкам
            if( $revenueClosingDeal != 0 ) {

                // todo вынести расчет в калькуляции
                // выручка агента по закрытию сделок
                $agentRevenueClosingDeal = $revenueClosingDeal * $paymentRevenueShare;
            }else{
                $agentRevenueClosingDeal = 0;
            }

            return $agentRevenueOpenLead + $agentRevenueClosingDeal;
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
    public static function systemProfit( $lead )
    {

        // доход системы
        $systemRevenue = PayInfo::SystemRevenueFromLeadSum( $lead->id );

        // прибыль депозитора лида
        $depositorProfit = self::depositorProfit( $lead );

        if( $lead['status']==1 ){

            return 0;
        }else{
            return $systemRevenue - $depositorProfit;
        }

    }

}