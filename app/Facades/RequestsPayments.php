<?php

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class RequestsPayments extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'requests_payments';
    }
}