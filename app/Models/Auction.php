<?php


namespace App\Models;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AgentBitmask;
use App\Models\LeadBitmask;
use MongoDB\Driver\Query;

class Auction extends Model
{
    use SoftDeletes;

    protected $table = "auctions";


    /**
     * Атрибуты, для которых разрешено массовое назначение.
     *
     * @var array
     */
    protected $fillable = [ 'sphere_id', 'user_id', 'lead_id', 'mask_id' ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;



    /**
     * Связь с таблицей лидов
     *
     * @return Query
     */
    public function lead()
    {
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }





    /**
     * Добавление данных взятые из битмаска агента
     *
     * todo доработать
     *
     * @param  AgentBitmask  $agentsBitmask
     * @param  integer  $sphere_id
     * @param  integer  $lead_id
     *
     * @return boolean
     */
    public static function addFromBitmask( $agentsBitmask, $sphere_id, $lead_id )
    {
        // переменная запроса
        $query = [];

        // перебираем всех агентов и добавляем данные в таблицу
        $agentsBitmask->each( function( $agent ) use( &$query, $sphere_id, $lead_id ){

            // формируем запрос
            $query[] = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead_id, 'user_id'=>$agent['user_id'], 'mask_id'=>$agent['id'] ];

        });

        // делаем запрос (записываем данные в таблицу аукциона)
        return Auction::insert( $query );
    }


    /**
     * Добавление данных взятые из битмаска агента
     *
     *
     * @param  integer  $mask_id
     * @param  integer  $sphere_id
     *
     * @return boolean
     */
    public static function addByAgentMask( $mask_id, $sphere_id )
    {

        // конструктор битмаска агента
        $agentBitmask = new AgentBitmask( $sphere_id );

        // конструктор битмаска лида
        $leadBitmask = new LeadBitmask( $sphere_id );

        // выбираем маску
        $agentBitmask = $agentBitmask->find( $mask_id );

        // короткая маска лида ("ключ"=>"значение")
        $agentBitmaskData = $agentBitmask->findFbMaskById();

        // id всех лидов по фильтру
        $list = $leadBitmask->filterByMask( $agentBitmaskData )->lists('user_id');

        // массив id пользователей, по которым нужно исключить выбор лидов
        $excludedUsers = User::excludedUsers($agentBitmask['user_id']);

        // получаем все лиды, помеченные к аукциону, по id из массива, без лидов автора
        $leadsByFilter =
            Lead::
              whereIn('id', $list)                     // все лиды полученыые по маске агента
            ->where('status', 3)                       // котрые помеченны к аукциону
            //->where('agent_id', '<>', $agentBitmask['user_id'])      // без лидов, которые занес агент
            ->whereNotIn('agent_id', $excludedUsers)  // без лидов, которые занес агени и его продавцы
            ->get();



        // переменная запроса
        $query = [];

        // перебираем всех агентов и добавляем данные в таблицу
        $leadsByFilter->each( function( $lead ) use( &$query, $sphere_id, $agentBitmask ){

            // формируем запрос
            $query[] = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead['id'], 'user_id'=>$agentBitmask['user_id'], 'mask_id'=>$agentBitmask['id'] ];

        });

        if( count($query)>0 ){
            // делаем запрос (записываем данные в таблицу аукциона)
            return Auction::insert( $query );
        }

        return false;
    }


    /**
     * Удаление данных по id лида
     *
     *
     * @param  integer  $lead_id
     *
     * @return boolean
     */
    public static function removeByLead( $lead_id )
    {
        Auction::where( 'lead_id', $lead_id)->update(['status' => 1]);
        return Auction::where( 'lead_id', $lead_id)->delete();
    }


    /**
     * Удаление данных по id сферы и id маски
     *
     *
     * @param  integer  $sphere_id
     * @param  integer  $mask_id
     *
     * @return boolean
     */
    public static function removeBySphereMask( $sphere_id, $mask_id)
    {
        Auction::where( 'sphere_id', $sphere_id)->where( 'mask_id', $mask_id)->update(['status' => 1]);
        return Auction::where( 'sphere_id', $sphere_id)->where( 'mask_id', $mask_id)->delete();
    }
}