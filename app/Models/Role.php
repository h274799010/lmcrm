<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Role extends Eloquent
{
    use \Dimsav\Translatable\Translatable;

    public $translatedAttributes = ['name', 'description'];
    protected $fillable = ['slug'];
    public $translationModel = 'App\Models\RoleTranslation';

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    // (optionaly)
    // protected $with = ['translations'];
}
