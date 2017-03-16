<?php

namespace App\Transformers;

use App\Models\Agent;
use App\Models\Auction;
use App\Models\LeadBitmask;
use App\Models\OpenLeads;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use League\Fractal\TransformerAbstract;

class ObtainedLeadsTransformer extends TransformerAbstract
{
    protected $user_id;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function transform(Auction $auction)
    {
        //dd($auction->toArray());
        $agent = Sentinel::findById($this->user_id);

        $openLead = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', $agent->id )->first();

        if( $openLead ){
            // если открыт - блокируем возможность открытия
            $open = view('agent.lead.datatables.obtain_already_open')->render();
        }else {
            // если не открыт - отдаем ссылку на открытия
            $open = view('agent.lead.datatables.obtain_open', ['data' => $auction])->render();
        }

        // проверяем открыт ли этот лид у других агентов
        $openLead = OpenLeads::where( 'lead_id', $auction['lead']['id'] )->where( 'agent_id', '<>', $agent->id )->first();

        if( $openLead ){
            // если открыт - блокируем ссылку
            $openAll =  view('agent.lead.datatables.obtain_already_open')->render();
        }else {
            // если не открыт - отдаем ссылку на открытие всех лидов
            $openAll =  view('agent.lead.datatables.obtain_open_all', ['data' => $auction])->render();
        }

        //2016-11-08 15:16:35
        $fields = array(
            0 => view('agent.lead.datatables.obtain_count', [ 'opened'=>$auction['lead']['opened'] ])->render(), //count
            1 => $open, // open
            2 => $openAll, // openAll
            3 => isset($auction->sphere->name) ? $auction->sphere->name : 'Deleted',
            4 => isset($auction['maskName']->name) ? $auction['maskName']->name : 'Deleted', // mask
            5 => $auction['lead']['updated_at']->toDateTimeString(), // updated
            6 => $auction['lead']['name'], // name
            7 => ( $auction['lead']->obtainedBy($agent['id'])->count() ) ? $auction['lead']['phone']->phone : trans('site/lead.hidden'), // phone
            8 => ( $auction['lead']->obtainedBy($agent['id'])->count() ) ? $auction['lead']['email'] : trans('site/lead.hidden'), // e-mail
            9 => view('agent.lead.datatables.obtain_info', [ 'id'=>$auction['id'] ])->render()
        );

        $recalcFieldsKeys = false;
        if(!Sentinel::hasAccess(['agent.lead.openAll'])) {
            unset($fields[2]);
            $recalcFieldsKeys = true;
        }

        if($recalcFieldsKeys == true) {
            $newFields = array();
            foreach ($fields as $key => $field) {
                if($key <= 1) {
                    $newFields[$key] = $field;
                } else {
                    $newFields[$key - 1] = $field;
                }
            }
            $fields = $newFields;
        }

        return $fields;
    }
}