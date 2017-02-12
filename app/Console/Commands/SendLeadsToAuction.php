<?php

namespace App\Console\Commands;


use App\Facades\Notice;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\Lead;
use App\Models\LeadBitmask;
use Illuminate\Console\Command;

class SendLeadsToAuction extends Command
{
    protected $lead_id;
    protected $sender_id;
    protected $type;

    public function __construct($lead_id, $sender_id, $type)
    {
        $this->lead_id = $lead_id;
        $this->sender_id = $sender_id;
        $this->type = $type;
    }

    public function handle()
    {
        if($this->type == 'toAuction') {
            $this->toAuction();
        }
    }

    private function toAuction()
    {
        $lead = Lead::find($this->lead_id);
        $lead->current_range = (int)$lead->current_range + 1;
        $lead->save();

        // выбираем маску лида по сфере
        $mask = new LeadBitmask( $lead->sphere_id );

        // выбираем маску лида
        $leadBitmaskData = $mask->findFbMask( $lead->id );

        // выбираем маски всех агентов
        $agentBitmasks = new AgentBitmask( $lead->sphere_id );

        // находим всех агентов которым подходит этот лид по фильтру
        // исключаем агента добавившего лид
        // + и его продавцов
        $agents = $agentBitmasks
            ->filterAgentsByMask( $leadBitmaskData, $lead->agent_id, $lead->sphere_id, $lead->id, $lead->current_range )
            ->orderBy('lead_price', 'desc')
            ->groupBy('user_id')
            ->get();

        // если агенты есть - добавляем лид им на аукцион и оповещаем
        if( $agents->count() ){
            // добавляем лид на аукцион всем подходящим агентам
            Auction::addFromBitmask( $agents, $lead->sphere_id,  $lead->id  );

            // подобрать название к этому уведомлению
            // рассылаем уведомления всем агентам которым подходит этот лид
            Notice::toMany( $this->sender_id, $agents, 'note');
        }
    }
}