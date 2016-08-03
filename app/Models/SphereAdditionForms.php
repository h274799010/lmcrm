<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SphereAdditionForms extends Model
{
    protected $table = 'sphere_addition_forms';
    protected $fillable = ['_type', 'label','icon','required', 'position' ];

    /**
     * Возваращает все строки из таблицы AdditionFormsOptions
     *
     */
    public function allFormsOptions() {
        return $this->hasMany('App\Models\AdditionFormsOptions','attr_id','id');
    }

    /**
     * Возваращает все опции из таблицы AdditionFormsOptions в которых _type=option
     *
     */
    public function options() {
        return $this->hasMany('App\Models\AdditionFormsOptions','attr_id','id')->where('_type','=','option')->orderBy('position');
    }

    public function validators() {
        return $this->hasMany('App\Models\AdditionFormsOptions','attr_id','id')->where('_type','=','validate')->orderBy('position');
    }

    public function field() {
        return $this->hasOne('App\Models\AdditionFormsOptions','attr_id','id')->where('_type','=','field');
    }


    public function validatorRules() {
        $validators=array();
        $rules=$this->validators()->get();
        foreach($rules as $rec){
            $validators[$rec->name]=($rec->value)?$rec->value:true;
        }
        return $validators;
    }

    public function sphere() {
        return $this->belongsTo('App\Models\Sphere','id','sphere_id');
    }
}
