<?php

namespace App\Http\Controllers\Agent;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Helper\Statistics;
use Illuminate\View\View;

class StatisticController extends Controller
{

    /**
     * Страница со статистикой агента
     *
     */
    public function agentStatistic()
    {

        // выбираем агента со сферами
        $agent = Agent::with('spheres')->find(Sentinel::getUser()->id);


        // переменная со статистикой по сферам
        $statistics = collect();

        // получаем статистику агента по каждой сфере
        $agent->spheres->each(function( $sphere ) use ( &$statistics, $agent ){

            $statistic = Statistics::openLeads( $agent->id, $sphere['id'] );

            if( $sphere['openLead'] >= $sphere['minLead'] ){

                $statistics->push( collect([
                    'status' => true,
                    'sphereId' => $sphere['id'],
                    'sphereName' => $sphere['name'],
                    'data' => $statistic
                ]) );

            }else{

                $statistics->push( collect([
                    'status' => false,
                    'sphereId' => $sphere['id'],
                    'sphereName' => $sphere['name'],
                    'minLead' => $sphere['minLead'],
                    'openLead' => $sphere['openLead'],
                    'data' => $statistic
                ]) );
            }
        });

        return view('agent.statistic.index', [
            'agent' => $agent,
            'statistics' => $statistics,
        ]);
    }


    /**
     * Данные по статистике агента
     *
     * @param Request $request
     *
     * @return View
     */
    public function agentStatisticData(Request $request)
    {


        $user_id = $request->agent_id;
        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;


        // выбираем агента со сферами
        $agent = Agent::with('spheres')->find($user_id);

        // переменная со статистикой по сферам
        $statistics = collect();

        // получаем статистику агента по каждой сфере
        $agent->spheres->each(function( $sphere ) use ( &$statistics, $user_id, $timeFrom, $timeTo ){

            // получаем полную статистику агента по сфере
            $statistic = Statistics::openLeads( $user_id, $sphere['id'], $timeFrom, $timeTo );

            /**
             * Проверяем достаточное ли количество открытых лидов для ститистики
             *
             */
            if( $sphere['openLead'] >= $sphere['minLead'] ){
                // если открытых лидов достаточно для статистики

                // добавляем данные в коллекцию по статистики
                $statistics->push( collect([
                    'status' => true,
                    'sphereId' => $sphere['id'],
                    'sphereName' => $sphere['name'],
                    'data' => $statistic
                ]) );

            }else{
                // если открытых лидов меньше чем нужно для статистики

                // добавляем данные в коллекцию по статистики
                $statistics->push( collect([
                    'status' => false,
                    'sphereId' => $sphere['id'],
                    'sphereName' => $sphere['name'],
                    'minLead' => $sphere['minLead'],
                    'openLead' => $sphere['openLead'],
                    'data' => $statistic
                ]) );
            }
        });

        return $statistics;


    }
}
