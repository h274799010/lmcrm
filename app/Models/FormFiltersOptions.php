<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormFiltersOptions extends Model
{
    protected $table = 'form_filters_options';

    protected $fillable = ['attr_id', 'name','value', 'icon','position' ];

    public function attribute() {
        return $this->belongsTo('App\Models\SphereFromFilters','id','attr_id');
    }
}
