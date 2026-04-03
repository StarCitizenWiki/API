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

    expect(StarmapLocation::query()->count())->toBe(2)
        ->and(StarmapLocationData::query()->count())->toBe(2)
        ->and(StarmapLocation::query()->firstWhere('uuid', $childUuid)?->system_uuid)->toBe($systemUuid);
});

it('imports starmap hierarchy, tags, amenities, and system uuids and upserts on re-run', function (): void {
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
        ->and($systemLocation->system_uuid)->toBe($systemUuid)
        ->and($planetLocation)->not->toBeNull()
        ->and($planetLocation->system_uuid)->toBe($systemUuid)
        ->and($stationLocation)->not->toBeNull()
        ->and($stationLocation->system_uuid)->toBe($systemUuid);

    $systemData = StarmapLocationData::query()->whereBelongsTo($systemLocation, 'location')->first();
    $planetData = StarmapLocationData::query()->whereBelongsTo($planetLocation, 'location')->first();
    $stationData = StarmapLocationData::query()->whereBelongsTo($stationLocation, 'location')->first();

    expect($systemData)->not->toBeNull()
        ->and($planetData)->not->toBeNull()
        ->and($stationData)->not->toBeNull()
        ->and($planetData->parent_data_id)->toBe($systemData->id)
        ->and($stationData->parent_data_id)->toBe($planetData->id)
        ->and($stationData->jurisdiction_name)->toBe('UEE')
        ->and($stationData->affiliation_name)->toBe('Private Security')
        ->and($stationData->amenities()->pluck('name')->all())->toBe(['Clinic', 'Docking']);

    expect($planetData->children()->pluck('name')->all())->toBe(['Baijini Point']);

    $tag = EntityTag::query()->firstWhere('uuid', $tagUuid);
    expect($tag)->not->toBeNull()
        ->and($tag->name)->toBe('Stanton System')
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

it('repairs stale system uuids when the starmap import command is rerun', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.1.2',
    ]);

    $systemUuid = fake()->uuid();
    $planetUuid = fake()->uuid();

    $systemLocation = StarmapLocation::query()->create([
        'uuid' => $systemUuid,
        'system_uuid' => null,
    ]);

    $planetLocation = StarmapLocation::query()->create([
        'uuid' => $planetUuid,
        'system_uuid' => null,
    ]);

    StarmapLocationData::query()->create([
        'starmap_location_id' => $systemLocation->id,
        'game_version_id' => $version->id,
        'parent_data_id' => null,
        'location_hierarchy_entity_tag_id' => null,
        'name' => 'Old Stanton',
        'description' => null,
        'type_name' => 'SolarSystem',
        'type_classification' => 'Solar System',
        'respawn_location_type' => 'None',
        'size' => 400,
        'minimum_display_size' => 0,
        'is_scannable' => false,
        'hide_in_starmap' => false,
        'hide_in_world' => false,
        'block_travel' => false,
        'jurisdiction_name' => null,
        'jurisdiction_is_prison' => null,
        'affiliation_name' => null,
        'quantum_travel' => null,
        'asteroid_ring' => null,
        'data' => [],
    ]);

    StarmapLocationData::query()->create([
        'starmap_location_id' => $planetLocation->id,
        'game_version_id' => $version->id,
        'parent_data_id' => null,
        'location_hierarchy_entity_tag_id' => null,
        'name' => 'Old ArcCorp',
        'description' => null,
        'type_name' => 'Planet',
        'type_classification' => 'Planet',
        'respawn_location_type' => 'None',
        'size' => 120,
        'minimum_display_size' => 5,
        'is_scannable' => false,
        'hide_in_starmap' => false,
        'hide_in_world' => false,
        'block_travel' => false,
        'jurisdiction_name' => null,
        'jurisdiction_is_prison' => null,
        'affiliation_name' => null,
        'quantum_travel' => null,
        'asteroid_ring' => null,
        'data' => [],
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

    expect($systemLocation->fresh()?->system_uuid)->toBe($systemUuid)
        ->and($planetLocation->fresh()?->system_uuid)->toBe($systemUuid);
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

    expect(StarmapLocation::query()->firstWhere('uuid', $solarSystemUuid)?->system_uuid)->toBe($solarSystemUuid)
        ->and(StarmapLocation::query()->firstWhere('uuid', $starUuid)?->system_uuid)->toBe($solarSystemUuid)
        ->and(StarmapLocation::query()->firstWhere('uuid', $planetUuid)?->system_uuid)->toBe($solarSystemUuid)
        ->and(StarmapLocation::query()->firstWhere('uuid', $outpostUuid)?->system_uuid)->toBe($solarSystemUuid);
});

it('leaves system uuid null when a root star has no matching solar system row', function (): void {
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

    expect(StarmapLocation::query()->firstWhere('uuid', $starUuid)?->system_uuid)->toBeNull()
        ->and(StarmapLocation::query()->firstWhere('uuid', $planetUuid)?->system_uuid)->toBeNull();
});
