<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsDetails extends Model
{

    protected $table = "transactions_details";

    // отключаем метки времени
    public $timestamps = false;

    protected $fillable = [
        'transaction_id', 'wallet_id','user_id', 'wallet_type',
    ];


    /**
     * Получаем транзакцию к которой относится строка записи
     *
     * todo доработать
     */
    public function transaction()
    {
        return $this->hasOne('App\Models\Transactions', 'id', 'transaction_id')->with('initiator');
    }


    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');

    }


}

