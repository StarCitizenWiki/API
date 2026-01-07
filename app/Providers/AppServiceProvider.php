<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Services\Translation\TranslationService;
use App\View\Composers\AppShellComposer;
use DeepL\Translator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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

        View::composer('layouts.app', AppShellComposer::class);

        Gate::define('access-admin', static function (User $user): bool {
            return $user->is_admin === true;
        });
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
