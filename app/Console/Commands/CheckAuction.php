<?php

namespace App\Console\Commands;


use App\Facades\Notice;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\Lead;
use App\Models\LeadBitmask;
use App\Models\OperatorHistory;
use Illuminate\Console\Command;

class CheckAuction extends Command
{

    /**
     * Ищет лидов которые отправлены на аукцион, но еще не добавлены какому-то из агентов
     * когда находит такого лида - ищет подходящих агентов и добавляет лида им на аукцион
     */
    public static function handle()
    {
        $leads = Lead::where('status', '=', 3)->whereNotIn('id', Auction::all()->pluck('lead_id')->toArray())->get();

        if(count($leads) > 0) {
            foreach ($leads as $lead) {
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

                    $sender = OperatorHistory::where('lead_id', '=', $lead->id)->first();
                    if($sender) {
                        $senderId = $sender->operator_id;
                    } else {
                        $senderId = 0;
                    }

                    // подобрать название к этому уведомлению
                    // рассылаем уведомления всем агентам которым подходит этот лид
                    Notice::toMany( $senderId, $agents, 'note');
                }
            }
        }
    }
}