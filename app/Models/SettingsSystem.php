<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class SettingsSystem extends Eloquent
{
    use \Dimsav\Translatable\Translatable;

    protected $table="settings_system";

    public $timestamps = false;

    public $translatedAttributes = ['value', 'description'];
    protected $fillable = ['name'];
    public $translationModel = 'App\Models\SettingsSystemTranslatable';

    const TYPE_NUMBER = 'number';
    const TYPE_TEXT = 'text';
    const TYPE_LONGTEXT = 'longtext';

    /**
     * Получаем значение настройки
     *
     * @param $name
     * @return mixed
     */
    public function get_setting($name) {
        $setting = SettingsSystem::where('name', '=', $name)->first();

        if($setting->type == self::TYPE_LONGTEXT) {
            if(empty($setting->description)) {
                $setting->description = $setting->name;
            }

            return $setting->description;
        }
        else {
            if(empty($setting->value)) {
                $setting->value = $setting->name;
                if($setting->type == self::TYPE_NUMBER) {
                    $setting->value = 0;
                }
            }

            return $setting->value;
        }
    }

    /**
     * Возвращаем список всех настроек
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get_settings() {
        $settings = SettingsSystem::all();

        foreach ($settings as $key => $setting) {
            if($setting->type == self::TYPE_LONGTEXT && empty($setting->description)) {
                $setting->description = $setting->name;
            }
            elseif($setting->type == self::TYPE_NUMBER && empty($setting->value)) {
                $setting->value = 0;
            }
            else {
                if(empty($setting->value)) {
                    $setting->value = $setting->name;
                }
            }
        }

        return $settings;
    }
}
