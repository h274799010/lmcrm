<?php

namespace App\Models;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


class User extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'name','email', 'password', 'banned'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * Исключенные пользователи из выборки лидов в Obtained leads
     * (например продавци агента)
     *
     * @param $id
     * @return array
     */
    public static function excludedUsers($id) {
        $user = Sentinel::findById($id);

        $excludedUsers = array(0 => $user->id);
        if($user->id && $user->inRole('agent')) {
            $agentSalesman = Agent::find($user->id)->salesmen()->get();

            if($agentSalesman) {
                foreach ($agentSalesman as $salesman) {
                    $excludedUsers[] = $salesman->id;
                }
            }
        } elseif ($user->id && $user->inRole('salesman')) {
            $salesmanAgent = Salesman::find($user->id)->agent()->first();

            $excludedUsers[] = $salesmanAgent->id;

            $agentSalesman = Agent::find($salesmanAgent->id)->salesmen()->get();
            if($agentSalesman) {
                foreach ($agentSalesman as $salesman) {
                    if($salesman->id != $user->id) {
                        $excludedUsers[] = $salesman->id;
                    }
                }
            }
        }

        return $excludedUsers;
    }
}
