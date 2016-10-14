<?php

namespace App\Http\Controllers\AccountManager;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentGroups;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Validator;

class AgentGroupsController extends Controller  {

    /**
     * Группы агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function groups()
    {
        $groups = AgentGroups::all();

        return view('account_manager.agent_groups.index', [ 'groups' => $groups ]);
    }

    /**
     * Вызов форми для создания группы агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('account_manager.agent_groups.create');
    }

    /**
     * Сохранение группы агентов
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json($validator);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        $group = new AgentGroups();
        $group->name = $request->input('name');
        $group->save();

        if($request->ajax()){
            return response()->json('reload');
        } else {
            return redirect()->route('accountManager.agentGroups.list');
        }
    }

    /**
     * Удаление группы агентов
     *
     * @param $group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function delete($group_id, Request $request)
    {
        AgentGroups::where('id', '=', $group_id)->first()->delete();

        if($request->ajax()){
            return response()->json('groupDeleted');
        } else {
            return redirect()->route('accountManager.agentGroups.list');
        }
    }

    /**
     * Просмотр агентов в группе
     *
     * @param $group_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agents($group_id)
    {
        $group = AgentGroups::find($group_id);
        $agents = $group->agents()->get();

        return view('account_manager.agent_groups.agentList', [ 'group' => $group, 'agents' => $agents ]);
    }

    /**
     * Страница добавления агентов в группу
     *
     * @param $group_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addAgents($group_id) {
        $group = AgentGroups::find($group_id);

        $agents = Agent::listAll()->whereNotIn('id', $group->agents()->get()->lists('id'))->get();

        return view('account_manager.agent_groups.addAgents', [ 'group' => $group, 'agents' => $agents ]);
    }

    /**
     * Добавление агента в группу
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function putAgent(Request $request) {
        $agent_id = $request->input('agent_id');
        $group_id = $request->input('group_id');

        $group = AgentGroups::find($group_id);

        if($group->id) {
            $group->agents()->attach($agent_id);

            if($request->ajax()){
                return response()->json('agentAdded');
            } else {
                return redirect()->route('accountManager.agentGroups.addAgents');
            }
        }
        return response()->json();
    }

    /**
     * Удаление агента из группы
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteAgent(Request $request)
    {
        $agent_id = $request->input('agent_id');
        $group_id = $request->input('group_id');

        $group = AgentGroups::find($group_id);

        if($group->id) {
            $group->agents()->detach($agent_id);

            if($request->ajax()){
                return response()->json('agentDeleted');
            } else {
                return redirect()->route('accountManager.agentGroups.agents');
            }
        }
        return response()->json();
    }
}