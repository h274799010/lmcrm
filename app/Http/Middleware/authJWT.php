<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use \Tymon\JWTAuth\Exceptions\TokenInvalidException;
use \Tymon\JWTAuth\Exceptions\TokenExpiredException;

class authJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {

            $user = JWTAuth::parseToken()->toUser();

            // JWTAuth::parseToken()->authenticate()

        } catch (Exception $e) {

            if ($e instanceof TokenInvalidException){

                return response()->json( 'Token is Invalid' );

//                return response()->json(['error'=>'Token is Invalid']);

            }else if ($e instanceof TokenExpiredException){

                return response()->json( 'Token is Expired' );

//                return response()->json(['error'=>'Token is Expired']);

            }else{

                return response()->json( 'Something is wrong' );

//                return response()->json(['error'=>'Something is wrong']);
            }
        }


        return $next($request);
    }
}
