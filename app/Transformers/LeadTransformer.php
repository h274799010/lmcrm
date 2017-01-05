<?php

namespace App\Transformers;

use App\Models\OpenLeads;
use League\Fractal\TransformerAbstract;
use App\Models\Lead;

class LeadTransformer extends TransformerAbstract
{
    public function transform(OpenLeads $openLead)
    {
        $lead = $openLead->lead()->first();
        $statusInfo = $openLead->statusInfo()->first();

        if($statusInfo) {
            $status = $statusInfo->stepname;
        } else {
            $status = '-';
        }

        if($openLead->state == 1) {
            $status = 'Bad';
        } elseif ($openLead->state == 2) {
            $status = 'Close deal';
        }

        $customer = $lead->phone()->first();

        $depositor = $lead->depositor()->first();

        return [
            'name' => $lead->name,
            'phone' => $customer->phone,
            'email' => $lead->email,
            'status' => $status,
            'opened' => $lead->opened,
            //'depositor' => $depositor->first_name.' '.$depositor->last_name,
            'depositor' => $depositor->email,
            'expiry_time' => $lead->expiry_time
        ];
    }
}