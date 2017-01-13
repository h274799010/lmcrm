<?php

namespace App\Providers;

use App\Helper\CreateLead;
use App\Models\Lead;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class LeadServiceProvider extends ServiceProvider
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
        App::bind('lead', function () {
            return new Lead();
        });
        $this->app->bind('createlead', function () {
            return new CreateLead();
        });
    }
}
