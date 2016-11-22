<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountManagersOperators extends Model
{
    protected $table="account_managers_operators";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_manager_id','operator_id'
    ];

    public function operators() {
        return $this->belongsToMany('\App\Models\User','account_managers_operators','account_manager_id','operator_id');
    }
}
