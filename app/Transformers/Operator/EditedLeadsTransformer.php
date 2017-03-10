<?php

namespace App\Transformers\Operator;

use App\Models\Lead;
use League\Fractal\TransformerAbstract;

class EditedLeadsTransformer extends TransformerAbstract
{
    public function transform(Lead $lead)
    {
        $statusName = $lead->statusName();

        if($lead->leadDepositorData->depositor_company == 'system_company_name') {
            $company = 'LM CRM';
        } else {
            $company = $lead->leadDepositorData->depositor_company;
        }

        return [
            0 => $lead->name,
            1 => $statusName,
            2 => $lead->updated_at->toDateTimeString(),
            3 => $lead->sphere->name,
            4 => $company,
            5 => view('sphere.lead.datatables.edit_lead_action', [
                'lead' => $lead
            ])->render()
        ];
    }
}