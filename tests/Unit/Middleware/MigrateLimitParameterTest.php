<?php

declare(strict_types=1);

use App\Http\Middleware\MigrateLimitParameter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

it('migrates legacy page and limit parameters into json api pagination', function (): void {
    $result = runLimitMiddleware('/api/items?limit=50&page=2');

    assertPageQuery($result['handledRequest'], [
        'number' => 2,
        'size' => 50,
    ]);

    expect($result['response'])->toBe($result['nextResponse'])
        ->and($result['handledRequest'])->not->toBeNull()
        ->and($result['handledRequest']->query->has('limit'))->toBeFalse();
});

it('keeps page number and page size regardless of parameter order', function (string $query): void {
    $result = runLimitMiddleware('/api/items?'.$query);

    assertPageQuery($result['handledRequest'], [
        'number' => 2,
        'size' => 50,
    ]);
})->with([
    'json-api-first' => 'page[size]=50&page=2',
    'legacy-first' => 'page=2&page[size]=50',
]);

it('accepts json api pagination arrays without throwing', function (): void {
    $result = runLimitMiddleware('/api/items?page[number]=38&page[size]=50');

    assertPageQuery($result['handledRequest'], [
        'number' => 38,
        'size' => 50,
    ]);
});

it('prefers explicit json api page number over legacy page fallback', function (string $query): void {
    $result = runLimitMiddleware('/api/items?'.$query);

    assertPageQuery($result['handledRequest'], [
        'number' => 3,
        'size' => 50,
    ]);
})->with([
    'json-api-first' => 'page[number]=3&page=2&page[size]=50',
    'legacy-first' => 'page=2&page[number]=3&page[size]=50',
]);

it('falls back to legacy limit when json api size is invalid', function (): void {
    $result = runLimitMiddleware('/api/items?page=2&page[size]=abc&limit=25');

    assertPageQuery($result['handledRequest'], [
        'number' => 2,
        'size' => 25,
    ]);

    expect($result['handledRequest'])->not->toBeNull()
        ->and($result['handledRequest']->query->has('limit'))->toBeFalse();
});

it('keeps valid json api size when legacy limit is invalid', function (): void {
    $result = runLimitMiddleware('/api/items?page[number]=3&page[size]=30&limit=abc');

    assertPageQuery($result['handledRequest'], [
        'number' => 3,
        'size' => 30,
    ]);

    expect($result['handledRequest'])->not->toBeNull()
        ->and($result['handledRequest']->query->has('limit'))->toBeFalse();
});

it('clamps numeric page values to at least one', function (string $query, array $expectedPage): void {
    $result = runLimitMiddleware('/api/items?'.$query);

    assertPageQuery($result['handledRequest'], $expectedPage);
})->with([
    'json-api-values' => ['page[number]=0&page[size]=-4', ['number' => 1, 'size' => 1]],
    'legacy-values' => ['page=-7&limit=0', ['number' => 1, 'size' => 1]],
]);

/**
 * @return array{handledRequest: Request|null, response: Response, nextResponse: Response}
 */
function runLimitMiddleware(string $uri): array
{
    $middleware = new MigrateLimitParameter;
    $request = Request::create($uri, 'GET');
    $handledRequest = null;
    $nextResponse = new Response;

    $response = $middleware->handle($request, function (Request $request) use (&$handledRequest, $nextResponse): Response {
        $handledRequest = $request;

        return $nextResponse;
    });

    return [
        'handledRequest' => $handledRequest,
        'response' => $response,
        'nextResponse' => $nextResponse,
    ];
}

function assertPageQuery(?Request $request, array $expectedPage): void
{
    expect($request)->not->toBeNull();

    $page = $request?->query->all()['page'] ?? [];
    ksort($page);
    ksort($expectedPage);

    expect($page)->toBe($expectedPage);
}
