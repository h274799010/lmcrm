<?php

namespace App\Http\Controllers\AccountManager;

use App\Http\Controllers\AccountManagerController;
use App\Models\Agent;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class AgentController extends AccountManagerController {

    /**
     * Список всех агентов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentList()
    {
        $agentRole = Sentinel::findRoleBySlug('agent');
        $agents = $agentRole->users()->get();

        return view('account_manager.agent.index', [ 'agents' => $agents ]);
    }

    /**
     * Подробная информация о агенте
     *
     * @param $agent_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentInfo($agent_id)
    {
        $agent = Sentinel::findById($agent_id);

        return view('account_manager.agent.info', [ 'agent' => $agent ]);
    }

}