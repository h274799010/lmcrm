<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Aider extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'aider';
    }
}