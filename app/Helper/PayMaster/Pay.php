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
class Pay
{

    /**
     * Оплата за открытие лида
     *
     */
    public static function openLead( $user_id, $lead_id, $mask_id )
    {

        // todo получаем цену лида

        // todo возможность пользователя заплатить за лид

        // todo оплачиваем лид
        Payment::toSystem(
            [
                'user_id' => 1

            ]
        );

        return true;

    }


}