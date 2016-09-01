<?php

namespace App\Helper;

use Illuminate\Database\Eloquent\Model;

use App\Models\Credits;
use App\Models\TransactionsHistory;

class CreditManager extends Model
{

    // типы транзакций
    public $type =
    [
        'manual' => 'ручное введение средств',
        'leadBayed' => 'покупка лида'

    ];



    /**
     * Ручное добавление кредитов на счет агента
     *
     */
    public static function addManual(){

        // todo добавить обработку
            // поиск статус агента
            // если это продавец, то нужно найти id агента

        // todo создание транзакции


        // todo запись в историю

        // todo изменение счета


    }


    /**
     * Получение всех данных пользователя по кредитам
     *
     */
    public static function userInfo( $user_id ){

        // todo добавить обработку
            // поиск статус агента
            // если это продавец, то нужно найти id агента
            // или это не сюда

        // todo получение кошелька пользователя cо всеми данными
        $info = Credits::where( 'agent_id', '=', $user_id )->with('transactionHistory')->first();

//        $credit = TransactionsHistory::all();

        // todo оформление данных в коллекцию для удобства

        return $info;


    }






}
