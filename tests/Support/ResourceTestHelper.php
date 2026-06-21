<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceCommodity;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;

/**
 * Create a ResourceData row linked to a commodity via ResourceCommodity.
 * Shared by ResourceShowTest and ResourceFiltersTest.
 *
 * @param  array<string, mixed>  $overrides  ResourceData factory attributes.
 */
function createResourceData(Commodity $commodity, string $kind = 'mineable', array $overrides = []): ResourceData
{
    $test = test();
    $resource = Resource::factory()->create();
    $resourceData = ResourceData::factory()->create(array_merge([
        'resource_id' => $resource->id,
        'game_version_id' => $test->defaultVersion->id,
        'kind' => $kind,
    ], $overrides));

    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $commodity->id,
    ]);

    return $resourceData;
}

/**
 * Attach a StarmapLocation + StarmapLocationData + ResourceLocation trio to a ResourceData.
 * Returns the ResourceLocation so callers can navigate relations if needed.
 *
 * @param  array<string, mixed>  $overrides  ResourceLocation factory attributes.
 */
function attachLocation(
    ResourceData $resourceData,
    string $system,
    string $typeName,
    string $locationName,
    string $groupName = 'SpaceShip_Mineables',
    string $resourceKind = 'mineable',
    array $overrides = [],
): ResourceLocation {
    $test = test();

    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLocation->id,
        'game_version_id' => $test->defaultVersion->id,
        'name' => $locationName,
        'type_name' => $typeName,
        'system' => $system,
    ]);

    $resourceLocation = ResourceLocation::factory()->create(array_merge([
        'resource_data_id' => $resourceData->id,
        'resource_kind' => $resourceKind,
        'group_name' => $groupName,
    ], $overrides));

    $resourceLocation->starmapLocationData()->attach($locationData->id);

    return $resourceLocation;
}
