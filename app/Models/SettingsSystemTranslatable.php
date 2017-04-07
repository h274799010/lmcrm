<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class SettingsSystemTranslatable extends Eloquent
{
    protected $table="settings_system_translatable";

    public $timestamps = false;
    protected $fillable = ['description', 'value'];
}
