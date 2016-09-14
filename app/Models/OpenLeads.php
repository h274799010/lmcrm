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
    public static function makeOrIncrement( $lead, $agent_id, $mask_id, $comment=NULL, $count=1 )
    {

        // интервал гарантированный агентом на работу с лидом, который он октрыл
        // после этого интервала агент не сможет ставить bad_lead
        $interval = $lead->sphere->lead_bad_status_interval;

        // время (дата) после которого bad_lead будет блокирован
        $expiration_time = date('Y-m-d H:i:s', time()+$interval);

        // проверяем есть ли такой лид
        $openLead = OpenLeads::
              where( 'lead_id', $lead->id )
            ->where( 'agent_id', $agent_id )
            ->where( 'mask_id', $mask_id )
            ->first();

        if( $openLead ){
            // если ЕСТЬ открытый лид с такими параметрами
            // обновляем счетчики и время гарантированное на bad_lead

            // инкрементим счетчик у открытого лида
            $openLead->count++;
            // добавляем время на bad_lead
            $openLead->expiration_time = $expiration_time;
            $openLead->save();

            // инкрементим opened у лида, (количество открытия лида)
            $lead->opened++;
            // время истечения открытых лидов
            $lead->open_lead_expired  = $expiration_time;
            $lead->save();

        }else{
            // если НЕТ открытого лида с такими параметрами
            // создаем его

            $openLead = new OpenLeads();
            $openLead->lead_id = $lead->id;                 // id лида
            $openLead->agent_id = $agent_id;                // id агента, который его открыл
            $openLead->mask_id = $mask_id;                  // комментарий (не обазательно)
            $openLead->expiration_time = $expiration_time;  // время истечения лида
            $openLead->comment = $comment;                  // комментарий (не обазательно)
            $openLead->count = $count;                      // количество открытий (при первом открытии = "1")

            $openLead->save();

            // инкрементим opened у лида, (количество открытия лида)
            $lead->opened++;
            // время истечения открытых лидов
            $lead->open_lead_expired  = $expiration_time;
            $lead->save();
        }

        return $openLead;
    }

}
