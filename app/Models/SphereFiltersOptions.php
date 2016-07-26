<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SphereFiltersOptions extends Model
{
    protected $table = 'sphere_filters_options';

    protected $fillable = ['sphere_ff_id','ctype','_type', 'name','value', 'icon','position' ];

    public function attribute() {
        return $this->belongsTo('App\Models\SphereFromFilters','id','sphere_ff_id');
    }
}
