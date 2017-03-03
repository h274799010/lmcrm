<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPhones extends Model
{
    protected $table = 'user_phones';

    protected $fillable = [
        'user_id'
    ];

    /**
     * Получаем данные пользователя которому пренадлежит номер телефона
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id', 'id');
    }
}
