<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{


    /**
     * Название таблицы
     *
     *
     * @var string
     */
    protected $table = "operator";

    /**
     * Связь с таблицей лидов
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lead()
    {
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }

    /**
     * Связь с таблицей пользователей
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function operator()
    {
        return $this->hasOne('App\Models\User', 'id', 'operator_id');
    }

    public function editedLeads()
    {
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }

}
