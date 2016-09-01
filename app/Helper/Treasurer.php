<?php

namespace App\Helper;

use Illuminate\Database\Eloquent\Model;

use App\Models\Wallet;
use App\Models\Transactions;
use App\Models\TransactionsDetails;




/**
 * Класс полностью отвечает за деньги
 *
 *
 * todo удалить потом
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
     * id пользователя по которому нужно получить финансовые данные
     * @param integer $user_id
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
