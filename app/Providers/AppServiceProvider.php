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
use Laravel\Nightwatch\Facades\Nightwatch;
use Laravel\Nightwatch\Records\Query;
use Laravel\Nightwatch\Records\QueuedJob;

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
                ->response(static function (Request $request, array $headers) {
                    return response()->json(['message' => 'Too many reverse image searches. Please try again later.'], 429, $headers);
                });
        });

        RateLimiter::for('similar-image-search', static function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(static function (Request $request, array $headers) {
                    return response()->json(['message' => 'Too many similar image searches. Please try again later.'], 429, $headers);
                });
        });

        RateLimiter::for('search', static function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->ip())
                ->response(static function (Request $request, array $headers) {
                    return response()->json(['message' => 'Too many search requests. Please try again later.'], 429, $headers);
                });
        });

        Nightwatch::rejectQueries(static function (Query $query) {
            return str_contains($query->sql, 'into "jobs"')
                || str_contains($query->sql, 'from "jobs"')
                || str_contains($query->sql, 'from "job_batches"')
                || str_contains($query->sql, 'update "jobs"')
                || str_contains($query->sql, 'update "job_batches"');
        });

        Nightwatch::rejectQueries(static function (Query $query) {
            return str_contains($query->sql, 'from "cache"')
                || str_contains($query->sql, 'into "cache"')
                || str_contains($query->sql, 'from "game_versions"');
        });

        Nightwatch::rejectQueries(static function (Query $query) {
            return $query->sql === 'select exists(select * from "game_starmap_locations" where "slug" = ?) as "exists"' ||
                str_contains($query->sql, 'update "game_mission_data_starmap_location"');
        });

        Nightwatch::rejectQueuedJobs(static function (QueuedJob $job) {
            return str_starts_with($job->name, 'App\Jobs\Game\Import');
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
