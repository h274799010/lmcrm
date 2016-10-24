<?php

Route::group(['prefix' => 'accountManager','middleware' => ['auth'] ], function() {

    // Список всех агентов
    Route::get('agent/list', ['as' => 'accountManager.agent.list', 'uses' => 'AccountManager\AgentController@agentList']);

    // Подробная информация о агенте
    Route::get('agent/info/{agent_id}', [ 'as' => 'accountManager.agent.info', 'uses' => 'AccountManager\AgentController@agentInfo' ]);

    // Подробная информация о агенте
    Route::get('agent/edit/{agent_id}', [ 'as' => 'accountManager.agent.edit', 'uses' => 'AccountManager\AgentController@agentEdit' ]);

    // Подробная информация о агенте
    Route::post('agent/update', [ 'as' => 'accountManager.agent.update', 'uses' => 'AccountManager\AgentController@update' ]);

    // Группы агентов
    Route::get('agentGroups/list', [ 'as' => 'accountManager.agentGroups.list', 'uses' => 'AccountManager\AgentGroupsController@groups' ]);

    // Вызов форми для создания группы агентов
    Route::get('agentGroups/create', [ 'as' => 'accountManager.agentGroups.create', 'uses' => 'AccountManager\AgentGroupsController@create' ]);

    // Сохранение группы агентов
    Route::post('agentGroups/store', [ 'as' => 'accountManager.agentGroups.store', 'uses' => 'AccountManager\AgentGroupsController@store' ]);

    // Удаление группы агентов
    Route::post('agentGroups/delete/{group_id}', [ 'as' => 'accountManager.agentGroups.delete', 'uses' => 'AccountManager\AgentGroupsController@delete' ]);

    // Просмотр агентов в группе
    Route::get('agentGroups/agents/{group_id}', [ 'as' => 'accountManager.agentGroups.agents', 'uses' => 'AccountManager\AgentGroupsController@agents' ]);

    // Страница добавления агентов в группу
    Route::get('agentGroups/addAgents/{group_id}', [ 'as' => 'accountManager.agentGroups.addAgents', 'uses' => 'AccountManager\AgentGroupsController@addAgents' ]);

    // Добавление агента в группу
    Route::post('agentGroups/addAgent', [ 'as' => 'accountManager.agentGroups.addAgent', 'uses' => 'AccountManager\AgentGroupsController@putAgent' ]);

    // Удаление агента из группы
    Route::post('agentGroups/deleteAgent', [ 'as' => 'accountManager.agentGroups.deleteAgent', 'uses' => 'AccountManager\AgentGroupsController@deleteAgent' ]);


    // Список всех операторов
    Route::get('operators/list', ['as' => 'accountManager.operators.list', 'uses' => 'AccountManager\OperatorController@operatorsList']);

    // Группы операторов
    Route::get('operatorGroups/list', [ 'as' => 'accountManager.operatorGroups.list', 'uses' => 'AccountManager\OperatorGroupsController@groups' ]);

    // Вызов форми для создания группы операторов
    Route::get('operatorGroups/create', [ 'as' => 'accountManager.operatorGroups.create', 'uses' => 'AccountManager\OperatorGroupsController@create' ]);

    // Сохранение группы операторов
    Route::post('operatorGroups/store', [ 'as' => 'accountManager.operatorGroups.store', 'uses' => 'AccountManager\OperatorGroupsController@store' ]);

    // Удаление группы операторов
    Route::post('operatorGroups/delete/{group_id}', [ 'as' => 'accountManager.operatorGroups.delete', 'uses' => 'AccountManager\OperatorGroupsController@delete' ]);

    // Просмотр операторов в группе
    Route::get('operatorGroups/operators/{group_id}', [ 'as' => 'accountManager.operatorGroups.operators', 'uses' => 'AccountManager\OperatorGroupsController@operators' ]);

    // Страница добавления операторов в группу
    Route::get('operatorGroups/addOperators/{group_id}', [ 'as' => 'accountManager.operatorGroups.addOperators', 'uses' => 'AccountManager\OperatorGroupsController@addOperators' ]);

    // Добавление оператора в группу
    Route::post('operatorGroups/addOperator', [ 'as' => 'accountManager.operatorGroups.addOperator', 'uses' => 'AccountManager\OperatorGroupsController@putOperator' ]);

    // Удаление оператора из группы
    Route::post('operatorGroups/deleteOperator', [ 'as' => 'accountManager.operatorGroups.deleteOperator', 'uses' => 'AccountManager\OperatorGroupsController@deleteOperator' ]);

});