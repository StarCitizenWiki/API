<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\Carbon;
use FilesystemIterator;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use URL;

/**
 * Class AppServiceProvider.
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

        if ('production' === config('app.env')) {
            URL::forceScheme('https');
        }

        if (config('app.debug') && 'local' === config('app.env')) {
            DB::listen(
                static function (QueryExecuted $query) {
                    //app('Log')::debug($query->sql);
                }
            );
        }
    }

    /**
     * Loads migrations in Sub-folders.
     */
    private function loadMigrations()
    {
        $directoryIterator = new RecursiveDirectoryIterator(database_path('migrations'), FilesystemIterator::SKIP_DOTS);
        $migrationDirectories = new RecursiveIteratorIterator(
            $directoryIterator,
            RecursiveIteratorIterator::SELF_FIRST
        );
        $migrationDirectories = collect($migrationDirectories);

        $migrationDirectories->filter(
            function (SplFileInfo $filename) {
                return $filename->isDir();
            }
        );

        $this->loadMigrationsFrom($migrationDirectories->toArray());
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
