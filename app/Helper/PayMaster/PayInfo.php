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
use phpDocumentor\Reflection\DocBlock\Type\Collection;


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
class PayInfo
{

    /**
     * Транзакции агента по лиду
     */
    public static function AgentOnLead()
    {

    }


    /**
     * Данные покупателей лида
     *
     * @param  integer  $lead_id
     *
     * @return Collection
     */
    public static function LeadBuyers( $lead_id )
    {
        // все транзакции в которых учавствовал лид
        $leads = TransactionsLeadInfo::
              where( 'lead_id', $lead_id )                 // только те данные в которых учавствовал лид
            ->lists( 'transaction_id' );                   // только список id транзакций

        // данные покупателей лида
        $byersDetails = TransactionsDetails::
              whereIn( 'transaction_id', $leads )           // получение деталей по найденным транзакциям
            ->where( 'type', 'openLead' )                   // только с типом "открытие лида"
            ->where( 'user_id', '<>', PayData::SYSTEM_ID )  // убираем из выборки данные системы
            ->with('lead')                                  // добавляем в выборку данные лида
            ->get();

        return $byersDetails;
    }

}
