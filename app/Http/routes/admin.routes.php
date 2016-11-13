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
    //Route::resource('user', 'Admin\UserController');

    Route::get('agent', ['as' => 'admin.agent.index', 'uses' => 'Admin\AgentController@index']);
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('agent/create',['as'=>'admin.agent.create', 'uses' => 'Admin\AgentController@create']);
    Route::post('agent/store',['as'=>'admin.agent.store', 'uses' => 'Admin\AgentController@store']);

    Route::post('agent/revenue',['as'=>'admin.agent.revenue', 'uses' => 'Admin\AgentController@revenueUpdate']);

    // страница редактирования данных агента
    Route::get('agent/{id}/edit',['as'=>'admin.agent.edit', 'uses' => 'Admin\AgentController@edit']);


    Route::get('operator', ['as' => 'admin.operator.index', 'uses' => 'Admin\OperatorController@index']);
    Route::get('operator/data', 'Admin\OperatorController@data');
    Route::get('operator/create',['as'=>'admin.operator.create', 'uses' => 'Admin\OperatorController@create']);
    Route::post('operator/store',['as'=>'admin.operator.store', 'uses' => 'Admin\OperatorController@store']);
    Route::get('operator/{id}/edit',['as'=>'admin.operator.edit', 'uses' => 'Admin\OperatorController@edit']);
    Route::match(['put','post'],'operator/{id}/update',['as'=>'admin.operator.update', 'uses' => 'Admin\OperatorController@update']);
    Route::get('operator/{id}/destroy', ['as'=>'admin.operator.delete', 'uses' => 'Admin\OperatorController@destroy']);

    // изменение состояние счета агента
    Route::match(['put','post'],'agent/{id}/update',['as'=>'admin.agent.update', 'uses' => 'Admin\AgentController@update']);


    Route::get('agent/{id}/destroy', ['as'=>'admin.agent.delete', 'uses' => 'Admin\AgentController@destroy']);


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

});
