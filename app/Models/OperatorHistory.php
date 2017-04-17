<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorHistory extends Model
{


    /**
     * Название таблицы
     *
     *
     * @var string
     */
    protected $table = "operator_history";

    /**
     * Связь с таблицей лидов (первый попавшийся, удалить)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lead()
    {
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }


    /**
     * Связь с таблицей лидов (все лиды оператора)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leads()
    {
        return $this->hasMany('App\Models\Lead', 'id', 'lead_id');
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

    public function spheres()
    {
        return $this->belongsToMany('App\Models\Sphere', 'operator_sphere', 'operator_id', 'sphere_id');
    }

    public function sphere(){
        return $this->spheres()->first();
    }
}
