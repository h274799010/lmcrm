<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Lead;

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

    public function getCanSetBadAttribute(){
        if (!$this->bad && $this->lead->checked && $this->lead->pending_time > date('Y-m-d H:i:s'))
        {
            return true;
        }
        return false;
    }


    /**
     * Создает открытый лид для агента
     *
     *
     * @param  Lead  $lead
     * @param  integer  $agent_id
     * @param  integer  $mask_id
     * @param  string  $comment
     * @param  integer  $count
     *
     * @return OpenLeads
     */
    public static function make( $lead, $agent_id, $mask_id, $comment=NULL, $count=1 )
    {


        return "open";


        $openLead = new OpenLeads();
        $openLead->lead_id = $lead->id;   // id лида
        $openLead->agent_id = $agent_id;  // id агента, который его открыл
        $openLead->mask_id = $mask_id;    // комментарий (не обазательно)

        // todo добавить время окончания лида
        $openLead->expiration_time = '';


        $openLead->comment = $comment;    // комментарий (не обазательно)
        $openLead->count = $count;        // количество открытий (при первом открытии = "1")

        $openLead->save();

        return $openLead;
    }

}
