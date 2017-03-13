<?php

/***************    Admin routes  **********************************/
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin'] ], function() {
# Admin Dashboard
    Route::get('/', ['as' => 'admin.index', 'uses' => 'Admin\DashboardController@index']);


# System

    // страница редактирования данных кошелька системы
    Route::get('systemWallet',['as'=>'admin.systemWallet', 'uses' => 'Admin\TransactionController@systemWallet']);

    // страница редактирования данных кошелька системы
    Route::get('allTransactions',['as'=>'admin.allTransactions', 'uses' => 'Admin\TransactionController@allTransactions']);

    // страница редактирования данных кошелька системы
    Route::get('allLeadsInfo',['as'=>'admin.allLeadsInfo', 'uses' => 'Admin\TransactionController@allLeadsInfo']);
    Route::get('allLeadsInfoData',['as'=>'admin.allLeadsInfoData', 'uses' => 'Admin\TransactionController@allLeadsInfoData']);







    // изменение состояния счета системы
    Route::post('manual/Wallet/{user_id}/Change',['as'=>'manual.wallet.change', 'uses' => 'Admin\TransactionController@ManualWalletChange']);

    // страница подробных финансовых данных о лиде
    Route::get('system/lead/{lead_id}',['as'=>'admin.system.lead', 'uses' => 'Admin\DashboardController@leadInfo']);


# Users
    Route::get('/user', ['as' => 'admin.user.index', 'uses' => 'Admin\UserController@index']);
    Route::get('/credit', ['as' => 'admin.credit.index', 'uses' => 'Admin\CreditController@index']);
    //Route::resource('/','Admin\UserController');
    Route::get('user/data', 'Admin\UserController@data');
    Route::get('creditHistory/data', 'Admin\CreditController@data');

    Route::get('user/create',['as'=>'admin.user.create', 'uses' => 'Admin\UserController@create']);
    Route::get('user/{id}/edit',['as'=>'admin.user.edit', 'uses' => 'Admin\UserController@edit']);
    Route::get('user/{id}/delete', ['as'=>'admin.user.delete', 'uses' => 'Admin\UserController@delete']);
    Route::match(['put','post'],'user/update/{user_id}', ['as'=>'admin.user.update', 'uses' => 'Admin\UserController@update']);
    Route::get('admin/create',['as'=>'admin.admin.create', 'uses' => 'Admin\UserController@adminCreate']);
    Route::match(['put','post'],'user/admin/store', ['as'=>'admin.admin.store', 'uses' => 'Admin\UserController@adminStore']);
    //Route::resource('user', 'Admin\UserController');

    Route::get('agent', ['as' => 'admin.agent.index', 'uses' => 'Admin\AgentController@index']);
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('agent/create',['as'=>'admin.agent.create', 'uses' => 'Admin\AgentController@create']);
    Route::post('agent/store',['as'=>'admin.agent.store', 'uses' => 'Admin\AgentController@store']);
    //Route::get('agent/{id}/block', ['as'=>'admin.agent.block', 'uses' => 'Admin\AgentController@ban']);
    Route::post('agent/unblockData', ['as'=>'admin.agent.unblockData', 'uses' => 'Admin\AgentController@unbanData']);
    Route::post('agent/unblock', ['as'=>'admin.agent.unblock', 'uses' => 'Admin\AgentController@unban']);

    /** Изменение прав пользователя */
    // изменение одного права
    Route::post('agent/permission/switch', ['as'=>'admin.agent.permission.switch', 'uses' => 'Admin\AgentController@switchPermission']);
    // изменение всех прав по заданному массиву
    Route::post('agent/permission/update', ['as'=>'admin.agent.permissions.update', 'uses' => 'Admin\AgentController@permissionsUpdate']);


    // баны пользователю
    Route::post('agent/block',['as'=>'admin.agent.block', 'uses' => 'Admin\AgentController@ban']);

    Route::get('newAgents', ['as'=>'admin.agent.newAgents', 'uses' => 'Admin\AgentController@newAgents']);
    Route::get('agent/activated/{id}', ['as'=>'admin.agent.activatedPage', 'uses' => 'Admin\AgentController@agentActivatedPage']);
    Route::match(['put','post'],'agent/{id}/activate', ['as'=>'admin.agent.activate', 'uses' => 'Admin\AgentController@agentActivate']);

    Route::post('agent/revenue',['as'=>'admin.agent.revenue', 'uses' => 'Admin\AgentController@revenueUpdate']);
    Route::post('agent/attachAccountManagers',['as'=>'admin.agent.attachAccountManagers', 'uses' => 'Admin\AgentController@attachAccountManagers']);
    Route::post('agent/rank',['as'=>'admin.agent.rank', 'uses' => 'Admin\AgentController@rankUpdate']);

    // страница редактирования данных агента
    Route::get('agent/{id}/edit',['as'=>'admin.agent.edit', 'uses' => 'Admin\AgentController@edit']);

    Route::post('agent/getFilter',['as'=>'admin.agent.getFilter', 'uses' => 'Admin\AgentController@getFilter']);

    // Сохранение телефонов агента
    Route::post('agent/phonesUpdate',['as'=>'admin.agent.phonesUpdate', 'uses' => 'Admin\AgentController@phonesUpdate']);
    Route::post('agent/phonesDelete',['as'=>'admin.agent.phonesDelete', 'uses' => 'Admin\AgentController@phonesDelete']);


    Route::get('operator', ['as' => 'admin.operator.index', 'uses' => 'Admin\OperatorController@index']);
    Route::get('operator/data', 'Admin\OperatorController@data');
    Route::post('operator/getFilterOperator',['as'=>'admin.operator.getFilterOperator', 'uses' => 'Admin\OperatorController@getFilterOperator']);
    Route::get('operator/create',['as'=>'admin.operator.create', 'uses' => 'Admin\OperatorController@create']);
    Route::post('operator/store',['as'=>'admin.operator.store', 'uses' => 'Admin\OperatorController@store']);
    Route::get('operator/{id}/edit',['as'=>'admin.operator.edit', 'uses' => 'Admin\OperatorController@edit']);
    Route::match(['put','post'],'operator/{id}/update',['as'=>'admin.operator.update', 'uses' => 'Admin\OperatorController@update']);
    Route::get('operator/{id}/destroy', ['as'=>'admin.operator.delete', 'uses' => 'Admin\OperatorController@destroy']);
    Route::post('operator/attachAccountManagers',['as'=>'admin.operator.attachAccountManagers', 'uses' => 'Admin\OperatorController@attachAccountManagers']);

    // изменение состояние счета агента
    Route::match(['put','post'],'agent/{id}/update',['as'=>'admin.agent.update', 'uses' => 'Admin\AgentController@update']);


    Route::get('agent/{id}/destroy', ['as'=>'admin.agent.delete', 'uses' => 'Admin\AgentController@destroy']);


    Route::get('accountManager', ['as' => 'admin.accountManager.index', 'uses' => 'Admin\AccountManagerController@index']);
    Route::get('accountManager/data', 'Admin\AccountManagerController@data');
    Route::get('accountManager/create',['as'=>'admin.accountManager.create', 'uses' => 'Admin\AccountManagerController@create']);
    Route::post('accountManager/store',['as'=>'admin.accountManager.store', 'uses' => 'Admin\AccountManagerController@store']);
    Route::get('accountManager/{id}/edit',['as'=>'admin.accountManager.edit', 'uses' => 'Admin\AccountManagerController@edit']);
    Route::match(['put','post'],'accountManager/{id}/update',['as'=>'admin.accountManager.update', 'uses' => 'Admin\AccountManagerController@update']);
    Route::get('accountManager/{id}/destroy', ['as'=>'admin.accountManager.delete', 'uses' => 'Admin\AccountManagerController@destroy']);


    Route::get('sphere/index', ['as' => 'admin.sphere.index', 'uses' => 'Admin\SphereController@index']);
    Route::get('sphere/create', ['as' => 'admin.sphere.create', 'uses' => 'Admin\SphereController@create']);

    Route::get('sphere/{id}/edit', ['as' => 'admin.sphere.edit', 'uses' => 'Admin\SphereController@edit']);

    Route::match(['put','post'],'sphere/{id}/update', ['as' => 'admin.sphere.update', 'uses' => 'Admin\SphereController@update']);
    Route::get('sphere/form/{id}/conf', ['as' => 'admin.attr.form', 'uses' => 'Admin\SphereController@get_config']);
    //Route::post('sphere/form/conf', ['as'=>'admin.chrct.form', 'uses'=> 'Admin\SphereController@save_config']);
    Route::get('sphere/{id}/delete', ['as' => 'admin.sphere.delete', 'uses' => 'Admin\SphereController@destroy']);

    Route::post('sphere/changeStatus', ['as' => 'admin.sphere.changeStatus', 'uses' => 'Admin\SphereController@changeStatus']);

    // страница не активных масок агентов
    Route::get('sphere/filters/reprice', ['as' => 'admin.sphere.reprice', 'uses' => 'Admin\SphereController@filtration']);
    Route::get('sphere/filters/repriceAll', ['as' => 'admin.sphere.repriceAll', 'uses' => 'Admin\SphereController@filtrationAll']);

    // страница редактирования маски
    Route::get('sphere/{sphere}/filters/reprice/{id}/edit/{mask_id}', ['as' => 'admin.sphere.reprice.edit', 'uses' => 'Admin\SphereController@filtrationEdit']);

    // сохранение прайса пользователя
    Route::match(['put','post'],'sphere/{sphere}/filters/reprice/{id}', ['as' => 'admin.sphere.reprice.update', 'uses' => 'Admin\SphereController@filtrationUpdate']);

    //Route::resource('sphere', 'Admin\SphereController');

    Route::get('lead/index', ['as' => 'admin.lead.index', 'uses' => 'Admin\LeadController@index']);
    Route::get('lead/data', ['as' => 'admin.lead.data', 'uses' => 'Admin\LeadController@data']);
    Route::post('lead/getFilter', ['as' => 'admin.lead.getFilter', 'uses' => 'Admin\LeadController@getFilter']);


    // статистика агента
    Route::get('statistic/agents', ['as' => 'admin.statistic.agents', 'uses' => 'Admin\StatisticController@agentsList']);
    Route::get('statistic/agent/{id}', ['as' => 'admin.statistic.agent', 'uses' => 'Admin\StatisticController@agentStatistic']);
    Route::get('statistic/agentsData', ['as' => 'admin.statistic.agentsData', 'uses' => 'Admin\StatisticController@agentsData']);
    Route::post('statistic/agentData/', ['as' => 'admin.statistic.agentData', 'uses' => 'Admin\StatisticController@agentStatisticData']);
    Route::post('statistic/getFilterAgent',['as'=>'admin.statistic.getFilterAgent', 'uses' => 'Admin\StatisticController@getFilterAgent']);

    // статистика по сфере
    Route::get('statistic/spheres', ['as' => 'admin.statistic.spheres', 'uses' => 'Admin\StatisticController@spheresList']);
    Route::get('statistic/sphere/{id}', ['as' => 'admin.statistic.sphere', 'uses' => 'Admin\StatisticController@sphereStatistic']);
    Route::get('statistic/spheresData', ['as' => 'admin.statistic.spheresData', 'uses' => 'Admin\StatisticController@spheresData']);
    Route::post('statistic/sphereData/', ['as' => 'admin.statistic.sphereData', 'uses' => 'Admin\StatisticController@sphereStatisticData']);

    // статистика аккаунт менеджера
    Route::get('statistic/accManagers', ['as' => 'admin.statistic.accManagers', 'uses' => 'Admin\StatisticController@accManagerList']);
    Route::get('statistic/accManager/{id}', ['as' => 'admin.statistic.accManager', 'uses' => 'Admin\StatisticController@accManagerStatistic']);
    Route::get('statistic/accManagerData', ['as' => 'admin.statistic.accManagerData', 'uses' => 'Admin\StatisticController@accManagerData']);
    Route::post('statistic/accManagerData/', ['as' => 'admin.statistic.accManagerData', 'uses' => 'Admin\StatisticController@accManagerStatisticData']);

    // статистика оператора
    Route::get('statistic/operators', ['as' => 'admin.statistic.operators', 'uses' => 'Admin\StatisticController@operatorsList']);
    Route::get('statistic/operator/{id}', ['as' => 'admin.statistic.operator', 'uses' => 'Admin\StatisticController@operatorStatistic']);
    Route::get('statistic/operatorsData', ['as' => 'admin.statistic.accManagerData', 'uses' => 'Admin\StatisticController@operatorsData']);
    Route::post('statistic/operatorData/', ['as' => 'admin.statistic.operatorData', 'uses' => 'Admin\StatisticController@operatorStatisticData']);
    Route::post('statistic/getFilterOperator',['as'=>'admin.statistic.getFilterOperator', 'uses' => 'Admin\StatisticController@getFilterOperator']);


    // данные по сделкам
    Route::get('deals/all', ['as' => 'admin.deals.all', 'uses' => 'Admin\DealController@AllDeals']);
    Route::get('deals/to/confirmation', ['as' => 'admin.deals.to.confirmation', 'uses' => 'Admin\DealController@ToConfirmationDeals']);
    Route::get('deal/{id}', ['as' => 'admin.deal', 'uses' => 'Admin\DealController@deal']);
    Route::post('dealData', ['as' => 'admin.dealData', 'uses' => 'Admin\DealController@dealData']);
    Route::post('lead/sendMessageDeal',['as'=>'admin.lead.sendMessageDeal', 'uses' => 'Admin\DealController@sendMessageDeal']);
    Route::post('lead/blockCheckDelete',['as'=>'admin.lead.blockCheckDelete', 'uses' => 'Admin\DealController@blockCheckDelete']);

    // Загрузка чека по сделке
    Route::post('deal/checkUpload',['as'=>'admin.deal.checkUpload', 'uses' => 'Admin\DealController@checkUpload']);

    // подробности по транзиту
    Route::post('statistic/transitionDetails', ['as' => 'admin.statistic.transition.details', 'uses' => 'Admin\StatisticController@transitionDetails']);

    // изменение статуса сделки
    Route::post('agent/deal/status/change', ['as'=>'admin.deal.status.change', 'uses' => 'Admin\DealController@changeDealStatus']);


    // Настройки
    Route::get('settings/roles', ['as' => 'admin.settings.roles', 'uses' => 'Admin\SettingsController@roles']);
    Route::match(['put','post'], 'settings/role/update', ['as' => 'admin.settings.roleUpdate', 'uses' => 'Admin\SettingsController@roleUpdate']);

    // Работа с групами агентов
    Route::get('groups/to/confirmation', ['as' => 'admin.groups.to.confirmation', 'uses' => 'Admin\AgentPrivateGroupsController@ToConfirmationAgentInGroup']);
    Route::get('groups/all', ['as' => 'admin.groups.all', 'uses' => 'Admin\AgentPrivateGroupsController@allAgentsPrivateGroups']);
    Route::get('groups/detail/{id}', ['as' => 'admin.groups.detail', 'uses' => 'Admin\AgentPrivateGroupsController@detail']);
    Route::get('groups/allDetail/{id}', ['as' => 'admin.groups.allDetail', 'uses' => 'Admin\AgentPrivateGroupsController@allDetail']);
    Route::post('groups/agent/confirm', ['as'=>'admin.groups.agent.confirm', 'uses' => 'Admin\AgentPrivateGroupsController@confirmAgent']);
    Route::post('groups/agent/reject', ['as'=>'admin.groups.agent.reject', 'uses' => 'Admin\AgentPrivateGroupsController@rejectAgent']);

});
