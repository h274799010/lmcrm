<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// оповещение пользователей
class notification extends Model
{
    // оповещение пользователей по id
    public function noticeById($event, $userId)
    {

    }

    // оповещение группы пользователей, id которых находятся в массиве
    public function noticeByArray($event, $arrUsersId)
    {

    }

}
