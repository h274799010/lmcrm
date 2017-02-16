<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentSphere extends Model {
    use SoftDeletes;

    protected $table="agent_sphere";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agent_id','sphere_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    public function lead(){
        return $this->hasMany('App\Models\Lead','id','agent_id');
    }

    public function sphere(){
        return $this->hasOne('App\Models\Sphere','id','sphere_id');
    }

}
