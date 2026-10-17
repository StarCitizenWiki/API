<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Vehicle\ImportShipMatrix;
use App\Services\RsiDownloadClient;
use App\Support\Filters\FilterCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('busts the shipmatrix filter cache when the job runs', function (): void {
    $indexKey = 'filters:index:shipmatrix';
    $cacheKey = FilterCache::shipMatrixKey();
    $secondaryCacheKey = 'filters:shipmatrix:filters-hash';

    Cache::forever($indexKey, [$cacheKey, $secondaryCacheKey]);
    Cache::forever($cacheKey, ['cached-shipmatrix-list']);
    Cache::forever($secondaryCacheKey, ['cached-filtered-shipmatrix-list']);

    // Invalid payload so the import aborts; the cache bust happens up front.
    Http::fake(['*' => Http::response(['success' => 0], 200)]);

    (new ImportShipMatrix)->handle(app(RsiDownloadClient::class));

    expect(Cache::get($indexKey))->toBeNull()
        ->and(Cache::get($cacheKey))->toBeNull()
        ->and(Cache::get($secondaryCacheKey))->toBeNull();
});
