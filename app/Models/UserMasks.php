<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AgentBitmask;

class UserMasks extends Model {
    use SoftDeletes;

    /**
     * Название таблицы
     *
     *
     * @var string
     */
    protected $table="user_masks";

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     * Получение битовой карты маски
     *
     */
    public function getBitmask()
    {

        // выбираем id сферы
        $sphereId = $this->sphere_id;

        // выбираем id маски
        $maskId = $this->mask_id;

        // создание объекта битмаска агента
        $bitmask = new AgentBitmask( $sphereId);

        // получение битмаска маски
        $bitmask = $bitmask->where( 'id',  $maskId)->first();

        // добавление битмаска в модель
        $this->bitmask = $bitmask;

        return $bitmask;
    }

}