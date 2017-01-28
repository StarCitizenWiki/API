<?php

namespace App\Providers;

use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('StarCitizen\StatsAPI', StatsRepository::class);
    }
}
