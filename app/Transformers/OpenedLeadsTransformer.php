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
            0 => '',
            1 => view('agent.lead.datatables.openedLeads_status', ['openLead' => $openLead])->render(),
            2 => $openLead->lead->name,
            3 => $openLead->lead->phone->phone,
            4 => $openLead->lead->email,
            5 => view('agent.lead.datatables.openedLeads_mask', ['openLead' => $openLead])->render(),
            6 => view('agent.lead.datatables.openedLeads_actions')->render(),
        );
    }
}