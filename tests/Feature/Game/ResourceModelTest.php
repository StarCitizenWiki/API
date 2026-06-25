<?php

declare(strict_types=1);

use App\Enums\Game\ResourceKind;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;

it('resolves data for a specific game version', function (): void {
    $liveVersion = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'is_default' => true,
    ]);
    $ptuVersion = GameVersion::factory()->create([
        'code' => '1.0.0-PTU',
        'is_default' => false,
    ]);

    $resource = Resource::factory()->create();

    ResourceData::factory()->for($resource)->for($liveVersion, 'gameVersion')->create([
        'name' => 'Live Resource',
        'kind' => 'harvestable',
    ]);
    ResourceData::factory()->for($resource)->for($ptuVersion, 'gameVersion')->create([
        'name' => 'PTU Resource',
        'kind' => ResourceKind::Salvage,
    ]);

    $liveData = $resource->dataForVersion('1.0.0-LIVE')->first();
    $ptuData = $resource->dataForVersion('1.0.0-PTU')->first();
    $defaultData = $resource->dataForVersion()->first();

    expect($liveData->name)->toBe('Live Resource')
        ->and($ptuData->name)->toBe('PTU Resource')
        ->and($defaultData->name)->toBe('Live Resource');
});

it('scopes query with data for version', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '2.0.0-LIVE',
        'is_default' => true,
    ]);

    $resource = Resource::factory()->create();
    ResourceData::factory()->for($resource)->for($version, 'gameVersion')->create([
        'name' => 'Scoped Resource',
    ]);

    $loaded = Resource::withDataForVersion('2.0.0-LIVE')->where('id', $resource->id)->first();

    expect($loaded->relationLoaded('data'))->toBeTrue()
        ->and($loaded->data->first()->name)->toBe('Scoped Resource');
});

it('filters resource data by requested or default version scope', function (): void {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'is_default' => true,
    ]);
    $ptuVersion = GameVersion::factory()->create([
        'code' => '1.0.0-PTU',
        'is_default' => false,
    ]);

    $resource = Resource::factory()->create();

    $defaultData = ResourceData::factory()->for($resource)->for($defaultVersion, 'gameVersion')->create(['name' => 'Default']);
    $ptuData = ResourceData::factory()->for($resource)->for($ptuVersion, 'gameVersion')->create(['name' => 'PTU']);

    $defaultResults = ResourceData::forRequestedOrDefaultVersion()->get();
    $ptuResults = ResourceData::forRequestedOrDefaultVersion('1.0.0-PTU')->get();

    expect($defaultResults->pluck('id')->toArray())->toContain($defaultData->id)
        ->and($defaultResults->pluck('id')->toArray())->not->toContain($ptuData->id)
        ->and($ptuResults->pluck('id')->toArray())->toContain($ptuData->id)
        ->and($ptuResults->pluck('id')->toArray())->not->toContain($defaultData->id);
});

it('filters resource locations by commodity key and quality range', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $resourceData = ResourceData::factory()->for($version, 'gameVersion')->create();
    $gold = Commodity::factory()->create(['key' => 'Ore_Gold']);
    $iron = Commodity::factory()->create(['key' => 'Ore_Iron']);

    ResourceLocation::factory()->for($resourceData)->create([
        'commodity_id' => $gold->id,
        'quality_min' => 501,
        'quality_max' => 1000,
    ]);
    ResourceLocation::factory()->for($resourceData)->create([
        'commodity_id' => $iron->id,
        'quality_min' => 1,
        'quality_max' => 500,
    ]);
    ResourceLocation::factory()->for($resourceData)->create([
        'commodity_id' => $gold->id,
        'quality_min' => 1,
        'quality_max' => 500,
    ]);

    $goldHighQuality = ResourceLocation::query()
        ->where('commodity_id', $gold->id)
        ->where('quality_min', '>=', 501)
        ->get();

    expect($goldHighQuality)->toHaveCount(1)
        ->and($goldHighQuality->first()->quality_min)->toBe(501);
});

it('location without quality data has null quality columns', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $resourceData = ResourceData::factory()->for($version, 'gameVersion')->create();

    $location = ResourceLocation::factory()->for($resourceData)->create([
        'commodity_id' => null,
        'quality_min' => null,
        'quality_max' => null,
        'quality_mean' => null,
        'quality_stddev' => null,
    ]);

    expect($location->commodity_id)->toBeNull()
        ->and($location->quality_min)->toBeNull()
        ->and($location->quality_max)->toBeNull()
        ->and($location->quality_mean)->toBeNull()
        ->and($location->quality_stddev)->toBeNull();
});
