<?php

declare(strict_types=1);

use App\Support\Filters\FilterCache;
use Illuminate\Support\Facades\Cache;

it('does not reuse a fill that finishes after a bust', function (): void {
    app()->detectEnvironment(fn () => 'production');
    $namespace = FilterCache::NAMESPACE_COMMODITIES;
    $key = FilterCache::commoditiesKey('race');

    $inFlight = FilterCache::rememberForever($namespace, $key, function () use ($namespace): array {
        FilterCache::bust($namespace);

        return ['stale'];
    });

    expect($inFlight)->toBe(['stale'])
        ->and(FilterCache::rememberForever($namespace, $key, fn () => ['fresh']))->toBe(['fresh'])
        ->and(FilterCache::rememberForever($namespace, $key, fn () => ['unexpected']))->toBe(['fresh']);
});

it('rejects an old fill even when it overwrites a fresh fill', function (): void {
    app()->detectEnvironment(fn () => 'production');
    $namespace = FilterCache::NAMESPACE_COMMODITIES;
    $key = FilterCache::commoditiesKey('race');

    FilterCache::rememberForever($namespace, $key, function () use ($namespace, $key): array {
        FilterCache::bust($namespace);
        expect(FilterCache::rememberForever($namespace, $key, fn () => ['fresh']))->toBe(['fresh']);

        return ['stale'];
    });

    expect(FilterCache::rememberForever($namespace, $key, fn () => ['fresh again']))->toBe(['fresh again']);
});

it('does not reuse a fill after its generation is evicted', function (): void {
    app()->detectEnvironment(fn () => 'production');
    $namespace = FilterCache::NAMESPACE_COMMODITIES;
    $key = FilterCache::commoditiesKey('race');

    FilterCache::rememberForever($namespace, $key, fn () => ['old']);
    Cache::forget('filters:generation:commodities');

    expect(FilterCache::rememberForever($namespace, $key, fn () => ['fresh']))->toBe(['fresh']);
});

it('returns false when filters are empty', function (): void {
    expect(FilterCache::hasEffectiveFilters([]))->toBeFalse()
        ->and(FilterCache::hasEffectiveFilters(null))->toBeFalse();
});

it('returns false when nested filters only contain blank values', function (): void {
    expect(FilterCache::hasEffectiveFilters([
        'type' => '',
        'nested' => [
            'manufacturer' => ' ',
            'values' => [null, ''],
        ],
    ]))->toBeFalse();
});

it('returns true when any scalar filter has a value', function (): void {
    expect(FilterCache::hasEffectiveFilters([
        'type' => 'Weapon',
    ]))->toBeTrue();
});

it('ignores configured keys when checking filter effectiveness', function (): void {
    expect(FilterCache::hasEffectiveFilters([
        'category' => 'food',
    ], ['category']))->toBeFalse()
        ->and(FilterCache::hasEffectiveFilters([
            'category' => 'food',
            'type' => 'Weapon',
        ], ['category']))->toBeTrue();
});
