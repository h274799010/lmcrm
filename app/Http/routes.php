<?php

use Illuminate\Http\Response;
use Illuminate\Http\Request;
//use Sentinel;

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

Route::get('token', function(){

    header("Access-Control-Allow-Origin: *");

    return csrf_token();
});


Route::match(['post','options'], 'mobileLogin', function( Request $request ){

    $credentials = [
        'email'    => $request['email'],
        'password' => $request['password'],
    ];


    if (Sentinel::authenticate($credentials, true)) {
        return 'залогинен';
    }

    return 'не залогинен';
});



Route::match(['post','options'], 'mobileLogout', function( Request $request ){

    Sentinel::logout();

    return 'Logout';
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

            $.get("http://lmcrm.biz.tm/token", function( data ){

                var token = data;


                $.ajax({
                    url: "http://lmcrm.biz.tm/ffffff",
                    type: "post",
                    data: {
                        _token: token
                    },
                  beforeSend: function(xhr){
                            xhr.setRequestHeader("XSRF-TOKEN","eyJpdiI6IlpPSXJtcm1HeDA5RUp2bkxYNWJpQkE9PSIsInZhbHVlIjoidUhzcTZPbEs5QzBYRlpkU2haamJ0SmlXSU1cL1VaWCtUcjhPcWFpdTFTM3VSS3g5eG5qZmhjM0dRb1ZWSGpcL0lZV0ZzRFdDQTVUS00xNzdaRG5BdUxPdz09IiwibWFjIjoiMmEzZTZkOTM3ZDM0ODdkY2VkNDE4YjQ0MDAwZjAwYzgzODFmZGMxOTRmMDcyYzc1NzY3Yjc3Mjk4MDBlNjg0NSJ9");
                            xhr.setRequestHeader("Accept","application/json");
                  },
                  dataType: "json",
                    success: function (data) {
                        alert(data.resp);
                    }
                });


//                $.post( "http://lmcrm.biz.tm/ffffff", { _token: token }, function( data ){
//    //            $.post( "http://lmcrm.biz.tm/ffffff", {}, function( data ){
//
//
//                    alert(data.resp);
//
//                });



            });



//            $.post( "http://lmcrm.biz.tm/ffffff", { "_token": \'' .$token .'\'}, function( data ){
////            $.post( "http://lmcrm.biz.tm/ffffff", {}, function( data ){
//
//
//                alert(data.resp);
//
//            });
        });
    </script>

    </body>
</html>




    '
    ;

});



Route::match(['post','options'], 'ffffff', function(){

    return response()->json(['resp'=>'дата7']);

} );



Route::get('loginTest', function(){

//        header("Access-Control-Allow-Origin: *");


    if( Sentinel::check() ){


        return 'Залогинен';
    }else{

//        return response()->json(['resp'=>'Не залогинен']);

        return 'Не залогинен';
    }

});

