<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenLeadsStatusDetails extends Model
{

    /**
     * Задаем таблицу
     *
     * @var string
     */
    protected $table="open_leads_status_details";


    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;


    public static function setStatus($open_lead_id, $user_id, $previous_status_id, $newStatus){

        $status = new OpenLeadsStatusDetails();

        $status->open_lead_id = $open_lead_id;
        $status->user_id = $user_id;
        $status->previous_status_id = $previous_status_id;
        $status->status_id = $newStatus;

        $status->save();
    }

    public function getStatisticAgentBySphereStatuses($agent_id)
    {
        //
    }

    public function getPerformanceAgent($agent_id)
    {
        //
    }

}
