<?php

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

Route::group(['prefix' => 'admin'], function() {
    Route::get('sphere/data', 'Admin\SphereController@data');
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('user/data', 'Admin\UserController@data');
    Route::get('credit/data', 'Admin\CreditController@data');
});


// роуты для разных групп пользователей
Route::group(['prefix' => LaravelLocalization::setLocale(),'middleware' => [ 'web','localeSessionRedirect','localizationRedirect', 'localize']], function() {
    include('routes/front.routes.php');
    include('routes/agent.routes.php');
    include('routes/operator.routes.php');
    include('routes/admin.routes.php');
});


// Роуты мобильного приложения
Route::group(['prefix' => 'api'], function(){
    include ('routes/api.routes.php');
});


// метод сообщает об уведомлениях
Route::get('notice', ['as' => 'notification', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notice']);

// в этом месте сообщения отмечаются как полученные
Route::post('notified', ['as' => 'notified', 'middleware' => ['auth', 'agent|salesman'], 'uses' => 'NoticeController@notified']);



/** todo Тестовое удалить */

// Тестовая страница для проверки JWT токена
Route::get('loginTestToken', [ 'uses' => 'Agent\ApiController@test' ]);


