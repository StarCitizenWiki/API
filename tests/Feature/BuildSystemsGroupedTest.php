<?php

declare(strict_types=1);

use App\Http\Resources\Game\Commodity\CommodityShowResource;

it('groups locations by system', function (): void {
    $locations = [
        ['name' => 'Location A', 'system' => 'Stanton', 'designation' => null, 'resources' => [['key' => 'dep1']]],
        ['name' => 'Location B', 'system' => 'Pyro', 'designation' => null, 'resources' => [['key' => 'dep2']]],
        ['name' => 'Location C', 'system' => 'Stanton', 'designation' => null, 'resources' => [['key' => 'dep3']]],
    ];

    $showResource = new CommodityShowResource(new stdClass);
    $result = $showResource->buildSystemsGrouped($locations);

    expect($result)->toHaveCount(2)
        ->and($result[0]['name'])->toBe('Pyro')
        ->and($result[0]['locations'])->toHaveCount(1)
        ->and($result[0]['locations'][0]['name'])->toBe('Location B')
        ->and($result[1]['name'])->toBe('Stanton')
        ->and($result[1]['locations'])->toHaveCount(2)
        ->and($result[1]['locations'][0]['name'])->toBe('Location A')
        ->and($result[1]['locations'][1]['name'])->toBe('Location C');
});

it('handles locations without a system', function (): void {
    $locations = [
        ['name' => 'Unknown Loc', 'system' => null, 'resources' => []],
    ];

    $showResource = new CommodityShowResource(new stdClass);
    $result = $showResource->buildSystemsGrouped($locations);

    expect($result)->toHaveCount(1)
        ->and($result[0]['name'])->toBe('Unknown System')
        ->and($result[0]['locations'])->toHaveCount(1);
});

it('returns empty array for no locations', function (): void {
    $showResource = new CommodityShowResource(new stdClass);
    $result = $showResource->buildSystemsGrouped([]);

    expect($result)->toBe([]);
});

it('sorts systems alphabetically', function (): void {
    $locations = [
        ['name' => 'Loc 1', 'system' => 'Stanton', 'designation' => null, 'resources' => []],
        ['name' => 'Loc 2', 'system' => 'Pyro', 'designation' => null, 'resources' => []],
        ['name' => 'Loc 3', 'system' => 'Castra', 'designation' => null, 'resources' => []],
    ];

    $showResource = new CommodityShowResource(new stdClass);
    $result = $showResource->buildSystemsGrouped($locations);

    expect($result)->toHaveCount(3)
        ->and($result[0]['name'])->toBe('Castra')
        ->and($result[1]['name'])->toBe('Pyro')
        ->and($result[2]['name'])->toBe('Stanton');
});

it('sorts locations by designation then name within each system', function (): void {
    $locations = [
        ['name' => 'Cellin', 'system' => 'Stanton', 'designation' => 'Stanton IIc', 'resources' => []],
        ['name' => 'Daymar', 'system' => 'Stanton', 'designation' => 'Stanton IIb', 'resources' => []],
        ['name' => 'Hurston', 'system' => 'Stanton', 'designation' => 'Stanton I', 'resources' => []],
        ['name' => 'Arial', 'system' => 'Stanton', 'designation' => 'Stanton IIa', 'resources' => []],
        ['name' => 'Unknown Belt', 'system' => 'Stanton', 'designation' => null, 'resources' => []],
        ['name' => 'Another Belt', 'system' => 'Stanton', 'designation' => null, 'resources' => []],
    ];

    $showResource = new CommodityShowResource(new stdClass);
    $result = $showResource->buildSystemsGrouped($locations);

    $names = collect($result[0]['locations'])->pluck('name')->all();

    expect($names)->toBe(['Hurston', 'Arial', 'Daymar', 'Cellin', 'Another Belt', 'Unknown Belt']);
});
