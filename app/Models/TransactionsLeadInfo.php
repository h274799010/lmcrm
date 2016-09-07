<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionsLeadInfo extends Model {

    protected $table="transactions_lead_info";

    public $timestamps = false;

    protected $fillable =
    [
        'number','lead_id'
    ];

//    public function parts(){
//        return $this->hasMany('App\Models\TransactionsDetails','transaction_id','id');
//    }

    /**
     * Получаем транзакцию деталей платежа
     *
     * @return Builder
     */
    public function transaction()
    {
        return $this
            ->hasOne('App\Models\Transactions', 'id', 'transaction_id')  // соединяем с таблицей транзакций
            ->with('details');                                         // добавляем данные инициатора транзакции
    }



    /**
     * Получаем транзакцию деталей платежа
     *
     * @return Builder
     */
    public function buyers()
    {
        return $this
            ->hasOne('App\Models\Transactions', 'id', 'transaction_id')  // соединяем с таблицей транзакций
            ->with('buyers');                                         // добавляем данные инициатора транзакции
    }

}
