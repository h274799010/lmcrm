<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsLeadInfo extends Model {

    protected $table="transactions_lead_info";

    public $timestamps = false;

    protected $fillable = [
        'number','lead_id'
    ];

    public function parts(){
        return $this->hasMany('App\Models\TransactionsDetails','transaction_id','id');
    }

}
