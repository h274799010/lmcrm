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

}
