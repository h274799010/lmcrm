<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}