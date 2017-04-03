<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helper\Aider;


class AiderProvider extends ServiceProvider
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
        $this->app->bind('aider', function () {
            return new Aider();
        });
    }
}
