<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsHistory extends Model {

    protected $table="transactions_history";

    // отключаем метки времени
    public $timestamps = false;


    /**
     * Получаем транзакцию к которой относится строка записи
     *
     * todo доработать
     */
    public function transaction()
    {
        return $this->hasOne('App\Models\Transactions', 'id', 'transaction_id')->with('user');
    }


    /**
     * Название ресурса
     *
     * todo доработать, когда переименуется bill_id
     *
     */
    public function sourceName()
    {
        return $this->hasOne('App\Models\CreditTypes', 'id', 'source');
    }


}
/// event on save/update/delete - change Credit


