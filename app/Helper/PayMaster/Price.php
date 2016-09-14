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






}