<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;

use App\Models\Sphere;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Query;
use PhpParser\Builder;

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

    public function SphereAdditionForms($sphere_id=NULL){
        $relation = $this->hasMany('App\Models\SphereAdditionForms', 'sphere_id', 'sphere_id');

        return ($sphere_id)? $relation->where('sphere_id','=',$sphere_id) : $relation;
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

    /**
     * Возвращает все статусы сферы лида
     *
     * todo доработать
     *
     */
    public function sphereStatuses(){

        $rel = $this->sphere()->with('statuses');

        return $rel;
    }


    public function openLeadStatus(){

        $openLead = $this->hasOne('App\Models\OpenLeads', 'lead_id', 'id');

        return $openLead;

    }


    public function phone(){
        return $this->hasOne('App\Models\Customer','id','customer_id');
    }

    public function obtainedBy($agent_id=NULL){
        $relation=$this->belongsToMany('App\Models\Agent','open_leads','lead_id','agent_id');
        return ($agent_id)? $relation->where('agent_id','=',$agent_id) : $relation;
    }

    public function ownerBill(){
        return $this->hasOne('\App\Models\Credits','agent_id','agent_id');
    }

    // todo метод установки статуса
    public function setStatus( $status )
    {
        $this->status = $status;
        $this->save();

        return $this;

    }

    // todo получение имени статуса
    public function statusName(){
        return $this->hasOne('App\Models\LeadStatus', 'id', 'status');
    }



    public function user(){

        return $this->hasOne('App\Models\Agent', 'id', 'agent_id')->select('id','first_name');

    }


    /**
     * Маска лида
     *
     * todo доработать
     *
     *
     * @return object
     */
    public function bitmask()
    {

        $tableName = 'lead_bitmask_' .$this->sphere_id;

        $mask = DB::table($tableName)->where('user_id', '=', $this->id)->first();

        return $mask;
    }

    public function getIsBadAttribute(){
        $outOfPending = $this->openLeads()->where('pending_time','>',date('Y-m-d H:i:s'))->count();
        $badOPenLeads = $this->openLeads()->where('bad','=',1)->count();
        //$goodOPenLeads = $this->openLeads()->where('bad','=',0)->count();
        //if ($badOPenLeads > $goodOPenLeads)
        if ($this->opened && !$outOfPending) {
            if ($badOPenLeads > $this->opened/2)
            {
                return true;
            }
        }
        return false;
    }


    /**
     * Цена лида по определенной маске агента
     *
     *
     * @param integer $agent_mask_id
     *
     * @return double
     */
    public function price( $agent_mask_id )
    {
        // выбираем таблицу битмаска по id сферы
        $mask = new AgentBitmask( $this->sphere->id );

        // выбираем прайс по заданной маске агента
        $price = $mask->find( $agent_mask_id )->lead_price;

        return $price;
    }

    /**
     * Открытие лида
     *
     * todo доработать пендингТайм
     *
     * @param integer $user_id
     * @param string $comment
     *
     * @return OpenLeads
     */
    public function open( $user_id, $comment='' )
    {

        // максимальное количество открытия лида
        $maxOpen = $this->sphere->openLead;

        // находим открытый лид, по заданным данным
        $openLead = OpenLeads::
              where( 'lead_id', '=', $this->id )
            ->where( 'agent_id', '=',  $user_id )
            ->first();

        if( $openLead ){
            //  если лид уже есть

            // инкрементим count, количество, сколько раз был открыт лид этим агентом
            $openLead->count++;
            $openLead->save();

            // инкрементим opened у лида, (количество открытия лида)
            $this->opened++;
            $this->save();

            // если лид открыт максимальное количество раз
            if( $this->opened >= $maxOpen ){
                // добавляем лиду статус "открыт максимальное количество раз"
                $this->status = 5;
                $this->save();
            }

        }else{
            // если лида нет

            // создаем его и записываем основные данные
            $openLead = new OpenLeads();     // создание лида
            $openLead->lead_id = $this->id;  // id лида
            $openLead->agent_id = $user_id;  // id агента, который его открыл
            $openLead->comment = $comment;   // комментарий (не обазательно)
            $openLead->count = 1;            // количество открытий (при первом открытии = "1")

            // сохраняем
            $openLead->save();

            // инкрементим opened у лида, (количество открытия лида)
            $this->opened++;
            $this->save();

            // если лид открыт максимальное количество раз
            if( $this->opened >= $maxOpen ){
                // добавляем лиду статус "открыт максимальное количество раз"
                $this->status = 5;
                $this->save();
            }
        }

        return $openLead;
    }

    /**
     * Количество максимального открытия лида
     *
     * @return integer
     */
    public function MaxOpenNumber ()
    {
        return $this->sphere->openLead;
    }



    /**
     * Время, после которого лид снимается с аукциона
     *
     * @return string
     */
    public function expiredTime()
    {
        // получение интервала "жизни" лида из сферы лида
        $interval = $this->sphere->expirationInterval();

        // текущее время ( объект DateTime )
        $data = new \DateTime();

        // добавление интервала к времени
        $data->add($interval);

        // перевод времени в формат DB
        $expiredTime = $data->format("Y-m-d H:i:s");

        return $expiredTime;
    }


    /**
     * Возвращает все просроченные к текущему времени лиды
     *
     *
     * @param Query $query
     *
     * @return Builder
     */
    public function scopeExpired( $query )
    {
        return $query->where( 'expiry_time', '<', date("Y-m-d H:i:s") );
    }
}
