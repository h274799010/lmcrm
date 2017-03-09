<?php

Route::group(['prefix' => 'salesman','middleware' => ['auth', 'agent|salesman'] ], function() {

    // страница с отданными лидами
    Route::get('lead/depostited', ['as' => 'salesman.lead.deposited', 'uses' => 'Agent\LeadController@deposited']);

    // страница с отфильтрованными лидами
    Route::get('lead/obtain', ['as' => 'salesman.lead.obtain', 'middleware' => ['redirectIfBanned'], 'uses' => 'Agent\LeadController@obtain']);

    // страница с открытыми лидами
    Route::get('openedLeads', ['as'=>'salesman.lead.opened', 'uses'=>'Agent\LeadController@openedLeads']);

    // страница открытия лида продавцом
    Route::get('lead/open/{lead_id}/{mask_id}/{salesman_id}', ['as'=>'salesman.lead.open', 'uses'=>'Agent\LeadController@openLead']);

    // страница максимального открытия лида продавцом
    Route::get('lead/openAll/{lead_id}/{mask_id}/{salesman_id}', ['as'=>'salesman.lead.openAll', 'uses'=>'Agent\LeadController@openAllLeads']);

});

Route::group(['prefix' => 'agent', 'middleware' => ['auth', 'agent|salesman'] ], function() {

    // todo эти две страницы, похоже, несуществуют, проверить и удалить
//    Route::get('/', ['as' => 'agent.index', 'uses' => 'Agent\AgentController@index']);
//    Route::get('lead', ['as' => 'agent.lead.index', 'uses' => 'Agent\LeadController@index']);

    /** Группа роутов для ролей агентов */
    Route::group([ 'middleware' => ['permissions'] ], function () {

        /** страница отданых лидов агентом */
        // страница с отданными лидами
        Route::get('lead/depostited', ['as' => 'agent.lead.deposited', 'uses' => 'Agent\LeadController@deposited']);
        Route::get('lead/depostited/data', ['as' => 'agent.lead.depostited.data', 'uses' => 'Agent\LeadController@depositedData']);
        // форма создания нового лида
        Route::get('lead/create', ['as' => 'agent.lead.create', 'middleware' => ['redirectIfNotActive'], 'uses' => 'Agent\LeadController@create']);
        Route::get('salesman/lead/create/{salesman_id}', ['as' => 'agent.salesman.lead.create', 'middleware' => ['redirectIfNotActive'], 'uses' => 'Agent\AgentSalesmanLeadController@create']);
        // сохранение нового лида
        Route::post('lead/store',['as'=>'agent.lead.store', 'middleware' => ['redirectIfNotActive'], 'uses' => 'Agent\LeadController@store']);
        Route::post('salesman/lead/store/{salesman_id}',['as'=>'agent.salesman.lead.store', 'middleware' => ['redirectIfNotActive'], 'uses' => 'Agent\AgentSalesmanLeadController@store']);

        /** страница фильтрации лидов */
        // страница с отфильтрованными лидами
        Route::get('lead/obtain', ['as' => 'agent.lead.obtain', 'uses' => 'Agent\LeadController@obtain']);
        // страница с открытыми лидами
        Route::get('openedLeads/{lead_id?}', ['as'=>'agent.lead.opened', 'uses'=>'Agent\LeadController@openedLeads']);
        Route::get('openedLeadsData', ['as'=>'agent.lead.openedData', 'uses'=>'Agent\LeadController@openedLeadsData']);
        // открытие лида
        Route::get('lead/open/{lead_id}/{mask_id}', ['as' => 'agent.lead.open', 'middleware' => ['redirectIfNotActive'], 'uses' => 'Agent\LeadController@openLead']);
        // максимальное открытие лида
        Route::get('lead/openAll/{lead_id}/{mask_id}', ['as' => 'agent.lead.openAll', 'middleware' => ['redirectIfNotActive'], 'uses' => 'Agent\LeadController@openAllLeads']);

        // todo добавить роуты для салесмана
        // страница передачи лида агентом другим агентам группы
        Route::get('lead/deposited/{lead_id}/details', ['as' => 'agent.lead.deposited.details', 'uses' => 'Agent\LeadController@depositedDetails']);

        // todo добавить роуты для салесмана
        // передача лида агентом другому агенту группы
        Route::post('lead/member/open', ['as' => 'agent.lead.member.open', 'uses' => 'Agent\LeadController@openForMember']);

    });




    // получение данных для таблицы на странице фильтра лидов
    Route::get('lead/obtain/data', ['as' => 'agent.lead.obtain.data', 'uses' => 'Agent\LeadController@obtainData']);


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

    Route::post('lead/checkUpload',['as'=>'agent.lead.checkUpload', 'uses' => 'Agent\LeadController@checkUpload']);

    Route::post('lead/checkDelete',['as'=>'agent.lead.checkDelete', 'uses' => 'Agent\LeadController@checkDelete']);

    Route::post('lead/paymentDealWallet',['as'=>'agent.lead.paymentDealWallet', 'uses' => 'Agent\LeadController@paymentDealWallet']);

    // todo удалить, установка следующего по счету статуса лида
    Route::get('lead/nextStatus/{id}',['as'=>'agent.lead.nextStatus', 'uses' => 'Agent\LeadController@nextStatus']);


    // получение подробной информации об открытом лиде
    Route::post('openedLeadsAjax', ['as'=>'agent.lead.openedAjax','uses'=>'Agent\LeadController@openedLeadsAjax']);


    Route::post('getBalance', ['as'=>'agent.sphere.getBalance','uses'=>'Agent\SphereController@getBalance']);

    #Route::get('lead/{id}/edit',['as'=>'agent.lead.edit', 'uses' => 'Agent\LeadController@edit']);
    #Route::match(['put','post'],'lead/{id}',['as'=>'agent.lead.update', 'uses' => 'Agent\LeadController@update']);
    //Route::resource('lead','Agent\LeadController@create');

    // Статистика
    Route::get('statistic/index', ['as' => 'agent.statistic.index', 'uses' => 'Agent\StatisticController@agentStatistic']);
    Route::post('statistic/agentData/', ['as' => 'agent.statistic.agentData', 'uses' => 'Agent\StatisticController@agentStatisticData']);

    Route::post('getAgentPrivateGroup', ['as' => 'agent.privateGroup.getAgentPrivateGroup', 'uses' => 'AgentController@getAgentPrivateGroup']);

    Route::get('privateGroup/index', ['as' => 'agent.privateGroup.index', 'uses' => 'AgentController@agentPrivateGroup']);
    Route::post('privateGroup/search', ['as' => 'agent.privateGroup.search', 'uses' => 'AgentController@searchPrivateGroup']);
    Route::post('privateGroup/addAgent', ['as' => 'agent.privateGroup.addAgent', 'uses' => 'AgentController@addAgentInPrivateGroup']);
    Route::post('privateGroup/deleteAgent', ['as' => 'agent.privateGroup.deleteAgent', 'uses' => 'AgentController@deleteAgentInPrivateGroup']);

    // Получение статусов для открытого лида
    Route::post('getOpenLeadStatuses', ['as' => 'agent.lead.getOpenLeadStatuses', 'uses' => 'Agent\LeadController@getOpenLeadStatuses']);

    // Подробная информация о сделке
    Route::get('lead/aboutDeal/{lead_id}',['as'=>'agent.lead.aboutDeal', 'uses' => 'Agent\LeadController@aboutDeal']);

    Route::post('lead/sendMessageDeal',['as'=>'agent.lead.sendMessageDeal', 'uses' => 'Agent\LeadController@sendMessageDeal']);

    Route::group( ['middleware'=>['agent']],function() {
        // Группа роутов для которых проверяются разрешения
        Route::group([ 'middleware' => ['permissions'] ], function () {
            // страница всех масок агента по сферам
            Route::get('sphere', ['middleware' => [ 'leadbayer|dealmaker' ], 'as' => 'agent.sphere.index', 'uses' => 'Agent\SphereController@index']);

            Route::post('sphere/activateMask', ['middleware' => [ 'leadbayer|dealmaker' ], 'as' => 'agent.sphere.activateMask', 'uses' => 'Agent\SphereController@activateMask']);

            // страница всех масок агента по сферам (под продавцом)
            Route::get('sphere/{salesman_id}', ['as' => 'agent.salesman.sphere.index', 'uses' => 'Agent\AgentSalesmanSphereController@index']);

            // страница создания/редактирования маски агента
            Route::get('sphere/{sphere_id}/{mask_id}/edit',['as'=>'agent.sphere.edit', 'uses' => 'Agent\SphereController@edit']);

            // страница создания/редактирования маски агента (под продавцом)
            Route::get('sphere/{sphere_id}/{mask_id}/edit/{salesman_id}',['as'=>'agent.salesman.sphere.edit', 'uses' => 'Agent\AgentSalesmanSphereController@edit']);

            // сохранение данных маски агента
            Route::match(['put','post'],'sphere/{sphere_id}/{mask_id}',['as'=>'agent.sphere.update', 'uses' => 'Agent\SphereController@update']);

            // сохранение данных маски агента (под продавцом)
            Route::match(['put','post'],'sphere/{sphere_id}/{mask_id}/{salesman_id}',['as'=>'agent.salesman.sphere.update', 'uses' => 'Agent\AgentSalesmanSphereController@update']);

            // удаление маски агента
            Route::post('mask/remove', ['as'=>'agent.sphere.removeMask', 'uses' => 'Agent\SphereController@removeMask']);


            //Route::resource('customer/filter','Agent\CustomerFilterController');

            Route::get('salesman', ['as' => 'agent.salesman.index', 'uses' => 'Agent\SalesmanController@index']);
            Route::get('salesman/create', ['as' => 'agent.salesman.create', 'uses' => 'Agent\SalesmanController@create']);
            Route::post('salesman/store', ['as' => 'agent.salesman.store', 'uses' => 'Agent\SalesmanController@store']);
            Route::get('salesman/{id}/edit', ['as' => 'agent.salesman.edit', 'uses' => 'Agent\SalesmanController@edit']);
            Route::match(['put', 'post'], 'salesman/{id}', ['as' => 'agent.salesman.update', 'uses' => 'Agent\SalesmanController@update']);
            //Route::resource('salesman','Agent\SalesmanController');


            Route::get('salesman/obtainedLead/{salesman_id}', ['as' => 'agent.salesman.obtainedLead', 'uses' => 'Agent\AgentSalesmanLeadController@obtain']);
            Route::get('salesman/obtain/data/{salesman_id}', ['as' => 'agent.salesman.obtain.data', 'uses' => 'Agent\AgentSalesmanLeadController@obtainData']);

            Route::get('salesman/openedLeads/{salesman_id}/{lead_id?}', ['as' => 'agent.salesman.openedLeads', 'uses' => 'Agent\AgentSalesmanLeadController@openedLeads']);
            Route::get('salesman/openedLeadsData/{salesman_id}', ['as'=>'agent.salesman.openedData', 'uses'=>'Agent\AgentSalesmanLeadController@openedLeadsData']);
            Route::post('salesman/openedLeadAjax', ['as' => 'agent.salesman.openedLeadAjax', 'uses' => 'Agent\LeadController@openedLeadsAjax']);
        });
        Route::get('salesman/{id}/block', ['as'=>'agent.salesman.block', 'uses' => 'Agent\SalesmanController@ban']);
        Route::get('salesman/{id}/unblock', ['as'=>'agent.salesman.unblock', 'uses' => 'Agent\SalesmanController@unban']);

        Route::get('salesman/depositedLead/{salesman_id}', ['as' => 'agent.salesman.depositedLead', 'uses' => 'Agent\AgentSalesmanLeadController@deposited']);
        Route::get('salesman/depositedLead/data/{salesman_id}', ['as' => 'agent.salesman.depositedLead.data', 'uses' => 'Agent\AgentSalesmanLeadController@depositedData']);

        // установка статуса лида
        Route::post('salesman/lead/setOpenLeadStatus/{salesman_id}',['as'=>'agent.salesman.lead.setOpenLeadStatus', 'uses' => 'Agent\AgentSalesmanLeadController@setOpenLeadStatus']);

        // форма добавление комментария
        Route::get('salesman/addСomment/{lead_id}/{salesman_id}',['as'=>'agent.salesman.addСomment', 'uses' => 'Agent\AgentSalesmanLeadController@addСomment']);

        // форма добавление напоминания
        Route::get('salesman/addReminder/{lead_id}/{salesman_id}',['as'=>'agent.salesman.addReminder', 'uses' => 'Agent\AgentSalesmanLeadController@addReminder']);
        // сохранение данных органайзера в БД
        Route::post('salesman/putReminder',['as'=>'agent.salesman.putReminder', 'uses' => 'Agent\LeadController@putReminder']);

        // удаление строки органайзера из БД
        Route::get('salesman/deleteReminder/{id}/{salesman_id}',['as'=>'agent.salesman.deleteReminder', 'uses' => 'Agent\AgentSalesmanLeadController@deleteReminder']);

        // редактирование строки органайзера из БД
        Route::get('salesman/editOrganizer/{id}/',['as'=>'agent.salesman.editOrganizer', 'uses' => 'Agent\LeadController@editOrganizer']);

        // обновление строки органайзера из БД
        Route::post('salesman/updateOrganizer',['as'=>'agent.salesman.updateOrganizer', 'uses' => 'Agent\LeadController@updateOrganizer']);
    });
});
