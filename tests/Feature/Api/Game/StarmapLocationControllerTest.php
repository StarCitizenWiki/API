<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapAmenity;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Support\Filters\FilterCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

function createStarmapLocationData(
    GameVersion $version,
    array $attributes,
    ?StarmapLocation $location = null,
): StarmapLocationData {
    $location ??= StarmapLocation::factory()->create();

    return StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($version, 'gameVersion')
        ->create($attributes);
}

beforeEach(function (): void {
    app()->instance('env', 'production');
    app('cache')->setDefaultDriver('array');
    app('cache')->forgetDriver(['array', 'database']);
    Cache::store('array')->flush();

    $this->defaultVersion = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->oldVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subMonth(),
    ]);
});

it('lists versioned starmap locations with filters and sorting', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $starLocation = StarmapLocation::factory()->create();
    $planetLocation = StarmapLocation::factory()->create();
    $stationLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'size' => 400.0,
        'data' => [
            'kind' => 'system',
            'type' => [
                'classification' => 'Solar System',
            ],
        ],
    ], $systemLocation);

    $starData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'Star',
        'data' => [
            'kind' => 'star',
            'type' => [
                'classification' => 'Star',
            ],
        ],
    ], $starLocation);

    $planetData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $starData->id,
        'star_data_id' => $starData->id,
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'size' => 120.0,
        'data' => [
            'kind' => 'planet',
            'type' => [
                'classification' => 'Planet',
            ],
        ],
    ], $planetLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $planetData->id,
        'star_data_id' => $starData->id,
        'name' => 'Baijini Point',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'size' => 10.0,
        'data' => [
            'kind' => 'station',
            'type' => [
                'classification' => 'Manmade',
            ],
            'respawnLocationType' => 'Hospital',
        ],
    ], $stationLocation);

    createStarmapLocationData($this->oldVersion, [
        'name' => 'Old Baijini Point',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'size' => 15.0,
        'data' => [
            'kind' => 'station-old',
            'type' => [
                'classification' => 'Manmade',
            ],
        ],
    ], $stationLocation);

    $response = $this->getJson('/api/locations?filter[type_name]=Station&filter[parent_name]=Arc&filter[system]=Stan&sort=-size');

    $response->assertSuccessful()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 1, fn (AssertableJson $json) => $json
                ->where('uuid', $stationLocation->uuid)
                ->where('name', 'Baijini Point')
                ->where('web_url', route('web.locations.show', ['identifier' => $stationLocation->uuid]))
                ->where('system', 'Stanton')
                ->where('star.uuid', $starLocation->uuid)
                ->where('parent.name', 'ArcCorp')
                ->where('type.name', 'Station')
                ->where('child_count', 0)
                ->where('version', $this->defaultVersion->code)
                ->missing('children')
                ->etc()
            )
            ->etc()
        );
});

it('returns version scoped child counts and allows sorting by child_count', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $parentWithChildren = StarmapLocation::factory()->create();
    $parentWithoutChildren = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => [
            'type' => [
                'classification' => 'Solar System',
            ],
        ],
    ], $systemLocation);

    $parentWithChildrenData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => [
            'type' => [
                'classification' => 'Planet',
            ],
        ],
    ], $parentWithChildren);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'MicroTech',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => [
            'type' => [
                'classification' => 'Planet',
            ],
        ],
    ], $parentWithoutChildren);

    $childLocationOne = StarmapLocation::factory()->create();
    $childLocationTwo = StarmapLocation::factory()->create();
    $legacyChildLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentWithChildrenData->id,
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'data' => [
            'type' => [
                'classification' => 'Landing Zone',
            ],
        ],
    ], $childLocationOne);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentWithChildrenData->id,
        'name' => 'Baijini Point',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'type' => [
                'classification' => 'Manmade',
            ],
        ],
    ], $childLocationTwo);

    createStarmapLocationData($this->oldVersion, [
        'parent_data_id' => $parentWithChildrenData->id,
        'name' => 'Legacy Child',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'type' => [
                'classification' => 'Manmade',
            ],
        ],
    ], $legacyChildLocation);

    $response = $this->getJson('/api/locations?filter[type_name]=Planet&sort=-child_count');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $parentWithChildren->uuid)
        ->assertJsonPath('data.0.child_count', 2)
        ->assertJsonPath('data.1.uuid', $parentWithoutChildren->uuid)
        ->assertJsonPath('data.1.child_count', 0);
});

it('filters starmap locations by amenity display name and comma delimited values', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $clinicLocation = StarmapLocation::factory()->create();
    $armorLocation = StarmapLocation::factory()->create();

    $clinicAmenity = StarmapAmenity::factory()->create([
        'name' => 'Clinic',
        'display_name' => 'Clinic',
    ]);

    $buyArmorAmenity = StarmapAmenity::factory()->create([
        'name' => 'BuyArmor',
        'display_name' => 'Buy Armor',
    ]);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => [
            'type' => [
                'classification' => 'Solar System',
            ],
        ],
    ], $systemLocation);

    $clinicData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Covalex Clinic',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'type' => [
                'classification' => 'Manmade',
            ],
        ],
    ], $clinicLocation);

    $armorData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Armor Hub',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'type' => [
                'classification' => 'Manmade',
            ],
        ],
    ], $armorLocation);

    $clinicData->amenities()->sync([$clinicAmenity->id]);
    $armorData->amenities()->sync([$buyArmorAmenity->id]);

    $this->getJson('/api/locations?filter[amenity]=Buy+Armor')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $armorLocation->uuid)
        ->assertJsonMissing([
            'uuid' => $clinicLocation->uuid,
        ]);

    $this->getJson('/api/locations?filter[amenity]=Buy+Armor,Clinic')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment([
            'uuid' => $clinicLocation->uuid,
        ])
        ->assertJsonFragment([
            'uuid' => $armorLocation->uuid,
        ]);

    $this->getJson('/api/locations?filter[amenity]='.$buyArmorAmenity->uuid)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $armorLocation->uuid);
});

it('filters starmap locations by parent uuid and system name', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $otherSystemLocation = StarmapLocation::factory()->create();
    $parentLocation = StarmapLocation::factory()->create();
    $otherParentLocation = StarmapLocation::factory()->create();
    $matchingLocation = StarmapLocation::factory()->create();
    $differentParentLocation = StarmapLocation::factory()->create();
    $differentSystemLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Pyro',
        'system' => 'Pyro',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $otherSystemLocation);

    $parentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => ['type' => ['classification' => 'Planet']],
    ], $parentLocation);

    $otherParentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Hurston',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => ['type' => ['classification' => 'Planet']],
    ], $otherParentLocation);

    $matchingData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentData->id,
        'name' => 'Baijini Point',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $matchingLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $otherParentData->id,
        'name' => 'Everus Harbor',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $differentParentLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Ruin Station',
        'system' => 'Pyro',
        'type_name' => 'Station',
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $differentSystemLocation);

    $this->getJson('/api/locations?filter[type_name]=Station&filter[parent_uuid]='.$parentLocation->uuid)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingLocation->uuid)
        ->assertJsonPath('data.0.name', $matchingData->name);

    $this->getJson('/api/locations?filter[type_name]=Station&filter[system]=Stan')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment([
            'uuid' => $matchingLocation->uuid,
        ])
        ->assertJsonFragment([
            'uuid' => $differentParentLocation->uuid,
        ])
        ->assertJsonMissing([
            'uuid' => $differentSystemLocation->uuid,
        ]);
});

it('treats wildcard characters in parent and system name filters as literal characters', function (): void {
    $literalSystemLocation = StarmapLocation::factory()->create();
    $wildcardSystemLocation = StarmapLocation::factory()->create();
    $literalParentLocation = StarmapLocation::factory()->create();
    $wildcardParentLocation = StarmapLocation::factory()->create();
    $otherSystemParentLocation = StarmapLocation::factory()->create();
    $matchingLocation = StarmapLocation::factory()->create();
    $parentWildcardLocation = StarmapLocation::factory()->create();
    $systemWildcardLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stan_100%',
        'system' => 'Stan_100%',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $literalSystemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'StanX100Y',
        'system' => 'StanX100Y',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $wildcardSystemLocation);

    $literalParentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Arc_100%',
        'system' => 'Stan_100%',
        'type_name' => 'Planet',
        'data' => ['type' => ['classification' => 'Planet']],
    ], $literalParentLocation);

    $wildcardParentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcA100Y',
        'system' => 'Stan_100%',
        'type_name' => 'Planet',
        'data' => ['type' => ['classification' => 'Planet']],
    ], $wildcardParentLocation);

    $otherSystemParentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Arc_100%',
        'system' => 'StanX100Y',
        'type_name' => 'Planet',
        'data' => ['type' => ['classification' => 'Planet']],
    ], $otherSystemParentLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $literalParentData->id,
        'name' => 'Baijini Point',
        'system' => 'Stan_100%',
        'type_name' => 'Station',
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $matchingLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $wildcardParentData->id,
        'name' => 'Area18 Station',
        'system' => 'Stan_100%',
        'type_name' => 'Station',
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $parentWildcardLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $otherSystemParentData->id,
        'name' => 'Orbituary',
        'system' => 'StanX100Y',
        'type_name' => 'Station',
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $systemWildcardLocation);

    $this->getJson('/api/locations?filter[type_name]=Station&filter[parent_name]=Arc_100%25&filter[system]=Stan_100%25')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingLocation->uuid)
        ->assertJsonMissing([
            'uuid' => $parentWildcardLocation->uuid,
        ])
        ->assertJsonMissing([
            'uuid' => $systemWildcardLocation->uuid,
        ]);
});

it('filters starmap locations by tag name without querying uuid columns with text values', function (): void {
    $systemLocation = StarmapLocation::factory()->create();

    $networkTag = EntityTag::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'ArcCorp Network',
    ]);

    $transitTag = EntityTag::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Transit',
    ]);

    $networkLocation = StarmapLocation::factory()->create();
    $transitLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'location_hierarchy_entity_tag_id' => $networkTag->id,
        'data' => ['type' => ['classification' => 'Landing Zone']],
    ], $networkLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Port Tressler',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'location_hierarchy_entity_tag_id' => $transitTag->id,
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $transitLocation);

    $this->getJson('/api/locations?filter[tag]=ArcCorp+Network')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $networkLocation->uuid)
        ->assertJsonMissing([
            'uuid' => $transitLocation->uuid,
        ]);

    $this->getJson('/api/locations?filter[tag]='.$networkTag->uuid)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $networkLocation->uuid);
});

it('filters out starmap locations that do not have a system', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $validLocation = StarmapLocation::factory()->create();
    $systemlessLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'data' => ['type' => ['classification' => 'Landing Zone']],
    ], $validLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Orphaned Location',
        'system' => null,
        'type_name' => 'Outpost',
        'data' => ['type' => ['classification' => 'Outpost']],
    ], $systemlessLocation);

    $response = $this->getJson('/api/locations');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonMissing([
            'uuid' => $systemlessLocation->uuid,
            'name' => 'Orphaned Location',
        ]);
});

it('shows a detailed starmap location by uuid', function (): void {
    $tag = EntityTag::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'ArcCorp Network',
    ]);

    $amenity = StarmapAmenity::factory()->create([
        'name' => 'Clinic',
        'display_name' => 'Clinic',
    ]);

    $systemLocation = StarmapLocation::factory()->create();
    $starLocation = StarmapLocation::factory()->create();
    $parentLocation = StarmapLocation::factory()->create();
    $childLocation = StarmapLocation::factory()->create();

    $systemData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => [
            'kind' => 'system',
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => false,
            ],
        ],
    ], $systemLocation);

    $starData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $systemData->id,
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'Star',
        'data' => [
            'kind' => 'star',
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'Star',
                'classification' => 'Star',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => false,
            ],
        ],
    ], $starLocation);

    $parentData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $starData->id,
        'star_data_id' => $starData->id,
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'location_hierarchy_entity_tag_id' => $tag->id,
        'data' => [
            'kind' => 'parent',
            'type' => [
                'classification' => 'Planet',
            ],
        ],
    ], $parentLocation);

    $childData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentData->id,
        'star_data_id' => $starData->id,
        'name' => 'Baijini Point',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'location_hierarchy_entity_tag_id' => $tag->id,
        'data' => [
            'kind' => 'child',
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'Station',
                'classification' => 'Manmade',
                'spawnNavPoints' => true,
                'validQuantumTravelDestination' => true,
            ],
            'jurisdiction' => [
                'uuid' => fake()->uuid(),
                'name' => 'UEE',
                'baseFine' => 125,
                'maxStolenGoodsPossessionScu' => 1,
                'isPrison' => false,
            ],
            'affiliation' => [
                'uuid' => fake()->uuid(),
                'displayName' => 'Covalex',
            ],
            'radarContactType' => [
                'uuid' => fake()->uuid(),
                'name' => 'SpaceStation',
                'displayName' => 'Nav Point',
                'tagUuid' => fake()->uuid(),
                'tagName' => 'SpaceStation',
                'isObjectOfInterest' => false,
            ],
        ],
    ], $childLocation);

    $childData->amenities()->sync([$amenity->id]);

    $response = $this->getJson('/api/locations/'.$childLocation->uuid);

    $response->assertSuccessful()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.uuid', $childLocation->uuid)
            ->where('data.web_url', route('web.locations.show', ['identifier' => $childLocation->uuid]))
            ->where('data.system', 'Stanton')
            ->where('data.star.uuid', $starLocation->uuid)
            ->where('data.parent.uuid', $parentLocation->uuid)
            ->where('data.child_count', 0)
            ->where('data.type.name', 'Station')
            ->where('data.type.spawn_nav_points', true)
            ->where('data.jurisdiction.name', 'UEE')
            ->where('data.affiliation.name', 'Covalex')
            ->where('data.amenities.0.display_name', 'Clinic')
            ->where('data.tag.uuid', $tag->uuid)
            ->where('data.radar_contact_type.name', 'SpaceStation')
            ->where('data.version', $this->defaultVersion->code)
            ->missing('data.children')
            ->etc()
        );
});

it('returns null for optional detailed starmap objects when source data is absent', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $childLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => [
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => false,
            ],
        ],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Ghost Hollow',
        'system' => 'Stanton',
        'type_name' => 'Outpost',
        'location_hierarchy_entity_tag_id' => null,
        'data' => [
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'Outpost',
                'classification' => 'Outpost',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => true,
            ],
        ],
    ], $childLocation);

    $this->getJson('/api/locations/'.$childLocation->uuid)
        ->assertSuccessful()
        ->assertJsonPath('data.star', null)
        ->assertJsonPath('data.parent', null)
        ->assertJsonPath('data.child_count', 0)
        ->assertJsonMissingPath('data.children')
        ->assertJsonPath('data.jurisdiction', null)
        ->assertJsonPath('data.affiliation', null)
        ->assertJsonPath('data.tag', null)
        ->assertJsonPath('data.radar_contact_type', null);
});

it('shows child links on the detailed parent starmap location response when requested', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $parentLocation = StarmapLocation::factory()->create();
    $childLocation = StarmapLocation::factory()->create();
    $grandchildLocation = StarmapLocation::factory()->create();

    $tag = EntityTag::factory()->create([
        'name' => 'Landing Zone',
    ]);

    $amenity = StarmapAmenity::factory()->create([
        'name' => 'refuel',
        'display_name' => 'Refuel',
    ]);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => [
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => false,
            ],
        ],
    ], $systemLocation);

    $parentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => [
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'Planet',
                'classification' => 'Planet',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => true,
            ],
        ],
    ], $parentLocation);

    $childData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentData->id,
        'location_hierarchy_entity_tag_id' => $tag->id,
        'name' => 'Area18',
        'system' => 'Stanton',
        'description' => 'Major landing zone on ArcCorp.',
        'type_name' => 'LandingZone',
        'size' => 12.5,
        'is_scannable' => true,
        'block_travel' => true,
        'data' => [
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'LandingZone',
                'classification' => 'Landing Zone',
                'spawnNavPoints' => true,
                'validQuantumTravelDestination' => true,
            ],
            'jurisdiction' => [
                'uuid' => fake()->uuid(),
                'name' => 'UEE',
                'baseFine' => 125,
                'maxStolenGoodsPossessionScu' => 1,
                'isPrison' => false,
            ],
            'affiliation' => [
                'uuid' => fake()->uuid(),
                'displayName' => 'ArcCorp',
            ],
            'radarContactType' => [
                'uuid' => fake()->uuid(),
                'name' => 'LandingZone',
                'displayName' => 'Landing Zone',
                'tagUuid' => fake()->uuid(),
                'tagName' => 'LandingZone',
                'isObjectOfInterest' => true,
            ],
            'respawnLocationType' => 'Hospital',
            'hideInStarmap' => true,
            'hideInWorld' => false,
            'quantumTravel' => [
                'arrivalRadius' => 1500,
            ],
            'asteroidRing' => [
                'innerRadius' => 25,
            ],
        ],
    ], $childLocation);

    $childData->amenities()->sync([$amenity->id]);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $childData->id,
        'name' => 'Area18 Commons',
        'system' => 'Stanton',
        'type_name' => 'District',
        'data' => [
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'District',
                'classification' => 'District',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => false,
            ],
        ],
    ], $grandchildLocation);

    $this->getJson('/api/locations/'.$parentLocation->uuid.'?include=children')
        ->assertSuccessful()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.parent', null)
            ->where('data.child_count', 1)
            ->has('data.children', 1, fn (AssertableJson $json) => $json
                ->where('uuid', $childLocation->uuid)
                ->where('name', 'Area18')
                ->where('web_url', route('web.locations.show', ['identifier' => $childLocation->uuid]))
                ->where('type_name', 'LandingZone')
                ->where('respawn_location_type', 'Hospital')
                ->where('amenities.0.display_name', 'Refuel')
                ->where('amenity_labels.0', 'Refuel')
                ->missing('type')
                ->missing('system')
                ->missing('parent')
                ->missing('children')
                ->etc()
            )
            ->etc()
        );
});

it('does not allow include children on the starmap index response', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $systemLocation);

    $this->getJson('/api/locations?include=children')
        ->assertStatus(400);
});

it('shows a star as child on the detailed solar system response when imported hierarchy links it', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $starLocation = StarmapLocation::factory()->create();

    $systemData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton System',
        'system' => 'Stanton System',
        'type_name' => 'SolarSystem',
        'data' => [
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => false,
            ],
        ],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $systemData->id,
        'name' => 'Stanton',
        'system' => 'Stanton System',
        'type_name' => 'Star',
        'data' => [
            'type' => [
                'uuid' => fake()->uuid(),
                'name' => 'Star',
                'classification' => 'Star',
                'spawnNavPoints' => false,
                'validQuantumTravelDestination' => false,
            ],
        ],
    ], $starLocation);

    $this->getJson('/api/locations/'.$systemLocation->uuid.'?include=children')
        ->assertSuccessful()
        ->assertJsonPath('data.parent', null)
        ->assertJsonPath('data.child_count', 1)
        ->assertJsonPath('data.children.0.uuid', $starLocation->uuid)
        ->assertJsonPath('data.children.0.name', 'Stanton')
        ->assertJsonPath('data.children.0.type_name', 'Star');
});

it('returns 404 when the location has no data for the requested or default version', function (): void {
    $location = StarmapLocation::factory()->create();

    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->oldVersion, 'gameVersion')
        ->create([
            'name' => 'Legacy Port',
            'type_name' => 'Station',
            'data' => ['kind' => 'legacy'],
        ]);

    $this->getJson('/api/locations/'.$location->uuid)
        ->assertNotFound();
});

it('returns filter facets scoped by the active request filters', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $planetLocation = StarmapLocation::factory()->create();
    $stationLocation = StarmapLocation::factory()->create();

    $clinicAmenity = StarmapAmenity::factory()->create([
        'name' => 'Clinic',
        'display_name' => 'Clinic',
    ]);

    $hangarAmenity = StarmapAmenity::factory()->create([
        'name' => 'Hangar',
        'display_name' => 'Hangar',
    ]);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => [
            'kind' => 'system',
            'type' => ['classification' => 'Solar System'],
        ],
    ], $systemLocation);

    $planetData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'block_travel' => false,
        'data' => [
            'kind' => 'planet',
            'type' => ['classification' => 'Planet'],
            'jurisdiction' => ['name' => 'UEE'],
            'affiliation' => ['displayName' => 'Empire'],
        ],
    ], $planetLocation);

    $stationData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $planetData->id,
        'name' => 'Klescher',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'block_travel' => true,
        'data' => [
            'kind' => 'station',
            'type' => ['classification' => 'Prison'],
            'respawnLocationType' => 'Hospital',
            'jurisdiction' => ['name' => 'Advocacy'],
            'affiliation' => ['displayName' => 'Corrections'],
        ],
    ], $stationLocation);

    $planetData->amenities()->sync([$clinicAmenity->id]);
    $stationData->amenities()->sync([$hangarAmenity->id]);

    $response = $this->getJson('/api/locations/filters?filter[block_travel]=false&filter[type_name]=Planet');

    $response->assertSuccessful()
        ->assertJsonPath('filters.type_name.0.value', 'Planet')
        ->assertJsonPath('filters.type_name.0.label', 'Planet')
        ->assertJsonPath('filters.type_name.0.count', 1)
        ->assertJsonPath('filters.type_classification.0.value', 'Planet')
        ->assertJsonPath('filters.type_classification.0.label', 'Planet')
        ->assertJsonPath('filters.type_classification.0.count', 1)
        ->assertJsonPath('filters.jurisdiction_name.0.value', 'UEE')
        ->assertJsonPath('filters.jurisdiction_name.0.label', 'UEE')
        ->assertJsonPath('filters.jurisdiction_name.0.count', 1)
        ->assertJsonPath('filters.affiliation_name.0.value', 'Empire')
        ->assertJsonPath('filters.affiliation_name.0.label', 'Empire')
        ->assertJsonPath('filters.affiliation_name.0.count', 1)
        ->assertJsonPath('filters.system.0.value', 'Stanton')
        ->assertJsonPath('filters.system.0.label', 'Stanton')
        ->assertJsonPath('filters.system.0.count', 1)
        ->assertJsonPath('filters.amenity.0.value', $clinicAmenity->uuid)
        ->assertJsonPath('filters.amenity.0.label', 'Clinic')
        ->assertJsonPath('filters.amenity.0.count', 1)
        ->assertJsonMissing([
            'value' => 'Hangar',
        ]);

    $this->getJson('/api/locations/filters?filter[block_travel]=true&filter[type_name]=Station')
        ->assertSuccessful()
        ->assertJsonPath('filters.parent_name.0.value', 'ArcCorp')
        ->assertJsonPath('filters.respawn_location_type.0.value', 'Hospital')
        ->assertJsonPath('filters.jurisdiction_name.0.value', 'Advocacy')
        ->assertJsonPath('filters.affiliation_name.0.value', 'Corrections');
});

it('filters starmap locations by restored json-backed fields', function (): void {
    $matchingLocation = StarmapLocation::factory()->create();
    $otherLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Everus Harbor',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'type' => ['classification' => 'Orbital Station'],
            'respawnLocationType' => 'Hospital',
            'jurisdiction' => ['name' => 'UEE'],
            'affiliation' => ['displayName' => 'Covalex'],
        ],
    ], $matchingLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Kareah',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'type' => ['classification' => 'Security Post'],
            'respawnLocationType' => 'Clinic',
            'jurisdiction' => ['name' => 'Crusader'],
            'affiliation' => ['displayName' => 'Crusader Security'],
        ],
    ], $otherLocation);

    $this->getJson('/api/locations?filter[type_classification]=Orbital+Station&filter[respawn_location_type]=Hospital&filter[jurisdiction_name]=UEE&filter[affiliation_name]=Covalex')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingLocation->uuid)
        ->assertJsonMissing([
            'uuid' => $otherLocation->uuid,
        ]);
});

it('returns separate amenity facet rows for duplicate labels with different uuids', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $clinicLocationOne = StarmapLocation::factory()->create();
    $clinicLocationTwo = StarmapLocation::factory()->create();

    $clinicAmenityOne = StarmapAmenity::factory()->create([
        'name' => 'ClinicPrimary',
        'display_name' => 'Clinic',
    ]);

    $clinicAmenityTwo = StarmapAmenity::factory()->create([
        'name' => 'ClinicSecondary',
        'display_name' => 'Clinic',
    ]);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $systemLocation);

    $clinicDataOne = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Covalex Clinic',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $clinicLocationOne);

    $clinicDataTwo = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Port Tressler Clinic',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['type' => ['classification' => 'Manmade']],
    ], $clinicLocationTwo);

    $clinicDataOne->amenities()->sync([$clinicAmenityOne->id]);
    $clinicDataTwo->amenities()->sync([$clinicAmenityTwo->id]);

    $this->getJson('/api/locations/filters?filter[type_name]=Station')
        ->assertSuccessful()
        ->assertJsonCount(2, 'filters.amenity')
        ->assertJsonFragment([
            'value' => $clinicAmenityOne->uuid,
            'label' => 'Clinic',
            'count' => 1,
        ])
        ->assertJsonFragment([
            'value' => $clinicAmenityTwo->uuid,
            'label' => 'Clinic',
            'count' => 1,
        ]);
});

it('caches only broad starmap facet responses', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $systemLocation);

    $broadKey = FilterCache::starmapLocationsKey($this->defaultVersion->code);

    $this->getJson(route('locations.filters', ['version' => $this->defaultVersion->code]))
        ->assertOk();

    expect(Cache::get('filters:index:starmap-locations'))->toBe([$broadKey])
        ->and(Cache::get($broadKey))->not->toBeNull();

    Cache::flush();

    $this->getJson(route('locations.filters', [
        'version' => $this->defaultVersion->code,
        'filter' => ['type_name' => 'SolarSystem'],
    ]))->assertOk();

    expect(Cache::get('filters:index:starmap-locations'))->toBeNull()
        ->and(Cache::get($broadKey))->toBeNull();
});

it('treats blank starmap facet inputs as broad cache requests', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => ['type' => ['classification' => 'Solar System']],
    ], $systemLocation);

    $broadKey = FilterCache::starmapLocationsKey($this->defaultVersion->code);

    $this->getJson(route('locations.filters', [
        'version' => $this->defaultVersion->code,
        'filter' => ['type_name' => ''],
    ]))->assertOk();

    expect(Cache::get('filters:index:starmap-locations'))->toBe([$broadKey])
        ->and(Cache::get($broadKey))->not->toBeNull();
});
