<?php

namespace App\Transformers\Admin;


use App\Models\Agent;
use League\Fractal\TransformerAbstract;

class AgentProfitTransformer extends TransformerAbstract
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

        return [
            0 => $agent->email,
            1 => $agent->spheres, // Spheres
            2 => $agent->accountManagers, // Account managers
            3 => view('admin.profit.datatables.all_actions', [
                'agent' => $agent
            ])->render(),
        ];
    }
}