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






/** todo Тестовое удалить */



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


Route::get('postData', function(){

    $token = csrf_token();

    echo
        '
<html>

    <head>

        <title>Тест поста</title>
        <script src="/components/jquery/jquery-2.min.js"></script>

    </head>

    <body>
        получение поста с сервера (только через csrf-токен) <br>

        <script>

        $(function(){

            // изменяем статусы на сервере
            $.post( "/ffffff", { "_token": \'' .$token .'\'}, function( data ){

                alert(data.resp);

            });
        });
    </script>

    </body>
</html>




    '
    ;

});


Route::get('postDataRemote', function(){

    $token = csrf_token();

    echo
        '
<html>

    <head>

        <title>Тест поста</title>
        <script src="/components/jquery/jquery-2.min.js"></script>

    </head>

    <body>
        получение поста с сервера (только через csrf-токен) <br>

        <script>

        $(function(){

            // изменяем статусы на сервере
//            $.post( "/ffffff", { "_token": \'' .$token .'\'}, function( data ){

            $.post( "http://lmcrm.biz.tm/ffffff", { "_token": \'' .$token .'\'}, function( data ){
//            $.post( "http://lmcrm.biz.tm/ffffff", {}, function( data ){


                alert(data.resp);

            });
        });
    </script>

    </body>
</html>




    '
    ;

});



//Route::post('ffffff', function(){
Route::match(['post','options'], 'ffffff', function(){
//    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
//    header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
//    header('Access-Control-Allow-Credentials: true');
    header("Access-Control-Allow-Origin: *");


//    echo 'sdfsdf';
////    echo json_encode('12313');
//    exit;

    return response()->json(['resp'=>'ответ сервера']);
//        ->header('Access-Control-Allow-Origin', '*')
//        ->header('Allow-Control-Allow-Credentials', 'true');

});



//Route::get()