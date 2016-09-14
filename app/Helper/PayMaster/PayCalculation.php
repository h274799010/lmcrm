<?php

namespace App\Helper\PayMaster;

use App\Models\OpenLeads;
use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\TransactionsLeadInfo;

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




}