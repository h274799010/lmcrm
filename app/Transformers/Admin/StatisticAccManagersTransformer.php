<?php

namespace App\Transformers\Admin;


use App\Models\AccountManagersAgents;
use App\Models\User;
use League\Fractal\TransformerAbstract;

class StatisticAccManagersTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $agents = AccountManagersAgents::
        where( 'account_manager_id', $user->id )
            ->lists('agent_id');

        $agents = $agents->unique()->count();

        $spheres = '';
        if(isset($user->accManagerSpheres) && count($user->accManagerSpheres) > 0) {
            $flag = false;
            foreach ($user->accManagerSpheres as $sphere) {
                if($flag === true) {
                    $spheres .= ', ';
                }
                $spheres .= $sphere->sphere->name;
                $flag = true;
            }
        }

        return [
            0 => $user->email,
            1 => $spheres,
            2 => $agents,
            3 => $user->created_at->toDateTimeString(),
            4 => view('admin.statistic.datatables.accManagerControls', [
                'manager' => $user
            ])->render()
        ];
    }
}