<?php
namespace App\Transformers;


use App\Models\Lead;
use League\Fractal\TransformerAbstract;

class DepositedLeadsTransformer extends TransformerAbstract
{
    public function transform(Lead $lead)
    {
        $statusName = $lead->statusName();

        return [
            0 => view('agent.lead.datatables.deposited_lead_status', [
                'statusName'=>  $statusName,
                'lead' => $lead
            ])->render(),
            1 => $lead->updated_at->toDateTimeString(),
            2 => $lead->sphere->name,
            3 => $lead->name,
            4 => $lead->phone->phone,
            5 => $lead->email
        ];
    }
}