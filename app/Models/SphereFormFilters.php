<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SphereFormFilters extends Model
{
    protected $table = 'sphere_form_filters';
    protected $fillable = ['_type', 'label','icon','required', 'position' ];

    public function options() {
        return $this->hasMany('App\Models\FormFiltersOptions','attr_id','id')->orderBy('position');
    }

    public function filterOptions() {
        return $this->hasMany('App\Models\FormFiltersOptions','attr_id','id')->orderBy('position');
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
