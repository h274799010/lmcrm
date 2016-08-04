<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenLeads extends Model {

    protected $table="open_leads";

    protected $fillable = [
        'comment',
    ];

    public function lead(){
        return $this->hasOne('App\Models\Lead','id', 'lead_id');
    }

    public function agent(){
        return $this->hasMany('App\Models\Agent','id', 'agent_id');
    }

    public function organizer(){
        return $this->hasMany('App\Models\Organizer','open_lead_id', 'id')->orderBy('time','desc');
    }

    public function statusInfo() {
        return $this->hasOne('App\Models\SphereStatuses','id','status')->orderBy('position');
    }
}
