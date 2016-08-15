<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;

use App\Models\Sphere;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

#class Lead extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
#    use Authenticatable, CanResetPassword;
class Lead extends EloquentUser {

    protected $table="leads";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agent_id','sphere_id','name', 'customer_id', 'comment', 'date', 'bad'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    #protected $hidden = [
    #    'password', 'remember_token',
    #];


    public function SphereFormFilters($sphere_id=NULL){
        $relation = $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'sphere_id');

        return ($sphere_id)? $relation->where('sphere_id','=',$sphere_id) : $relation;
    }

    public function sphereAttrByType($type=NULL, $sphere_id=NULL){

        $relation = $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'sphere_id');

        return ($sphere_id and $type)? $relation->where('sphere_id','=',$sphere_id)->where('_type', '=', $type) : $relation;
    }


    // возвращает все поля SphereFromFilters со значением поля label=radio
    public function sAttrRadio($sphere_id=NULL){
        $relation = $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'sphere_id');

        return ($sphere_id)? $relation->where('sphere_id','=',$sphere_id)->where('_type', '=', 'radio') : $relation;
    }

    // возвращает все поля SphereFromFilters со значением поля label=checkbox
    public function sAttrCheckbox($sphere_id=NULL){
        $relation = $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'sphere_id');

        return ($sphere_id)? $relation->where('sphere_id','=',$sphere_id)->where('_type', '=', 'checkbox') : $relation;
    }

    public function openLeads($agent_id=NULL){
        $relation = $this->hasMany('App\Models\OpenLeads', 'lead_id', 'id');

        return ($agent_id)? $relation->where('agent_id','=',$agent_id) : $relation;
    }

    public function sphere(){
        return $this->hasOne('App\Models\Sphere', 'id', 'sphere_id');
    }

    public function info(){
        return $this->hasMany('App\Models\LeadInfoEAV','lead_id','id');
    }

    public function phone(){
        return $this->hasOne('App\Models\Customer','id','customer_id');
    }

    public function obtainedBy($agent_id=NULL){
        $relation=$this->belongsToMany('App\Models\Agent','open_leads','lead_id','agent_id');
        return ($agent_id)? $relation->where('agent_id','=',$agent_id) : $relation;
    }


    /**
     * Выбор маски лида по id сферы
     *
     * Если id сферы не задан
     * вернет данные лида по всем битмаскам
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
                $mask = new LeadBitmask($item->id);
                return $mask->where('user_id', '=', $userId)->first();
            });

            return $allMasks;
        }


        $mask = new LeadBitmask($sphere);

        return $mask->where('user_id', '=', $this->id)->first();
    }

    public function getIsBadAttribute(){
        $badOPenLeads = $this->openLeads()->where('bad','=',1)->count();
        $goodOPenLeads = $this->openLeads()->where('bad','=',0)->count();
        if ($badOPenLeads > $goodOPenLeads)
            return true;
        return false;
    }
}
