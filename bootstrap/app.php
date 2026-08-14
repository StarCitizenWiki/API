<?php

use App\Http\Middleware\AddCloudflareCacheTags;
use App\Http\Middleware\Api\Game\ResolveGameVersion;
use App\Http\Middleware\MigrateLimitParameter;
use App\Http\Middleware\PersistSelectedGameVersion;
use App\Http\Middleware\TrustCloudflareProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'can:access-admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo('/profile');

        $middleware->replace(
            TrustProxies::class,
            TrustCloudflareProxies::class,
        );

        $middleware->web(append: [
            PersistSelectedGameVersion::class,
            AddCloudflareCacheTags::class,
        ]);

        $middleware->api(append: [
            AddCloudflareCacheTags::class,
        ]);

        $middleware->alias([
            'game.version' => ResolveGameVersion::class,
            'limit.parameter' => MigrateLimitParameter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
