<?php declare(strict_types = 1);

namespace App\Providers;

use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
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

        Route::model('production_status', ProductionStatus::class);
        Route::model('production_note', ProductionNote::class);
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
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        /** @var \Dingo\Api\Routing\Router $api */
        $api = app('Dingo\Api\Routing\Router');

        $api->version(
            'v1',
            [
                'namespace' => $this->namespace.'\API\V1',
                'middleware' => 'api',
            ],
            function (ApiRouter $api) {
                $api->group(
                    [],
                    function (ApiRouter $api) {
                        require_once base_path('routes/api/api_v1.php');
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
    protected function mapWebRoutes()
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

                                Route::name('admin.')
                                    ->namespace('Admin')
                                    ->prefix('admin')
                                    ->group(base_path('routes/web/admin.php'));

                                Route::name('user.')
                                    ->namespace('User')
                                    ->group(base_path('routes/web/user.php'));
                            }
                        );
                }
            );
    }
}
