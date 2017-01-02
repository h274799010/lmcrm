<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Данные депозитора каждого лида
 *
 */
class LeadDepositorData extends Model
{

    /**
     * Название таблицы
     *
     * @var string
     */
    protected $table="lead_depositor_data";


    /**
     * Возвращает роли пользователя в нужном формате
     * todo
     */
    public function roles( $type='array' ){

        $rolesArray = unserialize($this->depositor_role);

        if($type=='array'){

            return $rolesArray;
        }else {

            $collectData = collect($rolesArray);

            $roleString = '';

            $collectData->each(function($item) use (&$roleString){

                if($roleString == ''){

                    $roleString = $item;

                }else{

                    $roleString = $roleString .', ' .$item;
                }
            });

            return $roleString;
        }
    }

}
