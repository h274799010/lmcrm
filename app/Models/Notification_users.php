<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification_users extends Model
{

    /**
    * Add a new record to the table 'notification_users'
    *
    * @param  object  $notice
    * @param  object  $agent
    * @return object
    */
    public static function make( $notice, $agent )
    {
        $user = new Notification_users;
        $user->notification_id = $notice->id;
        $user->user_id = $agent->id;
        $user->time = date("Y-m-d H:i:s");
        $user->save();

        return $user;
    }

}
