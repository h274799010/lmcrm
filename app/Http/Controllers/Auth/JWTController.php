<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Sentinel;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Аутентификация по токену
 *
 *
 *
 *
 */
class JWTController extends Controller
{

    /**
     * Залогинивание пользователя по токену
     *
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function login( Request $request )
    {

        // Получение данных агента из реквеста
        $credentials = $request->only('email', 'password');

        try {
            // Попытка авторизации пользователя и получения токена
            if (! $token = JWTAuth::attempt($credentials)) {
                // если ошибка в логине/пароле - вернется сообщение об ошибке
//                return response()->json(['error' => 'invalid_credentials'], 401);
                return response()->json( 'invalid_credentials' );

            }
        } catch (JWTException $e) {
            // если что-то пошло не так, выскочит это сообщение
//            return response()->json(['error' => 'could_not_create_token'], 500);
            return response()->json( 'could_not_create_token' );
        }

        // todo "выкинуть" пользователя если пользователь админ или оператор

//        $user = JWTAuth::parseToken()->authenticate($token);

        $user = JWTAuth::toUser( $token );

        $role = false;

        $user->roles->each(function( $userRole ) use(&$role){

            if( $userRole['slug'] == 'agent' || $userRole['slug'] == 'salesman' ){
                $role = $userRole['slug'];
            }

        });

        if( !$role ){
            return response()->json( 'invalid_credentials' );
        }


//        $user = Sentinel::findUserById(6);
//
//        dd( $user->roles );



        return response()->json( [ 'status' => 'Ok', 'token' => $token ] );
    }


    /**
     * Разлогинивание пользователя
     *
     */
    public function logout()
    {

        // пробуем найти токет
        $token = JWTAuth::getToken();

        // если он есть
        if ($token) {
            // заносим в черный список (другого способа нет ((( )
            JWTAuth::setToken($token)->invalidate();
        }

        // возврат сообщения об успешном разлогинивании
        return response()->json(['Ok']);
    }


}