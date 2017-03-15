<?php

namespace App\Transformers\Admin;


use App\Models\Agent;
use League\Fractal\TransformerAbstract;

class AllPrivateGroupsTransformer extends TransformerAbstract
{
    public function transform(Agent $agent)
    {
        return [
            0 => $agent->first_name.' '.$agent->last_name,
            1 => $agent->email,
            2 => view('admin.agentsPrivateGroups.datatables.all_groups_action', [
                'id' => $agent->id
            ])->render(),
        ];
    }
}