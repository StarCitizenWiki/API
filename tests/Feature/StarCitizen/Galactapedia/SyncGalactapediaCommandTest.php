<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia;
use App\Support\Filters\FilterCache;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

it('dispatches a galactapedia sync job', function (): void {
    Bus::fake();

    $indexKey = 'filters:index:galactapedia';
    $cacheKey = FilterCache::galactapediaKey();
    $secondaryCacheKey = 'filters:galactapedia:filters-hash';

    Cache::forever($indexKey, [$cacheKey, $secondaryCacheKey]);
    Cache::forever($cacheKey, ['cached-galactapedia-list']);
    Cache::forever($secondaryCacheKey, ['cached-filtered-galactapedia-list']);

    $this->artisan('galactapedia:sync')
        ->expectsOutput('Dispatching Galactapedia Sync')
        ->assertExitCode(0);

    Bus::assertDispatched(SyncGalactapedia::class);
    expect(Cache::get($indexKey))->toBeNull()
        ->and(Cache::get($cacheKey))->toBeNull()
        ->and(Cache::get($secondaryCacheKey))->toBeNull();
});
