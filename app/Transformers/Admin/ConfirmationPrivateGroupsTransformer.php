<?php

namespace App\Transformers\Admin;


use App\Models\Agent;
use League\Fractal\TransformerAbstract;

class ConfirmationPrivateGroupsTransformer extends TransformerAbstract
{
    public function transform(Agent $agent)
    {
        return [
            0 => $agent->first_name.' '.$agent->last_name,
            1 => $agent->email,
            2 => view('admin.agentsPrivateGroups.datatables.confirmation_groups_actions', [
                'id' => $agent->id
            ])->render(),
        ];
    }
}