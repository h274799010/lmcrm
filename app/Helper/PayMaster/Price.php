<?php

namespace App\Helper\PayMaster;

use App\Models\AgentSphere;
use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\Lead;
use App\Models\TransactionsLeadInfo;
use App\Models\AgentsPrivateGroups;

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
     * @param  integer  $agent_id
     * @param  integer  $sphere_id
     * @param  float  $price
     *
     * @return double
     */
    public static function closeDeal( $agent_id, $sphere_id, $price )
    {

        // находим платежные данные агента
        //$agentInfo = AgentInfo::where( 'agent_id', $agent_id )->first();
        $agentSphere = AgentSphere::where( 'agent_id', '=', $agent_id )->whereAnd( 'sphere_id', '=', $sphere_id )->first();

        // процент агента за закрытие сделки по лиду
        $paymentRevenueShare = ($price / 100) * $agentSphere->payment_revenue_share;

        return $paymentRevenueShare;

    }


    /**
     * Цена за закрытие сделки в группе агентов
     *
     *
     * @param  integer  $owner_id
     * @param  integer  $member_id
     * @param  float  $price
     *
     * @return double
     */
    public static function closeDealInGroup( $member_id, $owner_id, $price )
    {

        // находим процент агента по сделке
        $agentRevenueShare = AgentsPrivateGroups::where('agent_member_id', $member_id)->where('agent_owner_id', $owner_id)->first()->revenue_share;

        // вычисляем процент агента от сделки
        $paymentRevenueShare = ($price / 100) * $agentRevenueShare;

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