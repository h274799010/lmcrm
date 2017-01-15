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
            4 => 'uncertain'
        ];


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