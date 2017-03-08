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

        /**
         * Transformers
         */
        $this->app->bind('StarCitizenWiki\Transformer\ShipsListTransformer', \App\Transformers\StarCitizenWiki\Ships\ShipsListTransformer::class);
        $this->app->bind('StarCitizenWiki\Transformer\ShipsTransformer', \App\Transformers\StarCitizenWiki\Ships\ShipsTransformer::class);

        $this->app->bind('StarCitizen\Transformer\StatsTransformer', \App\Transformers\StarCitizen\Stats\StatsTransformer::class);
        $this->app->bind('StarCitizen\Transformer\FundsTransformer', \App\Transformers\StarCitizen\Stats\FundsTransformer::class);
        $this->app->bind('StarCitizen\Transformer\FansTransformer', \App\Transformers\StarCitizen\Stats\FansTransformer::class);
        $this->app->bind('StarCitizen\Transformer\FleetTransformer', \App\Transformers\StarCitizen\Stats\FleetTransformer::class);
    }
}
