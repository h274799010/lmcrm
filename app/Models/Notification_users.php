<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification_users extends Model
{
    /**
     * Add a new record to the table 'notification_users'
     *
     * @param  object $user
     * @param  object $notice
     * @return object
     */
    public static function make($user, $notice)
    {

        $userNotice = new Notification_users;
        $userNotice->notification_id = $notice->id;
        $userNotice->user_id = $user->id;
        $userNotice->save();
        return $userNotice;
    }
}
