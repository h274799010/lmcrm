<?php

namespace App\Transformers;


use App\Models\OpenLeads;
use League\Fractal\TransformerAbstract;

class OpenedLeadsTransformer extends TransformerAbstract
{
    public function transform(OpenLeads $openLead)
    {

        return array(
            "DT_RowAttr" => array(
                'lead_id' => $openLead->lead->id,
                'opened_Lead_Id' => $openLead->id
            ),
            0 => view('agent.lead.datatables.openedLeads_actions')->render(),
            1 => '',
            2 => view('agent.lead.datatables.openedLeads_status', ['openLead' => $openLead])->render(),
            3 => $openLead->lead->name,
            4 => $openLead->lead->phone->phone,
            5 => $openLead->lead->email,
            6 => view('agent.lead.datatables.openedLeads_mask', ['openLead' => $openLead])->render(),
        );
    }
}