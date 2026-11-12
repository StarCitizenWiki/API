<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemData;
use App\Models\Game\Commodity\Commodity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    (new ReflectionProperty(ImportItemData::class, 'commodityUuidCache'))->setValue(null, null);
});

afterEach(function (): void {
    (new ReflectionProperty(ImportItemData::class, 'commodityUuidCache'))->setValue(null, null);
});

it('resolves commodities inserted after the cache is primed', function (): void {
    $resolve = new ReflectionMethod(ImportItemData::class, 'resolveCommodityIds');
    $job = new ImportItemData(1, 'items/test.json');
    $resolve->invoke($job, []);

    $commodity = Commodity::factory()->create();

    expect($resolve->invoke($job, [$commodity->uuid]))->toBe([$commodity->id]);
});

it('rechecks missing uuids in one query and caches the results', function (): void {
    $resolve = new ReflectionMethod(ImportItemData::class, 'resolveCommodityIds');
    $job = new ImportItemData(1, 'items/test.json');
    $resolve->invoke($job, []);
    $first = Commodity::factory()->create();
    $second = Commodity::factory()->create();

    DB::enableQueryLog();
    DB::flushQueryLog();
    try {
        expect($resolve->invoke($job, [$first->uuid, $second->uuid]))->toBe([$first->id, $second->id])
            ->and(DB::getQueryLog())->toHaveCount(1);
        expect($resolve->invoke($job, [$second->uuid, $first->uuid]))->toBe([$second->id, $first->id])
            ->and(DB::getQueryLog())->toHaveCount(1);
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }
});

it('logs commodity uuids that remain unknown', function (): void {
    $uuid = fake()->uuid();
    Log::shouldReceive('warning')->once()->with('Dropping unknown commodity UUIDs from item import.', [
        'path' => 'items/test.json',
        'commodity_uuids' => [$uuid],
    ]);

    $resolve = new ReflectionMethod(ImportItemData::class, 'resolveCommodityIds');
    $job = new ImportItemData(1, 'items/test.json');

    expect($resolve->invoke($job, [$uuid]))->toBe([]);
});
