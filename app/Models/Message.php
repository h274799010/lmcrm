<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table="messages";

    public function sender()
    {
        return $this->hasOne('App\Models\User', 'id', 'sender_id');
    }
}
