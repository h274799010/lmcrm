<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model {

    // отключаем метки времени
    public $timestamps = false;

    protected $table="transactions";
//    protected $fillable = [
//        'created_at','status'
//    ];





    public function initiator()
    {
        return $this->hasOne('App\Models\User', 'id', 'initiator_user_id');

    }



    public function details()
    {
        return $this->hasMany( 'App\Models\TransactionsDetails', 'transaction_id', 'id' )->with('user');
    }

//    public static function create($attributes){
//        $attributes['created_at'] = Date('Y-m-d H:i:s');
//        self::create($attributes);
//    }
}
