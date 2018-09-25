<?php declare(strict_types = 1);

namespace App\Providers;

use App\Models\Account\User\User;
use App\Models\Api\Notification;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\Api\StarCitizen\Vehicle\Focus\Focus;
use App\Models\Api\StarCitizen\Vehicle\Size\Size;
use App\Models\Api\StarCitizen\Vehicle\Type\Type;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series\Series;
use Dingo\Api\Http\RateLimit\Handler;
use Dingo\Api\Routing\Router as ApiRouter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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
                    'limit' => config('api.throttle.limit_unauthenticated'),
                    'expires' => config('api.throttle.period_unauthenticated'),
                ]
            )
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
     * Binds Model Slugs to Resolve Logic
     * Decodes Hashed IDs
     */
    private function bindAdminModelRoutes()
    {
        Route::bind(
            'user',
            function ($id) {
                // TODO unschöne Lösung. Implicit Model Binding läuft vor Policies -> Geblockter User bekommt für nicht existierendes Model 404 Fehler statt 403
                // Mögliche Lösung: Model Typehint aus Controller entfernen und Model explizit aus DB holen
                Gate::authorize('web.admin.users.view', Auth::guard('admin')->user());
                $id = $this->decodeId($id, User::class);

                return User::withTrashed()->findOrFail($id);
            }
        );
        Route::bind(
            'notification',
            function ($id) {
                $id = $this->decodeId($id, Notification::class);

                return Notification::findOrFail($id);
            }
        );

        /**
         * Star Citizen
         */
        Route::bind(
            'production_note',
            function ($id) {
                $this->authorizeTranslationView();

                $id = $this->decodeId($id, ProductionNote::class);

                return ProductionNote::findOrFail($id);
            }
        );

        Route::bind(
            'production_status',
            function ($id) {
                $this->authorizeTranslationView();

                $id = $this->decodeId($id, ProductionStatus::class);

                return ProductionStatus::findOrFail($id);
            }
        );

        /**
         * Vehicles
         */
        Route::bind(
            'focus',
            function ($id) {
                $this->authorizeTranslationView();

                $id = $this->decodeId($id, Focus::class);

                return Focus::findOrFail($id);
            }
        );

        Route::bind(
            'size',
            function ($id) {
                $this->authorizeTranslationView();

                $id = $this->decodeId($id, Size::class);

                return Size::findOrFail($id);
            }
        );

        Route::bind(
            'type',
            function ($id) {
                $this->authorizeTranslationView();

                $id = $this->decodeId($id, Type::class);

                return Type::findOrFail($id);
            }
        );

        /**
         * RSI
         */
        Route::bind(
            'comm_link',
            function ($id) {
                return CommLink::where('cig_id', $id)->firstOrFail();
            }
        );

        Route::bind(
            'category',
            function ($slug) {
                return Category::where('slug', $slug)->firstOrFail();
            }
        );

        Route::bind(
            'channel',
            function ($slug) {
                return Channel::where('slug', $slug)->firstOrFail();
            }
        );

        Route::bind(
            'series',
            function ($slug) {
                return Series::where('slug', $slug)->firstOrFail();
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
    private function decodeId($value, string $connection = 'main')
    {
        if (is_int($value)) {
            return $value;
        }

        $decoded = Hashids::connection($connection)->decode($value);

        return $decoded[0] ?? null;
    }

    /**
     * Check if User can View Translation Resource
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function authorizeTranslationView()
    {
        Gate::authorize('web.admin.translations.view', Auth::guard('admin')->user());
    }
}
