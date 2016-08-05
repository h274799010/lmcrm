<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionFormsOptions extends Model
{
    protected $table = 'addition_forms_options';

    protected $fillable = ['attr_id', '_type', 'name','value', 'icon','position' ];

    public function attribute() {
        return $this->belongsTo('App\Models\SphereAdditionForms','id','attr_id');
    }
}
