<?php declare(strict_types = 1);

namespace App\Providers;

use Carbon\Carbon;
use FilesystemIterator;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

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

        if (config('app.debug') && config('app.env') === 'local') {
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
    }

    /**
     * Loads migrations in Sub-folders
     */
    private function loadMigrations()
    {
        $directoryIterator = new RecursiveDirectoryIterator(database_path('migrations'), FilesystemIterator::SKIP_DOTS);
        $migrationDirectories = new RecursiveIteratorIterator(
            $directoryIterator, RecursiveIteratorIterator::SELF_FIRST
        );
        $migrationDirectories = collect($migrationDirectories);

        $migrationDirectories->filter(
            function (SplFileInfo $filename) {
                return $filename->isDir();
            }
        );

        $this->loadMigrationsFrom($migrationDirectories->toArray());
    }
}
