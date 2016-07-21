<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// оповещение пользователей
class Notification extends Model
{

    // добавление новой записи в таблицу notifications
    public static function make( $sender, $event )
    {
        $notice = new Notification;
        $notice->sender_id = $sender;
        $notice->event = $event;
        $notice->save();

        return $notice;
    }

    // добавление записи в таблицу notifications


    // оповещение группы пользователей, id которых находятся в массиве
    public function noticeByArray($event, $arrUsersId)
    {

    }

}
