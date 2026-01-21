<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Services\Translation\TranslationService;
use App\View\Composers\AppShellComposer;
use DeepL\Translator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        RateLimiter::for('reverse-image-search', static function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('Too many reverse image searches. Please try again later.', 429, $headers);
                });
        });

        RateLimiter::for('similar-image-search', static function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('Too many similar image searches. Please try again later.', 429, $headers);
                });
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
