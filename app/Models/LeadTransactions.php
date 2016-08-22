<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadTransactionInfo extends Model {
    public $timestamps = false;
    protected $table="lead_transaction_info";
    protected $fillable = [
        'number','lead_id'
    ];
    public function parts(){
        return $this->hasMany('App\Models\CreditHistory','transaction_id','id');
    }
}
