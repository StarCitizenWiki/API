<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

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
        //

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

        //
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
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            $files = File::allFiles(base_path('routes/web'));
            sort($files);
            foreach ($files as $route) {
                Route::group(['domain' => $this->getDomainForRoute($route)], function ($router) use ($route) {
                    require $route;
                });
            }
        });
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
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            $apiVersions = glob(base_path('routes/api/*'), GLOB_ONLYDIR);
            foreach ($apiVersions as $version) {
                $versionRoutePrefix = str_replace([base_path('routes/api'), '/'], '', $version);
                Route::group(['prefix' => $versionRoutePrefix], function ($router) use ($version) {
                    foreach (File::allFiles($version) as $route) {
                        Route::group(['domain' => $this->getDomainForRoute($route)], function ($router) use ($route) {
                            require $route;
                        });
                    }
                });
            }
        });
    }

    /**
     * Returns the Config for app.<filename>_url
     *
     * @param String $route
     *
     * @return String
     */
    private function getDomainForRoute(String $route) : String
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

        return config('app.'.$key.'_url');
    }
}
