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
    public static function make( $user, $notice )
    {

        $userNotice = new Notification_users;
        $userNotice->notification_id = $notice->id;
        $userNotice->user_id = $user->id;
        $userNotice->notified = 0;
        $userNotice->save();
        return $userNotice;
    }

    /**
     * Set users notified=1 by $notice
     *
     * @param  object $user
     * @param  object $notice
     * @return object
     */
    public static function takenByNotice( $user, $notice )
    {
        $userNotice = self::where('user_id', '=', $user)->where('notification_id', '=', $notice)->first();
        $userNotice->notified = 1;
        $userNotice->save();

        return $userNotice;
    }


}
