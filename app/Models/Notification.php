<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// оповещение пользователей
class Notification extends Model
{

    // добавление записи в таблицу notifications


    // оповещение группы пользователей, id которых находятся в массиве
    public function noticeByArray($event, $arrUsersId)
    {

    }

}
