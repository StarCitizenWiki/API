<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\Carbon;
use FilesystemIterator;
use Illuminate\Pagination\Paginator;
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

        Paginator::useBootstrap();
        Carbon::setLocale(config('app.locale'));

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
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
    public function register() {}
}
