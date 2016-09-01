<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsDetails extends Model
{

    protected $table = "transactions_details";

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

}

