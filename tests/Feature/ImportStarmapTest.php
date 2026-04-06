<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapAmenity;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('fails when the game version does not exist', function (): void {
    $this->artisan('game:import-starmap', ['version' => 'missing'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "missing" does not exist. Please create it first.');
});

it('imports starmap data synchronously for a version', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.1.0',
    ]);

    $systemUuid = fake()->uuid();
    $childUuid = fake()->uuid();

    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'UUID' => $systemUuid,
            'Name' => 'Stanton',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 400,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $childUuid,
            'Name' => 'Area18',
            'ParentUUID' => $systemUuid,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 10,
            'MinimumDisplaySize' => 1,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'LandingZone',
                'Classification' => 'Landing Zone',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-starmap', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported starmap data for version 4.1.0.');

    $childData = StarmapLocationData::query()
        ->whereHas('location', fn ($query) => $query->where('uuid', $childUuid))
        ->first();

    expect(StarmapLocation::query()->count())->toBe(2)
        ->and(StarmapLocationData::query()->count())->toBe(2)
        ->and($childData?->system)->toBe('Stanton');
});

it('imports starmap hierarchy, tags, amenities, and system names and upserts on re-run', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.1.1',
    ]);

    $systemUuid = fake()->uuid();
    $planetUuid = fake()->uuid();
    $stationUuid = fake()->uuid();
    $tagUuid = fake()->uuid();
    $dockingAmenityUuid = fake()->uuid();
    $clinicAmenityUuid = fake()->uuid();

    EntityTag::query()->create([
        'uuid' => $tagUuid,
        'name' => 'Old Name',
    ]);

    $payload = [
        [
            'UUID' => $systemUuid,
            'Name' => 'Stanton',
            'Description' => 'Primary system',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 400,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => [
                'ArrivalRadius' => 7000,
            ],
            'LocationHierarchyTag' => [
                'UUID' => $tagUuid,
                'Name' => 'Stanton System',
            ],
            'Type' => [
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $planetUuid,
            'Name' => 'ArcCorp',
            'Description' => 'Planet node',
            'ParentUUID' => $systemUuid,
            'RespawnLocationType' => 'None',
            'IsScannable' => true,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 120,
            'MinimumDisplaySize' => 5,
            'QuantumTravel' => [
                'ArrivalRadius' => 4000,
            ],
            'LocationHierarchyTag' => [
                'UUID' => $tagUuid,
                'Name' => 'Stanton System',
            ],
            'Type' => [
                'Name' => 'Planet',
                'Classification' => 'Planet',
            ],
            'Jurisdiction' => [
                'Name' => 'UEE',
                'IsPrison' => false,
            ],
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $stationUuid,
            'Name' => 'Baijini Point',
            'Description' => 'Station node',
            'ParentUUID' => $planetUuid,
            'RespawnLocationType' => 'Hospital',
            'IsScannable' => true,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 10,
            'MinimumDisplaySize' => 1,
            'QuantumTravel' => [
                'ArrivalRadius' => 1000,
            ],
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'Manmade',
                'Classification' => 'Manmade',
            ],
            'Jurisdiction' => [
                'Name' => 'UEE',
                'IsPrison' => false,
            ],
            'Affiliation' => [
                'DisplayName' => 'Private Security',
            ],
            'AsteroidRing' => null,
            'Amenities' => [
                [
                    'UUID' => $dockingAmenityUuid,
                    'Name' => 'Docking',
                    'DisplayName' => 'Docking',
                ],
                [
                    'UUID' => $clinicAmenityUuid,
                    'Name' => 'Clinic',
                    'DisplayName' => 'Clinic',
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('starmap.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-starmap', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported starmap data for version 4.1.1.');

    expect(StarmapLocation::query()->count())->toBe(3)
        ->and(StarmapLocationData::query()->count())->toBe(3)
        ->and(StarmapAmenity::query()->count())->toBe(2);

    $systemLocation = StarmapLocation::query()->firstWhere('uuid', $systemUuid);
    $planetLocation = StarmapLocation::query()->firstWhere('uuid', $planetUuid);
    $stationLocation = StarmapLocation::query()->firstWhere('uuid', $stationUuid);

    expect($systemLocation)->not->toBeNull()
        ->and($planetLocation)->not->toBeNull()
        ->and($stationLocation)->not->toBeNull();

    $systemData = StarmapLocationData::query()->whereBelongsTo($systemLocation, 'location')->first();
    $planetData = StarmapLocationData::query()->whereBelongsTo($planetLocation, 'location')->first();
    $stationData = StarmapLocationData::query()->whereBelongsTo($stationLocation, 'location')->first();

    expect($systemData)->not->toBeNull()
        ->and($planetData)->not->toBeNull()
        ->and($stationData)->not->toBeNull()
        ->and($systemData->system)->toBe('Stanton')
        ->and($planetData->system)->toBe('Stanton')
        ->and($stationData->system)->toBe('Stanton')
        ->and($planetData->parent_data_id)->toBe($systemData->id)
        ->and($stationData->parent_data_id)->toBe($planetData->id)
        ->and($stationData->jurisdiction_name)->toBe('UEE')
        ->and($stationData->affiliation_name)->toBe('Private Security')
        ->and($stationData->amenities()->pluck('name')->all())->toBe(['Clinic', 'Docking']);

    expect($planetData->children()->pluck('name')->all())->toBe(['Baijini Point']);

    $tag = EntityTag::query()->firstWhere('uuid', $tagUuid);
    expect($tag)->not->toBeNull()
        ->and($planetData->location_hierarchy_entity_tag_id)->toBe($tag->id);

    $payload[2]['Description'] = 'Updated station node';
    $payload[2]['Amenities'] = [
        [
            'UUID' => $dockingAmenityUuid,
            'Name' => 'Docking',
            'DisplayName' => 'Docking',
        ],
    ];

    Storage::disk('scunpacked')->put('starmap.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-starmap', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported starmap data for version 4.1.1.');

    $stationData->refresh();

    expect(StarmapLocationData::query()
        ->where('starmap_location_id', $stationLocation->id)
        ->where('game_version_id', $version->id)
        ->count())->toBe(1)
        ->and($stationData->description)->toBe('Updated station node')
        ->and($stationData->amenities()->pluck('name')->all())->toBe(['Docking']);
});

it('repairs stale system names when the starmap import command is rerun', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.1.2',
    ]);

    $systemUuid = fake()->uuid();
    $planetUuid = fake()->uuid();

    $systemLocation = StarmapLocation::query()->create([
        'uuid' => $systemUuid,
    ]);

    $planetLocation = StarmapLocation::query()->create([
        'uuid' => $planetUuid,
    ]);

    StarmapLocationData::query()->create([
        'starmap_location_id' => $systemLocation->id,
        'game_version_id' => $version->id,
        'parent_data_id' => null,
        'location_hierarchy_entity_tag_id' => null,
        'name' => 'Old Stanton',
        'system' => null,
        'description' => null,
        'type_name' => 'SolarSystem',
        'size' => 400,
        'is_scannable' => false,
        'block_travel' => false,
        'data' => [
            'Type' => [
                'Classification' => 'Solar System',
            ],
            'RespawnLocationType' => 'None',
            'MinimumDisplaySize' => 0,
            'HideInStarmap' => false,
            'HideInWorld' => false,
        ],
    ]);

    StarmapLocationData::query()->create([
        'starmap_location_id' => $planetLocation->id,
        'game_version_id' => $version->id,
        'parent_data_id' => null,
        'location_hierarchy_entity_tag_id' => null,
        'name' => 'Old ArcCorp',
        'system' => null,
        'description' => null,
        'type_name' => 'Planet',
        'size' => 120,
        'is_scannable' => false,
        'block_travel' => false,
        'data' => [
            'Type' => [
                'Classification' => 'Planet',
            ],
            'RespawnLocationType' => 'None',
            'MinimumDisplaySize' => 5,
            'HideInStarmap' => false,
            'HideInWorld' => false,
        ],
    ]);

    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'UUID' => $systemUuid,
            'Name' => 'Stanton',
            'Description' => 'Primary system',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 400,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $planetUuid,
            'Name' => 'ArcCorp',
            'Description' => 'Planet node',
            'ParentUUID' => $systemUuid,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 120,
            'MinimumDisplaySize' => 5,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'Planet',
                'Classification' => 'Planet',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-starmap', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported starmap data for version 4.1.2.');

    expect($systemLocation->fresh()?->dataForVersion($version->code)->first()?->system)->toBe('Stanton')
        ->and($planetLocation->fresh()?->dataForVersion($version->code)->first()?->system)->toBe('Stanton');
});

it('maps root stars to the solar system name when the source omits a parent uuid', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.1.21',
    ]);

    $solarSystemUuid = fake()->uuid();
    $starUuid = fake()->uuid();

    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'UUID' => $solarSystemUuid,
            'Name' => 'Stanton System',
            'Description' => 'System record',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 400,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $starUuid,
            'Name' => 'Stanton',
            'Description' => 'Root star',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 696000000,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'Star',
                'Classification' => 'Star',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-starmap', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported starmap data for version 4.1.21.');

    $solarSystemLocation = StarmapLocation::query()->firstWhere('uuid', $solarSystemUuid);
    $starLocation = StarmapLocation::query()->firstWhere('uuid', $starUuid);

    $solarSystemData = StarmapLocationData::query()->whereBelongsTo($solarSystemLocation, 'location')->first();
    $starData = StarmapLocationData::query()->whereBelongsTo($starLocation, 'location')->first();

    expect($solarSystemLocation)->not->toBeNull()
        ->and($starLocation)->not->toBeNull()
        ->and($solarSystemData)->not->toBeNull()
        ->and($starData)->not->toBeNull()
        ->and($solarSystemData->system)->toBe('Stanton System')
        ->and($starData->system)->toBe('Stanton System')
        ->and($solarSystemData->star_data_id)->toBeNull()
        ->and($starData->star_data_id)->toBe($starData->id)
        ->and($starData->parent_data_id)->toBeNull()
        ->and($solarSystemData->children()->pluck('name')->all())->toBe([]);
});

it('repairs stale system data for detached stars when the starmap import command is rerun', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.1.22',
    ]);

    $solarSystemUuid = fake()->uuid();
    $starUuid = fake()->uuid();

    $solarSystemLocation = StarmapLocation::query()->create([
        'uuid' => $solarSystemUuid,
    ]);

    $starLocation = StarmapLocation::query()->create([
        'uuid' => $starUuid,
    ]);

    $solarSystemData = StarmapLocationData::query()->create([
        'starmap_location_id' => $solarSystemLocation->id,
        'game_version_id' => $version->id,
        'parent_data_id' => null,
        'location_hierarchy_entity_tag_id' => null,
        'name' => 'Old Stanton System',
        'system' => null,
        'description' => null,
        'type_name' => 'SolarSystem',
        'size' => 400,
        'is_scannable' => false,
        'block_travel' => false,
        'data' => [
            'Type' => [
                'Classification' => 'Solar System',
            ],
            'RespawnLocationType' => 'None',
            'MinimumDisplaySize' => 0,
            'HideInStarmap' => false,
            'HideInWorld' => false,
        ],
    ]);

    $starData = StarmapLocationData::query()->create([
        'starmap_location_id' => $starLocation->id,
        'game_version_id' => $version->id,
        'parent_data_id' => null,
        'location_hierarchy_entity_tag_id' => null,
        'name' => 'Old Stanton',
        'system' => null,
        'description' => null,
        'type_name' => 'Star',
        'size' => 696000000,
        'is_scannable' => false,
        'block_travel' => false,
        'data' => [
            'Type' => [
                'Classification' => 'Star',
            ],
            'RespawnLocationType' => 'None',
            'MinimumDisplaySize' => 0,
            'HideInStarmap' => false,
            'HideInWorld' => false,
        ],
    ]);

    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'UUID' => $solarSystemUuid,
            'Name' => 'Stanton System',
            'Description' => 'System record',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 400,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $starUuid,
            'Name' => 'Stanton',
            'Description' => 'Root star',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 696000000,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'Star',
                'Classification' => 'Star',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-starmap', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported starmap data for version 4.1.22.');

    expect($solarSystemData->fresh()?->system)->toBe('Stanton System')
        ->and($starData->fresh()?->system)->toBe('Stanton System')
        ->and($solarSystemData->fresh()?->parent_data_id)->toBeNull()
        ->and($solarSystemData->fresh()?->star_data_id)->toBeNull()
        ->and($starData->fresh()?->star_data_id)->toBe($starData->id)
        ->and($starData->fresh()?->parent_data_id)->toBeNull();
});

it('maps descendants of a root star to the matching solar system uuid', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.1.3',
    ]);

    $solarSystemUuid = fake()->uuid();
    $starUuid = fake()->uuid();
    $planetUuid = fake()->uuid();
    $outpostUuid = fake()->uuid();

    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'UUID' => $solarSystemUuid,
            'Name' => 'Stanton System',
            'Description' => 'System record',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => true,
            'HideInWorld' => true,
            'BlockTravel' => true,
            'Size' => 0.1,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'SolarSystem',
                'Classification' => 'Solar System',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $starUuid,
            'Name' => 'Stanton',
            'Description' => 'Root star',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => true,
            'Size' => 696000000,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'Star',
                'Classification' => 'Star',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $planetUuid,
            'Name' => 'ArcCorp',
            'Description' => 'Planet node',
            'ParentUUID' => $starUuid,
            'RespawnLocationType' => 'None',
            'IsScannable' => true,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 120,
            'MinimumDisplaySize' => 5,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'Planet',
                'Classification' => 'Planet',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $outpostUuid,
            'Name' => 'Area18',
            'Description' => 'Landing zone node',
            'ParentUUID' => $planetUuid,
            'RespawnLocationType' => 'None',
            'IsScannable' => true,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 10,
            'MinimumDisplaySize' => 1,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'LandingZone',
                'Classification' => 'Landing Zone',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-starmap', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported starmap data for version 4.1.3.');

    $starLocation = StarmapLocation::query()->firstWhere('uuid', $starUuid);
    $planetLocation = StarmapLocation::query()->firstWhere('uuid', $planetUuid);
    $outpostLocation = StarmapLocation::query()->firstWhere('uuid', $outpostUuid);
    $starData = StarmapLocationData::query()->whereBelongsTo($starLocation, 'location')->first();
    $planetData = StarmapLocationData::query()->whereBelongsTo($planetLocation, 'location')->first();
    $outpostData = StarmapLocationData::query()->whereBelongsTo($outpostLocation, 'location')->first();

    expect(StarmapLocationData::query()->whereHas('location', fn ($query) => $query->where('uuid', $solarSystemUuid))->first()?->system)->toBe('Stanton System')
        ->and($starData?->system)->toBe('Stanton System')
        ->and($planetData?->system)->toBe('Stanton System')
        ->and($outpostData?->system)->toBe('Stanton System')
        ->and($starData?->star_data_id)->toBe($starData?->id)
        ->and($planetData?->star_data_id)->toBe($starData?->id)
        ->and($outpostData?->star_data_id)->toBe($starData?->id);
});

it('falls back to the root star name when a root star has no matching solar system row', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.1.4',
    ]);

    $starUuid = fake()->uuid();
    $planetUuid = fake()->uuid();

    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'UUID' => $starUuid,
            'Name' => 'Orion',
            'Description' => 'Root star',
            'ParentUUID' => null,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => true,
            'Size' => 100,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'Star',
                'Classification' => 'Star',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
        [
            'UUID' => $planetUuid,
            'Name' => 'Orion I',
            'Description' => 'Planet node',
            'ParentUUID' => $starUuid,
            'RespawnLocationType' => 'None',
            'IsScannable' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'BlockTravel' => false,
            'Size' => 50,
            'MinimumDisplaySize' => 0,
            'QuantumTravel' => null,
            'LocationHierarchyTag' => null,
            'Type' => [
                'Name' => 'Planet',
                'Classification' => 'Planet',
            ],
            'Jurisdiction' => null,
            'Affiliation' => null,
            'AsteroidRing' => null,
            'Amenities' => [],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-starmap', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported starmap data for version 4.1.4.');

    $starLocation = StarmapLocation::query()->firstWhere('uuid', $starUuid);
    $planetLocation = StarmapLocation::query()->firstWhere('uuid', $planetUuid);
    $starData = StarmapLocationData::query()->whereBelongsTo($starLocation, 'location')->first();
    $planetData = StarmapLocationData::query()->whereBelongsTo($planetLocation, 'location')->first();

    expect($starData?->system)->toBe('Orion')
        ->and($planetData?->system)->toBe('Orion')
        ->and($starData?->star_data_id)->toBe($starData?->id)
        ->and($planetData?->star_data_id)->toBe($starData?->id);
});
