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


    /**
     * Средства, которые были затрачены на обработку лида оператором
     *
     *
     * @param  integer  $lead_id
     *
     * @return double
     */
    public static function OperatorPayment( $lead_id )
    {
        // все транзакции в которых учавствовал лид
        $leads = TransactionsLeadInfo::
              where( 'lead_id', $lead_id )                 // только те данные в которых учавствовал лид
            ->lists( 'transaction_id' );                   // только список id транзакций

        // данные покупателей лида
        $operatorPayment = TransactionsDetails::
              whereIn( 'transaction_id', $leads )           // получение деталей по найденным транзакциям
            ->where( 'type', 'operatorPayment' )            // только с типом "оплата за оператора"
            ->first();

        return $operatorPayment->amount;
    }


    /**
     * Метод возвращает платежные данные по лиду
     *
     * Источники доходов (по умолчанию возвращает их):
     *    openLead   - открытие лида агентом
     *    closingDeal  - закрытие сделки агентом
     *
     * Но может возвращать данные по любому существующему типу,
     * если ему задать
     *
     *
     * @param  integer  $lead_id
     * @param  array|string  $type
     *
     * @return Collection
     */
    public static function SystemRevenueFromLeadDetails( $lead_id, $type=['openLead', 'closingDeal'] )
    {

        // если заданна строка или другой тип (не массив)
        // преобразовуем в массив
        $type = ( is_array($type) ) ? $type : [$type];

        // todo добавить еще один параметр

        // все транзакции в которых учавствовал лид
        $leads = TransactionsLeadInfo::
              where( 'lead_id', $lead_id )             // только те данные в которых учавствовал лид
            ->lists( 'transaction_id' );               // только список id транзакций

        // данные покупателей лида
        $systemRevenue = TransactionsDetails::
              whereIn( 'transaction_id', $leads )      // получение деталей по найденным транзакциям
            ->where( 'user_id', PayData::SYSTEM_ID )   // только с типом "оплата за оператора"
            ->whereIn( 'type', $type )
            ->get();

        return $systemRevenue;
    }


    /**
     * Метод возвращает сумму доходов по лиду
     *
     * То же самое что и SystemRevenueFromLeadDetails()
     * только возвращает доход в виде суммы
     *
     *
     * @param  integer  $lead_id
     * @param  array|string  $type
     *
     * @return double
     */
    public static function SystemRevenueFromLeadSum( $lead_id, $type=['openLead', 'closingDeal'] )
    {

        $systemRevenue =
            self::SystemRevenueFromLeadDetails( $lead_id, $type );

        return $systemRevenue->sum('amount');

    }

}
