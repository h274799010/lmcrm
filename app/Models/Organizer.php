<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Колонка type
 *
 * есть два типа полей:
 *    1 - комментарий
 *    2 - напоминание
 *
 */

class Organizer extends Model {

    protected $table="organizer";

    public $timestamps = false;
    protected $dates = ['time'];


    public function openLead(){
        return $this->hasOne('App\Models\OpenLeads','id', 'open_lead_id');
    }
}
