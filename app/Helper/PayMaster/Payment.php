<?php

namespace App\Helper\PayMaster;

use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\TransactionsLeadInfo;

use App\Models\Transactions;
use App\Models\TransactionsDetails;
use App\Helper\PayMaster\PayCalculation;


/**
 * Логика платежей
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
class Payment
{

    /**
     * Действие по кошельку
     *
     * если коротко:
     * - снятие денег с кошелька
     * - фиксация процесса в деталях
     *
     *
     * метод меняет состояние счета кошелька агента на величину 'amount'
     * и записывает подробности в таблицу деталей по транзакциям
     *
     * если нужно сделать отрицательный платеж
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
    public static function walletDirectOperation( $data, $userWallet=NULL )
    {

        // выбираем кошелек агента, либо берем заданный, если есть
        $wallet = $userWallet ? $userWallet : Agent::findOrFail( $data['user_id'] )->wallet;

        // если кошелек не найден - выходим из метода
        if( !$wallet ){ return false; }

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
            'user_id' => $data['user_id'],
            'amount' => $details->amount,
            'after' => $details->after,
            'wallet_type' => $details->wallet_type,
            'type' => $details->type,
        ];

        return $out;
    }


    /**
     * Выборочное снятие денег с кошелька
     *
     * Логика такая:
     * - проводится проверка на способность агента заплатить заданную сумму
     * - если на buyed достаточно денег снимается вся сумма с него
     * - если buyed пустой, снимается вся сумма с earned
     * - если на buyed деньги есть, но недостаточно:
     *    - снимается вся сумма с buyed
     *    - снимается остаток платежа с earned
     *
     *
     * ----- структура массива с данными ------
     *
     * [
     *    'transaction'  => ''   // id транзакции
     *    'user_id'      => ''   // id пользователя
     *    'type'         => ''   // тип самой транзакции
     *    'amount'       => ''   // прибавляемая сумма
     * ]
     *
     * -----------------------------------------
     *
     *
     * @param  array  $data         // данные по платежу
     * @param  Wallet  $userWallet  // кошелек
     *
     * @return array
     */
    public static function walletSelectiveOperation( $data, $userWallet=NULL )
    {

        // выбираем кошелек агента, либо берем заданный, если есть
        $wallet = $userWallet ? $userWallet : Agent::findOrFail( $data['user_id'] )->wallet;

        // если кошелек не найден - выходим из метода
        if( !$wallet ){ return false; }

        // приводим значение к целому числу ( на всякий случай, чтобы небыло отрицательное)
        $amount = ( $data['amount'] > 0 ) ? $data['amount'] : -1 * $data['amount'];


        /** --- Проверка возможностей агента по оплате заданной суммы --- */

        // проверка, может ли агент заплатить такую сумму
        $possibility = $wallet->isPossible( $amount );

        // если возможностей недостаточно - выходим из метода
        if( !$possibility ){ return false; }


        // переменная с результатом
        $payment = [];


        /** --- варианты действий в зависимости от средств на кошельках --- */

        if( $wallet['buyed'] >= $amount ){
            // если на buyed достаточно средств, снимаем средства только от туда

            // снимаем деньги с агента
            $payment['buyed'] = self::walletDirectOperation(
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
            $payment['earned'] = self::walletDirectOperation(
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
            $payment['buyed'] = self::walletDirectOperation(
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
            $payment['earned'] = self::walletDirectOperation(
                [
                    'transaction' => $data['transaction'],
                    'user_id'     => $data['user_id'],
                    'wallet_type' => 'earned',
                    'type'        => $data['type'],
                    'amount'      => (-1 * $rest)
                ], $wallet);
        }

        return $payment;
    }


    /**
     * Сохранение данных о лиде при транзакции
     *
     *
     * ----- структура массива с данными ------
     *
     * [
     *    'transaction'  => ''   // id транзакции
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
    public static function saveLeadInfo( $data )
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
     * Основная логика платежа
     *
     *
     *
     *
     * -----------------------  Структура массива с данными по платежу  --------------
     *
     * [
     *    'initiator_id'  => ''    // инициатор платежа
     *
     *    'donor'         =>       // плательщик (с которого снимаются деньги)
     *       [
     *          'user_id'      => ''   // пользователь с которого снимаются деньги
     *          'wallet_type'  => ''   // тип хранилища кошелька
     *       ]
     *
     *    'recipient'     => ''    // получатель платежа (которому деньги зачисляются)
     *       [
     *          'user_id'      => ''   // пользователь с которого снимаются деньги
     *          'wallet_type'  => ''   // тип хранилища кошелька
     *       ]
     *
     *    'lead_id'       => ''    // лид, который учавствует в трнзакции
     *
     *    'lead_number'   => ''    // количество открытых лидов
     *
     *    'type'          => ''    // тип самой транзакции
     *
     *    'amount'        => ''    // прибавляемая сумма
     * ]
     *
     * -----------------------------------------------------------------------------
     *
     *
     * @param  array  $data
     *
     * @return array
     */
    public static function payment( $data )
    {

        // создание транзакции
        $transaction = Transactions::open( $data['initiator_id'] );

        // приводим сумму к натуральному числу
        $amount = ( $data['amount'] > 0 )? $data['amount'] : $data['amount'] * (-1);

        // данные по транзакции
        $transactionInfo =
        [
            'time' => $transaction->created_at,
            'transaction' => $transaction->id,
            'initiator' => $transaction->initiator->name,
        ];


        /** ---- Снятие денег с указанного пользователя если задан донор ---- */

        // если задан донор (пользователь с которого нужно снять средства)
        if( isset($data['donor']) ){

            // проверяем указана ли у донора переменная 'wallet_type'
            // если не указанна то будет выполненна операция по выбору кошельков
            // т.е. с начала снимпться с buyer, затем с earned
            // если указан, то снимется только с указанного типа кошелька
            $wallet_type = ( isset($data['donor']['wallet_type']) ) ? true : false ;

            if( $wallet_type ){
                // если тип указан

                // выполняем прямую операцию снятия денег
                $transactionInfo['donor'] =
                self::walletDirectOperation(
                    [
                        'transaction'  => $transaction->id,              // id транзакции
                        'user_id'      => $data['donor']['user_id'],     // id пользователя
                        'wallet_type'  => $data['donor']['wallet_type'], // тип хранилища кошелька
                        'type'         => $data['type'],                 // тип самой транзакции
                        'amount'       => $amount * (-1),                // прибавляемая сумма
                    ]
                );

            }else{
                // если тип не указан

                // выполняется операция выборочного снятия денег с кошельков
                $transactionInfo['donor'] =
                self::walletSelectiveOperation(
                    [
                        'transaction'  => $transaction->id,              // id транзакции
                        'user_id'      => $data['donor']['user_id'],     // id пользователя
                        'type'         => $data['type'],        // тип самой транзакции
                        'amount'       => $amount * (-1),                // прибавляемая сумма
                    ]
                );
            }
        }


        /** ---- Занесение денег на указанный счет пользователя если задан реципиент ---- */

        // если задан реципиент (пользователь К которому должны прийти средства)
        if( isset($data['recipient']) ){

            // выполняем прямую операцию занесения денег
            $transactionInfo['recipient'] =
            self::walletDirectOperation(
                [
                    'transaction'  => $transaction->id,                  // id транзакции
                    'user_id'      => $data['recipient']['user_id'],     // id пользователя
                    'wallet_type'  => $data['recipient']['wallet_type'], // тип хранилища кошелька
                    'type'         => $data['type'],        // тип самой транзакции
                    'amount'       => $amount,                           // прибавляемая сумма
                ]
            );

        }


        /** ---- Фиксирование данных о лиде ---- */

        // если задан лид
        if( isset($data['lead_id']) ){

            // если незаданно количество лидов, ставиться 1
            $lead_number = (isset( $data['lead_number'] )) ? $data['lead_number'] : 1;

            // данные по транзакции по лиду
            $transactionInfo['leadInfo'] =
            self::saveLeadInfo(
                [
                    'transaction'  => $transaction->id,  // id транзакции
                    'number'       => $lead_number,      // количество лидов
                    'lead_id'      => $data['lead_id'],  // id лида
                ]
            );
        }

        // успешное завершение транзакции
        $transaction->completed();

        $transactionInfo['status'] = $transaction->status;

        // возвращаем массив данных по транзакции
        return $transactionInfo;
    }


    /**
     * Платеж К системе от пользователя
     *
     *
     * @param  array  $data
     *
     * @return array
     */
    public static function toSystem( $data )
    {
        /** Шаблон данных которые нужно передавать в массиве
        $data=
        [
            'initiator_id'  => '',  // id инициатора платежа
            'user_id'       => '',  // id пользователя, с кошелька которого снимается сумма
            'wallet_type'   => '',  // (не обязательно) тип кошелька с которого снимается сумма
            'type'          => '',  // тип транзакции
            'amount'        => '',  // снимаемая с пользователя сумма
            'lead_id'       => '',  // (не обязательно) id лида если он учавствует в платеже
            'lead_number'   => '',  // (не обязательно) количество лидов, если их несколько
        ];
        */


        // выставляем данные по платежу
        $paymentData =
        [
            'initiator_id'  => $data['initiator_id'],  // инициатор платежа

            'donor'         =>                         // плательщик (с которого снимаются деньги)
                [
                    'user_id'      => $data['user_id'],   // пользователь с которого снимаются деньги
                ],

            'recipient'     =>     // получатель платежа (которому деньги зачисляются)
                [
                    'user_id'      => config('payment.system_id'),  // пользователь с которого снимаются деньги
                    'wallet_type'  => 'earned',            // тип хранилища кошелька
                ],

            'type'          => $data['type'],          // тип самой транзакции

            'amount'        => $data['amount'],        // прибавляемая сумма
        ];

        // если задан тип кошелька - передаем его в данные массива
        if( isset($data['wallet_type']) ){
            // указываем тип кошелька
            $paymentData['donor']['wallet_type'] = $data['wallet_type'];
        }

        // если задан лид
        if( isset($data['lead_id']) ){
            // заносим id лида в платеж
            $paymentData['lead_id'] = $data['lead_id'];
        }

        // если заданно количество лидов
        if( isset($data['lead_number']) ){
            // заносим в платеж
            $paymentData['lead_number'] = $data['lead_number'];
        }

        // выполняем платеж
        $paymentInfo =
        self::payment( $paymentData );

        return $paymentInfo;
    }


    /**
     * Платеж ОТ системы к пользователю
     *
     */
    public static function fromSystem( $data )
    {

        /** Шаблон данных которые нужно передавать в массиве
        $data=
        [
            'initiator_id'  => '',  // id инициатора платежа
            'user_id'       => '',  // id пользователя, на кошелек которого будет зачисленна сумма
            'wallet_type'   => '',  // тип кошелька с которого снимается сумма
            'type'          => '',  // тип транзакции
            'amount'        => '',  // снимаемая с пользователя сумма
            'lead_id'       => '',  // (не обязательно) id лида если он учавствует в платеже
            'lead_number'   => '',  // (не обязательно) количество лидов, если их несколько
        ];
        */


        // выставляем данные по платежу
        $paymentData =
            [
                'initiator_id'  => $data['initiator_id'],  // инициатор платежа

                'donor'         =>                         // плательщик (с которого снимаются деньги)
                    [
                        'user_id'      => config('payment.system_id'),   // пользователь с которого снимаются деньги
                        'wallet_type'  => 'earned',             // тип хранилища кошелька
                    ],

                'recipient'     =>                         // получатель платежа (которому деньги зачисляются)
                    [
                        'user_id'      => $data['user_id'],      // пользователь с которого снимаются деньги
                        'wallet_type'  => $data['wallet_type'],  // тип хранилища кошелька
                    ],

                'type'          => $data['type'],          // тип самой транзакции

                'amount'        => $data['amount'],        // прибавляемая сумма
            ];


        // если задан лид
        if( isset($data['lead_id']) ){
            // заносим id лида в платеж
            $paymentData['lead_id'] = $data['lead_id'];
        }

        // если заданно количество лидов
        if( isset($data['lead_number']) ){
            // заносим в платеж
            $paymentData['lead_number'] = $data['lead_number'];
        }

        // выполняем платеж
        $paymentInfo =
        self::payment( $paymentData );

        return $paymentInfo;
    }


    /**
     * Единичный платеж
     *
     * не от "кого-то" - "кому-то"
     * а именно единичный платеж
     *
     * это может быть ручное пополнение
     * либо - оплата обработки лида оператором
     *
     */
    public static function single( $data )
    {

        /** Шаблон данных которые нужно передавать в массиве
        $data=
        [
            'initiator_id'  => '',  // id инициатора платежа
            'user_id'       => '',  // id пользователя, на кошелек которого будет зачисленна сумма
            'wallet_type'   => '',  // тип кошелька с которого снимается сумма
            'type'          => '',  // тип транзакции
            'amount'        => '',  // снимаемая с пользователя сумма
            'lead_id'       => '',  // (не обязательно) id лида если он учавствует в платеже
            'lead_number'   => '',  // (не обязательно) количество лидов, если их несколько
        ];
        */


        // основные данные по транзакции
        $paymentData =
        [
            'initiator_id'  => $data['initiator_id'],  // инициатор платежа
            'type'          => $data['type'],          // тип самой транзакции
            'amount'        => $data['amount'],        // прибавляемая сумма
        ];


        // выставляем донора или реципиента в зависимости от направления платежа (+\-)
        if( $data['amount'] > 0 ){
            // если сумма положительная

            // заносим пользователя в массив как реципиента
            $paymentData['recipient'] =
            [
                'user_id'      => $data['user_id'],      // пользователь с которого снимаются деньги
                'wallet_type'  => $data['wallet_type'],  // тип хранилища кошелька
            ];


        }else{
            // если сумма отрицательная

            // заносим пользователя в массив как донора
            $paymentData['donor'] =
            [
                'user_id'      => $data['user_id'],       // пользователь с которого снимаются деньги
                'wallet_type'  => $data['wallet_type'],   // тип хранилища кошелька
            ];
        }

        // если задан лид
        if( isset($data['lead_id']) ){
            // заносим id лида в платеж
            $paymentData['lead_id'] = $data['lead_id'];
        }

        // если заданно количество лидов
        if( isset($data['lead_number']) ){
            // заносим в платеж
            $paymentData['lead_number'] = $data['lead_number'];
        }

        // выполняем платеж
        $paymentInfo =
            self::payment( $paymentData );

        return $paymentInfo;
    }


}