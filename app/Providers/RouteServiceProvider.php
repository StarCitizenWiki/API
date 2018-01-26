<?php declare(strict_types = 1);

namespace App\Providers;

use App\Helpers\Hasher;
use App\Models\Notification;
use App\Models\ShortUrl\ShortUrl;
use App\Models\ShortUrl\ShortUrlWhitelist;
use App\Models\User;
use Hashids\HashidsException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class RouteServiceProvider
 * @package App\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $idResolver = function ($id) {
            try {
                return Hasher::decode($id);
            } catch (HashidsException $e) {
                throw new BadRequestHttpException();
            }
        };

        Route::bind(
            'user',
            function ($value) use ($idResolver) {
                return User::query()->where('id', $idResolver($value))->firstOrFail();
            }
        );

        Route::bind(
            'user_with_trashed',
            function ($value) use ($idResolver) {
                return User::query()->withTrashed()->where('id', $idResolver($value))->firstOrFail();
            }
        );

        Route::bind(
            'notification',
            function ($value) use ($idResolver) {
                return Notification::query()->where('id', $idResolver($value))->firstOrFail();
            }
        );

        Route::bind(
            'notification_with_trashed',
            function ($value) use ($idResolver) {
                return Notification::query()->withTrashed()->where('id', $idResolver($value))->firstOrFail();
            }
        );

        Route::bind(
            'url',
            function ($value) use ($idResolver) {
                return ShortUrl::query()->where('id', $idResolver($value))->firstOrFail();
            }
        );

        Route::bind(
            'url_hash',
            function ($value) use ($idResolver) {
                return ShortUrl::query()->where('hash', $value)->firstOrFail();
            }
        );

        Route::bind(
            'url_with_trashed',
            function ($value) use ($idResolver) {
                return ShortUrl::query()->withTrashed()->where('id', $idResolver($value))->firstOrFail();
            }
        );

        Route::bind(
            'whitelist_url',
            function ($value) use ($idResolver) {
                return ShortUrlWhitelist::query()->where('id', $idResolver($value))->firstOrFail();
            }
        );

        Route::bind(
            'id',
            $idResolver
        );

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        $apiVersions = glob(base_path('routes/api/*'), GLOB_ONLYDIR);

        Route::prefix('api')->middleware('api')->namespace($this->namespace)->group(
            function () use ($apiVersions) {
                foreach ($apiVersions as $version) {
                    $versionRoutePrefix = str_replace([base_path('routes/api'), '/'], '', $version);
                    Route::prefix($versionRoutePrefix)->group(
                        function () use ($version) {
                            foreach (File::allFiles($version) as $routeFile) {
                                Route::domain($this->getDomainForRoute((string) $routeFile))->group($routeFile);
                            }
                        }
                    );
                }
            }
        );
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        $files = File::allFiles(base_path('routes/web'));
        sort($files);

        Route::middleware('web')->namespace($this->namespace)->group(
            function () use ($files) {
                foreach ($files as $routeFile) {
                    Route::domain($this->getDomainForRoute((string) $routeFile))->group($routeFile);
                }
            }
        );
    }

    /**
     * Returns the Config for app.<filename>_url
     *
     * @param string $route
     *
     * @return string
     */
    private function getDomainForRoute(string $route): string
    {
        $key = str_replace(
            [
                base_path('routes/api'),
                base_path('routes/web'),
                '.php',
                '\\',
                '/',
            ],
            '',
            $route
        );

        $key = preg_replace('/v[0-9]/', '', $key);

        return (string) config('app.'.$key.'_url');
    }
}
