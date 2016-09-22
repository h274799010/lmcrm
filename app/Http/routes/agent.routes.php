<?php

Route::group(['prefix' => 'agent','middleware' => ['auth', 'agent|salesman'] ], function() {

    // todo эти две страницы, похоже, несуществуют, проверить и удалить
//    Route::get('/', ['as' => 'agent.index', 'uses' => 'Agent\AgentController@index']);
//    Route::get('lead', ['as' => 'agent.lead.index', 'uses' => 'Agent\LeadController@index']);


    /** Группа роутов для ролей агентов */
    Route::group([ 'middleware' => ['permissions'] ], function () {

        /** страница отданых лидов агентом */
        // страница с отданными лидами
        Route::get('lead/depostited', ['as' => 'agent.lead.deposited', 'uses' => 'Agent\LeadController@deposited']);
        // форма создания нового лида
        Route::get('lead/create', ['as' => 'agent.lead.create', 'uses' => 'Agent\LeadController@create']);
        // сохранение нового лида
        Route::post('lead/store',['as'=>'agent.lead.store', 'uses' => 'Agent\LeadController@store']);

        /** страница фильтрации лидов */
        // страница с отфильтрованными лидами
        Route::get('lead/obtain', ['as' => 'agent.lead.obtain', 'uses' => 'Agent\LeadController@obtain']);
        // страница с открытыми лидами
        Route::get('openedLeads', ['as'=>'agent.lead.opened', 'uses'=>'Agent\LeadController@openedLeads']);
        // открытие лида
        Route::get('lead/open/{lead_id}/{mask_id}', ['as' => 'agent.lead.open', 'uses' => 'Agent\LeadController@openLead']);
        // максимальное открытие лида
        Route::get('lead/openAll/{lead_id}/{mask_id}', ['as' => 'agent.lead.openAll', 'uses' => 'Agent\LeadController@openAllLeads']);
    });




    // получение данных для таблицы на странице фильтра лидов
    Route::get('lead/obtain/data', ['as' => 'agent.lead.obtain.data', 'uses' => 'Agent\LeadController@obtainData']);

    // todo страница фильтра лидов, тестовая, удалить
    Route::get('lead/obtain2', ['as' => 'agent.lead.obtain.2', 'uses' => 'Agent\LeadController@obtain2']);

    // todo получение данных для таблицы на странице фильтра лидов, удалить
    Route::get('lead/obtain2/data', ['as' => 'agent.lead.obtain.2.data', 'uses' => 'Agent\LeadController@obtain2Data']);

    Route::get('lead/showOpenedLead/{id}',['as'=>'agent.lead.showOpenedLead', 'uses' => 'Agent\LeadController@showOpenedLead']);


    // форма добавление комментария
    Route::get('lead/addСomment/{lead_id}',['as'=>'agent.lead.addСomment', 'uses' => 'Agent\LeadController@addСomment']);

    // форма добавление напоминания
    Route::get('lead/addReminder/{lead_id}',['as'=>'agent.lead.addReminder', 'uses' => 'Agent\LeadController@addReminder']);

    // todo разобраться, еще не понял где и зачем
    Route::post('lead/editOpenedLead',['as'=>'agent.lead.editOpenedLead', 'uses' => 'Agent\LeadController@editOpenedLead']);

    // получение записи органайзера по id
    Route::post('lead/OrganizerItem',['as'=>'agent.lead.OrganizerItem', 'uses' => 'Agent\LeadController@getOrganizerItem']);

    // сохранение данных органайзера в БД
    Route::post('lead/putReminder',['as'=>'agent.lead.putReminder', 'uses' => 'Agent\LeadController@putReminder']);

    // удаление строки органайзера из БД
    Route::get('lead/deleteReminder/{id}',['as'=>'agent.lead.deleteReminder', 'uses' => 'Agent\LeadController@deleteReminder']);

    // редактирование строки органайзера из БД
    Route::get('lead/editOrganizer/{id}',['as'=>'agent.lead.editOrganizer', 'uses' => 'Agent\LeadController@editOrganizer']);

    // обновление строки органайзера из БД
    Route::post('lead/updateOrganizer',['as'=>'agent.lead.updateOrganizer', 'uses' => 'Agent\LeadController@updateOrganizer']);

    // установка статуса лида
    Route::post('lead/setOpenLeadStatus',['as'=>'agent.lead.setOpenLeadStatus', 'uses' => 'Agent\LeadController@setOpenLeadStatus']);

    // todo удалить, установка следующего по счету статуса лида
    Route::get('lead/nextStatus/{id}',['as'=>'agent.lead.nextStatus', 'uses' => 'Agent\LeadController@nextStatus']);


    // получение подробной информации об открытом лиде
    Route::post('openedLeadsAjax', ['as'=>'agent.lead.openedAjax','uses'=>'Agent\LeadController@openedLeadsAjax']);

    #Route::get('lead/{id}/edit',['as'=>'agent.lead.edit', 'uses' => 'Agent\LeadController@edit']);
    #Route::match(['put','post'],'lead/{id}',['as'=>'agent.lead.update', 'uses' => 'Agent\LeadController@update']);
    //Route::resource('lead','Agent\LeadController@create');

    Route::group( ['middleware'=>['agent']],function() {
        // страница всех масок агента по сферам
        Route::get('sphere', ['as' => 'agent.sphere.index', 'uses' => 'Agent\SphereController@index']);

        // страница создания/редактирования маски агента
        Route::get('sphere/{sphere_id}/{mask_id}/edit',['as'=>'agent.sphere.edit', 'uses' => 'Agent\SphereController@edit']);

        // сохранение данных маски агента
        Route::match(['put','post'],'sphere/{sphere_id}/{mask_id}',['as'=>'agent.sphere.update', 'uses' => 'Agent\SphereController@update']);

        // удаление маски агента
        Route::post('mask/remove', ['as'=>'agent.remove.mask', 'uses' => 'Agent\SphereController@removeMask']);


        //Route::resource('customer/filter','Agent\CustomerFilterController');

        Route::get('salesman', ['as' => 'agent.salesman.index', 'uses' => 'Agent\SalesmanController@index']);
        Route::get('salesman/create', ['as' => 'agent.salesman.create', 'uses' => 'Agent\SalesmanController@create']);
        Route::post('salesman/store', ['as' => 'agent.salesman.store', 'uses' => 'Agent\SalesmanController@store']);
        Route::get('salesman/{id}/edit', ['as' => 'agent.salesman.edit', 'uses' => 'Agent\SalesmanController@edit']);
        Route::match(['put', 'post'], 'salesman/{id}', ['as' => 'agent.salesman.update', 'uses' => 'Agent\SalesmanController@update']);
        //Route::resource('salesman','Agent\SalesmanController');
    });
});
