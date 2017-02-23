<?php
/**
 * Created by PhpStorm.
 * User: Viacheslav
 * Date: 23.02.2017
 * Time: 15:28
 */

namespace App\Console\Commands;

use App\Models\Notification_users;
use Illuminate\Console\Command;

class SendMessages extends Command
{
    protected $message_id;

    public function __construct($message_id)
    {
        $this->message_id = $message_id;
    }

    public function handle()
    {
        $message_id = $this->message_id;

        $userNotic = Notification_users::find($message_id);
        $userNotic->notified = 0;
        $userNotic->save();
    }
}