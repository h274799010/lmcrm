<?php

/***************    Site routes  **********************************/
Route::get('/', ['as' => 'home', 'middleware'=>[ 'redirectAdmin', 'redirectAgent', 'redirectSalesman', 'redirectOperator', 'redirectAccountManager' ], 'uses' => 'Frontend\HomeController@index']);
Route::get('home', 'Frontend\HomeController@index');
Route::get('crone', 'Frontend\CroneController@index');

# Authentication
Route::get('/auth/login', ['as' => 'login', 'middleware' => ['guest'], 'uses' => 'Auth\SessionsController@create']);

Route::get('/auth/register', ['as' => 'register', 'middleware' => ['guest'], 'uses' => 'Auth\RegisterController@register']);
Route::post('/auth/registerStepOne', ['as' => 'register.stepOne', 'middleware' => ['guest'], 'uses' => 'Auth\RegisterController@registerStepOne']);
Route::get('registerStepTwo', ['as' => 'agent.registerStepTwo','middleware' => ['auth'], 'uses' => 'Auth\RegisterController@registerStepTwo']);
Route::post('/auth/registerStepTwo', ['as' => 'register.put', 'middleware' => ['auth'], 'uses' => 'Auth\RegisterController@putUser']);
Route::get('/auth/activation/{user_id}/{code}', ['as' => 'activation.link', 'middleware' => ['guest'], 'uses' => 'Auth\RegisterController@activation']);
Route::post('/auth/activation', ['as' => 'activation', 'middleware' => ['guest'], 'uses' => 'Auth\RegisterController@activation']);
Route::post('/auth/sendActivationCode', ['as' => 'sendActivationCode', 'middleware' => ['guest'], 'uses' => 'Auth\RegisterController@sendActivationCode']);

Route::get('/auth/logout', ['as' => 'logout', 'uses' => 'Auth\SessionsController@destroy']);
Route::any('/auth/store', ['as' => 'auth.store', 'uses' => 'Auth\SessionsController@store']);
Route::any('/auth/create', ['as' => 'auth.create', 'uses' => 'Auth\SessionsController@create']);
Route::any('/auth/destroy', ['as' => 'auth.destroy', 'uses' => 'Auth\SessionsController@destroy']);
//Route::resource('/auth', 'Auth\SessionsController', ['only' => ['create', 'store', 'destroy']]);

# Registration
/*
Route::group(['middleware' => 'guest'], function () {
    Route::get('/auth/register', ['as' => 'registration.form', 'uses' => 'RegistrationController@create']);
    Route::post('/auth/register', ['as' => 'registration.store', 'uses' => 'RegistrationController@store']);
});
*/
# Forgotten Password
/*
Route::group(['middleware' => 'guest'], function () {
    Route::get('forgot_password', 'Auth\PasswordController@getEmail');
    Route::post('forgot_password', 'Auth\PasswordController@postEmail');
    Route::get('reset_password/{token}', 'Auth\PasswordController@getReset');
    Route::post('reset_password/{token}', 'Auth\PasswordController@postReset');
});
*/
