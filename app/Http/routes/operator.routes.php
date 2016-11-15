<?php

Route::group(['prefix' => 'callcenter','middleware' => ['auth', 'operator'] ], function() {
    //Route::get('/', ['as' => 'dashboard.index', 'uses' => 'Operator\OperatorController@index']);

    Route::get('sphere', ['as' => 'operator.sphere.index', 'uses' => 'Operator\SphereController@index']);

    Route::get('sphereEdited', ['as' => 'operator.sphere.edited', 'uses' => 'Operator\SphereController@editedLids']);

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


    //Route::resource('customer/filter','Operator\CustomerFilterController@create');
});
