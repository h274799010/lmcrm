<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification_users extends Model
{
    /**
     * Новое оповещение
     *
     * Добавление записи в таблицу 'notification_users'
     *
     * @param  integer  $user
     * @param  integer  $notice
     *
     * @return object
     */
    public static function make( $user, $notice )
    {
        $userNotice = new Notification_users;
        $userNotice->notification_id = $notice;
        $userNotice->user_id = $user;
        $userNotice->notified = 0;
        $userNotice->save();

        return $userNotice;
    }



    /**
     * Все записи пользователя с пометкой "НЕ уведомлен"
     * Если пользователь не указан, возвращаются все записи с пометкой "НЕ уведомлен"
     *
     *
     * @param integer $userId
     *
     * @return object
     */
    public function notNotified( $userId = NULL )
    {
        // все записи с пометкой "НЕ уведомлен"
        $records = $this->where('notified', '=', '0');

        return ($userId)? $records->where('user_id','=',$userId)->get() : $records;
    }



    /**
     * Помечает заданную запись как "уведомлен"
     *
     * выставляет поле notified = 1
     *
     *
     * @return object
     */
    public function received()
    {
        $this->notified = 1;
        $this->save();

        return $this;
    }
}
