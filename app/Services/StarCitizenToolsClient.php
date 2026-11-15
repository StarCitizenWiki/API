<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class StarCitizenToolsClient
{
    private const string DEFAULT_API_URL = 'https://starcitizen.tools/api.php';

    private const int TIMEOUT = 60;

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function apiGet(array $query): array
    {
        $response = $this->request()
            ->get($this->apiUrl(), [...$query, 'maxlag' => 5])
            ->throw();

        usleep((int) config('images.throttle_microseconds', 200_000));

        $body = $response->json() ?? [];

        if (isset($body['error'])) {
            throw new RuntimeException('Wiki API error: '.(string) ($body['error']['info'] ?? $body['error']['code'] ?? 'unknown'));
        }

        return $body;
    }

    /**
     * Bare builder without apiGet's throw, maxlag and error-shape policy; callers own their failure handling.
     */
    public function request(int $timeout = self::TIMEOUT): PendingRequest
    {
        return Http::withUserAgent((string) config('services.rsi_user_agent'))
            ->timeout($timeout);
    }

    public function apiUrl(): string
    {
        return collect(config('images.sources'))
            ->firstWhere('name', 'starcitizen.tools')['api_url'] ?? self::DEFAULT_API_URL;
    }
}
