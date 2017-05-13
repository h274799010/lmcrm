<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FcmTokens extends Model
{

    use SoftDeletes;

    /**
     * Таблица
     *
     * @var array
     */
    protected $table="fcm_tokens";


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

}
