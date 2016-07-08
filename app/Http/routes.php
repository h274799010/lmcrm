<?php
Route::group(['prefix' => 'admin'], function() {
    Route::get('sphere/data', 'Admin\SphereController@data');
    Route::get('agent/data', 'Admin\AgentController@data');
    Route::get('user/data', 'Admin\UserController@data');
});
//
Route::group(['prefix' => LaravelLocalization::setLocale(),'middleware' => [ 'web','localeSessionRedirect','localizationRedirect', 'localize']], function() {
    include('routes/front.routes.php');
    include('routes/agent.routes.php');
    include('routes/operator.routes.php');

    include('routes/admin.routes.php');
});

Route::get('/userdata', ['as' => 'userdata', 'middleware' => ['auth', 'agent'], 'uses' => 'UserData@index']);
Route::post('/userdata', ['as' => 'userdata', 'middleware' => ['auth', 'agent'], 'uses' => 'UserData@create']);


