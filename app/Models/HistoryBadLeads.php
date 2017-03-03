<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryBadLeads extends Model
{
    protected $table = 'history_bad_leads';

    /**
     * Сфера плохого лида
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sphere()
    {
        return $this->hasOne('App\Models\Sphere', 'id', 'sphere_id');
    }

    /**
     * Данные плохого лида
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lead()
    {
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }
}
