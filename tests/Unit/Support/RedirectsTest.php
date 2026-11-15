<?php

declare(strict_types=1);

use App\Support\Redirects;
use Illuminate\Http\Request;

it('preserves the raw query string when redirecting', function (string $target, string $query, string $expected): void {
    $request = Request::create('/source');
    $request->server->set('QUERY_STRING', $query);

    $response = Redirects::withQueryString($request, $target, 302);

    expect($response->getTargetUrl())->toBe($expected)
        ->and($response->getStatusCode())->toBe(302);
})->with([
    ['https://example.test/target', '', 'https://example.test/target'],
    ['https://example.test/target', 'page=1&page%5Bsize%5D=50&name=a%2Bb', 'https://example.test/target?page=1&page%5Bsize%5D=50&name=a%2Bb'],
    ['https://example.test/target?existing=1', 'page=1&page=2', 'https://example.test/target?existing=1&page=1&page=2'],
]);
