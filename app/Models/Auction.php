<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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




    public static function addFromBitmask( $agentsBitmask, $sphere_id, $lead_id )
    {

        $query = [];

        $agentsBitmask->each( function( $agent ) use( &$query, $sphere_id, $lead_id ){

            $query[] = [ 'sphere_id'=>$sphere_id, 'lead_id'=>$lead_id, 'user_id'=>$agent['user_id'], 'mask_id'=>$agent['id'] ];

        });

        return Auction::insert( $query );
    }


}