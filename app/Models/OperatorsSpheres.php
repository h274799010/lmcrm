<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorsSpheres extends Model
{

    protected $table="operator_sphere";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'operator_id','sphere_id'
    ];

    public function sphere(){
        return $this->hasOne('App\Models\Sphere','id','sphere_id');
    }
}
