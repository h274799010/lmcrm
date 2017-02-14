<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helper\Statistics;

class StatisticProvider extends ServiceProvider
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
        $this->app->bind('statistic', function () {
            return new Statistics();
        });
    }
}
