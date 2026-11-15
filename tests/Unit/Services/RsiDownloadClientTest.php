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
            && $request->hasHeader('User-Agent', 'starcitizen-wiki-api/1.0 (+https://api.starcitizen.wiki)')
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
            && $request->hasHeader('User-Agent', 'starcitizen-wiki-api/1.0 (+https://api.starcitizen.wiki)')
            && $request->url() === 'https://robertsspaceindustries.com/comm-link';
    });
});

it('sends a custom user agent when configured', function (): void {
    config()->set('services.rsi_user_agent', 'my-custom-agent/2.0');

    Http::fake();

    $client = app(RsiDownloadClient::class);

    $client->base()->get('https://robertsspaceindustries.com/comm-link');

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('User-Agent', 'my-custom-agent/2.0');
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

it('classifies HTTP errors without treating redirects as errors', function (int $status, ?string $expected): void {
    Http::fake(['*' => Http::response('', $status)]);

    expect(RsiDownloadClient::errorType(Http::get('https://example.test')))->toBe($expected);
})->with([[200, null], [302, null], [404, 'client'], [500, 'server']]);

it('preserves strict and truthy envelope checks', function (mixed $success, bool $strict, bool $truthy): void {
    expect(RsiDownloadClient::envelopeSucceeded($success))->toBe($strict)
        ->and(RsiDownloadClient::envelopeSucceeded($success, false))->toBe($truthy);
})->with([[1, true, true], ['1', false, true], [true, false, true], [0, false, false], [null, false, false]]);

it('decodes envelopes in either representation and rejects invalid JSON', function (): void {
    expect(RsiDownloadClient::decodeEnvelope('{"success":1}')->success)->toBe(1)
        ->and(RsiDownloadClient::decodeEnvelope('{"success":1}', true))->toBe(['success' => 1]);

    RsiDownloadClient::decodeEnvelope('{');
})->throws(JsonException::class);
