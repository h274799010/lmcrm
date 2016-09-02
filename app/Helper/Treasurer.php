<?php

namespace App\Helper;

use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\Transactions;
use App\Models\TransactionsDetails;




/**
 * Класс полностью отвечает за деньги
 *
 *
 * todo удалить потом (удалить только объяснение название класса :) )
 * дословно переводиться как "казначей",
 * вроде подходящее название
 */
class Treasurer extends Model
{

    // типы транзакций
    public $type =
    [
        'manual' => 'ручное введение средств',
        'leadBayed' => 'покупка лида'

    ];

// todo старые типы на всякий случай
//    const LEAD_PURCHASE = -1;
//    const LEAD_SALE = 2;
//    const EXTERNAL_REFILL = 3;
//    const MANUAL_CHANGE = 4;
//    const LEAD_BAD_INC = 5;
//    const LEAD_BAD_DEC = -6;
//    const OPERATOR_PAYMENT = -7;



    /**
     * Ручное добавление денежных средств на счет агента
     *
     *
     * @param  integer  $initiator_id   // id инициатора транзакции, тот, кто ее запускает (по идее админ)
     * @param  integer  $user_id        // id пользователя в кошельке которого происходят изменения
     * @param  string  $wallet_type     // тип кошелька пользователя ( buyed, earned, wasted )
     * @param  float  $amount           // величина на которую изменяется сумма кошелька
     *
     * @return array
     */
    public static function addManual( $initiator_id, $user_id, $wallet_type, $amount){


        // создание новой транзакции
        $transaction = new Transactions();
        // записываем инициатора транзакции
        $transaction->initiator_user_id = $initiator_id;
        // устанавливаем время транзакции
        $transaction->created_at = Date('Y-m-d H:i:s');
        // сохраняем
        $transaction->save();

        // получаем кредиты агента
        $wallet = Agent::findOrFail($user_id)->wallet;

        // создаем новую запись в деталях транзакций
        $details = new TransactionsDetails();

        // записываем в историю кредитов id транзакции
        $details->transaction_id = $transaction->id;

        // тип хранилища кредитов
        $details->wallet_type = $wallet_type;

        // тип транзакции
        $details->type = 'manual';

        // величина на которую изменена сумма кредита
        $details->amount = $amount;


        if( $wallet_type == 'buyed' ){

            $wallet->buyed += $amount;

            // начальная сумма кредита
            $details->after = $wallet->buyed;

        }else if( $wallet_type == 'earned' ){

            $wallet->earned += $amount;

            // начальная сумма кредита
            $details->after = $wallet->earned;

        }else if( $wallet_type == 'wasted' ){

            $wallet->wasted += $amount;

            // начальная сумма кредита
            $details->after = $wallet->wasted;
        }

        $wallet->details()->save($details);
        $wallet->save();

        // выставляем статус нормального завершения транзакции
        $transaction->status = 'completed';
        $transaction->save();

        $transactionInfo =
        [
            'time' => $transaction->created_at,
            'amount' => $details->amount,
            'after' => $details->after,
            'wallet_type' => $details->wallet_type,
            'type' => $details->type,
            'transaction' => $transaction->id,
            'initiator' => $transaction->user->name,
            'status' => $transaction->status
        ];

        return $transactionInfo;
    }


    /**
     * Полная информация о денежных средствах пользователя
     *
     *
     * @param integer $user_id   // id пользователя по которому нужно получить финансовые данные
     *
     * @return object
     */
    public static function userInfo( $user_id ){

        // todo добавить обработку
            // поиск статус агента
            // если это продавец, то нужно найти id агента
            // или это не сюда

        // получение кошелька пользователя c подробными данными
        $info = Wallet::where( 'user_id', '=', $user_id )->with('details')->first();

        // todo оформление данных в коллекцию для удобства

        return $info;

    }






}
