<?php

namespace App\Console\Commands;


use App\Facades\Notice;
use App\Models\AgentBitmask;
use App\Models\Auction;
use App\Models\Lead;
use App\Models\LeadBitmask;
use App\Models\UserMasks;
use Illuminate\Console\Command;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

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
            ->filterAgentsByMask( $leadBitmaskData, $lead->agent_id, $lead->sphere_id, $lead->id, $lead->current_range );

        if(isset($lead->id) && $lead->specification == Lead::SPECIFICATION_FOR_DEALMAKER) {
            $role = Sentinel::findRoleBySlug('leadbayer');
            $excludedLeadbauers = $role->users()->lists('id')->toArray();
            $agents = $agents->whereNotIn('user_id', $excludedLeadbauers);
        }

        $agents = $agents->orderBy('lead_price', 'desc')
            ->get();

        // если агенты есть - добавляем лид им на аукцион и оповещаем
        if( $agents->count() ){

            // Если маска отключена и лид подходит по другой - удаляем ее
            // если лид подходит только по выключенной маске - пропускаем
            $tmp = array();
            foreach ($agents as $key => $mask) {
                if( !isset($tmp[ $mask->user_id ]) ) {
                    $tmp[ $mask->user_id ] = array();
                }
                $mask->key = $key;
                $tmp[ $mask->user_id ][] = $mask;
            }
            foreach ($tmp as $user_id => $masks) {
                if(count($masks) > 1) {
                    // Отключенные маски
                    $off = array();
                    // Включенные маски
                    $on = array();
                    foreach ($masks as $mask) {
                        $maskName = UserMasks::where('user_id', '=', $mask->user_id)
                            ->where('sphere_id', '=', $lead->sphere_id)
                            ->where('mask_id', '=', $mask->id)
                            ->first();
                        if($maskName->active == 1) {
                            $on[] = $mask->key;
                        }
                        else {
                            $off[] = $mask->key;
                        }
                    }
                    // Если есть хотябы одна включенная маска - удаляем остальные
                    if(count($on) > 0) {
                        foreach ($off as $key) {
                            unset($agents[$key]);
                        }
                    }
                }
                else {
                    continue;
                }
            }

            // Ищем самую дорогую маску
            $tmp = array();
            foreach ($agents as $key => $mask) {
                if(!isset($tmp[$mask->user_id])) {
                    $tmp[$mask->user_id] = ['key'=>$key,'price'=>$mask->lead_price];
                } else {
                    if($mask->lead_price > $tmp[$mask->user_id]['price']) {
                        unset($agents[$tmp[$mask->user_id]['key']]);
                        $tmp[$mask->user_id] = ['key'=>$key,'price'=>$mask->lead_price];
                    } else {
                        unset($agents[$key]);
                    }
                }
            }

            // добавляем лид на аукцион всем подходящим агентам
            Auction::addFromBitmask( $agents, $lead->sphere_id,  $lead->id  );

            // подобрать название к этому уведомлению
            // рассылаем уведомления всем агентам которым подходит этот лид
            Notice::toMany( $this->sender_id, $agents, 'note');
        }
    }
}