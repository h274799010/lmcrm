<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Sphere extends Model
{
    protected $table = 'spheres';

    protected $fillable = ['name','openLead','minLead','table_name' ,'status'];

    public function scopeActive( $query, $status = true) {
        return $query->where('status','=',($status)?true:false);
    }


    /**
     * Получение данных фильтра сферы
     *
     *
     * todo позже удалить
     * todo в объекте есть похожее свойство которое можно перепутать
     *
     */
    public function attributes() {
        return $this->hasMany('App\Models\SphereFormFilters','sphere_id','id')->orderBy('position');
    }


    /**
     * Получение данных фильтра сферы
     *
     *
     */
    public function filterAttr() {
        return $this->hasMany('App\Models\SphereFormFilters', 'sphere_id', 'id')->orderBy('position');
    }


    /**
     * Примечания по сфере
     *
     *
     */
    public function additionalNotes() {
        return $this->hasMany('App\Models\SphereAdditionalNotes', 'sphere_id', 'id');
    }


    /**
     * Получение дополнительных данных по сфере
     *
     *
     */
    public function leadAttr() {
        return $this->hasMany('App\Models\SphereAdditionForms','sphere_id','id')->orderBy('position');
    }

    /**
     * Все лиды сферы
     *
     */
    public function leads(){
        return $this->hasMany('App\Models\Lead','sphere_id', 'id');
    }

    /**
     * Все лиды, которые должны пройти проверку оператора
     *
     * лиды со статусом "2"
     *
     */
    public function leadsFoOperator(){
        return $this->hasMany('App\Models\Lead','sphere_id', 'id')->where('status', '=', 2);
    }

    public function statuses() {
        return $this->hasMany('App\Models\SphereStatuses','sphere_id','id')->orderBy('position');
    }

    public function agents(){
        return $this->hasManyThrough('\App\Models\Agent','\App\Models\AgentSphere','sphere_id','agent_id');
    }

    public function agentsAll(){
        return $this->belongsToMany('\App\Models\Agent','agent_sphere','sphere_id','agent_id');
    }

    public function accountManagers(){
        return $this->belongsToMany('\App\Models\AccountManager','account_manager_sphere','sphere_id','account_manager_id');
    }


    /**
     * Все маски по сфере из таблицы UserMasks
     *
     *
     * @param  integer  $user_id
     *
     * @return Builder
     */
    public function masks( $user_id=NULL ){

        // связь таблицы сферы с таблицей UserMasks
        $relation = $this->hasMany('App\Models\UserMasks', 'sphere_id', 'id');

        // если задан пользователь возвращается только маски пользователя
        // если нет - возвращаются все маски по сфере
        return $user_id ? $relation->where('user_id', $user_id) : $relation;
    }


    protected static function boot() {
        parent::boot();

        static::deleting(function($group) { // before delete() method call

            // выбираем все атрибуты (атрибуты агента)
            $attributes = $group->attributes();
            // удаляем опции всех атрибутов
            $attributes->each(function($attr){ $attr->options()->delete(); });
            // удаление атрибутов агента
            $attributes->delete();

            // выбираем все атрибутв лида
            $leadAttr = $group->leadAttr();
            // удаляем опции всех атрибутов лида
            $leadAttr->each(function($attr){ $attr->options()->delete(); });
            //  удаление атрибутов лида
            $leadAttr->delete();

            $group->statuses()->delete();

        });
    }




    /**
     * Временной интервал, после которого лид снимается с аукциона
     *
     * метод преобразует интервал из DB в объект DateInterval
     *
     * todo удалить, есть метод проще
     *
     * сам период храниты в DB в формате string
     * в поле "expiration_interval"
     *
     * Формат периода:
     *      09-10 2:35:00
     *      month-day hour:min:sec
     *
     *
     * @return \DateInterval
     */
    public function expirationInterval()
    {
        // данные поля из БД
        $intervalFromDB = $this->expiration_interval;

        // преобразование интервала в массив [ дата, время ]
        $intervalArray = explode( ' ', $intervalFromDB );

        // преобразование даты в массив [ месяц, день ]
        $data = explode( '-', $intervalArray[0] );
        // преобразование времени в массив [ час, минуты, секунды ]
        $time = explode( ':', $intervalArray[1] );

        // Преобразование полученных данных в интервал для объекта DateInterval
        $intervalString = 'P' .$data[0] .'M' .$data[1] .'DT' .$time[0] .'H' .$time[1] .'M';

        // вычисление интервала
        $interval = new \DateInterval( $intervalString );

        return $interval;
    }

}