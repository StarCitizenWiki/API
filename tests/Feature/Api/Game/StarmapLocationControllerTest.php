<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapAmenity;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
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
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $planetLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $stationLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
            'size' => 400.0,
            'data' => ['kind' => 'system'],
        ]);

    $planetData = StarmapLocationData::factory()
        ->for($planetLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'ArcCorp',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
            'size' => 120.0,
            'data' => ['kind' => 'planet'],
        ]);

    StarmapLocationData::factory()
        ->for($stationLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $planetData->id,
            'name' => 'Baijini Point',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
            'size' => 10.0,
            'respawn_location_type' => 'Hospital',
            'data' => ['kind' => 'station'],
        ]);

    StarmapLocationData::factory()
        ->for($stationLocation, 'location')
        ->for($this->oldVersion, 'gameVersion')
        ->create([
            'name' => 'Old Baijini Point',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
            'size' => 15.0,
            'data' => ['kind' => 'station-old'],
        ]);

    $response = $this->getJson('/api/starmap-locations?filter[type_name]=Station&filter[parent_name]=Arc&filter[system_name]=Stan&sort=-size');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $stationLocation->uuid)
        ->assertJsonPath('data.0.name', 'Baijini Point')
        ->assertJsonPath('data.0.system_name', 'Stanton')
        ->assertJsonPath('data.0.parent_name', 'ArcCorp')
        ->assertJsonPath('data.0.child_count', 0)
        ->assertJsonPath('data.0.version', $this->defaultVersion->code);
});

it('returns version scoped child counts and allows sorting by child_count', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $parentWithChildren = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $parentWithoutChildren = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    $parentWithChildrenData = StarmapLocationData::factory()
        ->for($parentWithChildren, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'ArcCorp',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
        ]);

    StarmapLocationData::factory()
        ->for($parentWithoutChildren, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'MicroTech',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
        ]);

    $childLocationOne = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $childLocationTwo = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $legacyChildLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    StarmapLocationData::factory()
        ->for($childLocationOne, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $parentWithChildrenData->id,
            'name' => 'Area18',
            'type_name' => 'LandingZone',
            'type_classification' => 'Landing Zone',
        ]);

    StarmapLocationData::factory()
        ->for($childLocationTwo, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $parentWithChildrenData->id,
            'name' => 'Baijini Point',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    StarmapLocationData::factory()
        ->for($legacyChildLocation, 'location')
        ->for($this->oldVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $parentWithChildrenData->id,
            'name' => 'Legacy Child',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    $response = $this->getJson('/api/starmap-locations?filter[type_name]=Planet&sort=-child_count');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $parentWithChildren->uuid)
        ->assertJsonPath('data.0.child_count', 2)
        ->assertJsonPath('data.1.uuid', $parentWithoutChildren->uuid)
        ->assertJsonPath('data.1.child_count', 0);
});

it('filters starmap locations by amenity display name and comma delimited values', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $clinicLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $armorLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $clinicAmenity = StarmapAmenity::factory()->create([
        'name' => 'Clinic',
        'display_name' => 'Clinic',
    ]);

    $buyArmorAmenity = StarmapAmenity::factory()->create([
        'name' => 'BuyArmor',
        'display_name' => 'Buy Armor',
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    $clinicData = StarmapLocationData::factory()
        ->for($clinicLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Covalex Clinic',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    $armorData = StarmapLocationData::factory()
        ->for($armorLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Armor Hub',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    $clinicData->amenities()->sync([$clinicAmenity->id]);
    $armorData->amenities()->sync([$buyArmorAmenity->id]);

    $this->getJson('/api/starmap-locations?filter[amenity]=Buy+Armor')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $armorLocation->uuid)
        ->assertJsonMissing([
            'uuid' => $clinicLocation->uuid,
        ]);

    $this->getJson('/api/starmap-locations?filter[amenity]=Buy+Armor,Clinic')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment([
            'uuid' => $clinicLocation->uuid,
        ])
        ->assertJsonFragment([
            'uuid' => $armorLocation->uuid,
        ]);

    $this->getJson('/api/starmap-locations?filter[amenity]='.$buyArmorAmenity->uuid)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $armorLocation->uuid);
});

it('filters starmap locations by parent and system uuids', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $otherSystemLocation = StarmapLocation::factory()->create();
    $otherSystemLocation->update([
        'system_uuid' => $otherSystemLocation->uuid,
    ]);

    $parentLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $otherParentLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $matchingLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $differentParentLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $differentSystemLocation = StarmapLocation::factory()->create([
        'system_uuid' => $otherSystemLocation->uuid,
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    StarmapLocationData::factory()
        ->for($otherSystemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Pyro',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    $parentData = StarmapLocationData::factory()
        ->for($parentLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'ArcCorp',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
        ]);

    $otherParentData = StarmapLocationData::factory()
        ->for($otherParentLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Hurston',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
        ]);

    $matchingData = StarmapLocationData::factory()
        ->for($matchingLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $parentData->id,
            'name' => 'Baijini Point',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    StarmapLocationData::factory()
        ->for($differentParentLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $otherParentData->id,
            'name' => 'Everus Harbor',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    StarmapLocationData::factory()
        ->for($differentSystemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Ruin Station',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    $this->getJson('/api/starmap-locations?filter[type_name]=Station&filter[parent_uuid]='.$parentLocation->uuid)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingLocation->uuid)
        ->assertJsonPath('data.0.name', $matchingData->name);

    $this->getJson('/api/starmap-locations?filter[type_name]=Station&filter[system_uuid]='.$systemLocation->uuid)
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
    $literalSystemLocation->update([
        'system_uuid' => $literalSystemLocation->uuid,
    ]);

    $wildcardSystemLocation = StarmapLocation::factory()->create();
    $wildcardSystemLocation->update([
        'system_uuid' => $wildcardSystemLocation->uuid,
    ]);

    $literalParentLocation = StarmapLocation::factory()->create([
        'system_uuid' => $literalSystemLocation->uuid,
    ]);

    $wildcardParentLocation = StarmapLocation::factory()->create([
        'system_uuid' => $literalSystemLocation->uuid,
    ]);

    $otherSystemParentLocation = StarmapLocation::factory()->create([
        'system_uuid' => $wildcardSystemLocation->uuid,
    ]);

    $matchingLocation = StarmapLocation::factory()->create([
        'system_uuid' => $literalSystemLocation->uuid,
    ]);

    $parentWildcardLocation = StarmapLocation::factory()->create([
        'system_uuid' => $literalSystemLocation->uuid,
    ]);

    $systemWildcardLocation = StarmapLocation::factory()->create([
        'system_uuid' => $wildcardSystemLocation->uuid,
    ]);

    StarmapLocationData::factory()
        ->for($literalSystemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stan_100%',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    StarmapLocationData::factory()
        ->for($wildcardSystemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'StanX100Y',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    $literalParentData = StarmapLocationData::factory()
        ->for($literalParentLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Arc_100%',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
        ]);

    $wildcardParentData = StarmapLocationData::factory()
        ->for($wildcardParentLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'ArcA100Y',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
        ]);

    $otherSystemParentData = StarmapLocationData::factory()
        ->for($otherSystemParentLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Arc_100%',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
        ]);

    StarmapLocationData::factory()
        ->for($matchingLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $literalParentData->id,
            'name' => 'Baijini Point',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    StarmapLocationData::factory()
        ->for($parentWildcardLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $wildcardParentData->id,
            'name' => 'Area18 Station',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    StarmapLocationData::factory()
        ->for($systemWildcardLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $otherSystemParentData->id,
            'name' => 'Orbituary',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    $this->getJson('/api/starmap-locations?filter[type_name]=Station&filter[parent_name]=Arc_100%25&filter[system_name]=Stan_100%25')
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
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $networkTag = EntityTag::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'ArcCorp Network',
    ]);

    $transitTag = EntityTag::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Transit',
    ]);

    $networkLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $transitLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    StarmapLocationData::factory()
        ->for($networkLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'type_name' => 'LandingZone',
            'type_classification' => 'Landing Zone',
            'location_hierarchy_entity_tag_id' => $networkTag->id,
        ]);

    StarmapLocationData::factory()
        ->for($transitLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Port Tressler',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
            'location_hierarchy_entity_tag_id' => $transitTag->id,
        ]);

    $this->getJson('/api/starmap-locations?filter[tag]=ArcCorp+Network')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $networkLocation->uuid)
        ->assertJsonMissing([
            'uuid' => $transitLocation->uuid,
        ]);

    $this->getJson('/api/starmap-locations?filter[tag]='.$networkTag->uuid)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $networkLocation->uuid);
});

it('filters out starmap locations that do not have a system', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $validLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $systemlessLocation = StarmapLocation::factory()->create([
        'system_uuid' => null,
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    StarmapLocationData::factory()
        ->for($validLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'type_name' => 'LandingZone',
            'type_classification' => 'Landing Zone',
        ]);

    StarmapLocationData::factory()
        ->for($systemlessLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Orphaned Location',
            'type_name' => 'Outpost',
            'type_classification' => 'Outpost',
        ]);

    $response = $this->getJson('/api/starmap-locations');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonMissing([
            'uuid' => $systemlessLocation->uuid,
            'name' => 'Orphaned Location',
        ]);
});

it('shows a starmap location by uuid with requested includes', function (): void {
    $tag = EntityTag::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'ArcCorp Network',
    ]);

    $amenity = StarmapAmenity::factory()->create([
        'name' => 'Clinic',
        'display_name' => 'Clinic',
    ]);

    $systemLocation = StarmapLocation::factory()->create();
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $parentLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $childLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
            'data' => ['kind' => 'system'],
        ]);

    $parentData = StarmapLocationData::factory()
        ->for($parentLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'ArcCorp',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
            'location_hierarchy_entity_tag_id' => $tag->id,
            'data' => ['kind' => 'parent'],
        ]);

    $childData = StarmapLocationData::factory()
        ->for($childLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $parentData->id,
            'name' => 'Baijini Point',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
            'respawn_location_type' => 'Hospital',
            'location_hierarchy_entity_tag_id' => $tag->id,
            'data' => ['kind' => 'child'],
        ]);

    $childData->amenities()->sync([$amenity->id]);

    $response = $this->getJson('/api/starmap-locations/'.$childLocation->uuid.'?include=location,parent,amenities,tag');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $childLocation->uuid)
        ->assertJsonPath('data.location.uuid', $childLocation->uuid)
        ->assertJsonPath('data.parent.uuid', $parentLocation->uuid)
        ->assertJsonPath('data.parent.name', 'ArcCorp')
        ->assertJsonPath('data.amenities.0.display_name', 'Clinic')
        ->assertJsonPath('data.tag.uuid', $tag->uuid)
        ->assertJsonPath('data.version', $this->defaultVersion->code);
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

    $this->getJson('/api/starmap-locations/'.$location->uuid)
        ->assertNotFound();
});

it('returns filter facets scoped by the active request filters', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $planetLocation = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $stationLocation = StarmapLocation::factory()->create([
        'system_uuid' => $planetLocation->system_uuid,
    ]);

    $clinicAmenity = StarmapAmenity::factory()->create([
        'name' => 'Clinic',
        'display_name' => 'Clinic',
    ]);

    $hangarAmenity = StarmapAmenity::factory()->create([
        'name' => 'Hangar',
        'display_name' => 'Hangar',
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
            'data' => ['kind' => 'system'],
        ]);

    $planetData = StarmapLocationData::factory()
        ->for($planetLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'ArcCorp',
            'type_name' => 'Planet',
            'type_classification' => 'Planet',
            'jurisdiction_name' => 'UEE',
            'jurisdiction_is_prison' => false,
            'data' => ['kind' => 'planet'],
        ]);

    $stationData = StarmapLocationData::factory()
        ->for($stationLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'parent_data_id' => $planetData->id,
            'name' => 'Klescher',
            'type_name' => 'Station',
            'type_classification' => 'Prison',
            'jurisdiction_name' => 'UEE',
            'jurisdiction_is_prison' => true,
            'data' => ['kind' => 'station'],
        ]);

    $planetData->amenities()->sync([$clinicAmenity->id]);
    $stationData->amenities()->sync([$hangarAmenity->id]);

    $response = $this->getJson('/api/starmap-locations/filters?filter[jurisdiction_name]=UEE&filter[type_name]=Planet');

    $response->assertSuccessful()
        ->assertJsonPath('filters.type_name.0.value', 'Planet')
        ->assertJsonPath('filters.type_name.0.label', 'Planet')
        ->assertJsonPath('filters.system_name.0.value', 'Stanton')
        ->assertJsonPath('filters.system_name.0.label', 'Stanton')
        ->assertJsonPath('filters.jurisdiction_is_prison.0.value', false)
        ->assertJsonPath('filters.jurisdiction_is_prison.0.label', 'No')
        ->assertJsonPath('filters.amenity.0.value', $clinicAmenity->uuid)
        ->assertJsonPath('filters.amenity.0.label', 'Clinic')
        ->assertJsonMissingPath('filters.type_name.0.count')
        ->assertJsonMissingPath('filters.system_name.0.count')
        ->assertJsonMissingPath('filters.jurisdiction_is_prison.0.count')
        ->assertJsonMissingPath('filters.amenity.0.count')
        ->assertJsonMissing([
            'value' => 'Hangar',
        ]);

    $this->getJson('/api/starmap-locations/filters?filter[jurisdiction_name]=UEE&filter[type_name]=Station')
        ->assertSuccessful()
        ->assertJsonPath('filters.parent_name.0.value', 'ArcCorp');
});

it('returns separate amenity facet rows for duplicate labels with different uuids', function (): void {
    $systemLocation = StarmapLocation::factory()->create();
    $systemLocation->update([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $clinicLocationOne = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $clinicLocationTwo = StarmapLocation::factory()->create([
        'system_uuid' => $systemLocation->uuid,
    ]);

    $clinicAmenityOne = StarmapAmenity::factory()->create([
        'name' => 'ClinicPrimary',
        'display_name' => 'Clinic',
    ]);

    $clinicAmenityTwo = StarmapAmenity::factory()->create([
        'name' => 'ClinicSecondary',
        'display_name' => 'Clinic',
    ]);

    StarmapLocationData::factory()
        ->for($systemLocation, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Stanton',
            'type_name' => 'SolarSystem',
            'type_classification' => 'Solar System',
        ]);

    $clinicDataOne = StarmapLocationData::factory()
        ->for($clinicLocationOne, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Covalex Clinic',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    $clinicDataTwo = StarmapLocationData::factory()
        ->for($clinicLocationTwo, 'location')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Port Tressler Clinic',
            'type_name' => 'Station',
            'type_classification' => 'Manmade',
        ]);

    $clinicDataOne->amenities()->sync([$clinicAmenityOne->id]);
    $clinicDataTwo->amenities()->sync([$clinicAmenityTwo->id]);

    $this->getJson('/api/starmap-locations/filters?filter[type_name]=Station')
        ->assertSuccessful()
        ->assertJsonCount(2, 'filters.amenity')
        ->assertJsonFragment([
            'value' => $clinicAmenityOne->uuid,
            'label' => 'Clinic',
        ])
        ->assertJsonFragment([
            'value' => $clinicAmenityTwo->uuid,
            'label' => 'Clinic',
        ]);
});
