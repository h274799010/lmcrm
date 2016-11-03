<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class SentinelLeadbayerUser
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
        // текущий агент
        $user = Sentinel::getUser();
        // роль, которой агент должен соответствовать
        $role = Sentinel::findRoleBySlug('leadbayer');

        // если такой роли не существует
        if( !$role ){
            // возвращаемся на главную страницу
            return redirect()->route('home');
        }

        // фильтруем все роли агента, находим соответствие заданной роли
        $filteredRoles = $user->roles->filter(function( $userRole ) use ($role){
            // возвращаем роль если id роли агента соответствует id заданной роли
            return $userRole->id == $role->id;
        });

        // если заданная роль не соответствует ни одной роли агента
        if ( $filteredRoles->count() == 0 ) {
            // возвращаемся на главную страницу
            return redirect()->route('home');
        }

        // продолжаем реквест
        return $next($request);
    }
}
