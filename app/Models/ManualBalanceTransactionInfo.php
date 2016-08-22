<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualBalanceTransactionInfo extends Model {
    public $timestamps = false;
    protected $table="manual_balance_transaction_info";
    protected $fillable = [
        'initiator_id'
    ];
}
