<?php

namespace App\Transformers\Admin;


use App\Models\Agent;
use App\Models\RequestPayment;
use League\Fractal\TransformerAbstract;

class CreditsReportTransformer extends TransformerAbstract
{
    public function transform(Agent $agent)
    {
        $spheres = $agent->onlySpheres()->get()->lists('name')->toArray();

        if(count($spheres)) {
            $agent->spheres = implode(', ', $spheres);
        } else {
            $agent->spheres = '-';
        }

        $accountManagers = $agent->accountManagers()->get()->lists('email')->toArray();

        if(count($accountManagers)) {
            $agent->accountManagers = implode(', ', $accountManagers);
        } else {
            $agent->accountManagers = '-';
        }

        $withdrew = RequestPayment::
        where('initiator_id', $agent->id)
            ->where('status', RequestPayment::STATUS_CONFIRMED)
            ->where('type', 2)
            ->sum('amount');

        $agent->withdrew = $withdrew ? $withdrew : 0;


        $replenishment = RequestPayment::
        where('initiator_id', $agent->id)
            ->where('status', RequestPayment::STATUS_CONFIRMED)
            ->where('type', 1)
            ->sum('amount');

        $agent->replenishment = $replenishment ? $replenishment : 0;

        return [
            0 => $agent->email,
            1 => $agent->spheres, // Spheres
            2 => $agent->accountManagers, // Account managers
            3 => $agent->replenishment, // Replenishment
            4 => $agent->withdrew, // Withdrawal
            5 => view('admin.transactionReport.datatables.all_actions', [
                'agent' => $agent
            ])->render(),
        ];
    }
}