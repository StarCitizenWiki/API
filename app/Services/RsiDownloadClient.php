<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final class RsiDownloadClient
{
    private const RSI_TOKEN = 'STAR-CITIZEN.WIKI_DE_API_REQUEST';

    public function base(): PendingRequest
    {
        return Http::withHeaders([
            'X-RSI-Token' => self::RSI_TOKEN,
        ])
            ->withUserAgent(config('services.rsi_user_agent'))
            ->timeout(60)
            ->maxRedirects(10);
    }

    public function forRsi(): PendingRequest
    {
        $baseUrl = config('services.rsi_url');

        return $this->withBaseUrl(is_string($baseUrl) ? $baseUrl : null);
    }

    public static function errorType(Response $response): ?string
    {
        if ($response->serverError()) {
            return 'server';
        }

        return $response->clientError() ? 'client' : null;
    }

    public static function decodeEnvelope(string $body, bool $associative = false): mixed
    {
        return json_decode($body, $associative, 512, JSON_THROW_ON_ERROR);
    }

    public static function envelopeSucceeded(mixed $success, bool $strict = true): bool
    {
        return $strict ? $success === 1 : (bool) $success;
    }

    private function withBaseUrl(?string $baseUrl): PendingRequest
    {
        $client = $this->base();

        if ($baseUrl === null || $baseUrl === '') {
            return $client;
        }

        return $client->baseUrl($baseUrl);
    }
}
