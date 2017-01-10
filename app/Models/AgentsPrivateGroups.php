<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AgentsPrivateGroups extends Model
{

    /**
     * Таблица модели
     *
     * @var string
     */
    protected $table="agents_private_groups";


    /**
     * Связь с таблицей пользователей
     *
     * @return Builder
     */
    public function memberData(){
        return $this->hasOne('App\Models\User', 'id', 'agent_member_id')
            ->select(
                'id',
                'email',
                'permissions',
                'last_login',
                'first_name',
                'last_name',
                'created_at',
                'banned_at',
                'updated_at'
            );
    }


    public function openLead(){

        $openLead = $this->hasMany('App\Models\OpenLeads', 'agent_id', 'agent_member_id');

        return $openLead;

    }
}
