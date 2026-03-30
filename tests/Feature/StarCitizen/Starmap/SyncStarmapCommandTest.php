<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Starmap\Sync\SyncStarmap;
use App\Support\Filters\FilterCache;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

it('dispatches a sync job', function (): void {
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
