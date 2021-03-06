<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountManagerSphere extends Model {

    protected $table="account_manager_sphere";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_manager_id','sphere_id'
    ];


    public function spheres() {
        return $this->belongsToMany('\App\Models\Sphere','account_manager_sphere','account_manager_id','sphere_id');
    }


    /**
     * Сферы аккаунт менеджера
     *
     */
    public function sphere() {
        return $this->hasOne('\App\Models\Sphere','id', 'sphere_id');
    }


    /**
     * Данные аккаунт менеджера
     *
     */
    public function accManager(){
        return $this->hasOne('\App\Models\User', 'id', 'account_manager_id');
    }

}
