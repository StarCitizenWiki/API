<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia;
use App\Jobs\StarCitizen\Starmap\Sync\SyncStarmap;
use App\Jobs\StarCitizen\Stat\SyncStats;
use App\Support\Filters\FilterCache;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

describe('galactapedia:sync', function (): void {
    it('dispatches a galactapedia sync job and clears filter caches', function (): void {
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
});

describe('starmap:sync', function (): void {
    it('dispatches a sync job and clears filter caches', function (): void {
        Bus::fake();

        $indexKey = 'filters:index:starsystems';
        $firstCacheKey = FilterCache::starsystemsKey();
        $secondCacheKey = 'filters:starsystems:filters-hash';

        Cache::forever($indexKey, [$firstCacheKey, $secondCacheKey]);
        Cache::forever($firstCacheKey, ['cached-starsystem-list']);
        Cache::forever($secondCacheKey, ['cached-filtered-starsystem-list']);

        $this->artisan('starmap:sync')
            ->expectsOutput('Dispatching Starmap Sync')
            ->assertExitCode(0);

        Bus::assertDispatched(SyncStarmap::class);
        expect(Cache::get($indexKey))->toBeNull()
            ->and(Cache::get($firstCacheKey))->toBeNull()
            ->and(Cache::get($secondCacheKey))->toBeNull();
    });
});

describe('stats:sync', function (): void {
    it('dispatches a stats sync job', function (): void {
        Bus::fake();

        $this->artisan('stats:sync')
            ->expectsOutput('Dispatching Stats Sync')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(SyncStats::class, 1);
    });
});
