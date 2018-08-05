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
