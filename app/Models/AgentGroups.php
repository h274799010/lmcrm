<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AgentGroups extends Model {

    /**
     * Получить список агентов группы
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agents() {
        return $this->belongsToMany('App\Models\Agent', 'agents_groups', 'group_id', 'agent_id');
    }

}