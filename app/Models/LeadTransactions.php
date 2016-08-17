<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadTransactions extends Model {
    public $timestamps = false;
    protected $table="lead_transactions";
    protected $fillable = [
        'number','lead_id'
    ];
    public function parts(){
        return $this->hasMany('App\Models\CreditHistory','transaction_id','id');
    }
}
