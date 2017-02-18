<?php

namespace App\Providers;

use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
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
            'enableJS' => false,
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
        $this->app->bind('StarCitizen\StatsRepository', StatsRepository::class);
    }
}
