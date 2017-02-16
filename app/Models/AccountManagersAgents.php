<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountManagersAgents extends Model
{

    protected $table="account_managers_agents";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_manager_id','agent_id'
    ];

    public function agents() {
        return $this->belongsToMany('\App\Models\User','account_managers_agents','account_manager_id','agent_id');
    }

    /**
     * Агенты аккаунт менеджера
     */
    public function agent(){
        return $this->hasOne('\App\Models\Agent', 'id', 'agent_id');
    }
}
