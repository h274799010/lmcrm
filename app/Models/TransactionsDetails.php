<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionsDetails extends Model
{

    /**
     * Подключаем таблицу из БД
     *
     * @var string
     */
    protected $table = "transactions_details";

    /**
     * Отключаем временные метки
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Атрибуты, для которых разрешено массовое назначение
     *
     * @var array
     */
    protected $fillable =
    [
        'transaction_id',
        'wallet_id',
        'user_id',
        'wallet_type',
    ];


    /**
     * Получаем транзакцию деталей платежа
     *
     * @return Builder
     */
    public function transaction()
    {
        return $this
            ->hasOne('App\Models\Transactions', 'id', 'transaction_id')  // соединяем с таблицей транзакций
            ->with('initiator');                                         // добавляем данные инициатора транзакции
    }


    /**
     * Находим пользователя который платит/получает платеж
     *
     * @return Builder
     */
    public function user()
    {
        return $this
            ->hasOne('App\Models\User', 'id', 'user_id');  // соединяем с таблицей пользователей
    }

}

