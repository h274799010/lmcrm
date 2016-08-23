<?php

Route::group(['prefix' => 'agent','middleware' => ['auth', 'agent|salesman'] ], function() {
    Route::get('/', ['as' => 'agent.index', 'uses' => 'Agent\AgentController@index']);

    Route::get('lead', ['as' => 'agent.lead.index', 'uses' => 'Agent\LeadController@index']);
    Route::get('lead/depostited', ['as' => 'agent.lead.deposited', 'uses' => 'Agent\LeadController@deposited']);
    Route::get('lead/obtain', ['as' => 'agent.lead.obtain', 'uses' => 'Agent\LeadController@obtain']);
    Route::get('lead/obtain/data', ['as' => 'agent.lead.obtain.data', 'uses' => 'Agent\LeadController@obtainData']);
    Route::get('lead/open/{id}', ['as' => 'agent.lead.open', 'uses' => 'Agent\LeadController@openLead']);
    Route::get('lead/openAll/{id}', ['as' => 'agent.lead.openAll', 'uses' => 'Agent\LeadController@openAllLeads']);
    Route::get('lead/create', ['as' => 'agent.lead.create', 'uses' => 'Agent\LeadController@create']);
    Route::post('lead/store',['as'=>'agent.lead.store', 'uses' => 'Agent\LeadController@store']);
    Route::get('lead/showOpenedLead/{id}',['as'=>'agent.lead.showOpenedLead', 'uses' => 'Agent\LeadController@showOpenedLead']);


    // форма добавление комментария
    Route::get('lead/addСomment/{lead_id}',['as'=>'agent.lead.addСomment', 'uses' => 'Agent\LeadController@addСomment']);

    // форма добавление напоминания
    Route::get('lead/addReminder/{lead_id}',['as'=>'agent.lead.addReminder', 'uses' => 'Agent\LeadController@addReminder']);


    Route::post('lead/editOpenedLead',['as'=>'agent.lead.editOpenedLead', 'uses' => 'Agent\LeadController@editOpenedLead']);

    // получение записи органайзера по id
    Route::post('lead/OrganizerItem',['as'=>'agent.lead.OrganizerItem', 'uses' => 'Agent\LeadController@getOrganizerItem']);


    // сохранение данных органайзера в БД
    Route::post('lead/putReminder',['as'=>'agent.lead.putReminder', 'uses' => 'Agent\LeadController@putReminder']);

    // удаление строки органайзера из БД
    Route::get('lead/deleteReminder/{id}',['as'=>'agent.lead.deleteReminder', 'uses' => 'Agent\LeadController@deleteReminder']);

    // установка статуса лида
    Route::post('lead/setOpenLeadStatus',['as'=>'agent.lead.setOpenLeadStatus', 'uses' => 'Agent\LeadController@setOpenLeadStatus']);

    // установка следующего по счету статуса лида
    Route::get('lead/nextStatus/{id}',['as'=>'agent.lead.nextStatus', 'uses' => 'Agent\LeadController@nextStatus']);

    Route::get('openedLeads', ['as'=>'agent.openedLeads', 'uses'=>'Agent\LeadController@openedLeads']);

    // получение подробной информации об открытом лиде
    Route::post('openedLeadsAjax', ['as'=>'agent.openedLeadsAjax','uses'=>'Agent\LeadController@openedLeadsAjax']);

    #Route::get('lead/{id}/edit',['as'=>'agent.lead.edit', 'uses' => 'Agent\LeadController@edit']);
    #Route::match(['put','post'],'lead/{id}',['as'=>'agent.lead.update', 'uses' => 'Agent\LeadController@update']);
    //Route::resource('lead','Agent\LeadController@create');

    Route::group(['middleware'=>['agent']],function() {
        Route::get('sphere', ['as' => 'agent.sphere.index', 'uses' => 'Agent\SphereController@index']);
        Route::get('sphere/create', ['as' => 'agent.sphere.create', 'uses' => 'Agent\SphereController@create']);
        Route::post('sphere/store',['as'=>'agent.sphere.store', 'uses' => 'Agent\SphereController@store']);
        Route::get('sphere/{id}/edit',['as'=>'agent.sphere.edit', 'uses' => 'Agent\SphereController@edit']);
        Route::match(['put','post'],'sphere/{id}',['as'=>'agent.sphere.update', 'uses' => 'Agent\SphereController@update']);
        //Route::resource('customer/filter','Agent\CustomerFilterController');

        Route::get('salesman', ['as' => 'agent.salesman.index', 'uses' => 'Agent\SalesmanController@index']);
        Route::get('salesman/create', ['as' => 'agent.salesman.create', 'uses' => 'Agent\SalesmanController@create']);
        Route::post('salesman/store', ['as' => 'agent.salesman.store', 'uses' => 'Agent\SalesmanController@store']);
        Route::get('salesman/{id}/edit', ['as' => 'agent.salesman.edit', 'uses' => 'Agent\SalesmanController@edit']);
        Route::match(['put', 'post'], 'salesman/{id}', ['as' => 'agent.salesman.update', 'uses' => 'Agent\SalesmanController@update']);
        //Route::resource('salesman','Agent\SalesmanController');
    });
});
?>