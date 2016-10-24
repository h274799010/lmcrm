<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadsStatusDetails extends Model
{

    /**
     * Подключаем таблицу из БД
     *
     * @var string
     */
    protected $table = "leads_status_details";

    /**
     * Отключаем временные метки
     *
     * @var boolean
     */
    public $timestamps = false;


    /**
     * Сохранение данных по статистике в таблицу
     *
     */
    public static function add(){

    }

}
