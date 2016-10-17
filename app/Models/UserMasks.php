<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMasks extends Model {

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
}