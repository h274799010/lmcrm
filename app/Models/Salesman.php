<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


class Salesman extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
    use Authenticatable, CanResetPassword;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name','name','email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function info(){
        return $this->hasOne('App\Models\SalesmanInfo','salesman_id','id');
    }

    public function agent(){
        return $this->belongsToMany('\App\Models\Agent','salesman_info','salesman_id','agent_id');
    }

    public function leads(){
        return $this->hasMany('\App\Models\Lead','agent_id','id');
    }

    public function spheres(){
        return $this->belongsToMany('\App\Models\Sphere','salesman_info','salesman_id','sphere_id');
    }

    public function sphere(){
        return $this->spheres()->first();
    }

    public function wallet(){
        return $this->belongsToMany('\App\Models\Wallet','salesman_info','salesman_id','wallet_id');
    }

    public function getNameAttribute(){
        return $this->attributes['first_name'].' '.$this->attributes['last_name'];
    }


    /**
     * Выбор маски пользователя по id сферы
     *
     * Если индекс сферы не задан
     * вернет данные пользователя по всем битмаскам
     *
     *
     * @param  integer  $sphere
     *
     * @return object
     */
    public function bitmask($sphere=NULL)
    {

        // если сфера не заданна
        if(!$sphere){

            // находим все сферф
            $spheres = Sphere::all();
            // получаем id юзера
            $userId = $this->id;

            // перебираем все сферы и выбираем из каждой данные юзера
            $allMasks = $spheres->map(function($item) use ($userId){
                $mask = new AgentBitmask($item->id);
                return $mask->where('user_id', '=', $userId)->first();
            });

            return $allMasks;
        }


        $mask = new AgentBitmask($sphere);

        return $mask->where('user_id', '=', $this->id)->first();
    }

}