<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credits extends Model {
    //todo: залочить создание updated_at
    protected $table="credits";
    public $buyedChange = 0;
    public $earnedChange = 0;
    public $transaction_id = 0;

    // отключаем метки времени
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agent_id','buyed','earned'
    ];

    public function agent(){
        return $this->belongsTo('App\Models\Agent', 'id', 'agent_id');
    }

    public function getBalanceAttribute(){
        return $this->attributes['buyed']+$this->attributes['earned'];
    }

    /**
     * Возвращает всю историю по кредиту
     *
     * todo доработать, когда переименуется bill_id
     *
     */
    public function history()
    {
        return $this->hasMany('App\Models\CreditHistory', 'bill_id', 'id')->with('sourceName')->orderBy('id', 'desc');
//        return $this->hasMany('App\Models\CreditHistory', 'bill_id', 'id')->orderBy('id', 'desc');

    }

    public function transactionHistory()
    {
        return $this->hasMany('App\Models\TransactionsHistory', 'credit_id', 'id')->with('transaction')->orderBy('id', 'desc');
//        return $this->hasMany('App\Models\CreditHistory', 'bill_id', 'id')->orderBy('id', 'desc');

    }


    /**
     * Название ресурса
     *
     * todo доработать, когда переименуется bill_id
     *
     */
    public function sourceName()
    {
        return $this->hasOne('App\Models\CreditTypes', 'id', 'source')->orderBy('id', 'desc');
    }




    //сначала вычитаем стоимость из buyed. Если buyed закончилось, а стоимость ещё нет, то остаток стоимости вычитаем из earned.
    public function setPaymentAttribute($value){
        if($this->attributes['buyed'] < $value) {
            $change = ($value - $this->attributes['buyed']);
            $this->earnedChange = $change;
            $this->attributes['earned'] -= $change;
            $this->attributes['buyed'] = 0;
        } else {
            $this->attributes['buyed'] -= $value;
            $this->buyedChange = $value;
        }
    }

//    public function save(array $options = []){
    public function setUp( $source ){

        $history = new CreditHistory();
        if ($this->source < 0){
            $this->earnedChange *= -1;
            $this->buyedChange *= -1;
        }

//        $history->buyed = $this->buyed;
//        $history->earned = $this->earned;

        $history->cash = $this->buyed;

        $history->earnedChange = $this->earnedChange;
        $history->buyedChange = $this->buyedChange;
        $history->bill_id = $this->id;

//        $history->source = $this->source;
        $history->source = $source;

        $history->transaction_id = $this->transaction_id;
        $history->save();
//        unset($this->source);
//        parent::save($options);
    }
}
