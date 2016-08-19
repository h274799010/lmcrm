<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{

    /**
     * должен верунть всех лидов по их статусу
     *
     *
     * */
    public function getLead(){

        return $this->hasMany('App/Models/Lead', 'status', 'id');

    }
}
