<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credits extends Model {

    protected $table="credits";
    public $descrHistory = false;

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

    //сначала вычитаем стоимость из buyed. Если buyed закончилось, а стоимость ещё нет, то остаток стоимости вычитаем из earned.
    public function setPaymentAttribute($value){
        if($this->attributes['buyed'] < $value) {
            $this->attributes['earned'] -= ($value - $this->attributes['buyed']);
            $this->attributes['buyed'] = 0;
        } else {
            $this->attributes['buyed'] -= $value;
        }
    }

    public function save(array $options = []){
        $history = new CreditHistory();
        $history->buyed = ($this->source < 0)?$history->buyed*-1:$history->buyed;
        $history->earned = ($this->source < 0)?$history->earned*-1:$history->earned;
        $history->agent_id = $this->agent_id;
        if ($this->descrHistory)
            $history->descr = $this->descrHistory;
        $history->save();
        parent::save($options);
    }
}
