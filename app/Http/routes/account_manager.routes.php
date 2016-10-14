<?php

Route::group(['prefix' => 'accountManager','middleware' => ['auth'] ], function() {

    // Список всех агентов
    Route::get('agent/list', ['as' => 'accountManager.agent.list', 'uses' => 'AccountManager\AgentController@agentList']);

    // Подробная информация о агенте
    Route::get('agent/info/{agent_id}', [ 'as' => 'accountManager.agent.info', 'uses' => 'AccountManager\AgentController@agentInfo' ]);

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

});