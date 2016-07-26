<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SphereFromFilters extends Model
{
    protected $table = 'sphere_from_filters';
    protected $fillable = ['_type', 'label','icon','required', 'position' ];

    public function options() {
        return $this->hasMany('App\Models\SphereFiltersOptions','sphere_ff_id','id')->where('ctype','=','agent')->orderBy('position');
    }

    public function sphere() {
        return $this->belongsTo('App\Models\Sphere','id','sphere_id');
    }

    protected static function boot() {
        parent::boot();

        static::deleting(function($attr) { // before delete() method call
            $attr->options()->delete();
        });
    }
}
