<?php

namespace App\Providers;

use App\Helper\CreateLead;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\Lead;
use App\Models\LeadDepositorData;
use App\Models\OperatorHistory;
use App\Models\Operator;
use App\Models\Salesman;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class LeadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Agent::saved(function($agent) {
            $depositorData = LeadDepositorData::where('depositor_id', '=', $agent->id)->get();

            if(!empty($depositorData)) {
                // выбираем все роли пользователя
                $userRoles = $agent->roles()->get();

                // массив с ролями пользователя
                $userRolesArray = [];

                // перебираем объект с ролями и формируем массив
                $userRoles->each(function( $item ) use(&$userRolesArray){
                    // добавляем роль в массив
                    $userRolesArray[] = $item->slug;
                });

                // преобразовываем массив с ролями в строку
                $userRolesString = serialize($userRolesArray);

                $agentStatus = $agent->banned_at ? 'banned':'active';

                foreach ($depositorData as $depositor) {
                    $depositor->depositor_name = $agent->first_name;
                    $depositor->depositor_role = $userRolesString;
                    $depositor->depositor_status = $agentStatus;
                    $depositor->save();
                }
            }
        });

        AgentInfo::saved(function($agentInfo) {
            $agent = $agentInfo->agent()->first();
            $salesmansIds = $agent->salesmen()->get()->lists('id')->toArray();

            $salesmansIds[] = $agent->id;

            $depositorData = LeadDepositorData::whereIn('depositor_id', $salesmansIds)->get();

            if(!empty($depositorData)) {
                foreach ($depositorData as $depositor) {
                    $depositor->depositor_company = $agentInfo->company;
                    $depositor->save();
                }
            }
        });

        Salesman::saved(function($salesman) {
            $depositorData = LeadDepositorData::where('depositor_id', '=', $salesman->id)->get();

            if(!empty($depositorData)) {
                // выбираем все роли пользователя
                $userRoles = $salesman->roles()->get();

                // массив с ролями пользователя
                $userRolesArray = [];

                // перебираем объект с ролями и формируем массив
                $userRoles->each(function( $item ) use(&$userRolesArray){
                    // добавляем роль в массив
                    $userRolesArray[] = $item->slug;
                });

                // преобразовываем массив с ролями в строку
                $userRolesString = serialize($userRolesArray);

                $salesmanStatus = $salesman->banned_at ? 'banned':'active';

                foreach ($depositorData as $depositor) {
                    $depositor->depositor_name = $salesman->first_name;
                    $depositor->depositor_role = $userRolesString;
                    $depositor->depositor_status = $salesmanStatus;
                    $depositor->save();
                }
            }
        });

        Operator::saved(function($operator) {
            $depositorData = LeadDepositorData::where('depositor_id', '=', $operator->id)->get();

            if(!empty($depositorData)) {
                $operatorStatus = $operator->banned_at ? 'banned':'active';
                foreach ($depositorData as $depositor) {
                    $depositor->depositor_name = $operator->first_name;
                    $depositor->depositor_status = $operatorStatus;
                    $depositor->save();
                }
            }
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('lead', function () {
            return new Lead();
        });
        $this->app->bind('createlead', function () {
            return new CreateLead();
        });
    }
}
