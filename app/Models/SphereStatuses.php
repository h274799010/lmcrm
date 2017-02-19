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
            1 => 'process',
            2 => 'uncertain',
            3 => 'refuseniks',
            4 => 'bad'
        ];

    const STATUS_TYPE_PROCESS = 1;
    const STATUS_TYPE_UNCERTAIN = 2;
    const STATUS_TYPE_REFUSENIKS = 3;
    const STATUS_TYPE_BAD = 4;
    const STATUS_TYPE_CLOSED_DEAL = 5;


    /**
     * Получение сфер по статусу
     *
     *
     * @return Builder
     */
    public function sphere() {
        return $this->belongsTo('App\Models\Sphere','id','sphere_id');
    }

}