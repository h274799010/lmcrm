<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckRequestPayment extends Model
{
    protected $table = "checks_requests_payments";

    /**
     * Отключаем метки времени
     *
     * @var boolean
     */
    public $timestamps = false;
}
