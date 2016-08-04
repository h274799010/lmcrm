<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class dellSphereFiltersOptions extends Model
{
    protected $table = 'sphere_filters_options';

    protected $fillable = ['attr_id','ctype','_type', 'name','value', 'icon','position' ];

    public function attribute() {
        return $this->belongsTo('App\Models\SphereFromFilters','id','attr_id');
    }
}
