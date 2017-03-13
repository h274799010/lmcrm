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


    $user = Sentinel::findUserById(6);

    dd( $user->roles );


    $u = \App\Models\User::find(6);


    dd($u->roles);



//    dd(
//
////        \App\Models\Lead::find(108)
//        \App\Models\OpenLeads::where('lead_id', 108)->get()
//
//
//    );
//
//
//    $offset = 0;
//
//    $a = \App\Models\Auction::
//          where( 'status', 0 )
//        ->where( 'user_id', 6 )
//        ->orderBy( 'created_at', 'desc' )
//        ->skip( $offset )
//        ->take( PHP_INT_MAX )
//        ->lists( 'id' )
//    ;
//
//
//    $b = $a->splice( 0, 30 );
//
//
//
////    dd( $b );
//
//
//
//    $auctionData = \App\Models\Auction::
//          whereIn('id', $b)
////        ->where( 'user_id', 6 )
//        ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id', 'created_at')
//        ->with(
//            [
//                'lead' => function($query)
//                {
//                    $query
//                        ->select('id', 'opened', 'email', 'sphere_id', 'name', 'created_at')
//                    ;
//                },
////                'sphere' => function($query){
////                    $query
////                        ->select('id', 'name')
////                    ;
////                },
////                'maskName' => function($query){
////                    $query
////                        ->select('id', 'name')
////                    ;
////                }
//            ])
//        ->orderBy('created_at', 'desc')
////        ->orderBy('id')
////        ->latest()
////        ->skip(10)
////        ->take(PHP_INT_MAX)
////        ->take(10)
////        ->offset(1)
////        ->take(10)
////        ->limit(3)
////        ->paginate(10)
////        ->statement()
////        ->select(DB::raw('LIMIT 10,10'))
////                ->offset($offset)
//        ->get()
////        ->lists('id')
//        ->toArray()
//    ;
//
//
//    dd($auctionData);





    $auctionData = \App\Models\Auction::
          where('status', 0)
        ->where( 'user_id', 6 )
        ->select('id', 'lead_id', 'sphere_id', 'mask_id', 'mask_name_id', 'created_at')
        ->with(
            [
                'lead' => function($query)
                {
                    $query
                        ->select('id', 'opened', 'email', 'sphere_id', 'name', 'created_at')
                    ;
                },
                'sphere' => function($query){
                    $query
                        ->select('id', 'name')
                    ;
                },
                'maskName' => function($query){
                    $query
                        ->select('id', 'name')
                    ;
                }
            ])
        ->orderBy('created_at', 'desc')
        ->orderBy('id')
//        ->latest()
        ->skip(5)
//        ->take(PHP_INT_MAX)
        ->take(10)
//        ->offset(1)
//        ->take(10)
//        ->limit(3)
//        ->paginate(10)
//        ->statement()
//        ->select(DB::raw('LIMIT 10,10'))
//                ->offset($offset)
        ->get()
//        ->lists('id')
    ;



    dd($auctionData);



    return 'ok';

});

