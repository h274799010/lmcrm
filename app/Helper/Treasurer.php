<?php

namespace App\Helper;

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
 * Класс полностью отвечает за деньги
 *
 *
 * todo удалить потом (удалить только объяснение название класса :) )
 * дословно переводиться как "казначей",
 * вроде подходящее название
 */
class Treasurer extends Model
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
    ];


    /**
     * todo пересмотреть и удалиь
     *
     *


        todo старые типы на всякий случай
        const LEAD_PURCHASE = -1;
        const LEAD_SALE = 2;
        const EXTERNAL_REFILL = 3;
        const MANUAL_CHANGE = 4;
        const LEAD_BAD_INC = 5;
        const LEAD_BAD_DEC = -6;
        const OPERATOR_PAYMENT = -7;
    */

    /**
     * Открытие транзакции транзакции
     *
     * создание транзакции для проведение платежей
     *
     *
     * @param integer $initiator_id  // id транзакци
     *
     * @return Transactions
     */
    public static function transaction( $initiator_id )
    {
        // создание новой транзакции
        $transaction = new Transactions();

        // записываем инициатора транзакции
        $transaction->initiator_user_id = $initiator_id;

        // устанавливаем время транзакции
        $transaction->created_at = Date('Y-m-d H:i:s');

        // сохраняем
        $transaction->save();

        return $transaction;
    }


    /**
     * Проведение платежа
     *
     * метод меняет состояние счета кошелька агента на величину 'amount'
     * и записывает подробности в таблицу деталей по транзакциям
     *
     * метод только прибавляет данные, если нужно сделать отрицательный платеж
     * параметр 'amount' должен быть отрицательным
     *
     * если кошелек не задан, метод выберет его по user_id
     * если задан, будет использовать заданный
     *
     * ----- структура массива с данными ------
     *
     * [
     *    'transaction'  => ''   // id транзакции
     *    'user_id'      => ''   // id пользователя
     *    'wallet_type'  => ''   // тип хранилища кошелька
     *    'type'         => ''   // тип самой транзакции
     *    'amount'       => ''   // прибавляемая сумма
     * ]
     *
     * -----------------------------------------
     *
     *
     * @param  array  $data          // массив с данными по платежу
     * @param  object  $userWallet   // кошелек пользователя
     *
     * @return object
     */
    public static function payment( $data, $userWallet=NULL )
    {

        // выбираем кошелек агента, либо берем заданный, если есть
        $wallet = $userWallet ? $userWallet : Agent::findOrFail( $data['user_id'] )->wallet;

        // создаем новую запись в деталях транзакций
        $details = new TransactionsDetails();

        // записываем в историю кредитов id транзакции
        $details->transaction_id = $data['transaction'];

        // id пользователя
        $details->user_id = $data['user_id'];

        // тип хранилища кредитов
        $details->wallet_type = $data['wallet_type'];

        // тип транзакции
        $details->type = $data['type'];

        // величина на которую изменена сумма кредита
        $details->amount = $data['amount'];

        // прибавление данных к соответствующему типу хранилища кошелька
        $wallet->$data['wallet_type'] += $data['amount'];

        // сумма после проведения изменения
        $details->after = $wallet->$data['wallet_type'];

        // сохраняем данные
        $wallet->details()->save($details);
        $wallet->save();

        $out =
        [
            'amount' => $details->amount,
            'after' => $details->after,
            'wallet_type' => $details->wallet_type,
            'type' => $details->type,
        ];

        return $out;
    }


    // todo обработать
    // todo добавить проверку на овердрафт
    public static function pay( $data, $userWallet=NULL )
    {

        // todo добавить проверку баланса и выход елси баланса недостаточно

        // выбираем кошелек агента, либо берем заданный, если есть
        $wallet = $userWallet ? $userWallet : Agent::findOrFail( $data['user_id'] )->wallet;

        // приводим значение к целому числу (если отрицательное)
        $amount = ( $data['amount'] > 0 ) ? $data['amount'] : -1 * $data['amount'];

        // переменная с результатом
        $payer = [];

        // варианты действий в зависимости от средств на кошельках
        if( $wallet['buyed'] >= $amount ){
            // если на buyed достаточно средств, снимаем средства только от туда

            // снимаем деньги с агента
            $payer['buyed'] = self::payment(
            [
                'transaction' => $data['transaction'],
                'user_id'     => $data['user_id'],
                'wallet_type' => 'buyed',
                'type'        => $data['type'],
                'amount'      => (-1 * $amount)
            ], $wallet);

        }elseif( $wallet['buyed'] == 0 ){
            // если на buyed нет средств, снимаем средства только с earned

            // снимаем деньги с агента
            $payer['earned'] = self::payment(
            [
                'transaction' => $data['transaction'],
                'user_id'     => $data['user_id'],
                'wallet_type' => 'earned',
                'type'        => $data['type'],
                'amount'      => (-1 * $amount)
            ], $wallet);

        }elseif( $wallet['buyed'] < $amount ){
            // если на buyed средств меньше чем требуется по прайсу

            // сумма на buyed
            $buyed = $wallet['buyed'];

            // с начала снимаем деньги с buyed
            $payer['buyed'] = self::payment(
            [
                'transaction' => $data['transaction'],
                'user_id'     => $data['user_id'],
                'wallet_type' => 'buyed',
                'type'        => $data['type'],
                'amount'      => (-1 * $wallet['buyed'])
            ], $wallet);

            // находим оставшуюся сумму
            $rest = $amount - $buyed;

            // затем снимаем оставшуюся сумму с earned
            $payer['earned'] = self::payment(
            [
                'transaction' => $data['transaction'],
                'user_id'     => $data['user_id'],
                'wallet_type' => 'earned',
                'type'        => $data['type'],
                'amount'      => (-1 * $rest)
            ], $wallet);
        }

        return $payer;
    }


    /**
     * Сохранение данных о лиде при транзакции
     *
     *
     * ----- структура массива с данными ------
     *
     * [
     *    'transaction'  => ''   // id транзакции
     *    'user_id'      => ''   // id пользователя
     *    'number'       => ''   // количество лидов
     *    'lead_id'      => ''   // id лида
     * ]
     *
     * -----------------------------------------
     *
     *
     * @param  array  $data   // данные для записи в таблицу
     *
     * @return array
     */
    public static function leadInfo( $data )
    {
        // создаем новую запись в таблице transactions_lead_info
        $leadInfo = new TransactionsLeadInfo();

        // сохраняем id транзакции
        $leadInfo->transaction_id = $data['transaction'];

        // сохраняем количество лидов, которые были открыты
        $leadInfo->number = $data['number'];

        // id открытого лида
        $leadInfo->lead_id = $data['lead_id'];

        // сохраняем
        $leadInfo->save();

        // возвращаем данные по лиду
        $lead =
        [
            'number'  => $leadInfo->number,
            'lead_id' => $leadInfo->lead_id,
        ];

        return $lead;
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
        $transaction = self::transaction( $initiator_id );

        // заносим деньги на счет системы
        $payee = self::payment(
        [
            'transaction' => $transaction->id,
            'user_id'     => $user_id,
            'wallet_type' => $wallet_type,
            'type'        => 'manual',
            'amount'      => $amount
        ]);

        // выставляем статус нормального завершения транзакции
        $transaction->completed();

        // переменная с данным по транзакции
        $transactionInfo =
        [
            'time'        => $transaction->created_at,
            'amount'      => $payee['amount'],
            'after'       => $payee['after'],
            'wallet_type' => $payee['wallet_type'],
            'type'        => $payee['type'],
            'transaction' => $transaction->id,
            'initiator'   => $transaction->initiator->name,
            'status'      => $transaction->status
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
        $info = Wallet::where( 'user_id', '=', self::SYSTEM_ID )->with('details')->first();

        return $info;
    }


    /**
     * Все транзакции системы
     *
     */
    public static function allTransactions()
    {
        return Transactions::with('details', 'initiator')
            ->orderBy('id', 'desc')
            ->get();
    }


    /**
     * Оплата работы оператора
     *
     * todo доделать
     *
     * @param  integer  $initiator_id   // id оператора
     * @param  integer  $lead_id        // id лида
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
        $wallet = Wallet::where( 'user_id', '=', self::SYSTEM_ID )->first();;

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


    /**
     * Транзакция при открытии данных лида агентом
     *
     *
     * если у агента недостаточно средства,
     * метод возвращает "low balance"
     *
     * если все прошло хорошо, возвращает true
     *
     *
     * @param  integer  $user_id   // id агента который открывает лид
     * @param  integer  $lead_id   // id открываемого лида
     * @param  integer  $mask_id   // id маски по которой лид открывается
     *
     * @return array
     */
    public static function openLead( $user_id, $lead_id, $mask_id )
    {

        /** --  Ключевые данные  -- */

        // данные лида
        $lead = Lead::find( $lead_id );

        // кошелек агента
        $wallet = Agent::findOrFail( $user_id )->wallet;

        // цена лида по маске
        $price = $lead->price( $mask_id );



        /** --  Проверка, достаточно ли средств у агента для покупки лида  -- */

        // сравниваем возможности агента с ценой лида
        if( !$wallet->possibility( $price ) ){
                // если возможности меньше чем цена лида
                //  - выходим
            return 'low balance';
        }



        /** --  Проверка количества открытия лида  -- */

        // если лид открыт максимальное количество раз - выходим из метода
        if( $lead->opened >= $lead->MaxOpenNumber() ){

            return 'the maximum number of open';
        }



        /** --  Проводим транзакцию  -- */

        // открываем транзакцию
        $transaction = self::transaction( $user_id );

        $payer = false;   // агент (который платит)
        $payee = false;   // система (получает платеж)


        // вычитание платежа с кошелька агента (если транзакция созданна ормально)
        if( $transaction ) {
            $payer = self::pay(
            [
                'transaction' => $transaction->id,
                'user_id' => $user_id,
                'type' => 'openLead',
                'amount' => $price
            ], $wallet);
        }

        // занесение платежа на счет системы (если платеж агента прошел нормально)
        if( $payer ) {
            $payee = self::payment(
            [
                'transaction' => $transaction->id,
                'user_id' => self::SYSTEM_ID,
                'wallet_type' => 'earned',
                'type' => 'openLead',
                'amount' => $price
            ]);
        }

        // запись данных о лиде (если платеж получен нормально)
        if( $payee ) {
            $leadInfo = self::leadInfo(
            [
                'transaction' => $transaction->id,
                'user_id' => $user_id,
                'number' => 1,
                'lead_id' => $lead_id

            ]);
        }

        // если платеж прошел нормально
        if( $payee ){

            // открытие лида
            $lead->open( $user_id );

            // выставляем статус успешного завершения транзакции
            $transaction->completed();


            $transactionInfo =
            [
                'time' => $transaction->created_at,
                'transaction' => $transaction->id,
                'initiator' => $transaction->initiator->name,
                'status' => $transaction->status,
                'payer' => $payer,
                'payee' => $payee,
                'lead' => $leadInfo
            ];

            return $transactionInfo;
        }

        return false;
    }


    /**
     * Транзакция при совершении сделки агента с клиентом
     * ( статус лида = 6 )
     *
     * если у агента недостаточно средства,
     * метод возвращает "low balance"
     *
     * если все прошло хорошо, возвращает true
     *
     *
     * @param  integer  $user_id   // id агента который открывает лид
     * @param  integer  $lead_id   // id открываемого лида
     * @param  integer  $mask_id   // id маски по которой лид открывается
     *
     * @return array
     */
    public static function closingDeal( $user_id, $lead_id, $mask_id )
    {

        /** --  Ключевые данные  -- */

        // данные лида
        $lead = Lead::find( $lead_id );

        // кошелек агента
        $wallet = Agent::findOrFail( $user_id )->wallet;

        // todo пока эти данные не известны, доработать позже
        // цена закрытия сделки
        $price = $lead->price( $mask_id );



        /** --  Проверка, достаточно ли средств у агента для покупки лида  -- */

        // сравниваем возможности агента с ценой лида
        if( !$wallet->possibility( $price ) ){
            // если возможности меньше чем цена лида
            //  - выходим
            return 'low balance';
        }



        /** --  Проводим транзакцию  -- */

        // открываем транзакцию
        $transaction = self::transaction( $user_id );

        $payer = false;   // агент (который платит)
        $payee = false;   // система (получает платеж)


        // вычитание платежа с кошелька агента (если транзакция созданна ормально)
        if( $transaction ) {
            $payer = self::pay(
            [
                'transaction' => $transaction->id,
                'user_id' => $user_id,
                'type' => 'closingDeal',
                'amount' => $price
            ], $wallet);
        }

        // занесение платежа на счет системы (если платеж агента прошел нормально)
        if( $payer ) {
            $payee = self::payment(
            [
                'transaction' => $transaction->id,
                'user_id' => self::SYSTEM_ID,
                'wallet_type' => 'earned',
                'type' => 'closingDeal',
                'amount' => $price
            ]);
        }


        // если платеж прошел нормально
        if( $payee ){

            // открытие лида
            $lead->status = 6;
            $lead->save();

            // выставляем статус успешного завершения транзакции
            $transaction->completed();


            $transactionInfo =
            [
                'time' => $transaction->created_at,
                'transaction' => $transaction->id,
                'initiator' => $transaction->initiator->name,
                'status' => $transaction->status,
                'payer' => $payer,
                'payee' => $payee,
            ];

            return $transactionInfo;
        }

        return false;


    }




}
