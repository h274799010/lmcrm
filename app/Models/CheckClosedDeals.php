<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckClosedDeals extends Model
{
    protected $table = "checks_closed_deals";

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;
}
