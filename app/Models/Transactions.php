<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model {
    public $timestamps = false;
    protected $table="transactions";
    protected $fillable = [
        'created_at','status'
    ];
//    public static function create($attributes){
//        $attributes['created_at'] = Date('Y-m-d H:i:s');
//        self::create($attributes);
//    }
}
