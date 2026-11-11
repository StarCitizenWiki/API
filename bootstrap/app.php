<?php

use App\Exceptions\NotFoundInVersionException;
use App\Http\Middleware\AddApiDiscoveryLinkHeader;
use App\Http\Middleware\AddCloudflareCacheTags;
use App\Http\Middleware\Api\Game\ResolveGameVersion;
use App\Http\Middleware\MigrateLimitParameter;
use App\Http\Middleware\PersistSelectedGameVersion;
use App\Http\Middleware\TrustCloudflareProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

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
            AddApiDiscoveryLinkHeader::class,
        ]);

        $middleware->api(append: [
            AddCloudflareCacheTags::class,
            AddApiDiscoveryLinkHeader::class,
        ]);

        $middleware->alias([
            'game.version' => ResolveGameVersion::class,
            'limit.parameter' => MigrateLimitParameter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api') || $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (NotFoundInVersionException $exception, Request $request): ?JsonResponse {
            if (! ($request->is('api') || $request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage(),
                'requested_version' => $exception->requestedVersion,
                'available_versions' => $exception->availableVersions,
            ], 404);
        });

        $exceptions->respond(function (Response $response): Response {
            if ($response->getStatusCode() >= 400 && (request()->is('api') || request()->is('api/*'))) {
                $response->headers->set('Cache-Control', 'no-store, private');
            }

            return $response;
        });
    })->create();
