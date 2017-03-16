<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
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
        $bootstrapModules = [
            'enableCSS' =>  true,
            'enableJS' => true,
        ];

        View::share('bootstrapModules', $bootstrapModules);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Star Citizen API Interfaces
         */
        $this->app->bind('StarCitizen\API\StatsRepository', \App\Repositories\StarCitizen\APIv1\Stats\StatsRepository::class);

        /**
         * Star Citizen Wiki API Interfaces
         */
        $this->app->bind('StarCitizenWiki\API\ShipsRepository', \App\Repositories\StarCitizenWiki\APIv1\Ships\ShipsRepository::class);
    }
}
