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

// проверка авторизации пользователя
Route::post('authCheck', ['as' => 'jwt.logout', 'uses' => 'Auth\JWTController@check']);


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




    // создание нового лида
    Route::post('newLead', ['as' => 'api.opened', 'uses' => 'Agent\ApiController@createLead']);


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



    // данные по сферам и состоянием масок агента
    Route::post('agentSphereMasks', ['as' => 'api.sphere.masks', 'uses' => 'Agent\ApiController@agentSphereMasks']);


    // получение данных по маскам сферы
    Route::post('agentSphereMasksData', ['as' => 'api.sphere.masks.data', 'uses' => 'Agent\ApiController@agentSphereMasksData']);


    // переключение активности маски агентом (включение/выключение)
    Route::post('maskActiveSwitch', ['as' => 'api.mask.active.switch', 'uses' => 'Agent\ApiController@maskActiveSwitch']);


    // создание нового лида
    Route::post('sphereMasksEdit', ['as' => 'api.mask.sphere.edit', 'uses' => 'Agent\ApiController@sphereMasksEdit']);


    // сохранение отредактированной маски
    Route::post('saveMask', ['as' => 'api.save.mask', 'uses' => 'Agent\ApiController@saveMask']);


    // создание новой маски
    Route::post('saveNewMask', ['as' => 'api.save.new.mask', 'uses' => 'Agent\ApiController@createMask']);


    // создание новой маски
    Route::post('dellMask', ['as' => 'api.dell.mask', 'uses' => 'Agent\ApiController@dellMask']);


    // смена статуса открытого лида
    Route::post('changeOpenLeadStatus', ['as' => 'api.change.status', 'uses' => 'Agent\ApiController@changeOpenLeadStatus']);


    // смена статуса открытого лида
    Route::post('getOrganizerData', ['as' => 'api.organizer.data', 'uses' => 'Agent\ApiController@getOrganizerData']);


    // получение всех салесманов агента
    Route::post('getAllSalesmen', ['as' => 'api.all.salesmen', 'uses' => 'Agent\ApiController@getAllSalesmen']);


    // обновление прав салесмана агентом
    Route::post('permissionsUpdate', ['as' => 'api.salesmen.permissions.update', 'uses' => 'Agent\ApiController@permissionsUpdate']);


    // создание нового салесмана агентом
    Route::post('createSalesmen', ['as' => 'api.create.salesmen', 'uses' => 'Agent\ApiController@createSalesmen']);


    // обновление данных салесмана агентом
    Route::post('updateSalesmenData', ['uses' => 'Agent\ApiController@updateSalesmenData']);

    // получение сфер пользователя
    Route::post('getSpheres', ['uses' => 'Agent\ApiController@getSpheres']);

    // получение участников из группы пользователя
    Route::post('getGroupMembers', ['uses' => 'Agent\ApiController@getGroupMembers']);

    // получение данных статистики
    Route::post('getStatistic', ['uses' => 'Agent\ApiController@getStatistic']);

    // получение кредитной истории агента
    Route::post('getCredits', ['uses' => 'Agent\ApiController@getCredits']);

    // создание запроса на ввод денег
    Route::post('createReplenishment', ['uses' => 'Agent\ApiController@createReplenishment']);

    // оплата запроса на ввод денег
    Route::post('payReplenishment', ['uses' => 'Agent\ApiController@payReplenishment']);

    // сохранение fcm токена
    Route::post('registerFcmToken', ['uses' => 'Agent\ApiController@registerFcmToken']);

});





