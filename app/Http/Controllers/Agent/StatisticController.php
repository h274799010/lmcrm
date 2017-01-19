<?php

namespace App\Http\Controllers\Agent;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Agent;

class StatisticController extends Controller
{

    /**
     * Страница со статистикой агента
     *
     * @param $agent_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function agentStatistic()
    {
        $agent = Agent::find(Sentinel::getUser()->id);

        return view('agent.statistic.index', [
            'agent' => $agent
        ]);
    }

    public function agentStatisticData(Request $request)
    {
        $agent = Agent::find(Sentinel::getUser()->id);
        $spheres = $agent->openLeadsStatistic2($request->input('period'));

        return view('agent.statistic.partials.agentStatistic', [
            'spheres' => $spheres
        ]);
    }
}
