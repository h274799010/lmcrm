<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Agent;
use App\Models\AgentsPrivateGroups;
use App\Transformers\Admin\AllPrivateGroupsTransformer;
use App\Transformers\Admin\ConfirmationPrivateGroupsTransformer;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use Yajra\Datatables\Facades\Datatables;

class AgentPrivateGroupsController extends AdminController
{
    public function __construct()
    {
        view()->share('type', 'agent');
    }

    /**
     * Список групп, в которых есть агенты для подтверждения
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ToConfirmationAgentInGroup()
    {
        return view('admin.agentsPrivateGroups.to_confirmations_groups');
    }

    /**
     * Список групп, в которых есть агенты для подтверждения
     * Datatable
     *
     * @return mixed
     */
    public function ToConfirmationAgentInGroupData()
    {
        $groups = AgentsPrivateGroups::where('status', '=', AgentsPrivateGroups::AGENT_WAITING_FOR_CONFIRMATION)
            ->groupBy('agent_owner_id')
            ->get()->lists('agent_owner_id')->toArray();

        $groups = Agent::whereIn('id', $groups);

        return Datatables::of( $groups )
            ->setTransformer(new ConfirmationPrivateGroupsTransformer())
            ->make();
    }

    /**
     * Список всех приватных групп агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allAgentsPrivateGroups()
    {
        return view('admin.agentsPrivateGroups.all_groups');
    }

    /**
     * Список всех приватных групп агентов
     * Datatable
     *
     * @return mixed
     */
    public function allAgentsPrivateGroupsData()
    {
        $groups = AgentsPrivateGroups::groupBy('agent_owner_id')
            ->get()->lists('agent_owner_id')->toArray();

        $groups = Agent::whereIn('id', $groups);

        return Datatables::of( $groups )
            ->setTransformer(new AllPrivateGroupsTransformer())
            ->make();
    }

    /**
     * Список агентов для подтверждения в группе
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail($id)
    {
        $agent = Agent::find($id);

        $agents = $agent->agentsPrivetGroups()
            ->where('agents_private_groups.status', '=', AgentsPrivateGroups::AGENT_WAITING_FOR_CONFIRMATION)
            ->with('phones')
            ->get();

        return view('admin.agentsPrivateGroups.to_confirmation_group_detail', [
            'agents' => $agents,
            'owner' => $agent
        ]);
    }

    public function allDetail($id)
    {
        $agent = Agent::find($id);

        $agents = $agent->agentsPrivetGroups()
            ->select('users.*', 'agents_private_groups.status')
            ->with('phones')
            ->get();

        $statuses = AgentsPrivateGroups::getStatusTypeName();

        return view('admin.agentsPrivateGroups.group_detail', [
            'agents' => $agents,
            'owner' => $agent,
            'statuses' => $statuses
        ]);
    }

    /**
     * Подтверждение агента в группу
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmAgent(Request $request)
    {
        $owner_id = (int)$request->input('owner');
        $agent_id = (int)$request->input('id');

        if( !$owner_id ) {
            abort(403, 'Wrong owner id');
        }

        if( !$agent_id ) {
            abort(403, 'Wrong agent id');
        }

        $group = AgentsPrivateGroups::where('agent_owner_id', '=', $owner_id)
            ->where('agent_member_id', '=', $agent_id)->first();

        if(isset($group->id)) {
            $group->status = AgentsPrivateGroups::AGENT_ACTIVE;
            $group->save();
        } else {
            return response()->json(false);
        }

        return response()->json(true);
    }

    /**
     * Не разрешаем добавления агента в группу
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectAgent(Request $request)
    {
        $owner_id = (int)$request->input('owner');
        $agent_id = (int)$request->input('id');

        if( !$owner_id ) {
            abort(403, 'Wrong owner id');
        }

        if( !$agent_id ) {
            abort(403, 'Wrong agent id');
        }

        $group = AgentsPrivateGroups::where('agent_owner_id', '=', $owner_id)
            ->where('agent_member_id', '=', $agent_id)->first();

        if(isset($group->id)) {
            $group->status = AgentsPrivateGroups::AGENT_REJECTED;
            $group->save();
        } else {
            return response()->json(false);
        }

        return response()->json(true);
    }
}
