<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SphereStatusTransitions extends Model
{
    protected $table = 'sphere_status_transitions';

    public function previewStatus()
    {
        return $this->hasOne('\App\Models\SphereStatuses', 'id', 'previous_status_id');
    }

    public function status()
    {
        return $this->hasOne('\App\Models\SphereStatuses', 'id', 'status_id');
    }
}
