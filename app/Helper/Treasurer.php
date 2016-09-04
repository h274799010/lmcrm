<?php

namespace App\Helper;

use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\TransactionsLeadInfo;

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

    // id, под которым в БД зарегистрирована система, как пользователь
    private static $system_id = 1;

    // типы транзакций
    public static $type =
    [
        'manual' => 'ручное введение средств',
        'operatorPayment' => 'обработка лида оператором',
        'leadBayed' => 'покупка лида',
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
     * Основная логика транзакций
     *
     * todo доработать
     */
    public static function transaction()
    {

        // todo переменная с данными по транзакции
        $data =
        [
            'initiator' => '',
            'user' => '',
            'wallet' => '',
            'amount' => '',
            'type' => ''
        ];


        // todo доработать проверить все ли данные для операции в наличии
        if( false ){
            return false;
        }

        // todo создание транзакции
        // создание новой транзакции
        $transaction = new Transactions();
        // записываем инициатора транзакции
        $transaction->initiator_user_id = $data['initiator'];
        // устанавливаем время транзакции
        $transaction->created_at = Date('Y-m-d H:i:s');
        // сохраняем
        $transaction->save();






        // получаем кредиты агента
        $wallet = Agent::findOrFail( $data['user'] )->wallet;

        // создаем новую запись в деталях транзакций
        $details = new TransactionsDetails();

        // записываем в историю кредитов id транзакции
        $details->transaction_id = $transaction->id;

        $details->user_id = $wallet->user_id;

        // тип хранилища кредитов
        $details->wallet_type = $data['wallet'];

        // тип транзакции
        $details->type = $data['type'];

        // величина на которую изменена сумма кредита
        $details->amount = $data['amount'];



        // todo обработка по типу транзакции


        // todo логика простого ручного пополнения администратором

        if( $data['type'] == 'buyed' ){

            $wallet->buyed += $data['amount'];

            // начальная сумма кредита
            $details->after = $wallet->buyed;

        }else if( $data['type'] == 'earned' ){

            $wallet->earned += $data['amount'];

            // начальная сумма кредита
            $details->after = $wallet->earned;

        }else if( $data['type'] == 'wasted' ){

            $wallet->wasted += $data['amount'];

            // начальная сумма кредита
            $details->after = $wallet->wasted;
        }











        // todo сохранение данных
        $wallet->details()->save($details);
        $wallet->save();

        // выставляем статус нормального завершения транзакции
        $transaction->status = 'completed';
        $transaction->save();



    }


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
    public static function changeManual( $initiator_id, $user_id, $wallet_type, $amount )
    {
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

        $details->user_id = $wallet->user_id;

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
            'initiator' => $transaction->initiator->name,
            'status' => $transaction->status
        ];

        return $transactionInfo;
    }


    /**
     * Полная информация о денежных средствах пользователя
     *
     *
     * @param  integer  $user_id   // id пользователя по которому нужно получить финансовые данные
     *
     * @return object
     */
    public static function userInfo( $user_id )
    {
        // todo добавить обработку
            // поиск статус агента
            // если это продавец, то нужно найти id агента
            // или это не сюда

        // получение кошелька пользователя c подробными данными
        $info = Wallet::where( 'user_id', '=', $user_id )->with('details')->first();

        return $info;
    }


    /**
     * Полная информация о денежных средствах системы
     *
     * предполагается что id системы (как пользователя) будет равнятся "1"
     *
     *
     * @return object
     */
    public static function systemInfo()
    {
        // получение кошелька пользователя c подробными данными
        $info = Wallet::where( 'user_id', '=', self::$system_id )->with('details')->first();

        return $info;
    }


    /**
     * Все транзакции системы
     *
     */
    public static function allTransactions()
    {
        return Transactions::with('details', 'initiator')->orderBy('id', 'desc')->get();
    }


    /**
     * Оплата работы оператора
     *
     * @param  integer  $initiator_id    // id оператора
     * @param  integer  $lead_id  // id лида
     *
     * @return object
     */
    public static function operatorPayment( $initiator_id, $lead_id )
    {

        // данные лида вместе с сферой
        $lead = Lead::with('sphere')->find($lead_id);

        // создание новой транзакции
        $transaction = new Transactions();
        // записываем инициатора транзакции
        $transaction->initiator_user_id = $initiator_id;
        // устанавливаем время транзакции
        $transaction->created_at = Date('Y-m-d H:i:s');
        // сохраняем
        $transaction->save();

        // получаем кредиты агента
        $wallet = Wallet::where( 'user_id', '=', self::$system_id )->first();;

        // создаем новую запись в деталях транзакций
        $details = new TransactionsDetails();

        // записываем в историю кредитов id транзакции
        $details->transaction_id = $transaction->id;

        // записываем id пользователя кошелька в таблицу details
        $details->user_id = $wallet->user_id;

        // тип хранилища кредитов
        $details->wallet_type = 'earned';

        // тип транзакции (обработка лида оператором)
        $details->type = 'operatorPayment';

        // записываем стоимость лида
        $details->amount = $lead['sphere']['price_call_center'] * (-1);

        // вычитаем стоимость лида с кошелька системы
        $wallet->earned += $details->amount;

        // сумма после транзакции
        $details->after = $wallet->earned;

        // сохранение
        $wallet->details()->save($details);
        $wallet->save();


        // сохранение данных о лиде
        $leadInfo = new TransactionsLeadInfo();
        $leadInfo->transaction_id = $transaction->id;
        $leadInfo->number = 1;
        $leadInfo->lead_id = $lead_id;
        $leadInfo->save();


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
                'initiator' => $transaction->initiator->name,
                'status' => $transaction->status
            ];


        return $transactionInfo;

    }

}
