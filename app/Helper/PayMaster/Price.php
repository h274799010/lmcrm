<?php

namespace App\Helper\PayMaster;

use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\Lead;
use App\Models\TransactionsLeadInfo;

use App\Models\Transactions;
use App\Models\TransactionsDetails;
use App\Models\AgentBitmask;




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
class Price
{


    /**
     * Цена за открытие лида
     *
     * @param  Lead  $lead
     * @param  integer  $agent_mask_id
     *
     * @return double
     */
    public static function openLead( $lead, $agent_mask_id )
    {

        // выбираем таблицу битмаска по id сферы лида
        $mask = new AgentBitmask( $lead->sphere->id );

        // выбираем прайс по заданной маске агента
        $price = $mask->find( $agent_mask_id )->lead_price;

        return $price;
    }


    /**
     * Цена за закрытие сделки агентом по лиду
     *
     *
     * @param  Agent  $agent
     *
     * @return double
     */
    public static function closeDeal( $agent )
    {

        // находим платежные данные агента
        $agentInfo = AgentInfo::where( 'agent_id', $agent->id )->first();

        // процент агента за закрытие сделки по лиду
        $paymentRevenueShare = $agentInfo->payment_revenue_share * 20;

        return $paymentRevenueShare;

    }


    /**
     * Цена за обработку оператором
     *
     *
     * @param integer $lead_id
     *
     * @return double
     */
    public static function processingOperator( $lead_id )
    {
        // данные лида вместе с сферой
        $lead = Lead::with('sphere')->find($lead_id);

        return $lead['sphere']['price_call_center'] * (-1);
    }


}