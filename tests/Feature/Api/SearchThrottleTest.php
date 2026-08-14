<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

describe('search throttling', function (): void {
    it('rate limits expensive search endpoints', function (string $method, string $uri): void {
        GameVersion::factory()->create([
            'code' => '4.0.0-LIVE',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
        ]);

        // The real limiter allows 60/min
        RateLimiter::for('search', static fn (Request $request) => Limit::perMinute(2)->by($request->ip()));

        $call = fn () => $this->{$method}($uri, $method === 'postJson' ? ['query' => 'Test'] : []);

        $call();
        $call();

        $call()->assertStatus(429);
    })->with([
        'items search' => ['postJson', 'api/items/search'],
        'vehicles search' => ['postJson', 'api/vehicles/search'],
        'v2 vehicles search' => ['postJson', 'api/v2/vehicles/search'],
        'v3 vehicles search' => ['postJson', 'api/v3/vehicles/search'],
        'ground vehicles search' => ['postJson', 'api/ground-vehicles/search'],
        'gravlev vehicles search' => ['postJson', 'api/gravlev-vehicles/search'],
        'manufacturers search' => ['postJson', 'api/manufacturers/search'],
        'comm-links search' => ['postJson', 'api/comm-links/search'],
        'comm-link-images search' => ['postJson', 'api/comm-link-images/search'],
        'galactapedia search' => ['postJson', 'api/galactapedia/search'],
        'starsystems search' => ['postJson', 'api/starsystems/search'],
        'celestial objects search' => ['postJson', 'api/celestial-objects/search'],
        'shipmatrix vehicles search' => ['postJson', 'api/shipmatrix/vehicles/search'],
        'starmap positions' => ['getJson', 'api/locations/positions'],
        'comm-link images random' => ['getJson', 'api/comm-link-images/random'],
    ]);
});
