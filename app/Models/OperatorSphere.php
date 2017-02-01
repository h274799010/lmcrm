<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

class OperatorSphere extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name','email', 'password',
    ];


    /**
     * Название таблицы
     *
     *
     * @var string
     */
    protected $table = "users";


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function spheres() {
        return $this->belongsToMany('\App\Models\Sphere','operator_sphere','operator_id','sphere_id')->where('status', '=', 1);
    }

    public function accountManagers() {
        return $this->belongsToMany('\App\Models\User','account_managers_operators','operator_id','account_manager_id');
    }
    /**
     * Связь с таблицей лидов (все лиды оператора)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leads(){
        return $this->hasMany('\App\Models\Lead','agent_id','id');
    }

}
