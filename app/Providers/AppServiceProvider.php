<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $paths = $this->allMigrationDirectories(database_path('migrations'));

        $this->loadMigrationsFrom($paths);
    }

    /**
     * Recursively collect all directories under the given folder.
     */
    protected function allMigrationDirectories(string $dir): array
    {
        $dirs = [$dir];

        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir.DIRECTORY_SEPARATOR.$item;

            if (is_dir($path)) {
                $dirs = array_merge($dirs, $this->allMigrationDirectories($path));
            }
        }

        return array_unique($dirs);
    }
}
