<?php

Route::group(['prefix' => 'callcenter','middleware' => ['auth', 'operator'] ], function() {
    //Route::get('/', ['as' => 'dashboard.index', 'uses' => 'Operator\OperatorController@index']);

    // все лиды на обработку оператору (новые лиды, либо недообработанные)
    Route::get('sphere', ['as' => 'operator.sphere.index', 'uses' => 'Operator\SphereController@index']);

    // лиды которые обрабатывал этот оператор (история обработки лидов оператором)
    Route::get('sphereEdited', ['as' => 'operator.sphere.edited', 'uses' => 'Operator\SphereController@editedLids']);

    // все лиды помеченные к перезвону на определенное время
    Route::get('leadsMarkedForCall', ['as' => 'leads.marked.for.call', 'uses' => 'Operator\SphereController@leadsMarkedForCall']);

    //Route::get('sphere/create', ['as' => 'operator.sphere.create', 'uses' => 'Operator\SphereController@create']);
    //Route::post('sphere/store',['as'=>'operator.sphere.store', 'uses' => 'Operator\SphereController@store']);
    Route::get('sphere/{sphere}/lead/{id}/edit',['as'=>'operator.sphere.lead.edit', 'uses' => 'Operator\SphereController@edit']);
    Route::match(['put','post'],'sphere/{sphere}/lead/{id}',['as'=>'operator.sphere.lead.update', 'uses' => 'Operator\SphereController@update']);

    Route::post('check', ['as' => 'operator.sphere.lead.check', 'uses' => 'Operator\SphereController@checkLead']);

    // устанавливает лиду статус badLead и делает полный расчет по нему
    Route::get('setBad/lead/{id}',['as'=>'set.bad.lead', 'uses' => 'Operator\SphereController@setBadLead']);

    // установка напоминания на звонок
    Route::post('operator/set/reminder/time', ['as' => 'operator.set.reminder.time', 'uses' => 'Operator\SphereController@setReminderTime']);

    // добавление комментария оператора
    Route::post('operator/add/comment', ['as' => 'operator.add.comment', 'uses' => 'Operator\SphereController@addOperatorComment']);

    // удаление напоминания о звонке у оператора
    Route::post('operator/remove/reminder/time', ['as' => 'operator.remove.reminder.time', 'uses' => 'Operator\SphereController@removeReminderTime']);

    // получение данных агентов, которым этот лид подходит
    Route::post('operator/agents/selection', ['as' => 'operator.agents.selection', 'uses' => 'Operator\SphereController@agentsSelection']);

    // действия по лиду (отправка на аукцион, открытие, закрытие сделки)
    Route::post('lead/action', ['as' => 'operator.lead.action', 'uses' => 'Operator\SphereController@leadAction']);

    // отправка лида оператором на аукцион агента
    Route::post('send/to/auction', ['as' => 'send.to.auction', 'uses' => 'Operator\SphereController@sendToAuction']);

    // форма добавления нового лида
    Route::get('lead/create', ['as' => 'operator.lead.create', 'uses' => 'Operator\SphereController@create']);

    // сохранение нового лида
    Route::post('lead/store', ['as' => 'operator.lead.store', 'uses' => 'Operator\SphereController@store']);


});
