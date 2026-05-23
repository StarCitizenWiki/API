<?php

declare(strict_types=1);

use App\Attributes\CacheTag;
use App\Http\Middleware\AddCloudflareCacheTags;
use App\Models\Game\GameVersion;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Symfony\Component\HttpFoundation\Response;

#[CacheTag('vehicles')]
class CacheTaggedControllerForCacheTagsTest
{
    public function __invoke(): void {}
}

class UntaggedControllerForCacheTagsTest
{
    public function __invoke(): void {}
}

describe('AddCloudflareCacheTags middleware', function (): void {
    it('sets scoped cache tags from controller attributes', function (string $path, string $expectedHeader): void {
        $response = handleCacheTagRequest($path, CacheTaggedControllerForCacheTagsTest::class);

        expect($response->headers->get('Cache-Tag'))->toBe($expectedHeader);
    })->with([
        'api route' => ['/api/vehicles', 'api, api-vehicles'],
        'web route' => ['/vehicles', 'web, web-vehicles'],
    ]);

    it('appends default version cache tags', function (): void {
        $request = cacheTagRequest('/api/vehicles', CacheTaggedControllerForCacheTagsTest::class);
        $request->attributes->set('game_version', new GameVersion([
            'code' => '4.8.0-LIVE.123',
            'is_default' => true,
        ]));

        $response = (new AddCloudflareCacheTags)->handle($request, cacheTagResponse(...));

        expect($response->headers->get('Cache-Tag'))->toBe('api, api-vehicles, api-default');
    });

    it('appends semantic version cache tags for non-default versions', function (): void {
        $request = cacheTagRequest('/api/vehicles', CacheTaggedControllerForCacheTagsTest::class);
        $request->attributes->set('game_version', new GameVersion([
            'code' => '4.7.1-PTU.456',
            'is_default' => false,
        ]));

        $response = (new AddCloudflareCacheTags)->handle($request, cacheTagResponse(...));

        expect($response->headers->get('Cache-Tag'))->toBe('api, api-vehicles, api-v4.7.1');
    });

    it('does not set cache tags for unsafe methods, closure routes, or untagged controllers', function (Request $request): void {
        $response = (new AddCloudflareCacheTags)->handle($request, cacheTagResponse(...));

        expect($response->headers->has('Cache-Tag'))->toBeFalse();
    })->with([
        'unsafe method' => fn (): Request => cacheTagRequest('/api/vehicles', CacheTaggedControllerForCacheTagsTest::class, 'POST'),
        'closure route' => fn (): Request => closureRouteCacheTagRequest('/api/openapi'),
        'untagged controller' => fn (): Request => cacheTagRequest('/admin', UntaggedControllerForCacheTagsTest::class),
    ]);
});

function handleCacheTagRequest(string $path, string $controller, string $method = 'GET'): Response
{
    return (new AddCloudflareCacheTags)->handle(
        cacheTagRequest($path, $controller, $method),
        cacheTagResponse(...),
    );
}

function cacheTagRequest(string $path, string $controller, string $method = 'GET'): Request
{
    $request = Request::create($path, $method);
    $route = new RoutingRoute([$method], $path, ['uses' => $controller]);
    $route->setContainer(app());

    $request->setRouteResolver(fn (): RoutingRoute => $route);

    return $request;
}

function closureRouteCacheTagRequest(string $path): Request
{
    $request = Request::create($path);
    $route = new RoutingRoute(['GET'], $path, fn (): string => 'ok');
    $route->setContainer(app());

    $request->setRouteResolver(fn (): RoutingRoute => $route);

    return $request;
}

function cacheTagResponse(): Response
{
    return new Response('ok');
}
