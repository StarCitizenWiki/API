<?php

namespace App\Providers;

use App\Services\Translation\TranslationService;
use DeepL\Translator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Translator::class, function () {
            return new Translator(config('services.deepl.auth_key'));
        });

        $this->app->singleton(TranslationService::class);
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
