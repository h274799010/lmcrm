<?php

namespace App\Providers;

use App\Helper\RequestsPayments;
use Illuminate\Support\ServiceProvider;

class RequestsPaymentsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('requests_payments', function () {
            return new RequestsPayments();
        });
    }
}
