<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class OperatorGroups extends Model {

    /**
     * Получить список операторов группы
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function operators() {
        return $this->belongsToMany('App\Models\Agent', 'operators_groups', 'group_id', 'operator_id');
    }

}