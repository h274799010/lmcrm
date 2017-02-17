<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosedDeals extends Model
{
    protected $table = "closed_deals";

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = true;
}
