<?php
Route::group(['prefix' => 'admin'], function() {
    Route::get('sphere/data', 'Admin\SphereController@data');
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('user/data', 'Admin\UserController@data');
    Route::get('credit/data', 'Admin\CreditController@data');
});


// роуты для разных групп пользователей
Route::group(['prefix' => LaravelLocalization::setLocale(),'middleware' => ['localeSessionRedirect','localizationRedirect', 'localize']], function() {
    include('routes/front.routes.php');
    include('routes/agent.routes.php');
    include('routes/operator.routes.php');

    include('routes/admin.routes.php');
    include('routes/accountManager.routes.php');
});


// Роуты мобильного приложения
Route::group(['prefix' => 'api'], function(){
    include ('routes/api.routes.php');
});


// метод сообщает об уведомлениях
Route::get('notice', ['as' => 'notification', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notice']);

// в этом месте сообщения отмечаются как полученные
Route::post('notified', ['as' => 'notified', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notified']);

// todo удалить, это для проверки выдачи статистики
Route::get('transitionTest/{status}', function($status){

//    $a = \App\Models\SphereStatusTransitions::all();

    $a = \App\Models\SphereStatusTransitions::getRating( 1, 2, $status);


//    dd($a);

    return $a;

});

Route::get('stat', function(){



    dd( \Carbon\Carbon::now()->subDays(0)->diffForHumans() );


    $leadId = 250;

    // получаем лид
    $lead = Lead::find( $leadId );

    // выбираем статусы сферы
    $sphereStatuses = $lead->sphereStatuses->statuses;

    // массив со статусами ( status_id => stepname )
    $statuses[0] = 'No status';

    // перебираем все статусы и формируем массив со статусами
    $sphereStatuses->each(function( $status ) use (&$statuses){
        // добавление статуса в массив статусов
        $statuses[$status->id] = $status->stepname;
    });

    // получаем всех участников группы агента
    $members = \App\Models\AgentsPrivateGroups::
          where( 'agent_owner_id', $lead['agent_id'] )
        ->with(
            [
                'memberData',
                'openLead'=>function($query) use ($leadId){
                    // получаем только текущий лид
                    $query->where('lead_id', $leadId);
                }
            ]
        )
        ->get();

    // коллекция с агентами для которых лид был открыт
    $membersOpen = collect();
    // коллекция с агентами для которых лид небыл открыт
    $membersNotOpen = collect();

    // перебор всех участников группы и выборка нужных данных
    $members->each(function($item) use (&$membersOpen, &$membersNotOpen, $statuses){

        // проверка открытых лидов у участника
        if( $item['openLead']->count()==0 ){
            // если нет открытых лидов

            $data =
                [
                    'id' => $item['memberData']['id'],
                    'email' => $item['memberData']['email'],
                ];


            // todo добавляем данные в массив с агентами, которым лид не добавлен
            $membersNotOpen->push($data);

        }else{
            // если лид открыт для участника

            $data =
            [
                'id' => $item['memberData']['id'],
                'email' => $item['memberData']['email'],
                'status' => $statuses[ $item['openLead'][0]['status'] ]
            ];

            // todo добавляем данные в массив с агентами, которым лид был добавлен
            $membersOpen->push($data);
        }
    });

    return response()->json(
        [
            'membersOpen' => $membersOpen,
            'membersNotOpen' => $membersNotOpen
        ]
    );

//        dd( $membersOpen );


        dd( $membersNotOpen );




//    dd( Lead::find(250) );

    $c = \App\Helper\PayMaster\PayInfo::getClosedDealData( 250 );

    dd($c);



    $c = \App\Helper\PayMaster\PayInfo::getAgentsOpenedLeadsData( 3, true );

    dd($c);


    $a  = \App\Models\TransactionsLeadInfo::where('lead_id', 3)->lists('transaction_id');

    $b = \App\Models\TransactionsDetails::whereIn('transaction_id', $a)->get();

    dd($b);




    $userId = 6;

    $user = \App\Models\User::find( $userId );



    $openLeads = \App\Models\OpenLeads::
          whereIn('agent_id', $userIds);


    $openLeads = $openLeads
        ->with(
            [
                'lead' => function ($query) {
                    $query->with('sphereStatuses', 'sphere');
                },
                'maskName2',
                'statusInfo',
                'closeDealInfo'
            ]
        )
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
    ;

    dd( $openLeads[4] );

    return 'ok';

});

