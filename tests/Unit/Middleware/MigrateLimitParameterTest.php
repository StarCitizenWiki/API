<?php

declare(strict_types=1);

use App\Http\Middleware\MigrateLimitParameter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

uses(Tests\TestCase::class);

it('migrates legacy page and limit parameters into json api pagination', function (): void {
    $middleware = new MigrateLimitParameter;
    $request = Request::create('/api/items?limit=50&page=2', 'GET');
    $handledRequest = null;

    $middleware->handle($request, function (Request $request) use (&$handledRequest): Response {
        $handledRequest = $request;

        return new Response;
    });

    expect($handledRequest)->not->toBeNull();
    expect($handledRequest->query->all()['page'])->toBe([
        'number' => 2,
        'size' => 50,
    ]);
});

it('keeps page size when page number is provided in a legacy query order', function (): void {
    $middleware = new MigrateLimitParameter;
    $request = Request::create('/api/items?page[size]=50&page=2', 'GET');
    $handledRequest = null;

    $middleware->handle($request, function (Request $request) use (&$handledRequest): Response {
        $handledRequest = $request;

        return new Response;
    });

    expect($handledRequest)->not->toBeNull();
    expect($handledRequest->query->all()['page'])->toBe([
        'number' => 2,
        'size' => 50,
    ]);
});

it('accepts json api pagination arrays without throwing', function (): void {
    $middleware = new MigrateLimitParameter;
    $request = Request::create('/api/items?page[number]=38&page[size]=50', 'GET');
    $handledRequest = null;

    $middleware->handle($request, function (Request $request) use (&$handledRequest): Response {
        $handledRequest = $request;

        return new Response;
    });

    expect($handledRequest)->not->toBeNull();
    expect($handledRequest->query->all()['page'])->toBe([
        'number' => 38,
        'size' => 50,
    ]);
});
