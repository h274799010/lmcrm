<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


class AccountManager extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name','email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function spheres() {
        return $this->belongsToMany('\App\Models\Sphere','account_manager_sphere','account_manager_id','sphere_id');
    }

    public function agents() {
        return $this->belongsToMany('\App\Models\User','account_managers_agents','account_manager_id','agent_id')
            ->select(array('users.id','users.first_name','users.last_name', 'users.email', 'users.created_at', 'users.banned_at'));
    }

    public function agentsAll()
    {
        return $this->belongsToMany('\App\Models\User','account_managers_agents','account_manager_id','agent_id');
    }

    public function operators() {
        return $this->belongsToMany('\App\Models\User','account_managers_operators','account_manager_id','operator_id')
            ->select(array('users.id','users.first_name','users.last_name', 'users.email', 'users.created_at'));
    }

}