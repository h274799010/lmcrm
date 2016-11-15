<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorOrganizer extends Model
{

    /**
     * Название таблицы
     *
     *
     * @var string
     */
    protected $table = "operator_organizer";

    /**
     * Устанавливаем поле со временем
     *
     * @var array
     */
    protected $dates = ['time_reminder'];



}
