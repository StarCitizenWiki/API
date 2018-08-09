<?php declare(strict_types = 1);

namespace App\Providers;

use App\Models\Api\Notification;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus;
use App\Models\Api\StarCitizen\Vehicle\GroundVehicle;
use App\Models\Api\StarCitizen\Vehicle\Ship;
use App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize;
use App\Models\Api\StarCitizen\Vehicle\Type\VehicleType;
use Dingo\Api\Http\RateLimit\Handler;
use Dingo\Api\Routing\Router as ApiRouter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Vinkla\Hashids\Facades\Hashids;

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

        $this->bindAdminModelRoutes();

        app(Handler::class)->extend(
            new \App\Http\Throttle\ApiThrottle(
                [
                    'limit' => env('THROTTLE_GUEST_REQUESTS', 10),
                    'expires' => env('THROTTLE_PERIOD', 1),
                ]
            )
        );
    }

    /**
     * Binds Model Slugs to Resolve Logic
     * Decodes Hashed IDs
     */
    private function bindAdminModelRoutes()
    {
        Route::bind(
            'notification',
            function ($id) {
                $id = $this->hasher($id, Notification::class);

                return Notification::where('id', $id)->firstOrFail();
            }
        );

        /**
         * Star Citizen
         */
        Route::bind(
            'manufacturer',
            function ($id) {
                $id = $this->hasher($id, Manufacturer::class);

                return Manufacturer::where('id', $id)->firstOrFail();
            }
        );

        Route::bind(
            'production_note',
            function ($id) {
                $id = $this->hasher($id, ProductionNote::class);

                return ProductionNote::where('id', $id)->firstOrFail();
            }
        );

        Route::bind(
            'production_status',
            function ($id) {
                $id = $this->hasher($id, ProductionStatus::class);

                return ProductionStatus::where('id', $id)->firstOrFail();
            }
        );

        /**
         * Vehicles
         */
        Route::bind(
            'focus',
            function ($id) {
                $id = $this->hasher($id, VehicleFocus::class);

                return VehicleFocus::where('id', $id)->firstOrFail();
            }
        );

        Route::bind(
            'size',
            function ($id) {
                $id = $this->hasher($id, VehicleSize::class);

                return VehicleSize::where('id', $id)->firstOrFail();
            }
        );

        Route::bind(
            'type',
            function ($id) {
                $id = $this->hasher($id, VehicleType::class);

                return VehicleType::where('id', $id)->firstOrFail();
            }
        );
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
                'namespace' => $this->namespace.'\Api\V1',
                'middleware' => 'api',
            ],
            function (ApiRouter $api) {
                $api->group(
                    [],
                    function (ApiRouter $api) {
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

    /**
     * Tries to decode a hashid string into an id
     *
     * @param string $value
     *
     * @param string $connection
     *
     * @return int
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    private function hasher($value, string $connection = 'main')
    {
        if (is_int($value)) {
            return $value;
        }

        $decoded = Hashids::connection($connection)->decode($value);

        if (empty($decoded) || !is_integer($decoded[0])) {
            throw new BadRequestHttpException();
        }

        return $decoded[0];
    }
}
