<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model {

    protected $table="wallet";


    // отключаем метки времени
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','buyed','earned','wasted'
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
    public function details()
    {
         return $this->hasMany('App\Models\TransactionsDetails', 'wallet_id', 'id')->with('transaction')->orderBy('id', 'desc');

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
