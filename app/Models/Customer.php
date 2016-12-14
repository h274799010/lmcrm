<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model {

    protected $table="customers";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone'
    ];


    public function lead(){
        return $this->hasMany('App\Models\Lead','customer_id', 'id');
    }

    /**
     * Поиск лидов по номеру телефона
     *  В выборку попадают лиды которые еще активны на аукционе или еще находятся на обработке у оператора
     *  и срок ихней жизни еще не прошел
     *
     * Просто получаем список лидов с одним из след. статусов:
     * -> 0 - новый лид в системе
     * -> 1 - лид обрабатывается оператором
     * -> 3 - лид на аукционе
     * И "expiry_time" равным NULL или больше текущей даты
     *
     * @param $sphere_id
     * @return mixed
     */
    public function checkExistingLeads($sphere_id){
        $query = $this->lead()
            ->where('sphere_id', '=', $sphere_id)
            ->where(function ($query) {
                $query->where('expiry_time', '>', Carbon::now())
                    ->orWhereNull('expiry_time');
            })
            ->whereIn('status', array(0, 1, 3));

        return $query;
    }
}
