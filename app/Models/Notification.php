<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * Add a new record to the table 'notifications'
     *
     * @param  int  $sender
     * @param  string  $event
     * @return object
     */
    public static function make( $sender, $event )
    {
        $notice = new Notification;
        $notice->sender_id = $sender;
        $notice->event = $event;
        $notice->save();
        return $notice;
    }}
