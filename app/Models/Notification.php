<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * Новое уведомление
     *
     * Добавление записи в таблицу 'notifications'
     *
     *
     * @param  integer  $sender
     * @param  string  $event
     * @param  string  $message
     * @param  integer  $parent
     *
     * @return object
     */
    public static function make( $sender, $event, $message='', $parent=0 )
    {
        $notice = new Notification;
        $notice->sender_id = $sender;
        $notice->event = $event;
        $notice->message = $message;
        $notice->parent = $parent;
        $notice->save();

        return $notice;
    }
}
