<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;


class CreateLead extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'createlead';
    }
}