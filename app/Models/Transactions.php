<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Sirius\Upload\Result\Collection;

class Transactions extends Model {

    /**
     * Задаем таблицу
     *
     * @var string
     */
    protected $table="transactions";

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;


    /**
     * Поля БД с датой
     *
     */
    protected $dates = ['created_at'];


    /**
     * Данные пользователя запустишего транзакцию
     *
     * @return Builder
     */
    public function initiator()
    {
        return $this
            ->hasOne('App\Models\User', 'id', 'initiator_user_id');  // соединяем с таблицей пользователей
    }


    /**
     * Открытие транзакции транзакции
     *
     * создание транзакции для проведение платежей
     *
     *
     * @param integer $initiator_id  // id инициатора транзакции
     *
     * @return Transactions
     */
    public static function open( $initiator_id )
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
     * Присваивает записи статус 'completed'
     *
     * @return Transactions
     */
    public function completed()
    {
        //  присваивае статус 'completed'
        $this->status = 'completed';
        // сохранение записи
        $this->save();

        return $this;
    }


    /**
     * Получение деталей по транзакциям (в обратном порядке)
     *
     * @return Builder
     */
    public function details()
    {
        return $this
            ->hasMany( 'App\Models\TransactionsDetails', 'transaction_id', 'id' )  // соединяем с таблицей деталей
            ->with('user')                                                         // вместе с пользователями
            ->orderBy('id', 'desc');                                               // в обратном порядке
    }


    public function buyers()
    {
        return $this
            ->hasMany( 'App\Models\TransactionsDetails', 'transaction_id', 'id' )  // соединяем с таблицей деталей
            ->where( 'type', 'openLead' )
            ->with('user')                                                         // вместе с пользователями
            ->orderBy('id', 'desc');                                               // в обратном порядке
    }

}
