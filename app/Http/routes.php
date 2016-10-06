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

/** Получение токена */
Route::get('token', function(){

    return csrf_token();
});


/** Залогинивание пользователя */
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


/** Разлогинивание пользователя */
Route::match(['post','options'], 'mobileLogout', function( Request $request ){

    Sentinel::logout();

    return 'Logout';
});


/** Проверка авторизации пользователя */
Route::get('loginTest', function(){

    if( Sentinel::check() ){

        return 'Залогинен';

    }else{

        return 'Не залогинен';
    }
});




Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {

    Route::post('register', 'APIController@register');

    Route::post('login', 'APIController@login');

    Route::group(['middleware' => 'jwt-auth'], function () {

        Route::post('get_user_details', 'APIController@get_user_details');

    });

});
