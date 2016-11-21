<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Query\Builder;


class Agent extends EloquentUser implements AuthenticatableContract, CanResetPasswordContract {
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


    public function scopelistAll($query){
        return $query->whereIn('id',\Sentinel::findRoleBySlug('agent')->users()->lists('id'))->select(array('users.id','users.first_name','users.last_name', 'users.email', 'users.created_at', 'users.banned'));
    }


    public function leads(){
        return $this->hasMany('\App\Models\Lead','agent_id','id');
    }


    public function openLead($id){
        return $this->hasOne('\App\Models\OpenLeads','agent_id','id')->where('open_leads.lead_id', '=', $id);
    }


    public function salesmen(){
        return $this->belongsToMany('\App\Models\Salesman','salesman_info','agent_id','salesman_id');
    }


    /**
     * Сферы к которым прикреплен агент
     *
     */
    public function spheres(){
        return $this->belongsToMany('\App\Models\Sphere','agent_sphere','agent_id','sphere_id')->where('status', 1);
    }

    public function accountManagers() {
        return $this->belongsToMany('\App\Models\User','account_managers_agents','agent_id','account_manager_id');
    }


    public function sphere(){
        return $this->spheres()->first();
    }


    public function sphereLink(){
        return $this->hasOne('\App\Models\AgentSphere','agent_id','id');
    }


    public function agentInfo()
    {
        return $this->hasOne('\App\Models\AgentInfo', 'agent_id', 'id');
    }


    /**
     * Список групп агентов
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('\App\Models\AgentGroups', 'agents_groups', 'agent_id', 'group_id');
    }


    /**
     * Кредиты агента
     *
     * todo доработать
     */
    public function wallet(){
        return $this->hasOne('\App\Models\Wallet','user_id','id');
    }

    public function agentSphere()
    {
        return $this->hasMany('\App\Models\AgentSphere', 'agent_id', 'id');
    }


    /**
     * Все маски по всем сферам агента
     *
     *
     * @param  integer  $user_id
     *
     * @return Builder
     */
    public function spheresWithMasks( $user_id=NULL ){

        // id агента
        $agent_id = $user_id ? $user_id : $this->id;

        // находим все сферы агента вместе с масками, которые тоже относятся к агенту
        $spheres = $this
            ->spheres()                                                 // все сферы агента
            ->with(['masks' => function( $query ) use ( $agent_id ){    // вместе с масками
                // маски которые принадлежат текущему агенту
                $query->where( 'user_id', $agent_id );
//                $query->where( 'status', '<>', 0 );

        }]);

        return $spheres;
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
    public function bitmaskAll($sphere_id)
    {
        $mask = new AgentBitmask($sphere_id);

        return $mask->where('user_id', '=', $this->id)->get();
    }
    public function bitmaskAllWithNames($sphere_id)
    {
        $masks = new AgentBitmask($sphere_id);
        $masks = $masks->where('user_id', '=', $this->id)->get();

        foreach ($masks as $key => $mask) {
            $masks[$key]->name = UserMasks::where('user_id', '=', $mask->user_id)->where('mask_id', '=', $mask->id)->first()->name;
        }

        return $masks;
    }

}