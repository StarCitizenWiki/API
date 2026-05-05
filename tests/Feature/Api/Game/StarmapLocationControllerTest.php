<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\EntityTag;
use App\Models\Game\Faction;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceCommodity;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\Resource\ResourceProvider;
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
            'Type' => [
                'Classification' => 'Solar System',
            ],
        ],
    ], $systemLocation);

    $starData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'Star',
        'data' => [
            'kind' => 'star',
            'Type' => [
                'Classification' => 'Star',
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
            'Type' => [
                'Classification' => 'Planet',
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
            'Type' => [
                'Classification' => 'Manmade',
            ],
            'RespawnLocationType' => 'Hospital',
        ],
    ], $stationLocation);

    createStarmapLocationData($this->oldVersion, [
        'name' => 'Old Baijini Point',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'size' => 15.0,
        'data' => [
            'kind' => 'station-old',
            'Type' => [
                'Classification' => 'Manmade',
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
            'Type' => [
                'Classification' => 'Solar System',
            ],
        ],
    ], $systemLocation);

    $parentWithChildrenData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => [
            'Type' => [
                'Classification' => 'Planet',
            ],
        ],
    ], $parentWithChildren);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'MicroTech',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => [
            'Type' => [
                'Classification' => 'Planet',
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
            'Type' => [
                'Classification' => 'Landing Zone',
            ],
        ],
    ], $childLocationOne);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentWithChildrenData->id,
        'name' => 'Baijini Point',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'Type' => [
                'Classification' => 'Manmade',
            ],
        ],
    ], $childLocationTwo);

    createStarmapLocationData($this->oldVersion, [
        'parent_data_id' => $parentWithChildrenData->id,
        'name' => 'Legacy Child',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'Type' => [
                'Classification' => 'Manmade',
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
            'Type' => [
                'Classification' => 'Solar System',
            ],
        ],
    ], $systemLocation);

    $clinicData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Covalex Clinic',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'Type' => [
                'Classification' => 'Manmade',
            ],
        ],
    ], $clinicLocation);

    $armorData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Armor Hub',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'Type' => [
                'Classification' => 'Manmade',
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
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Pyro',
        'system' => 'Pyro',
        'type_name' => 'SolarSystem',
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $otherSystemLocation);

    $parentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => ['Type' => ['Classification' => 'Planet']],
    ], $parentLocation);

    $otherParentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Hurston',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => ['Type' => ['Classification' => 'Planet']],
    ], $otherParentLocation);

    $matchingData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentData->id,
        'name' => 'Baijini Point',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
    ], $matchingLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $otherParentData->id,
        'name' => 'Everus Harbor',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
    ], $differentParentLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Ruin Station',
        'system' => 'Pyro',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
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
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $literalSystemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'StanX100Y',
        'system' => 'StanX100Y',
        'type_name' => 'SolarSystem',
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $wildcardSystemLocation);

    $literalParentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Arc_100%',
        'system' => 'Stan_100%',
        'type_name' => 'Planet',
        'data' => ['Type' => ['Classification' => 'Planet']],
    ], $literalParentLocation);

    $wildcardParentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcA100Y',
        'system' => 'Stan_100%',
        'type_name' => 'Planet',
        'data' => ['Type' => ['Classification' => 'Planet']],
    ], $wildcardParentLocation);

    $otherSystemParentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Arc_100%',
        'system' => 'StanX100Y',
        'type_name' => 'Planet',
        'data' => ['Type' => ['Classification' => 'Planet']],
    ], $otherSystemParentLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $literalParentData->id,
        'name' => 'Baijini Point',
        'system' => 'Stan_100%',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
    ], $matchingLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $wildcardParentData->id,
        'name' => 'Area18 Station',
        'system' => 'Stan_100%',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
    ], $parentWildcardLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $otherSystemParentData->id,
        'name' => 'Orbituary',
        'system' => 'StanX100Y',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
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
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'location_hierarchy_entity_tag_id' => $networkTag->id,
        'data' => ['Type' => ['Classification' => 'Landing Zone']],
    ], $networkLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Port Tressler',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'location_hierarchy_entity_tag_id' => $transitTag->id,
        'data' => ['Type' => ['Classification' => 'Manmade']],
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
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'data' => ['Type' => ['Classification' => 'Landing Zone']],
    ], $validLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Orphaned Location',
        'system' => null,
        'type_name' => 'Outpost',
        'data' => ['Type' => ['Classification' => 'Outpost']],
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
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => false,
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
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'Star',
                'Classification' => 'Star',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => false,
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
            'Type' => [
                'Classification' => 'Planet',
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
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'Station',
                'Classification' => 'Manmade',
                'SpawnNavPoints' => true,
                'ValidQuantumTravelDestination' => true,
            ],
            'Jurisdiction' => [
                'UUID' => fake()->uuid(),
                'Name' => 'UEE',
                'BaseFine' => 125,
                'MaxStolenGoodsPossessionScu' => 1,
                'IsPrison' => false,
            ],
            'Affiliation' => [
                'UUID' => fake()->uuid(),
                'DisplayName' => 'Covalex',
            ],
            'RadarContactType' => [
                'UUID' => fake()->uuid(),
                'Name' => 'SpaceStation',
                'DisplayName' => 'Nav Point',
                'TagUUID' => fake()->uuid(),
                'TagName' => 'SpaceStation',
                'IsObjectOfInterest' => false,
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
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => false,
            ],
        ],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Ghost Hollow',
        'system' => 'Stanton',
        'type_name' => 'Outpost',
        'location_hierarchy_entity_tag_id' => null,
        'data' => [
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'Outpost',
                'Classification' => 'Outpost',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => true,
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
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => false,
            ],
        ],
    ], $systemLocation);

    $parentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => [
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'Planet',
                'Classification' => 'Planet',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => true,
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
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'LandingZone',
                'Classification' => 'Landing Zone',
                'SpawnNavPoints' => true,
                'ValidQuantumTravelDestination' => true,
            ],
            'Jurisdiction' => [
                'UUID' => fake()->uuid(),
                'Name' => 'UEE',
                'BaseFine' => 125,
                'MaxStolenGoodsPossessionScu' => 1,
                'IsPrison' => false,
            ],
            'Affiliation' => [
                'UUID' => fake()->uuid(),
                'DisplayName' => 'ArcCorp',
            ],
            'RadarContactType' => [
                'UUID' => fake()->uuid(),
                'Name' => 'LandingZone',
                'DisplayName' => 'Landing Zone',
                'TagUUID' => fake()->uuid(),
                'TagName' => 'LandingZone',
                'IsObjectOfInterest' => true,
            ],
            'RespawnLocationType' => 'Hospital',
            'HideInStarmap' => true,
            'HideInWorld' => false,
            'QuantumTravel' => [
                'ArrivalRadius' => 1500,
            ],
            'AsteroidRing' => [
                'InnerRadius' => 25,
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
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'District',
                'Classification' => 'District',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => false,
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
                ->where('has_resources', false)
                ->missing('type')
                ->missing('system')
                ->missing('parent')
                ->missing('children')
                ->etc()
            )
            ->etc()
        );
});

it('includes has_resources on child summaries in show response', function (): void {
    $parentLocation = StarmapLocation::factory()->create();
    $childWithResourcesLocation = StarmapLocation::factory()->create();
    $childWithoutResourcesLocation = StarmapLocation::factory()->create();

    $parentData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
    ], $parentLocation);

    $childWithData = createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentData->id,
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
    ], $childWithResourcesLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $parentData->id,
        'name' => 'Area04',
        'system' => 'Stanton',
        'type_name' => 'Outpost',
    ], $childWithoutResourcesLocation);

    $resourceLocation = ResourceLocation::factory()->create();
    $childWithData->resourceLocations()->sync([$resourceLocation->id]);

    $this->getJson('/api/locations/'.$parentLocation->uuid.'?include=children')
        ->assertSuccessful()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.children', 2, fn (AssertableJson $childJson) => $childJson
                ->where('has_resources', fn (mixed $value): bool => is_bool($value))
                ->etc()
            )
            ->etc()
        )
        ->assertJsonPath('data.children.0.has_resources', false)
        ->assertJsonPath('data.children.1.has_resources', true);
});

it('ignores include children on the starmap index response', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton',
        'system' => 'Stanton',
        'type_name' => 'SolarSystem',
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $systemLocation);

    $this->getJson('/api/locations?include=children')
        ->assertSuccessful()
        ->assertJsonMissingPath('data.0.children');
});

it('shows a star as child on the detailed solar system response when imported hierarchy links it', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $starLocation = StarmapLocation::factory()->create();

    $systemData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Stanton System',
        'system' => 'Stanton System',
        'type_name' => 'SolarSystem',
        'data' => [
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => false,
            ],
        ],
    ], $systemLocation);

    createStarmapLocationData($this->defaultVersion, [
        'parent_data_id' => $systemData->id,
        'name' => 'Stanton',
        'system' => 'Stanton System',
        'type_name' => 'Star',
        'data' => [
            'Type' => [
                'UUID' => fake()->uuid(),
                'Name' => 'Star',
                'Classification' => 'Star',
                'SpawnNavPoints' => false,
                'ValidQuantumTravelDestination' => false,
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
            'Type' => ['Classification' => 'Solar System'],
        ],
    ], $systemLocation);

    $planetData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'block_travel' => false,
        'data' => [
            'kind' => 'planet',
            'Type' => ['Classification' => 'Planet'],
            'Jurisdiction' => ['Name' => 'UEE'],
            'Affiliation' => ['DisplayName' => 'Empire'],
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
            'Type' => ['Classification' => 'Prison'],
            'RespawnLocationType' => 'Hospital',
            'Jurisdiction' => ['Name' => 'Advocacy'],
            'Affiliation' => ['DisplayName' => 'Corrections'],
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
            'Type' => ['Classification' => 'Orbital Station'],
            'RespawnLocationType' => 'Hospital',
            'Jurisdiction' => ['Name' => 'UEE'],
            'Affiliation' => ['DisplayName' => 'Covalex'],
        ],
    ], $matchingLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Kareah',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => [
            'Type' => ['Classification' => 'Security Post'],
            'RespawnLocationType' => 'Clinic',
            'Jurisdiction' => ['Name' => 'Crusader'],
            'Affiliation' => ['DisplayName' => 'Crusader Security'],
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
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $systemLocation);

    $clinicDataOne = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Covalex Clinic',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
    ], $clinicLocationOne);

    $clinicDataTwo = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Port Tressler Clinic',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
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
        'data' => ['Type' => ['Classification' => 'Solar System']],
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
        'data' => ['Type' => ['Classification' => 'Solar System']],
    ], $systemLocation);

    $broadKey = FilterCache::starmapLocationsKey($this->defaultVersion->code);

    $this->getJson(route('locations.filters', [
        'version' => $this->defaultVersion->code,
        'filter' => ['type_name' => ''],
    ]))->assertOk();

    expect(Cache::get('filters:index:starmap-locations'))->toBe([$broadKey])
        ->and(Cache::get($broadKey))->not->toBeNull();
});

it('filters starmap locations by has_resources flag and includes has_resources in index response', function (): void {
    $withResourcesLocation = StarmapLocation::factory()->create();
    $withoutResourcesLocation = StarmapLocation::factory()->create();

    $withData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Hurston',
        'system' => 'Stanton',
        'type_name' => 'Planet',
        'data' => ['Type' => ['Classification' => 'Planet']],
    ], $withResourcesLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'data' => ['Type' => ['Classification' => 'Landing Zone']],
    ], $withoutResourcesLocation);

    $resourceLocation = ResourceLocation::factory()->create();
    $withData->resourceLocations()->sync([$resourceLocation->id]);

    $this->getJson('/api/locations?filter[has_resources]=true')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $withResourcesLocation->uuid)
        ->assertJsonPath('data.0.has_resources', true)
        ->assertJsonMissing(['uuid' => $withoutResourcesLocation->uuid]);

    $this->getJson('/api/locations?filter[has_resources]=false')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $withoutResourcesLocation->uuid)
        ->assertJsonPath('data.0.has_resources', false)
        ->assertJsonMissing(['uuid' => $withResourcesLocation->uuid]);

    $this->getJson('/api/locations')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('filters starmap locations by hide_minor_locations flag excluding OnlyShowWhenParentSelected', function (): void {
    $majorLocation = StarmapLocation::factory()->create();
    $minorLocation = StarmapLocation::factory()->create();
    $minorHiddenLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'data' => [
            'Type' => ['Classification' => 'Landing Zone'],
            'OnlyShowWhenParentSelected' => 'false',
        ],
    ], $majorLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp Security Post 011',
        'system' => 'Stanton',
        'type_name' => 'Outpost',
        'data' => [
            'Type' => ['Classification' => 'Outpost'],
            'OnlyShowWhenParentSelected' => 'true',
        ],
    ], $minorLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'ArcCorp Mining Area 045',
        'system' => 'Stanton',
        'type_name' => 'Outpost',
        'data' => [
            'Type' => ['Classification' => 'Outpost'],
            'OnlyShowWhenParentSelected' => 'true',
        ],
    ], $minorHiddenLocation);

    $this->getJson('/api/locations?filter[hide_minor_locations]=true')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $majorLocation->uuid)
        ->assertJsonMissing(['uuid' => $minorLocation->uuid])
        ->assertJsonMissing(['uuid' => $minorHiddenLocation->uuid]);

    $this->getJson('/api/locations')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('filters starmap locations by resource commodity name and uuid', function (): void {
    $quantaniumLocation = StarmapLocation::factory()->create();
    $hephaestaniteLocation = StarmapLocation::factory()->create();
    $noResourcesLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Daymar',
        'system' => 'Stanton',
        'type_name' => 'Moon',
        'data' => ['Type' => ['Classification' => 'Moon']],
    ], $quantaniumLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Yela',
        'system' => 'Stanton',
        'type_name' => 'Moon',
        'data' => ['Type' => ['Classification' => 'Moon']],
    ], $hephaestaniteLocation);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Port Olisar',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
    ], $noResourcesLocation);

    $quantanium = Commodity::factory()->create([
        'name' => 'Quantanium (Raw)',
    ]);

    $hephaestanite = Commodity::factory()->create([
        'name' => 'Hephaestanite (Raw)',
    ]);

    $quantaniumResourceData = ResourceData::factory()->create([
        'game_version_id' => $this->defaultVersion->id,
    ]);
    $quantaniumResourceData->commodities()->sync([$quantanium->id]);

    $hephaestaniteResourceData = ResourceData::factory()->create([
        'game_version_id' => $this->defaultVersion->id,
    ]);
    $hephaestaniteResourceData->commodities()->sync([$hephaestanite->id]);

    $quantaniumRL = ResourceLocation::factory()->create([
        'resource_data_id' => $quantaniumResourceData->id,
    ]);
    $hephaestaniteRL = ResourceLocation::factory()->create([
        'resource_data_id' => $hephaestaniteResourceData->id,
    ]);

    $quantaniumLocationData = StarmapLocationData::where('name', 'Daymar')
        ->where('game_version_id', $this->defaultVersion->id)
        ->first();
    $quantaniumLocationData->resourceLocations()->sync([$quantaniumRL->id]);

    $hephaestaniteLocationData = StarmapLocationData::where('name', 'Yela')
        ->where('game_version_id', $this->defaultVersion->id)
        ->first();
    $hephaestaniteLocationData->resourceLocations()->sync([$hephaestaniteRL->id]);

    $this->getJson('/api/locations?filter[resource]='.urlencode('Quantanium (Raw)'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $quantaniumLocation->uuid)
        ->assertJsonMissing(['uuid' => $hephaestaniteLocation->uuid]);

    $this->getJson('/api/locations?filter[resource]='.$quantanium->uuid)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $quantaniumLocation->uuid);

    $this->getJson('/api/locations?filter[resource]='.urlencode('Quantanium (Raw)').','.urlencode('Hephaestanite (Raw)'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['uuid' => $quantaniumLocation->uuid])
        ->assertJsonFragment(['uuid' => $hephaestaniteLocation->uuid]);

    $this->getJson('/api/locations?filter[resource]='.$quantanium->uuid.','.$hephaestanite->uuid)
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('groups resources by deposit in include=resources', function (): void {
    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Daymar',
        'system' => 'Stanton',
        'type_name' => 'Moon',
    ], $starmapLocation);

    $gold = Commodity::factory()->create(['name' => 'Gold', 'key' => 'Ore_Gold', 'tier' => 'rare']);
    $borase = Commodity::factory()->create(['name' => 'Borase', 'key' => 'Ore_Borase', 'tier' => 'legendary']);

    $resource = Resource::factory()->create();
    $resourceData = ResourceData::factory()->create([
        'resource_id' => $resource->id,
        'game_version_id' => $this->defaultVersion->id,
        'key' => 'MineableRock_SurfaceRare_Gold',
        'kind' => 'mineable',
    ]);

    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $gold->id,
        'max_percentage' => 0.7,
    ]);
    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $borase->id,
        'max_percentage' => 0.3,
    ]);

    $resourceLocation = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'quality_min' => 100,
        'quality_max' => 500,
    ]);
    $resourceLocation->starmapLocationData()->attach($locationData->id);

    $response = $this->getJson('/api/locations/'.$starmapLocation->uuid.'?include=resources');

    $response->assertSuccessful();

    $resources = $response->json('data.resources');
    $shipMiningGroup = collect($resources)->first(fn (array $g): bool => $g['mining_type'] === 'Ship Mining');
    expect($shipMiningGroup)->not->toBeNull();

    $groupResources = $shipMiningGroup['resources'];
    expect($groupResources)->toHaveCount(1);

    $deposit = $groupResources[0];
    expect($deposit)->not->toHaveKey('deposits')
        ->and($deposit['key'])->toBe('MineableRock_SurfaceRare_Gold')
        ->and($deposit['name'])->toBe('Gold')
        ->and($deposit['uuid'])->toBe($gold->uuid)
        ->and($deposit['tier'])->toBe('rare');
});

it('deduplicates resource locations when deposit has multiple commodities', function (): void {
    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Yela',
        'system' => 'Stanton',
        'type_name' => 'Moon',
    ], $starmapLocation);

    $gold = Commodity::factory()->create(['name' => 'Gold', 'key' => 'Ore_Gold', 'tier' => 'rare']);
    $bexalite = Commodity::factory()->create(['name' => 'Bexalite', 'key' => 'Ore_Bexalite', 'tier' => 'epic']);

    $resource = Resource::factory()->create();
    $resourceData = ResourceData::factory()->create([
        'resource_id' => $resource->id,
        'game_version_id' => $this->defaultVersion->id,
        'key' => 'MineableRock_SurfaceRare_Gold',
        'kind' => 'mineable',
    ]);

    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $gold->id,
        'max_percentage' => 0.5,
    ]);
    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $bexalite->id,
        'max_percentage' => 0.6,
    ]);

    $provider = ResourceProvider::factory()->create();

    $rl1 = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $provider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'quality_min' => 100,
        'quality_max' => 300,
    ]);
    $rl1->starmapLocationData()->attach($locationData->id);

    $rl2 = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $provider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'quality_min' => 400,
        'quality_max' => 800,
    ]);
    $rl2->starmapLocationData()->attach($locationData->id);

    $response = $this->getJson('/api/locations/'.$starmapLocation->uuid.'?include=resources');

    $response->assertSuccessful();

    $resources = $response->json('data.resources');
    $shipMiningGroup = collect($resources)->first(fn (array $g): bool => $g['mining_type'] === 'Ship Mining');
    $deposit = $shipMiningGroup['resources'][0];

    expect($deposit['materials'])->toHaveCount(2);

    $entryQualityMins = collect($deposit['materials'])->pluck('quality_min')->sort()->values()->all();
    expect($entryQualityMins)->toBe([100, 400]);
});

it('selects primary commodity by highest max_percentage', function (): void {
    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'microTech',
        'system' => 'Stanton',
        'type_name' => 'Planet',
    ], $starmapLocation);

    $gold = Commodity::factory()->create(['name' => 'Gold', 'key' => 'Ore_Gold', 'tier' => 'rare']);
    $bexalite = Commodity::factory()->create(['name' => 'Bexalite', 'key' => 'Ore_Bexalite', 'tier' => 'epic']);

    $resource = Resource::factory()->create();
    $resourceData = ResourceData::factory()->create([
        'resource_id' => $resource->id,
        'game_version_id' => $this->defaultVersion->id,
        'key' => 'MineableRock_SurfaceEpic_Bexalite',
        'kind' => 'mineable',
    ]);

    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $gold->id,
        'max_percentage' => 0.3,
    ]);
    ResourceCommodity::create([
        'resource_data_id' => $resourceData->id,
        'commodity_id' => $bexalite->id,
        'max_percentage' => 0.8,
    ]);

    $provider = ResourceProvider::factory()->create();

    $rlGold = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $provider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'commodity_id' => $gold->id,
    ]);
    $rlGold->starmapLocationData()->attach($locationData->id);

    $rlBexalite = ResourceLocation::factory()->create([
        'resource_data_id' => $resourceData->id,
        'resource_provider_id' => $provider->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
        'commodity_id' => $bexalite->id,
    ]);
    $rlBexalite->starmapLocationData()->attach($locationData->id);

    $response = $this->getJson('/api/locations/'.$starmapLocation->uuid.'?include=resources');

    $response->assertSuccessful();

    $resources = $response->json('data.resources');
    $shipMiningGroup = collect($resources)->first(fn (array $g): bool => $g['mining_type'] === 'Ship Mining');
    $deposit = $shipMiningGroup['resources'][0];

    expect($deposit['name'])->toBe('Bexalite')
        ->and($deposit['uuid'])->toBe($bexalite->uuid)
        ->and($deposit['tier'])->toBe('epic');

    $materials = $deposit['materials'];
    $bexaliteEntry = collect($materials)->first(fn (array $c): bool => $c['key'] === 'Ore_Bexalite');
    $goldEntry = collect($materials)->first(fn (array $c): bool => $c['key'] === 'Ore_Gold');
    expect($bexaliteEntry['is_current'])->toBeTrue()
        ->and($goldEntry['is_current'])->toBeFalse();
});

it('separates deposits with different keys within same mining type', function (): void {
    $starmapLocation = StarmapLocation::factory()->create();
    $locationData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Daymar',
        'system' => 'Stanton',
        'type_name' => 'Moon',
    ], $starmapLocation);

    $iron = Commodity::factory()->create(['name' => 'Iron', 'key' => 'Ore_Iron', 'tier' => 'common']);
    $gold = Commodity::factory()->create(['name' => 'Gold', 'key' => 'Ore_Gold', 'tier' => 'rare']);

    $ironResource = Resource::factory()->create();
    $ironResourceData = ResourceData::factory()->create([
        'resource_id' => $ironResource->id,
        'game_version_id' => $this->defaultVersion->id,
        'key' => 'MineableRock_SurfaceCommon_Iron',
        'kind' => 'mineable',
    ]);
    ResourceCommodity::create([
        'resource_data_id' => $ironResourceData->id,
        'commodity_id' => $iron->id,
        'max_percentage' => 1.0,
    ]);

    $goldResource = Resource::factory()->create();
    $goldResourceData = ResourceData::factory()->create([
        'resource_id' => $goldResource->id,
        'game_version_id' => $this->defaultVersion->id,
        'key' => 'MineableRock_SurfaceRare_Gold',
        'kind' => 'mineable',
    ]);
    ResourceCommodity::create([
        'resource_data_id' => $goldResourceData->id,
        'commodity_id' => $gold->id,
        'max_percentage' => 0.7,
    ]);

    $ironRL = ResourceLocation::factory()->create([
        'resource_data_id' => $ironResourceData->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
    ]);
    $ironRL->starmapLocationData()->attach($locationData->id);

    $goldRL = ResourceLocation::factory()->create([
        'resource_data_id' => $goldResourceData->id,
        'group_name' => 'SpaceShip_Mineables',
        'resource_kind' => 'mineable',
    ]);
    $goldRL->starmapLocationData()->attach($locationData->id);

    $response = $this->getJson('/api/locations/'.$starmapLocation->uuid.'?include=resources');

    $response->assertSuccessful();

    $resources = $response->json('data.resources');
    $shipMiningGroup = collect($resources)->first(fn (array $g): bool => $g['mining_type'] === 'Ship Mining');
    $groupResources = $shipMiningGroup['resources'];

    expect($groupResources)->toHaveCount(2);

    $names = collect($groupResources)->pluck('name')->sort()->values()->all();
    expect($names)->toBe(['Gold', 'Iron']);
});

it('includes mission_count on starmap location index responses', function (): void {
    $locationWithMissions = StarmapLocation::factory()->create();
    $locationWithoutMissions = StarmapLocation::factory()->create();

    $dataWithMissions = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'data' => ['Type' => ['Classification' => 'Landing Zone']],
    ], $locationWithMissions);

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Port Olisar',
        'system' => 'Stanton',
        'type_name' => 'Station',
        'data' => ['Type' => ['Classification' => 'Manmade']],
    ], $locationWithoutMissions);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'title' => 'Test Mission',
            'mission_type' => 'Bounty Hunter',
            'illegal' => true,
        ]);

    $dataWithMissions->missions()->attach($missionData->id, ['purpose' => 'Availability']);

    $this->getJson('/api/locations')
        ->assertSuccessful()
        ->assertJsonPath('data.0.mission_count', fn (mixed $count): bool => is_int($count))
        ->assertJsonPath('data.1.mission_count', fn (mixed $count): bool => is_int($count));

    $foundWith = false;
    $foundWithout = false;

    foreach ($this->getJson('/api/locations')->json('data') as $location) {
        if ($location['uuid'] === $locationWithMissions->uuid) {
            expect($location['mission_count'])->toBe(1);
            $foundWith = true;
        }
        if ($location['uuid'] === $locationWithoutMissions->uuid) {
            expect($location['mission_count'])->toBe(0);
            $foundWithout = true;
        }
    }

    expect($foundWith)->toBeTrue()
        ->and($foundWithout)->toBeTrue();
});

it('shows missions grouped by purpose on show response when requested via include', function (): void {
    $starmapLocation = StarmapLocation::factory()->create();

    $locationData = createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'data' => ['Type' => ['Classification' => 'Landing Zone']],
    ], $starmapLocation);

    $faction = Faction::factory()->create([
        'name' => 'Nine Tails',
    ]);

    $missionOne = Mission::factory()->create();
    $missionDataOne = MissionData::factory()
        ->for($missionOne, 'mission')
        ->for($this->defaultVersion, 'gameVersion')
        ->for($faction, 'faction')
        ->create([
            'title' => 'Bounty Hunt Target',
            'mission_type' => 'Bounty Hunter',
            'illegal' => false,
            'has_combat' => true,
            'reward_min' => 5000,
            'reward_max' => 10000,
            'reward_currency' => 'aUEC',
        ]);

    $missionTwo = Mission::factory()->create();
    $missionDataTwo = MissionData::factory()
        ->for($missionTwo, 'mission')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'title' => 'Deliver Package',
            'mission_type' => 'Delivery',
            'illegal' => false,
            'has_combat' => false,
            'reward_min' => 1000,
            'reward_max' => 3000,
            'reward_currency' => 'aUEC',
        ]);

    $locationData->missions()->attach($missionDataOne->id, ['purpose' => 'Availability']);
    $locationData->missions()->attach($missionDataTwo->id, ['purpose' => 'Completion']);

    $response = $this->getJson('/api/locations/'.$starmapLocation->uuid.'?include=missions');

    $response->assertSuccessful()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.mission_count', 2)
            ->has('data.missions', 2)
            ->has('data.missions.0', fn (AssertableJson $group) => $group
                ->where('purpose', 'Availability')
                ->has('missions', 1)
                ->has('missions.0', fn (AssertableJson $mission) => $mission
                    ->where('uuid', $missionOne->uuid)
                    ->where('title', 'Bounty Hunt Target')
                    ->where('mission_type', 'Bounty Hunter')
                    ->where('illegal', false)
                    ->where('has_combat', true)
                    ->where('faction.name', 'Nine Tails')
                    ->where('faction.uuid', $faction->uuid)
                    ->where('link', route('missions.show', ['mission' => $missionOne->uuid]))
                    ->where('web_url', route('web.missions.show', ['mission' => $missionOne->slug]))
                    ->etc()
                )
                ->etc()
            )
            ->has('data.missions.1', fn (AssertableJson $group) => $group
                ->where('purpose', 'Completion')
                ->has('missions', 1)
                ->has('missions.0', fn (AssertableJson $mission) => $mission
                    ->where('uuid', $missionTwo->uuid)
                    ->where('title', 'Deliver Package')
                    ->where('mission_type', 'Delivery')
                    ->etc()
                )
                ->etc()
            )
            ->etc()
        );
});

it('does not include missions on show response when include is not requested', function (): void {
    $starmapLocation = StarmapLocation::factory()->create();

    createStarmapLocationData($this->defaultVersion, [
        'name' => 'Area18',
        'system' => 'Stanton',
        'type_name' => 'LandingZone',
        'data' => ['Type' => ['Classification' => 'Landing Zone']],
    ], $starmapLocation);

    $this->getJson('/api/locations/'.$starmapLocation->uuid)
        ->assertSuccessful()
        ->assertJsonMissingPath('data.missions')
        ->assertJsonPath('data.mission_count', 0);
});

it('returns slug-based web_url when slug is available', function (): void {
    $location = StarmapLocation::factory()->create(['slug' => 'area18']);
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => [
                'Type' => [
                    'Classification' => 'Landing Zone',
                ],
            ],
        ]);

    $this->getJson('/api/locations/'.$location->uuid)
        ->assertSuccessful()
        ->assertJsonPath('data.slug', 'area18')
        ->assertJsonPath('data.web_url', route('web.locations.show', ['identifier' => 'area18']))
        ->assertJsonPath('data.link', route('locations.show', ['identifier' => $location->uuid]));
});

it('resolves a starmap location by slug', function (): void {
    $location = StarmapLocation::factory()->create(['slug' => 'port-tressler']);
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Port Tressler',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => [
                'Type' => [
                    'Classification' => 'Landing Zone',
                ],
            ],
        ]);

    $this->getJson('/api/locations/port-tressler')
        ->assertSuccessful()
        ->assertJsonPath('data.slug', 'port-tressler')
        ->assertJsonPath('data.name', 'Port Tressler');
});

it('returns uuid-based web_url when slug is null', function (): void {
    $location = StarmapLocation::factory()->create(['slug' => null]);
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Slugless Station',
            'system' => 'Stanton',
            'type_name' => 'Station',
            'data' => [
                'Type' => [
                    'Classification' => 'Manmade',
                ],
            ],
        ]);

    $this->getJson('/api/locations/'.$location->uuid)
        ->assertSuccessful()
        ->assertJsonPath('data.slug', null)
        ->assertJsonPath('data.web_url', route('web.locations.show', ['identifier' => $location->uuid]));
});

it('returns 404 for non-existent UUID', function (): void {
    $this->getJson('/api/locations/00000000-0000-0000-0000-000000000000')
        ->assertNotFound();
});
