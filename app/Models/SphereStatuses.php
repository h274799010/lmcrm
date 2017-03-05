<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class SphereStatuses extends Model
{

    protected $table = 'sphere_statuses';
    protected $fillable = ['stepname', 'type','comment', 'position' ];


    /**
     * Описание типов статусов
     *
     */
    private $statusType =
        [
            0 => 'no_status',
            1 => 'process',
            2 => 'uncertain',
            3 => 'refuseniks',
            4 => 'bad',
            5 => 'closed_deal',
            6 => 'collect_status'
        ];


    /**
     * Номера типов статусов в БД
     *
     */
    const STATUS_TYPE_PROCESS = 1;
    const STATUS_TYPE_UNCERTAIN = 2;
    const STATUS_TYPE_REFUSENIKS = 3;
    const STATUS_TYPE_BAD = 4;
    const STATUS_TYPE_CLOSED_DEAL = 5;


    /**
     * id сборных статусов в таблице статусов
     *
     */
    const STATUS_COLLECTING_PROCESS = 70;
    const STATUS_COLLECTING_UNCERTAIN = 71;
    const STATUS_COLLECTING_REFUSENIKS = 72;
    const STATUS_COLLECTING_BAD = 73;
    const STATUS_COLLECTING_CLOSED_DEAL = 74;


    /** todo раскоментировать при обновлении миграций по системе, а верхнее удалить */
//    const STATUS_COLLECTING_PROCESS = 1;
//    const STATUS_COLLECTING_UNCERTAIN = 2;
//    const STATUS_COLLECTING_REFUSENIKS = 3;
//    const STATUS_COLLECTING_BAD = 4;
//    const STATUS_COLLECTING_CLOSED_DEAL = 5;

    /**
     * Получение сфер по статусу
     *
     *
     * @return Builder
     */
    public function sphere() {
        return $this->belongsTo('App\Models\Sphere','id','sphere_id');
    }


    /**
     * Ф-ция возвращает имена типов статусов сферы
     *
     * @return array
     */
    public static function getStatusTypeName()
    {
        return array(
            self::STATUS_TYPE_PROCESS => 'Process',
            self::STATUS_TYPE_UNCERTAIN => 'Uncertain',
            self::STATUS_TYPE_REFUSENIKS => 'Refuseniks',
            self::STATUS_TYPE_BAD => 'Bad',
            self::STATUS_TYPE_CLOSED_DEAL => 'Close deal',
        );
    }

}