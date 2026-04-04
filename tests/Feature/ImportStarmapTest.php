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
            'uuid' => $systemUuid,
            'name' => 'Stanton',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 400,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $childUuid,
            'name' => 'Area18',
            'parentUuid' => $systemUuid,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 10,
            'minimumDisplaySize' => 1,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'LandingZone',
                'classification' => 'Landing Zone',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
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
            'uuid' => $systemUuid,
            'name' => 'Stanton',
            'description' => 'Primary system',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 400,
            'minimumDisplaySize' => 0,
            'quantumTravel' => [
                'arrivalRadius' => 7000,
            ],
            'locationHierarchyTag' => [
                'uuid' => $tagUuid,
                'name' => 'Stanton System',
            ],
            'type' => [
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $planetUuid,
            'name' => 'ArcCorp',
            'description' => 'Planet node',
            'parentUuid' => $systemUuid,
            'respawnLocationType' => 'None',
            'isScannable' => true,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 120,
            'minimumDisplaySize' => 5,
            'quantumTravel' => [
                'arrivalRadius' => 4000,
            ],
            'locationHierarchyTag' => [
                'uuid' => $tagUuid,
                'name' => 'Stanton System',
            ],
            'type' => [
                'name' => 'Planet',
                'classification' => 'Planet',
            ],
            'jurisdiction' => [
                'name' => 'UEE',
                'isPrison' => false,
            ],
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $stationUuid,
            'name' => 'Baijini Point',
            'description' => 'Station node',
            'parentUuid' => $planetUuid,
            'respawnLocationType' => 'Hospital',
            'isScannable' => true,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 10,
            'minimumDisplaySize' => 1,
            'quantumTravel' => [
                'arrivalRadius' => 1000,
            ],
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'Manmade',
                'classification' => 'Manmade',
            ],
            'jurisdiction' => [
                'name' => 'UEE',
                'isPrison' => false,
            ],
            'affiliation' => [
                'displayName' => 'Private Security',
            ],
            'asteroidRing' => null,
            'amenities' => [
                [
                    'uuid' => $dockingAmenityUuid,
                    'name' => 'Docking',
                    'displayName' => 'Docking',
                ],
                [
                    'uuid' => $clinicAmenityUuid,
                    'name' => 'Clinic',
                    'displayName' => 'Clinic',
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

    $payload[2]['description'] = 'Updated station node';
    $payload[2]['amenities'] = [
        [
            'uuid' => $dockingAmenityUuid,
            'name' => 'Docking',
            'displayName' => 'Docking',
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
            'type' => [
                'classification' => 'Solar System',
            ],
            'respawnLocationType' => 'None',
            'minimumDisplaySize' => 0,
            'hideInStarmap' => false,
            'hideInWorld' => false,
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
            'type' => [
                'classification' => 'Planet',
            ],
            'respawnLocationType' => 'None',
            'minimumDisplaySize' => 5,
            'hideInStarmap' => false,
            'hideInWorld' => false,
        ],
    ]);

    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'uuid' => $systemUuid,
            'name' => 'Stanton',
            'description' => 'Primary system',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 400,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $planetUuid,
            'name' => 'ArcCorp',
            'description' => 'Planet node',
            'parentUuid' => $systemUuid,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 120,
            'minimumDisplaySize' => 5,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'Planet',
                'classification' => 'Planet',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
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
            'uuid' => $solarSystemUuid,
            'name' => 'Stanton System',
            'description' => 'System record',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 400,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $starUuid,
            'name' => 'Stanton',
            'description' => 'Root star',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 696000000,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'Star',
                'classification' => 'Star',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
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
            'type' => [
                'classification' => 'Solar System',
            ],
            'respawnLocationType' => 'None',
            'minimumDisplaySize' => 0,
            'hideInStarmap' => false,
            'hideInWorld' => false,
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
            'type' => [
                'classification' => 'Star',
            ],
            'respawnLocationType' => 'None',
            'minimumDisplaySize' => 0,
            'hideInStarmap' => false,
            'hideInWorld' => false,
        ],
    ]);

    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'uuid' => $solarSystemUuid,
            'name' => 'Stanton System',
            'description' => 'System record',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 400,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $starUuid,
            'name' => 'Stanton',
            'description' => 'Root star',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 696000000,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'Star',
                'classification' => 'Star',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
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
            'uuid' => $solarSystemUuid,
            'name' => 'Stanton System',
            'description' => 'System record',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => true,
            'hideInWorld' => true,
            'blockTravel' => true,
            'size' => 0.1,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'SolarSystem',
                'classification' => 'Solar System',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $starUuid,
            'name' => 'Stanton',
            'description' => 'Root star',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => true,
            'size' => 696000000,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'Star',
                'classification' => 'Star',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $planetUuid,
            'name' => 'ArcCorp',
            'description' => 'Planet node',
            'parentUuid' => $starUuid,
            'respawnLocationType' => 'None',
            'isScannable' => true,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 120,
            'minimumDisplaySize' => 5,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'Planet',
                'classification' => 'Planet',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $outpostUuid,
            'name' => 'Area18',
            'description' => 'Landing zone node',
            'parentUuid' => $planetUuid,
            'respawnLocationType' => 'None',
            'isScannable' => true,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 10,
            'minimumDisplaySize' => 1,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'LandingZone',
                'classification' => 'Landing Zone',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
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
            'uuid' => $starUuid,
            'name' => 'Orion',
            'description' => 'Root star',
            'parentUuid' => null,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => true,
            'size' => 100,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'Star',
                'classification' => 'Star',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
        ],
        [
            'uuid' => $planetUuid,
            'name' => 'Orion I',
            'description' => 'Planet node',
            'parentUuid' => $starUuid,
            'respawnLocationType' => 'None',
            'isScannable' => false,
            'hideInStarmap' => false,
            'hideInWorld' => false,
            'blockTravel' => false,
            'size' => 50,
            'minimumDisplaySize' => 0,
            'quantumTravel' => null,
            'locationHierarchyTag' => null,
            'type' => [
                'name' => 'Planet',
                'classification' => 'Planet',
            ],
            'jurisdiction' => null,
            'affiliation' => null,
            'asteroidRing' => null,
            'amenities' => [],
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
