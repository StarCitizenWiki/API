<?php declare(strict_types = 1);

namespace App\Providers;

use Carbon\Carbon;
use FilesystemIterator;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class AppServiceProvider
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
        $this->loadMigrations();

        Carbon::setLocale(config('app.locale'));

        if (config('app.debug')) {
            DB::listen(
                function (QueryExecuted $query) {
                    app('Log')::debug($query->sql);
                }
            );
        }
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

        /**
         * Star Citizen Api Interfaces
         */
        $this->app->bind(
            \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface::class,
            \App\Repositories\StarCitizen\Api\v1\Starmap\StarmapRepository::class
        );
    }

    /**
     * Loads migrations in Sub-folders
     */
    private function loadMigrations()
    {
        $dirs = [];
        $directoryIterator = new RecursiveDirectoryIterator(database_path('migrations'), FilesystemIterator::SKIP_DOTS);
        $iteratorIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iteratorIterator as $filename) {
            if ($filename->isDir()) {
                $dirs[] = $filename;
            }
        }

        $dirs = array_sort($dirs);

        $this->loadMigrationsFrom($dirs);
    }
}
