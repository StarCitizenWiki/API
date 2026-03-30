<?php

declare(strict_types=1);

use App\Services\RsiDownloadClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('builds a client with base url and token header', function (): void {
    config()->set('services.rsi_url', 'https://api.example.test');

    Http::fake();

    $client = app(RsiDownloadClient::class);

    $client->forRsi()->post('/galactapedia/graphql', [
        'query' => 'test',
    ]);

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('X-RSI-Token', 'STAR-CITIZEN.WIKI_DE_API_REQUEST')
            && $request->url() === 'https://api.example.test/galactapedia/graphql';
    });
});

it('builds a base client that preserves full urls', function (): void {
    Http::fake();

    $client = app(RsiDownloadClient::class);

    $client->base()->get('https://robertsspaceindustries.com/comm-link');

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('X-RSI-Token', 'STAR-CITIZEN.WIKI_DE_API_REQUEST')
            && $request->url() === 'https://robertsspaceindustries.com/comm-link';
    });
});

it('preserves full urls when rsi base url is missing', function (?string $rsiUrl): void {
    config()->set('services.rsi_url', $rsiUrl);

    Http::fake();

    $client = app(RsiDownloadClient::class);

    $client->forRsi()->get('https://robertsspaceindustries.com/comm-link');

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('X-RSI-Token', 'STAR-CITIZEN.WIKI_DE_API_REQUEST')
            && $request->url() === 'https://robertsspaceindustries.com/comm-link';
    });
})->with([
    'null url' => null,
    'empty url' => '',
]);
