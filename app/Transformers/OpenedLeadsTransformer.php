<?php

namespace App\Transformers;


use App\Models\OpenLeads;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use League\Fractal\TransformerAbstract;

class OpenedLeadsTransformer extends TransformerAbstract
{
    public function transform(OpenLeads $openLead)
    {
        $user = Sentinel::findById( $openLead->agent_id );

        if($user->inRole('salesman')) {
            $buyer = $user->email;
        } else {
            $buyer = 'Your';
        }

        return array(
            "DT_RowAttr" => array(
                'lead_id' => $openLead->lead->id,
                'opened_Lead_Id' => $openLead->id
            ),
            0 => view('agent.lead.datatables.openedLeads_actions')->render(),
            1 => '',
            2 => view('agent.lead.datatables.openedLeads_status', ['openLead' => $openLead])->render(),
            3 => $buyer,
            4 => $openLead->lead->name,
            5 => $openLead->lead->phone->phone,
            6 => $openLead->lead->email,
            7 => view('agent.lead.datatables.openedLeads_mask', ['openLead' => $openLead])->render(),
        );
    }
}