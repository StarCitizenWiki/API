<?php declare(strict_types = 1);

namespace App\Providers;

use App\Helpers\Hasher;
use App\Models\Account\User;
use App\Models\Api\Notification;
use Hashids\HashidsException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::middleware('api')
            ->name('api.')
            ->namespace($this->namespace)
            ->prefix('api')
            ->group(
                function () {
                    Route::namespace('Api')
                        ->group(
                            function () {
                                Route::name('v1.')
                                    ->namespace('Api')
                                    ->prefix('v1')
                                    ->group(base_path('routes/api/api_v1.php'));
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
