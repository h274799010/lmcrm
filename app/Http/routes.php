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

    $userId = 6;

    $user = \App\Models\User::find( $userId );


//    $user = \App\Models\Agent::find(6);

    $userIds = [$user->id];

    if($user->inRole('agent')) {

        $agent = \App\Models\Agent::find( $user->id );

        $salesmans = $agent->salesmen()->get()->lists('id')->toArray();

//        dd($salesmans);

        if(count($salesmans) > 0) {
            $userIds = array_merge($userIds, $salesmans);
        }

    }


    $openLeads = \App\Models\OpenLeads::
          whereIn('agent_id', $userIds);

//    if (count($request->only('filter'))) {
//        // если фильтр есть
//
//        // получаем данные фильтра
//        $eFilter = $request->only('filter')['filter'];
//
//        if(!empty($eFilter)) {
//            // перебираем данные и проверяем на соответствие
//            foreach ($eFilter as $eFKey => $eFVal) {
//
//                // проверяем ключ
//                switch($eFKey) {
//
//                    // если фильтр по дате
//                    case 'sphere':
//
//                        if($eFVal != '') {
//                            $openLeads = $openLeads->join('leads', function ($join) use ($eFVal) {
//                                $join->on('open_leads.lead_id', '=', 'leads.id')
//                                    ->where('leads.sphere_id', '=', $eFVal);
//                            });
//                        }
//
//                        break;
//                    case 'status':
//
//                        if($eFVal != '') {
//                            $openLeads = $openLeads->where('open_leads.status', '=', $eFVal);
//                        }
//
//                        break;
//                    case 'date':
//                        if($eFVal != 'empty' && $eFVal != '') {
//                            $eFVal = explode('/', $eFVal);
//
//                            $start = trim($eFVal[0]);
//                            $end = trim($eFVal[1]);
//
//                            $openLeads = $openLeads->where('open_leads.created_at', '>=', $start . ' 00:00:00')
//                                ->where('open_leads.created_at', '<=', $end . ' 23:59:59');
//                        }
//                        break;
//                    default: ;
//                }
//            }
//        }
//    }

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

Route::get('settings/create', function () {
    $settings = \App\Facades\Settings::get_settings();

    return view('admin.settings.create', [
        'settings' => $settings,
        'type' => 'tmp'
    ]);
});

Route::post('settings/save', function (Illuminate\Http\Request $request) {
    $locale = App::getLocale();

    $setting = new \App\Models\SettingsSystem();
    $setting->type = $request['type'];
    $setting->name = $request['name'];
    $setting->translateOrNew($locale)->value = $request['value'];
    $setting->translateOrNew($locale)->description = $request['description'];
    $setting->save();
});