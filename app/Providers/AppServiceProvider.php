<?php declare(strict_types = 1);

namespace App\Providers;

use Hashids\Hashids;
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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $adminAuthImpl = \App\Repositories\StarCitizenWiki\Auth\AuthRepository::class;

        switch ($this->app->environment()) {
            case 'local':
                $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
                $this->app->register(\Hesto\MultiAuth\MultiAuthServiceProvider::class);
                $adminAuthImpl = \App\Repositories\StarCitizenWiki\Auth\AuthRepositoryStub::class;
                break;

            case 'testing':
                $adminAuthImpl = \App\Repositories\StarCitizenWiki\Auth\AuthRepositoryStub::class;
                break;

            case 'production':
                break;

            default:
                break;
        }

        $this->app->bind(
            \App\Repositories\StarCitizenWiki\Interfaces\AuthRepositoryInterface::class,
            $adminAuthImpl
        );

        $this->app->singleton(
            Hashids::class,
            function () {
                return new Hashids(ADMIN_INTERNAL_PASSWORD, 8);
            }
        );

        /**
         * Star Citizen Api Interfaces
         */
        $this->app->bind(
            \App\Repositories\StarCitizen\Interfaces\StatsRepositoryInterface::class,
            \App\Repositories\StarCitizen\ApiV1\StatsRepository::class
        );
        $this->app->bind(
            \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface::class,
            \App\Repositories\StarCitizen\ApiV1\StarmapRepository::class
        );

        /**
         * Star Citizen Wiki Api Interfaces
         */
        $this->app->bind(
            \App\Repositories\StarCitizenWiki\Interfaces\ShipsRepositoryInterface::class,
            \App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository::class
        );
    }
}
