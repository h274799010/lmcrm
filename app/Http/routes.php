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

Route::get('stat/{user}/{sphere}', function( $user, $sphere ){

//    $masks = \App\Models\UserMasks::all();

    $openLeadsId = \App\Models\OpenLeads::
                          where('agent_id', 3)
                        ->lists('lead_id');

    $leadsId = \App\Models\Lead::
                          whereIn( 'id', $openLeadsId )
                        ->where( 'sphere_id', 1 )
                        ->lists('id');

//    $openLeads = \App\Models\OpenLeads::
//                      whereIn( 'lead_id', $leadsId )
//                    ->where('agent_id', 6 )
//                    ->update( [ 'mask_name_id'=>33 ] );

    $openLeads = \App\Models\OpenLeads::
                          whereIn( 'lead_id', $leadsId )
                        ->where('agent_id', 3 )
                        ->get();

    dd( $openLeads );


    $aaaa = \App\Helper\Statistics::openLeads( $user, $sphere );

//    $aaaa = \App\Helper\Statistics::openLeads( 6, 1, '2017-01-10', '2017-01-24' );


    dd($aaaa);

    $agent = \App\Models\Agent::find(6);
//    $agent = \App\Models\Agent::find(3);


    $agent->statistics();

//    dd($agent->sphereTransitions);
//    dd($agent->history);
    dd($agent->statistics);

    dd($agent);

    return 'ok';

});

