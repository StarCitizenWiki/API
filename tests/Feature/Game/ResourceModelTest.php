<?php

declare(strict_types=1);

use App\Enums\Game\ResourceKind;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;

it('creates a resource with uuid only', function (): void {
    $resource = Resource::factory()->create();

    expect($resource)->toBeInstanceOf(Resource::class)
        ->and($resource->uuid)->not->toBeEmpty()
        ->and($resource->getRouteKeyName())->toBe('uuid');
});

it('has many resource data records', function (): void {
    $resource = Resource::factory()->create();
    $versionA = GameVersion::factory()->create(['code' => '1.0.0-LIVE', 'is_default' => true]);
    $versionB = GameVersion::factory()->create(['code' => '2.0.0-LIVE', 'is_default' => false]);

    ResourceData::factory()->for($resource)->for($versionA, 'gameVersion')->create(['name' => 'Data A']);
    ResourceData::factory()->for($resource)->for($versionB, 'gameVersion')->create(['name' => 'Data B']);

    expect($resource->data)->toHaveCount(2);
});

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

it('creates resource data with commodity relationship', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $resource = Resource::factory()->create();
    $resourceData = ResourceData::factory()->for($resource)->for($version, 'gameVersion')->create();
    $commodity = Commodity::factory()->create();

    $resourceData->commodities()->attach($commodity->id, [
        'weight' => 0.5000,
        'min_percentage' => 10.0000,
        'max_percentage' => 50.0000,
        'probability' => 0.7500,
        'quality_scale' => 1.2000,
        'curve_exponent' => 0.8000,
    ]);

    expect($resourceData->commodities)->toHaveCount(1)
        ->and($resourceData->commodities->first()->pivot->weight)->toBe('0.5000')
        ->and($resourceData->commodities->first()->pivot->min_percentage)->toBe('10.0000')
        ->and($resourceData->commodities->first()->pivot->curve_exponent)->toBe('0.8000');
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

it('commodity has physical properties', function (): void {
    $commodity = Commodity::factory()->create([
        'instability' => 123.4567,
        'resistance' => -0.5000,
        'density_g_per_cc' => 2.7000,
    ]);

    expect((float) $commodity->instability)->toBe(123.4567)
        ->and((float) $commodity->resistance)->toBe(-0.5000)
        ->and((float) $commodity->density_g_per_cc)->toBe(2.7000);
});

it('creates a resource location with quality data', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $resourceData = ResourceData::factory()->for($version, 'gameVersion')->create();
    $commodity = Commodity::factory()->create(['key' => 'Ore_Gold']);

    $location = ResourceLocation::factory()->for($resourceData)->create([
        'group_name' => 'Mineables',
        'group_probability' => 0.500000,
        'relative_probability' => 0.1234567890,
        'resource_kind' => 'mineable',
        'commodity_id' => $commodity->id,
        'quality_min' => 501,
        'quality_max' => 1000,
        'quality_mean' => 750,
        'quality_stddev' => 150,
        'data' => ['clustering' => ['Key' => 'A'], 'cave_type' => 'rock'],
    ]);

    expect($location->resource_data_id)->toBe($resourceData->id)
        ->and($location->group_name)->toBe('Mineables')
        ->and((float) $location->group_probability)->toBe(0.500000)
        ->and((float) $location->relative_probability)->toBe(0.1234567890)
        ->and($location->resource_kind)->toBe(ResourceKind::Mineable)
        ->and($location->commodity_id)->toBe($commodity->id)
        ->and($location->quality_min)->toBe(501)
        ->and($location->quality_max)->toBe(1000)
        ->and($location->quality_mean)->toBe(750)
        ->and($location->quality_stddev)->toBe(150)
        ->and($location->data->get('clustering'))->not->toBeNull()
        ->and($location->data->get('cave_type'))->toBe('rock');
});

it('resource location belongs to resource data', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $resourceData = ResourceData::factory()->for($version, 'gameVersion')->create();
    $location = ResourceLocation::factory()->for($resourceData)->create();

    expect($location->resourceData)->toBeInstanceOf(ResourceData::class)
        ->and($location->resourceData->id)->toBe($resourceData->id);
});

it('resource data has many locations', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $resourceData = ResourceData::factory()->for($version, 'gameVersion')->create();

    ResourceLocation::factory()->for($resourceData)->count(3)->create();

    expect($resourceData->locations)->toHaveCount(3);
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
