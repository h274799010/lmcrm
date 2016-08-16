<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualTransactions extends Model {
    public $timestamps = false;
    protected $table="manual_transactions";
    protected $fillable = [
        'initiator_id'
    ];
}
