<?php

namespace App\Http\Controllers\Agent;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use Statistic;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Salesman;

class StatisticController extends Controller
{

    /**
     * Страница со статистикой агента
     *
     */

    public function agentStatistic()
    {

        // выбираем агента
        $userId = Sentinel::getUser()->id;


        // выбираем пользователя с ролями
        $userSystemData = User::with('roles')->find( $userId );

        // определяем роль, agent или salesman
        $userRole = false;
        // перебираем все роли пользователя и выбираем нужную роль
        $userSystemData->roles->each(function( $role ) use ( &$userRole ){
            // выбираем нужную роль
            if( $role->slug == 'agent' ){
                // если роль пользователя "agent"

                // выставляем роль пользователя как 'agent'
                $userRole = 'agent';

            }elseif( $role->slug == 'salesman' ){
                // если роль пользователя "salesman"

                // выставляем роль пользователя как 'salesman'
                $userRole = 'salesman';
            }
        });

        // выбор пользователя в зависимости от его роли
        if( $userRole == 'agent' ){
            // если агент
            // выбираем модель агента
            $user = Agent::with('spheres')->find( $userId );

        }elseif( $userRole == 'salesman' ){
            // если салесман
            // выбираем модель salesman
            $user = Salesman::with('spheres')->find( $userId );

        }else{
            // если нет совпадений по роли
            // выходим c ошибкой
            abort( 403, 'Wrong user slug' );
        }


        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $user->spheres->each(function( $sphere ) use( &$spheres ){
            // добавляем данные по сфере в $spheres
            $spheres->push(
                collect(
                    [
                        'id' => $sphere->id,
                        'name' => $sphere->name,
                        'openLead' => $sphere->openLead,
                        'minLead' => $sphere->minLead,
                    ]
                )
            );
        });

        // проверка на количество сфер
        if( $spheres->count() == 0){
            // если у агента нет сфер

            // указываем что статистики нет
            $statistic = false;

        }else {
            // если у агента есть сферы

            // выбираем первую сферу из списка
            $sphere = $spheres->first();

            $statistic = Statistic::agentBySphere( $user['id'], $sphere['id'], true );

        }

        return view('agent.statistic.index', [
            'user' => $user,
            'spheres' => $spheres,
            'statistic' => $statistic,
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

//        dd( $request->all() );

        // данные из реквеста
        $user_id = (int)$request->agent_id;
        $sphere_id = (int)$request->sphere_id;
        $timeFrom = $request->timeFrom;
        $timeTo =$request->timeTo;

        // если id пользователя равен нулю - выходим
        if( !$user_id ){ abort(403, 'Wrong user id'); }

        // если id сферы равен нулю - выходим
        if( !$sphere_id ){ abort(403, 'Wrong sphere id'); }

        // выбираем пользователя с ролями
        $userSystemData = User::with('roles')->find( $user_id );

        // определяем роль, agent или salesman
        $userRole = false;
        // перебираем все роли пользователя и выбираем нужную роль
        $userSystemData->roles->each(function( $role ) use ( &$userRole ){
            // выбираем нужную роль
            if( $role->slug == 'agent' ){
                // если роль пользователя "agent"

                // выставляем роль пользователя как 'agent'
                $userRole = 'agent';

            }elseif( $role->slug == 'salesman' ){
                // если роль пользователя "salesman"

                // выставляем роль пользователя как 'salesman'
                $userRole = 'salesman';
            }
        });

        // выбор пользователя в зависимости от его роли
        if( $userRole == 'agent' ){
            // если агент
            // выбираем модель агента
            $user = Agent::with('spheres')->find( $user_id );

        }elseif( $userRole == 'salesman' ){
            // если салесман
            // выбираем модель salesman
            $user = Salesman::with('spheres')->find( $user_id );

        }else{
            // если нет совпадений по роли
            // выходим c ошибкой
            abort( 403, 'Wrong user slug' );
        }

        // переменная с текущей сферой
        $currentSphere = false;

        // переменная со сферами
        $spheres = collect();
        // перебираем все сферы пользователя и выбираем данные по сфере в отдельную коллекцию
        $user->spheres->each(function( $sphere ) use( &$spheres, &$currentSphere, $sphere_id ){

            // ищем среди сфер заданную сферу
            if( $sphere->id == $sphere_id ){
                // если id сферы такое же как id заданной сферы

                // добавляем в текущую сферу коллекцию
                $currentSphere = collect(
                    [
                        'id' => $sphere->id,
                        'name' => $sphere->name,
                        'openLead' => $sphere->openLead,
                        'minLead' => $sphere->minLead,
                    ]
                );
            }

            // добавляем данные по сфере в $spheres
            $spheres->push(
                collect(
                    [
                        'id' => $sphere->id,
                        'name' => $sphere->name,
                        'openLead' => $sphere->openLead,
                        'minLead' => $sphere->minLead,
                    ]
                )
            );
        });

        // если заданной сферы нет в списке пользователя - возвращаем на фронтенд что сфера отсутствует
        if( !$currentSphere ){ return 'false'; }

        // выбираем статистику
        $statistic = Statistic::agentBySphere( $user['id'], $currentSphere['id'], true, $timeFrom, $timeTo );

        return response()->json([ 'spheres'=>$spheres, 'statistic'=>$statistic ]);

//
//
//        // выбираем агента со сферами
//        $agent = Agent::with('spheres')->find($user_id);
//
//        // переменная со статистикой по сферам
//        $statistics = collect();
//
//        // получаем статистику агента по каждой сфере
//        $agent->spheres->each(function( $sphere ) use ( &$statistics, $user_id, $timeFrom, $timeTo ){
//
//            // получаем полную статистику агента по сфере
//            $statistic = Statistics::openLeads( $user_id, $sphere['id'], $timeFrom, $timeTo );
//
//            /**
//             * Проверяем достаточное ли количество открытых лидов для ститистики
//             *
//             */
//            if( $sphere['openLead'] >= $sphere['minLead'] ){
//                // если открытых лидов достаточно для статистики
//
//                // добавляем данные в коллекцию по статистики
//                $statistics->push( collect([
//                    'status' => true,
//                    'sphereId' => $sphere['id'],
//                    'sphereName' => $sphere['name'],
//                    'data' => $statistic
//                ]) );
//
//            }else{
//                // если открытых лидов меньше чем нужно для статистики
//
//                // добавляем данные в коллекцию по статистики
//                $statistics->push( collect([
//                    'status' => false,
//                    'sphereId' => $sphere['id'],
//                    'sphereName' => $sphere['name'],
//                    'minLead' => $sphere['minLead'],
//                    'openLead' => $sphere['openLead'],
//                    'data' => $statistic
//                ]) );
//            }
//        });
//
//        return $statistics;


    }
}
