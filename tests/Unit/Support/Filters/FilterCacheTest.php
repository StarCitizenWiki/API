<?php

declare(strict_types=1);

use App\Support\Filters\FilterCache;

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
