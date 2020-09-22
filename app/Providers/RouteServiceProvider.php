<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Throttle\ApiThrottle;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use Dingo\Api\Http\RateLimit\Handler;
use Dingo\Api\Routing\Router as ApiRouter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Class RouteServiceProvider
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
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
        parent::boot();

        app(Handler::class)->extend(
            new ApiThrottle(
                [
                    'limit' => config('api.throttle.limit_unauthenticated'),
                    'expires' => config('api.throttle.period_unauthenticated'),
                ]
            )
        );

        /**
         * Star Citizen
         */
        Route::bind(
            'production_note',
            function ($id) {
                return ProductionNote::findOrFail($id);
            }
        );
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(): void
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "api" routes for the application.
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes(): void
    {
        /** @var ApiRouter $api */
        $api = app(ApiRouter::class);

        $api->version(
            'v1',
            [
                'namespace' => $this->namespace . '\Api\V1',
                'middleware' => 'api',
            ],
            static function (ApiRouter $api) {
                $api->group(
                    [],
                    static function (ApiRouter $api) {
                        require base_path('routes/api/api_v1.php');
                    }
                );
            }
        );
    }

    /**
     * Define the "web" routes for the application.
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->name('web.')
            ->namespace($this->namespace)
            ->group(
                function () {
                    Route::namespace('Web')
                        ->group(
                            function () {
                                Route::name('api.')
                                    ->namespace('Api')
                                    ->group(base_path('routes/web/api.php'));

                                Route::name('user.')
                                    ->namespace('User')
                                    ->group(base_path('routes/web/user.php'));
                            }
                        );
                }
            );
    }
}
