<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credits extends Model {

    protected $table="credits";
    public $descrHistory = false;
    public $buyedChange = 0;
    public $earnedChange = 0;
    public $lead_id = 0;

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
            $change = ($value - $this->attributes['buyed']);
            $this->earnedChange = $change;
            $this->attributes['earned'] -= $change;
            $this->attributes['buyed'] = 0;
        } else {
            $this->attributes['buyed'] -= $value;
            $this->buyedChange = $value;
        }
    }

    public function save(array $options = []){
        $history = new CreditHistory();
        $history->buyed = $this->buyed;
        $history->earned = $this->earned;
        $history->earnedChange = $this->earnedChange;
        $history->buyedChange = $this->buyedChange;
        $history->agent_id = $this->agent_id;
        $history->lead_id = $this->lead_id;
        $history->source = $this->source;
        if ($this->descrHistory)
            $history->descr = $this->descrHistory;
        $history->save();
        unset($this->source);
        parent::save($options);
    }
}
