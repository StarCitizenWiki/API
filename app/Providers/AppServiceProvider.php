<?php declare(strict_types = 1);

namespace App\Providers;

use Hashids\Hashids;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
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
            'enableCSS' => true,
            'enableJS'  => true,
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
        if ('production' !== $this->app->environment()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register('Hesto\MultiAuth\MultiAuthServiceProvider');
        }

        $this->app->bind(
            Hashids::class,
            function () {
                return new Hashids(ADMIN_INTERNAL_PASSWORD, 5);
            }
        );

        /**
         * Star Citizen API Interfaces
         */
        $this->app->bind('StarCitizen\API\StatsRepository', \App\Repositories\StarCitizen\ApiV1\StatsRepository::class);

        /**
         * Star Citizen Wiki API Interfaces
         */
        $this->app->bind(
            'StarCitizenWiki\API\ShipsRepository',
            \App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository::class
        );
    }
}
