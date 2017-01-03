<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Lead;

class LeadTransformer extends TransformerAbstract
{
    public function transform(Lead $lead)
    {
        $customer = $lead->phone()->first();

        $depositor = $lead->depositor()->first();

        return [
            'name' => $lead->name,
            'phone' => $customer->phone,
            'email' => $lead->email,
            'status' => view('admin.lead.datatables.status', [
                'status' => $lead->statusName(),
                'auctionStatus' => $lead->auctionStatusName(),
                'paymentStatus' => $lead->paymentStatusName()
            ])->render(),
            'opened' => $lead->opened,
            //'depositor' => $depositor->first_name.' '.$depositor->last_name,
            'depositor' => $depositor->email,
            'expiry_time' => $lead->expiry_time
        ];
    }
}