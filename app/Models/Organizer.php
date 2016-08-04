<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organizer extends Model {

    protected $table="organizer";

    public function openLead(){
        return $this->hasOne('App\Models\OpenLeads','id', 'open_lead_id');
    }
}
