<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use App\Helper\PayMaster\PayCalculation;

class Wallet extends Model {

    /**
     * Подключаем таблицу из БД
     *
     * @var string
     */
    protected $table="wallet";

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable =
    [
        'user_id','buyed','earned','wasted'
    ];

    public function agent(){
        return $this->belongsTo('App\Models\Agent', 'id', 'agent_id');
    }

    /**
     * Подсчет полного баланса по кошельку
     *
     */
    public function getBalanceAttribute(){

        $balance  = $this->attributes['buyed'];
        $balance += $this->attributes['earned'];
        $balance += $this->attributes['overdraft'];

        // отнимаем wasted
        $balance -= $this->attributes['wasted'];

        return $balance;
    }

    /**
     * Возвращает всю историю по кредиту
     *
     * @return Builder
     */
    public function details()
    {
         return $this
             ->hasMany('App\Models\TransactionsDetails', 'wallet_id', 'id')  // соединяемся с таблицей деталей
             ->with('transaction')                                           // вместе с данными по транзакциям
             ->orderBy('id', 'desc');                                        // в обратном порядке
    }


    /**
     * Проверка на возможность агента оплатить цену
     *
     *
     * @param  double  $price   // цена лида
     *
     * @return boolean
     */
    public function isPossible( $price )
    {

        $possibility = PayCalculation::possibilityPayment( $this );

        // сравниваем возможности кошелька агента с его прайсом
        if( $possibility >= $price ){
            // возможности превышают прайс
            return true;

        }else{
            // возможности агента ниже прайса
            return false;
        }
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
