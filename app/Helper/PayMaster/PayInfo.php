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

        if( $operatorPayment ){
            return $operatorPayment->amount;
        }else{
            return Price::processingOperator( $lead_id );
        }
    }


    /**
     * Средства, которые были потраченны системой на лид
     *
     *
     * @param  integer  $lead_id
     *
     * @return double
     */
    public static function LeadSpend( $lead_id )
    {
        // все транзакции в которых учавствовал лид
        $leads = TransactionsLeadInfo::
        where( 'lead_id', $lead_id )                 // только те данные в которых учавствовал лид
        ->lists( 'transaction_id' );                   // только список id транзакций

        // данные покупателей лида
        $operatorPayment = TransactionsDetails::
          whereIn( 'transaction_id', $leads )           // получение деталей по найденным транзакциям
        ->where( 'user_id', PayData::SYSTEM_ID )
        ->sum('amount');

        return $operatorPayment<0 ? $operatorPayment : 0;
    }


    /**
     * Доход системы по лиду
     *
     * --- Если задан агент ---
     * находятся все отрицательные суммы по лиду,
     * с его участием
     *
     * --- Если агент НЕзадан ---
     * выбирается система как пользователь
     * все положительные цифры по лиду к системе
     *
     *
     * todo дописать
     */
    public static function leadSystemReceived( $lead_id )
    {

        // выбираем все id транзакции из лидИнфо по лиду
        $leads = TransactionsLeadInfo::
        where( 'lead_id', $lead_id )  // данные только по заданному лиду
        ->lists( 'transaction_id' );    // возвращается только массив из id транзакций

        if( !$leads ){ return '0'; }

        // по id транзакциям выбираем все детали которые принадлежат пользователя со знаком (+)
        $received = TransactionsDetails::
        where( 'amount', '>', 0 )            // платежи только со знаком +
        ->where( 'user_id', '=', PayData::SYSTEM_ID )    // только платежи пользователя
        ->whereIn( 'transaction_id', $leads )  //
        ->get();

        // todo попробовать так, вроде так должно быть проще
//        sum('amount')


        // суммируем их
        return $received->sum('amount');
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
    public static function SystemRevenueFromLeadSum( $lead_id, $type=['openLead', 'closingDeal', 'repaymentForLead'] )
    {

        $systemRevenue =
            self::SystemRevenueFromLeadDetails( $lead_id, $type );

        return $systemRevenue->sum('amount');

    }

}
