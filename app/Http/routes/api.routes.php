<?php

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;


/**
 * Роуты мобильного приложения
 *
 */


// Залогинивание по токену
Route::post('login', ['as' => 'jwt.login', 'uses' => 'Auth\JWTController@login']);
// Разлогинивание по токену
Route::post('logout', ['as' => 'jwt.logout', 'uses' => 'Auth\JWTController@logout']);


// todo Регистрация в системе, добавить если нужно
Route::post('register', ['as' => 'jwt.register', 'uses' => 'Auth\JWTController@register']);



/** Страницы для авторизованных пользователей */
Route::group(['middleware' => 'jwt-auth'], function () {


    /** Проверка на авторизацию по токену */ // todo тестовое, удалить если что
    Route::post('mobileLoginTest', function( Request $request ){

        return 'залогинен';

//        try {
//
//            if (! $user = JWTAuth::parseToken()->authenticate()) {
//                return response()->json(['user_not_found'], 404);
//            }
//
//        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
//
//            return response()->json(['token_expired'], $e->getStatusCode());
//
//        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
//
//            return response()->json(['token_invalid'], $e->getStatusCode());
//
//        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
//
//            return response()->json(['token_absent'], $e->getStatusCode());
//
//        }

        // Токен прошел проверку успешно, юзер залогинен
//    return response()->json(compact('user'));
//        return response()->json('user');

    });

    // лиды отфильтрованные для агента
    Route::post('obtain', ['as' => 'api.obtain', 'uses' => 'Agent\ApiController@obtain']);

    // лиды отфильтрованные для агента
    Route::post('obtainNew', ['as' => 'api.obtain.new', 'uses' => 'Agent\ApiController@obtainNew']);

    // лиды, которые агент внес в систему
    Route::post('deposited', ['as' => 'api.deposited', 'uses' => 'Agent\ApiController@deposited']);

    // лиды, которые агент открыл
    Route::post('opened', ['as' => 'api.opened', 'uses' => 'Agent\ApiController@openedLeads']);

    // лиды, которые агент открыл
    Route::post('openLead', ['as' => 'api.open.lead', 'uses' => 'Agent\ApiController@openLead']);


    // данные приватной группы агента по лиду
    Route::post('privateGroup', ['as' => 'api.private.group', 'uses' => 'Agent\ApiController@privateGroup']);


    // создание нового лида
    Route::post('newLead', ['as' => 'api.opened', 'uses' => 'Agent\ApiController@createLead']);

});





