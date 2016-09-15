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
class PayData
{

    /**
     * id системы
     * id под которым в БД зарегистрированна система в таблице пользователей
     * по этому id будет выбираться кошелек
     *
     * @var integer
     */
    const SYSTEM_ID = 1;

    /**
     * Типы транзакций
     *
     * @var array
     */
    public static $type =
    [
        'manual' => 'ручное введение средств',
        'operatorPayment' => 'обработка лида оператором',
        'openLead' => 'открытие лида',
        'closingDeal' => 'закрытие сделки',
        'repaymentForLead ' => 'возврат средств за bad lead',  // возврат средств за bad_lead агентам которые его купили
        'operatorRepayment' => 'refund for operator handling', // "штраф" за оператора автору "плохого" лида
        'rewardForLead' => 'Agent reward for the Lead',        // "награждение" агента за "хороший" лид
    ];


}