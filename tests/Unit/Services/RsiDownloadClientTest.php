<?php

declare(strict_types=1);

use App\Services\RsiDownloadClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('it builds an client with base url and token header', function () {
    config()->set('services.rsi_url', 'https://api.example.test');

    Http::fake();

    $client = app(RsiDownloadClient::class);

    $client->forRsi()->post('/galactapedia/graphql', [
        'query' => 'test',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('X-RSI-Token', 'STAR-CITIZEN.WIKI_DE_API_REQUEST')
            && $request->url() === 'https://api.example.test/galactapedia/graphql';
    });
});

test('it builds a base client that preserves full urls', function () {
    Http::fake();

    $client = app(RsiDownloadClient::class);

    $client->base()->get('https://robertsspaceindustries.com/comm-link');

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('X-RSI-Token', 'STAR-CITIZEN.WIKI_DE_API_REQUEST')
            && $request->url() === 'https://robertsspaceindustries.com/comm-link';
    });
});
