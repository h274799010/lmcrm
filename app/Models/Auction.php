<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AgentBitmask;

class Auction extends Model
{

    protected $table = "auctions";


    /**
     * Атрибуты, для которых разрешено массовое назначение.
     *
     * @var array
     */
    protected $fillable =
        [ 'sphere_id', 'user_id', 'lead_id', 'mask_id' ];

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;



    /**
     * Добавление данных взятые из битмаска агента
     *
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


    public function lead()
    {
        return $this->hasOne('App\Models\Lead', 'id', 'lead_id');
    }

}