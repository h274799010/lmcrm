<?php

Route::group(['prefix' => 'accountManager','middleware' => ['auth'] ], function() {

    /*
     * Agents routes
     */
    Route::get('agent', ['as' => 'accountManager.agent.index', 'uses' => 'AccountManager\AgentController@index']);
    Route::get('agent/data', ['as' => 'accountManager.agent.data', 'uses' => 'AccountManager\AgentController@data']);
    Route::get('agent/create',['as'=>'accountManager.agent.create', 'uses' => 'AccountManager\AgentController@create']);
    Route::post('agent/store',['as'=>'accountManager.agent.store', 'uses' => 'AccountManager\AgentController@store']);
    Route::post('agent/revenue',['as'=>'accountManager.agent.revenue', 'uses' => 'AccountManager\AgentController@revenueUpdate']);
    Route::post('agent/rank',['as'=>'accountManager.agent.rank', 'uses' => 'AccountManager\AgentController@rankUpdate']);
    Route::get('agent/{id}/edit',['as'=>'accountManager.agent.edit', 'uses' => 'AccountManager\AgentController@edit']);
    Route::match(['put','post'],'agent/{id}/update',['as'=>'accountManager.agent.update', 'uses' => 'AccountManager\AgentController@update']);
    Route::get('agent/{id}/destroy', ['as'=>'accountManager.agent.delete', 'uses' => 'AccountManager\AgentController@destroy']);

    Route::post('agent/unblockData', ['as'=>'accountManager.agent.unblockData', 'uses' => 'AccountManager\AgentController@unbanData']);
    Route::post('agent/unblock', ['as'=>'accountManager.agent.unblock', 'uses' => 'AccountManager\AgentController@unban']);
    Route::post('agent/block',['as'=>'accountManager.agent.block', 'uses' => 'AccountManager\AgentController@ban']);

    Route::get('newAgents', ['as'=>'accountManager.agent.newAgents', 'uses' => 'AccountManager\AgentController@newAgents']);
    Route::get('agent/activated/{id}', ['as'=>'accountManager.agent.activatedPage', 'uses' => 'AccountManager\AgentController@agentActivatedPage']);
    Route::match(['put','post'],'agent/{id}/activate', ['as'=>'accountManager.agent.activate', 'uses' => 'AccountManager\AgentController@agentActivate']);

    /*
     * Operators routes
     */
    Route::get('operator', ['as' => 'accountManager.operator.index', 'uses' => 'AccountManager\OperatorController@index']);
    Route::get('operator/data', ['as' => 'accountManager.operator.data', 'uses' => 'AccountManager\OperatorController@data']);
    Route::get('operator/create',['as'=>'accountManager.operator.create', 'uses' => 'AccountManager\OperatorController@create']);
    Route::post('operator/store',['as'=>'accountManager.operator.store', 'uses' => 'AccountManager\OperatorController@store']);
    Route::get('operator/{id}/edit',['as'=>'accountManager.operator.edit', 'uses' => 'AccountManager\OperatorController@edit']);
    Route::match(['put','post'],'operator/{id}/update',['as'=>'accountManager.operator.update', 'uses' => 'AccountManager\OperatorController@update']);
    Route::get('operator/{id}/destroy', ['as'=>'accountManager.operator.delete', 'uses' => 'AccountManager\OperatorController@destroy']);

    /*
     * Spheres routes
     */
    Route::get('sphere/filters/reprice', ['as' => 'accountManager.sphere.reprice', 'uses' => 'AccountManager\SphereController@filtration']);
    Route::get('sphere/filters/maskAll', ['as' => 'accountManager.sphere.repriceAll', 'uses' => 'AccountManager\SphereController@filtrationAll']);
    Route::get('sphere/{sphere}/filters/reprice/{id}/edit/{mask_id}', ['as' => 'accountManager.sphere.reprice.edit', 'uses' => 'AccountManager\SphereController@filtrationEdit']);
    Route::match(['put','post'],'sphere/{sphere}/filters/reprice/{id}', ['as' => 'accountManager.sphere.reprice.update', 'uses' => 'AccountManager\SphereController@filtrationUpdate']);

    Route::get('lead/index', ['as' => 'accountManager.lead.index', 'uses' => 'AccountManager\LeadController@index']);
    Route::get('lead/data', ['as' => 'accountManager.lead.data', 'uses' => 'AccountManager\LeadController@data']);
    Route::post('lead/getFilter', ['as' => 'accountManager.lead.getFilter', 'uses' => 'AccountManager\LeadController@getFilter']);

    // Статистика
    Route::get('statistic/agents', ['as' => 'accountManager.statistic.agents', 'uses' => 'AccountManager\StatisticController@agentsList']);
    Route::get('statistic/agentsData', ['as' => 'accountManager.statistic.agentsData', 'uses' => 'AccountManager\StatisticController@agentsData']);
    Route::get('statistic/agent/{id}', ['as' => 'accountManager.statistic.agent', 'uses' => 'AccountManager\StatisticController@agentStatistic']);
    Route::post('statistic/agentData/', ['as' => 'accountManager.statistic.agentData', 'uses' => 'AccountManager\StatisticController@agentStatisticData']);
    Route::post('statistic/getFilterAgent',['as'=>'accountManager.statistic.getFilterAgent', 'AccountManager' => 'Admin\StatisticController@getFilterAgent']);

    // Сохранение телефонов агента
    Route::post('agent/phonesUpdate',['as'=>'accountManager.agent.phonesUpdate', 'uses' => 'AccountManager\AgentController@phonesUpdate']);
    Route::post('agent/phonesDelete',['as'=>'accountManager.agent.phonesDelete', 'uses' => 'AccountManager\AgentController@phonesDelete']);
});