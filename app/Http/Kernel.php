<?php declare(strict_types = 1);

namespace App\Http;

use App\Http\Throttle\ApiThrottle;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * Class Kernel
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\CheckUserState::class,
            \App\Http\Middleware\PiwikTracking::class,
        ],

        'api' => [
            'bindings',
            'update_token_timestamp',
            'check_user_state',
            'piwik_tracking',
            'api.throttle',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin' => \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin::class,
        'admin.guest' => \App\Http\Middleware\Web\Admin\RedirectIfAdmin::class,
        'api.throttle' => ApiThrottle::class,
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'check_user_state' => \App\Http\Middleware\CheckUserState::class,
        'guest' => \App\Http\Middleware\Web\User\RedirectIfAuthenticated::class,
        'update_token_timestamp' => \App\Http\Middleware\Api\UpdateTokenTimestamp::class,
        'piwik_tracking' => \App\Http\Middleware\PiwikTracking::class,
    ];
}
