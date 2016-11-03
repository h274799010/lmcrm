<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class SentinelLeadbayerOrDealmakerUser
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
        // роли, котором агент должен соответствовать
        $role[0] = Sentinel::findRoleBySlug('dealmaker');
        $role[1] = Sentinel::findRoleBySlug('leadbayer');


        // если такой роли не существует
        if( !($role[0] && $role[1]) ){
            // возвращаемся на главную страницу
            return redirect()->route('home');
        }

        // фильтруем все роли агента, находим соответствие заданной роли
        $filteredRoles = $user->roles->filter(function( $userRole ) use ($role){
            // возвращаем роль если id роли агента соответствует id заданной роли
            return $userRole->id == $role[0]->id || $userRole->id == $role[1]->id;
        });

        // если заданная роль не соответствует ни одной роли агента
        if ( $filteredRoles->count() == 0 ) {
            return redirect()->route('home');
        }

        // продолжаем реквест
        return $next($request);
    }
}
