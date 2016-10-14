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
    protected $table="mask_names";

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;
}