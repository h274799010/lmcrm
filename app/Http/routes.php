<?php

use Illuminate\Http\Response;

Route::group(['prefix' => 'admin'], function() {
    Route::get('sphere/data', 'Admin\SphereController@data');
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('user/data', 'Admin\UserController@data');
    Route::get('credit/data', 'Admin\CreditController@data');
});
//
Route::group(['prefix' => LaravelLocalization::setLocale(),'middleware' => [ 'web','localeSessionRedirect','localizationRedirect', 'localize']], function() {
    include('routes/front.routes.php');
    include('routes/agent.routes.php');
    include('routes/operator.routes.php');

    include('routes/admin.routes.php');
});


// метод сообщает об уведомлениях
Route::get('notice', ['as' => 'notification', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notice']);

// в этом месте сообщения отмечаются как полученные
Route::post('notified', ['as' => 'notified', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notified']);


// todo тестовый, удалить
//Route::post('ffffff', function(){
//    //header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
//    //header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
//    //header('Access-Control-Allow-Credentials: true');
//    //header("Access-Control-Allow-Origin: *");
//
//
//
////    echo 'sdfsdf';
//    //echo json_encode('12313');
//    //exit;
//
//    return response()->json(['k'=>'aaa']);
////        ->header('Access-Control-Allow-Origin', '*')
////        ->header('Allow-Control-Allow-Credentials', 'true');
//
//});



Route::get('token', function(){

    header("Access-Control-Allow-Origin: *");

    return csrf_token();

});

Route::post('ffffff', function(){
//Route::match(['options', 'post'], 'ffffff', function(){
//    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
//    header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
//    header('Access-Control-Allow-Credentials: true');
//    header("Access-Control-Allow-Origin: *");

//    echo '
//
//    tcho
//
//
//    ';



//    echo 'sdfsdf';
////    echo json_encode('12313');
//    exit;

    return response()->json(['k'=>'aaa']);
//        ->header('Access-Control-Allow-Origin', '*')
//        ->header('Allow-Control-Allow-Credentials', 'true');

});



//Route::get()