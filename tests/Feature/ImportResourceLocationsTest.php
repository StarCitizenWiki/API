<?php

declare(strict_types=1);

use App\Enums\Game\ResourceKind;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\Resource\ResourceProvider;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Shared helpers for starmap placement tests
|--------------------------------------------------------------------------
*/

function setupVersionWithResources(): array
{
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $starmapLoc1 = StarmapLocation::factory()->create(['uuid' => $uuid1 = fake()->uuid()]);
    $starmapData1 = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLoc1->id,
        'game_version_id' => $version->id,
    ]);
    $starmapLoc1->refresh();

    $starmapLoc2 = StarmapLocation::factory()->create(['uuid' => $uuid2 = fake()->uuid()]);
    $starmapData2 = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLoc2->id,
        'game_version_id' => $version->id,
    ]);
    $starmapLoc2->refresh();

    $starmapLoc3 = StarmapLocation::factory()->create(['uuid' => $uuid3 = fake()->uuid()]);
    $starmapData3 = StarmapLocationData::factory()->create([
        'starmap_location_id' => $starmapLoc3->id,
        'game_version_id' => $version->id,
    ]);
    $starmapLoc3->refresh();

    $resourceData = ResourceData::factory()->create([
        'game_version_id' => $version->id,
        'key' => 'MineableRock_Copper',
        'name' => 'Copper',
        'kind' => 'mineable',
    ]);

    return compact(
        'version',
        'starmapLoc1', 'starmapData1', 'uuid1',
        'starmapLoc2', 'starmapData2', 'uuid2',
        'starmapLoc3', 'starmapData3', 'uuid3',
        'resourceData',
    );
}

function buildPayload(array $s): array
{
    return [
        [
            'Provider' => ['Name' => 'TestProvider_1'],
            'Locations' => [
                ['Object' => $s['uuid1'], 'Key' => 'Loc1', 'Tag' => 'TAG1', 'System' => 'Stanton', 'Name' => 'Hurston', 'Type' => 'Planet'],
            ],
            'Areas' => [],
            'Groups' => [
                [
                    'GroupName' => 'SpaceShip_Mineables',
                    'GroupProbability' => 1.0,
                    'Deposits' => [
                        [
                            'ResourceUUID' => $s['resourceData']->resource->uuid,
                            'ResourceKey' => 'Copper',
                            'RelativeProbability' => 0.5,
                        ],
                    ],
                ],
            ],
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| Basic import logic
|--------------------------------------------------------------------------
*/

describe('basic import', function (): void {
    it('fails when the game version does not exist', function (): void {
        Storage::fake('scunpacked');

        $this->artisan('game:import-resource-locations', ['version' => 'missing'])
            ->assertExitCode(Command::FAILURE)
            ->expectsOutput('Game version "missing" does not exist. Please create it first.');
    });

    it('fails when the locations file is missing', function (): void {
        Storage::fake('scunpacked');

        GameVersion::factory()->create(['code' => '4.0.0-LIVE']);

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::FAILURE)
            ->expectsOutput('resources/locations.json not found in scunpacked storage.');
    });

    it('imports resource locations with starmap data links', function (): void {
        Storage::fake('scunpacked');

        $version = GameVersion::factory()->create([
            'code' => '4.0.0-LIVE',
            'channel' => 'live',
            'released_at' => now(),
            'is_default' => true,
        ]);

        $resource = Resource::factory()->create(['uuid' => $resourceUuid = fake()->uuid()]);
        $resourceData = ResourceData::factory()->create([
            'resource_id' => $resource->id,
            'game_version_id' => $version->id,
        ]);

        $starmapLocation = StarmapLocation::factory()->create(['uuid' => $objectUuid = fake()->uuid()]);
        $starmapLocationData = StarmapLocationData::factory()->create([
            'starmap_location_id' => $starmapLocation->id,
            'game_version_id' => $version->id,
        ]);

        $payload = [[
            'Provider' => [
                'UUID' => fake()->uuid(),
                'Name' => 'HPP_Test',
                'PresetFile' => 'hpp_test',
            ],
            'Locations' => [[
                'Key' => 'Stanton4',
                'Object' => $objectUuid,
                'Location' => 'Stanton4',
                'Tag' => fake()->uuid(),
                'MatchStrategy' => 'tag',
                'System' => 'Stanton',
                'Name' => 'microTech',
                'Type' => 'planet',
            ]],
            'Areas' => [
                ['Name' => 'Surface', 'GlobalModifier' => 1.5],
            ],
            'Groups' => [[
                'GroupName' => 'SpaceShip_Mineables',
                'GroupProbability' => 0.8,
                'Deposits' => [[
                    'ResourceUUID' => $resourceUuid,
                    'RelativeProbability' => 0.5,
                    'ResourceKey' => 'Ore_Quartz',
                    'ResourceQualities' => [
                        [
                            'ResourceKey' => 'Ore_Quartz',
                            'QualityRange' => [
                                'Min' => 100,
                                'Max' => 500,
                                'Mean' => 300,
                                'Stddev' => 50,
                            ],
                        ],
                    ],
                ]],
            ]],
        ]];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload, JSON_THROW_ON_ERROR));

        $this->artisan('game:import-resource-locations', ['version' => $version->code])
            ->assertExitCode(Command::SUCCESS)
            ->expectsOutputToContain('Imported 1 resource locations for version 4.0.0-LIVE');

        $location = ResourceLocation::query()->first();
        expect($location)->not->toBeNull()
            ->and($location->resource_data_id)->toBe($resourceData->id)
            ->and($location->group_name)->toBe('SpaceShip_Mineables')
            ->and((float)$location->group_probability)->toBe(0.8)
            ->and((float)$location->relative_probability)->toBe(0.5)
            ->and($location->resource_kind)->toBe(ResourceKind::Mineable)
            ->and($location->commodity_id)->toBeNull()
            ->and($location->quality_min)->toBe(100)
            ->and($location->quality_max)->toBe(500)
            ->and($location->quality_mean)->toBe(300)
            ->and($location->quality_stddev)->toBe(50)
            ->and($location->starmapLocationData->pluck('id')->all())->toBe([$starmapLocationData->id]);

    });

    it('explodes multi-quality deposits into separate rows', function (): void {
        Storage::fake('scunpacked');

        $version = GameVersion::factory()->create(['code' => '4.0.1-LIVE']);

        $resource = Resource::factory()->create(['uuid' => $resourceUuid = fake()->uuid()]);
        ResourceData::factory()->create([
            'resource_id' => $resource->id,
            'game_version_id' => $version->id,
        ]);

        $payload = [[
            'Provider' => [
                'UUID' => fake()->uuid(),
                'Name' => 'HPP_MultiQuality',
                'PresetFile' => 'hpp_mq',
            ],
            'Locations' => [[
                'Key' => 'loc_mq',
                'Object' => fake()->uuid(),
                'Location' => fake()->uuid(),
                'MatchStrategy' => 'none',
                'System' => 'Stanton',
                'Name' => 'Test',
                'Type' => 'unknown',
            ]],
            'Areas' => [],
            'Groups' => [[
                'GroupName' => 'Harvestables',
                'GroupProbability' => 1.0,
                'Deposits' => [[
                    'ResourceUUID' => $resourceUuid,
                    'RelativeProbability' => 0.3,
                    'ResourceQualities' => [
                        [
                            'ResourceKey' => 'Ore_Iron',
                            'QualityRange' => ['Min' => 10, 'Max' => 50, 'Mean' => 30, 'Stddev' => 5],
                        ],
                        [
                            'ResourceKey' => 'Ore_Iron',
                            'QualityRange' => ['Min' => 50, 'Max' => 100, 'Mean' => 75, 'Stddev' => 10],
                        ],
                        [
                            'ResourceKey' => 'Ore_Iron',
                            'QualityRange' => ['Min' => 100, 'Max' => 200, 'Mean' => 150, 'Stddev' => 20],
                        ],
                    ],
                ]],
            ]],
        ]];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload, JSON_THROW_ON_ERROR));

        $this->artisan('game:import-resource-locations', ['version' => $version->code])
            ->assertExitCode(Command::SUCCESS);

        expect(ResourceLocation::query()->count())->toBe(3);

        $qualities = ResourceLocation::query()
            ->orderBy('quality_min')
            ->get();

        expect($qualities->get(0)->quality_min)->toBe(10)
            ->and($qualities->get(0)->quality_max)->toBe(50)
            ->and($qualities->get(1)->quality_min)->toBe(50)
            ->and($qualities->get(1)->quality_max)->toBe(100)
            ->and($qualities->get(2)->quality_min)->toBe(100)
            ->and($qualities->get(2)->quality_max)->toBe(200);
    });

    it('skips deposits with unresolved resource UUIDs', function (): void {
        Storage::fake('scunpacked');

        $version = GameVersion::factory()->create(['code' => '4.0.2-LIVE']);

        $payload = [[
            'Provider' => [
                'UUID' => fake()->uuid(),
                'Name' => 'HPP_Unknown',
                'PresetFile' => 'hpp_unk',
            ],
            'Locations' => [[
                'Key' => 'loc_unk',
                'Object' => fake()->uuid(),
                'Location' => fake()->uuid(),
                'MatchStrategy' => 'none',
                'System' => 'Stanton',
                'Name' => 'Test',
                'Type' => 'unknown',
            ]],
            'Areas' => [],
            'Groups' => [[
                'GroupName' => 'FPS_Mineables',
                'GroupProbability' => 0.5,
                'Deposits' => [[
                    'ResourceUUID' => fake()->uuid(),
                    'RelativeProbability' => 0.1,
                ]],
            ]],
        ]];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload, JSON_THROW_ON_ERROR));

        $this->artisan('game:import-resource-locations', ['version' => $version->code])
            ->assertExitCode(Command::SUCCESS)
            ->expectsOutputToContain('Skipped 0 invalid providers, 1 unresolved resource UUIDs');

        expect(ResourceLocation::query()->count())->toBe(0);
    });

    it('derives resource kind from group name', function (): void {
        Storage::fake('scunpacked');

        $version = GameVersion::factory()->create(['code' => '4.0.3-LIVE']);

        $resource = Resource::factory()->create(['uuid' => $resourceUuid = fake()->uuid()]);
        ResourceData::factory()->create([
            'resource_id' => $resource->id,
            'game_version_id' => $version->id,
        ]);

        $payload = [
            [
                'Provider' => [
                    'UUID' => fake()->uuid(),
                    'Name' => 'HPP_Kinds',
                    'PresetFile' => 'hpp_kinds',
                ],
                'Locations' => [[
                    'Object' => fake()->uuid(),
                    'Location' => fake()->uuid(),
                    'MatchStrategy' => 'none',
                    'System' => 'Stanton',
                    'Name' => 'Test',
                    'Type' => 'unknown',
                ]],
                'Areas' => [],
                'Groups' => [
                    ['GroupName' => 'SpaceShip_Mineables', 'GroupProbability' => 0.5, 'Deposits' => [
                        ['ResourceUUID' => $resourceUuid, 'RelativeProbability' => 0.1],
                    ]],
                    ['GroupName' => 'Harvestables', 'GroupProbability' => 0.5, 'Deposits' => [
                        ['ResourceUUID' => $resourceUuid, 'RelativeProbability' => 0.2],
                    ]],
                    ['GroupName' => 'Salvage_BrokenShips_Poor', 'GroupProbability' => 0.5, 'Deposits' => [
                        ['ResourceUUID' => $resourceUuid, 'RelativeProbability' => 0.3],
                    ]],
                    ['GroupName' => 'Remains', 'GroupProbability' => 0.5, 'Deposits' => [
                        ['ResourceUUID' => $resourceUuid, 'RelativeProbability' => 0.4],
                    ]],
                    ['GroupName' => 'Loot crates', 'GroupProbability' => 0.5, 'Deposits' => [
                        ['ResourceUUID' => $resourceUuid, 'RelativeProbability' => 0.5],
                    ]],
                    ['GroupName' => 'Fossils', 'GroupProbability' => 0.5, 'Deposits' => [
                        ['ResourceUUID' => $resourceUuid, 'RelativeProbability' => 0.6],
                    ]],
                ],
            ],
        ];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload, JSON_THROW_ON_ERROR));

        $this->artisan('game:import-resource-locations', ['version' => $version->code])
            ->assertExitCode(Command::SUCCESS);

        $locations = ResourceLocation::query()->orderBy('id')->get();

        expect($locations->get(0)->resource_kind)->toBe(ResourceKind::Mineable)
            ->and($locations->get(1)->resource_kind)->toBe(ResourceKind::Harvestable)
            ->and($locations->get(2)->resource_kind)->toBe(ResourceKind::Salvage)
            ->and($locations->get(3)->resource_kind)->toBe(ResourceKind::Remains)
            ->and($locations->get(4)->resource_kind)->toBe(ResourceKind::Loot)
            ->and($locations->get(5)->resource_kind)->toBe(ResourceKind::Fossil);
    });

    it('upserts resource locations on re-import', function (): void {
        Storage::fake('scunpacked');

        $version = GameVersion::factory()->create(['code' => '4.0.4-LIVE']);

        $resource = Resource::factory()->create(['uuid' => $resourceUuid = fake()->uuid()]);
        ResourceData::factory()->create([
            'resource_id' => $resource->id,
            'game_version_id' => $version->id,
        ]);

        $payload = [[
            'Provider' => [
                'UUID' => fake()->uuid(),
                'Name' => 'HPP_Upsert',
                'PresetFile' => 'hpp_upsert',
            ],
            'Locations' => [[
                'Object' => fake()->uuid(),
                'Location' => fake()->uuid(),
                'MatchStrategy' => 'none',
                'System' => 'Stanton',
                'Name' => 'Test',
                'Type' => 'unknown',
            ]],
            'Areas' => [],
            'Groups' => [[
                'GroupName' => 'GroundVehicle_Mineables',
                'GroupProbability' => 0.7,
                'Deposits' => [[
                    'ResourceUUID' => $resourceUuid,
                    'RelativeProbability' => 0.4,
                    'ResourceQualities' => [
                        [
                            'ResourceKey' => 'Ore_Gold',
                            'QualityRange' => ['Min' => 1, 'Max' => 10, 'Mean' => 5, 'Stddev' => 1],
                        ],
                    ],
                ]],
            ]],
        ]];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload, JSON_THROW_ON_ERROR));
        $this->artisan('game:import-resource-locations', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

        expect(ResourceLocation::query()->count())->toBe(1);

        $payload[0]['Groups'][0]['Deposits'][0]['ResourceQualities'][0]['QualityRange']['Mean'] = 8;
        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload, JSON_THROW_ON_ERROR));
        $this->artisan('game:import-resource-locations', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

        expect(ResourceLocation::query()->count())->toBe(1)
            ->and(ResourceLocation::query()->first()->quality_mean)->toBe(8);
    });

    it('normalizes variant group names from source data', function (): void {
        Storage::fake('scunpacked');

        $version = GameVersion::factory()->create(['code' => '4.0.5-LIVE']);

        $resource = Resource::factory()->create(['uuid' => $resourceUuid = fake()->uuid()]);
        ResourceData::factory()->create([
            'resource_id' => $resource->id,
            'game_version_id' => $version->id,
        ]);

        $payload = [
            [
                'Provider' => [
                    'UUID' => fake()->uuid(),
                    'Name' => 'HPP_FPS_Spaces',
                    'PresetFile' => 'hpp_fps',
                ],
                'Locations' => [[
                    'Object' => fake()->uuid(),
                    'Location' => fake()->uuid(),
                    'MatchStrategy' => 'none',
                    'System' => 'Stanton',
                    'Name' => 'Test',
                    'Type' => 'unknown',
                ]],
                'Areas' => [],
                'Groups' => [
                    ['GroupName' => 'FPS mineables', 'GroupProbability' => 0.5, 'Deposits' => [
                        ['ResourceUUID' => $resourceUuid, 'RelativeProbability' => 0.5],
                    ]],
                ],
            ],
            [
                'Provider' => [
                    'UUID' => fake()->uuid(),
                    'Name' => 'HPP_Typo',
                    'PresetFile' => 'hpp_typo',
                ],
                'Locations' => [[
                    'Object' => fake()->uuid(),
                    'Location' => fake()->uuid(),
                    'MatchStrategy' => 'none',
                    'System' => 'Stanton',
                    'Name' => 'Test',
                    'Type' => 'unknown',
                ]],
                'Areas' => [],
                'Groups' => [
                    ['GroupName' => 'Havestables', 'GroupProbability' => 0.5, 'Deposits' => [
                        ['ResourceUUID' => $resourceUuid, 'RelativeProbability' => 0.5],
                    ]],
                ],
            ],
        ];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload, JSON_THROW_ON_ERROR));

        $this->artisan('game:import-resource-locations', ['version' => $version->code])
            ->assertExitCode(Command::SUCCESS);

        $fpsLocation = ResourceLocation::query()
            ->where('group_name', 'FPS_Mineables')
            ->first();

        expect($fpsLocation)->not->toBeNull()
            ->and($fpsLocation->resource_kind)->toBe(ResourceKind::Mineable);

        $harvestableLocation = ResourceLocation::query()
            ->where('group_name', 'Harvestables')
            ->first();

        expect($harvestableLocation)->not->toBeNull()
            ->and($harvestableLocation->resource_kind)->toBe(ResourceKind::Harvestable)
            ->and(ResourceLocation::query()->whereIn('group_name', ['FPS mineables', 'Havestables'])->count())->toBe(0);
    });
});

/*
|--------------------------------------------------------------------------
| Starmap placement idempotency
|--------------------------------------------------------------------------
*/

describe('starmap placements', function (): void {
    it('re-importing same data does not duplicate placements', function (): void {
        Storage::fake('scunpacked');
        $s = setupVersionWithResources();
        $payload = buildPayload($s);

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        $placementCount = DB::table('game_resource_location_placements')->count();
        expect($placementCount)->toBeGreaterThan(0);

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        expect(DB::table('game_resource_location_placements')->count())->toBe($placementCount);
    });

    it('removes stale placements on re-import when starmap locations change', function (): void {
        Storage::fake('scunpacked');
        $s = setupVersionWithResources();

        $payloadV1 = [
            [
                'Provider' => ['Name' => 'TestProvider_V1'],
                'Locations' => [
                    ['Object' => $s['uuid1'], 'Key' => 'Loc1', 'Tag' => 'TAG1', 'System' => 'Stanton', 'Name' => 'Hurston', 'Type' => 'Planet'],
                    ['Object' => $s['uuid2'], 'Key' => 'Loc2', 'Tag' => 'TAG2', 'System' => 'Stanton', 'Name' => 'Lyria', 'Type' => 'Moon'],
                ],
                'Areas' => [],
                'Groups' => [
                    [
                        'GroupName' => 'SpaceShip_Mineables',
                        'GroupProbability' => 1.0,
                        'Deposits' => [
                            [
                                'ResourceUUID' => $s['resourceData']->resource->uuid,
                                'ResourceKey' => 'Copper',
                                'RelativeProbability' => 0.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payloadV1));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        $location = ResourceLocation::query()->first();
        expect($location->starmapLocationData)->toHaveCount(2);

        $payloadV2 = [
            [
                'Provider' => ['Name' => 'TestProvider_V1'],
                'Locations' => [
                    ['Object' => $s['uuid1'], 'Key' => 'Loc1', 'Tag' => 'TAG1', 'System' => 'Stanton', 'Name' => 'Hurston', 'Type' => 'Planet'],
                ],
                'Areas' => [],
                'Groups' => [
                    [
                        'GroupName' => 'SpaceShip_Mineables',
                        'GroupProbability' => 1.0,
                        'Deposits' => [
                            [
                                'ResourceUUID' => $s['resourceData']->resource->uuid,
                                'ResourceKey' => 'Copper',
                                'RelativeProbability' => 0.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payloadV2));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        $location->refresh();
        expect($location->starmapLocationData)->toHaveCount(1)
            ->and($location->starmapLocationData->first()->id)->toBe($s['starmapData1']->id);
    });

    it('accumulates starmap placements from multiple providers for same resource location', function (): void {
        Storage::fake('scunpacked');
        $s = setupVersionWithResources();

        $payload = [
            [
                'Provider' => ['Name' => 'TestProvider_A'],
                'Locations' => [
                    ['Object' => $s['uuid1'], 'Key' => 'Loc1', 'Tag' => 'TAG1', 'System' => 'Stanton', 'Name' => 'Hurston', 'Type' => 'Planet'],
                ],
                'Areas' => [],
                'Groups' => [
                    [
                        'GroupName' => 'SpaceShip_Mineables',
                        'GroupProbability' => 1.0,
                        'Deposits' => [
                            [
                                'ResourceUUID' => $s['resourceData']->resource->uuid,
                                'ResourceKey' => 'Copper',
                                'RelativeProbability' => 0.5,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'Provider' => ['Name' => 'TestProvider_B'],
                'Locations' => [
                    ['Object' => $s['uuid2'], 'Key' => 'Loc2', 'Tag' => 'TAG2', 'System' => 'Stanton', 'Name' => 'Lyria', 'Type' => 'Moon'],
                ],
                'Areas' => [],
                'Groups' => [
                    [
                        'GroupName' => 'SpaceShip_Mineables',
                        'GroupProbability' => 1.0,
                        'Deposits' => [
                            [
                                'ResourceUUID' => $s['resourceData']->resource->uuid,
                                'ResourceKey' => 'Copper',
                                'RelativeProbability' => 0.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payload));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        $locations = ResourceLocation::query()
            ->where('resource_data_id', $s['resourceData']->id)
            ->where('group_name', 'SpaceShip_Mineables')
            ->where('relative_probability', 0.5)
            ->get();

        expect($locations)->toHaveCount(2);

        $allPlacements = $locations->flatMap(fn (ResourceLocation $loc) => $loc->starmapLocationData->pluck('id'));
        expect($allPlacements)->toHaveCount(2)
            ->and($allPlacements->unique())->toHaveCount(2);
    });

    it('removes stale provider starmap placements on re-import', function (): void {
        Storage::fake('scunpacked');
        $s = setupVersionWithResources();

        $payloadV1 = [
            [
                'Provider' => ['Name' => 'TestProvider_Stale'],
                'Locations' => [
                    ['Object' => $s['uuid1'], 'Key' => 'Loc1', 'Tag' => 'TAG1', 'System' => 'Stanton', 'Name' => 'Hurston', 'Type' => 'Planet'],
                    ['Object' => $s['uuid2'], 'Key' => 'Loc2', 'Tag' => 'TAG2', 'System' => 'Stanton', 'Name' => 'Lyria', 'Type' => 'Moon'],
                ],
                'Areas' => [],
                'Groups' => [
                    [
                        'GroupName' => 'SpaceShip_Mineables',
                        'GroupProbability' => 1.0,
                        'Deposits' => [
                            [
                                'ResourceUUID' => $s['resourceData']->resource->uuid,
                                'ResourceKey' => 'Copper',
                                'RelativeProbability' => 0.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payloadV1));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        $provider = ResourceProvider::query()->first();
        expect($provider->starmapLocationData)->toHaveCount(2);

        $payloadV2 = [
            [
                'Provider' => ['Name' => 'TestProvider_Stale'],
                'Locations' => [
                    ['Object' => $s['uuid1'], 'Key' => 'Loc1', 'Tag' => 'TAG1', 'System' => 'Stanton', 'Name' => 'Hurston', 'Type' => 'Planet'],
                ],
                'Areas' => [],
                'Groups' => [
                    [
                        'GroupName' => 'SpaceShip_Mineables',
                        'GroupProbability' => 1.0,
                        'Deposits' => [
                            [
                                'ResourceUUID' => $s['resourceData']->resource->uuid,
                                'ResourceKey' => 'Copper',
                                'RelativeProbability' => 0.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payloadV2));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        $provider->refresh();
        expect($provider->starmapLocationData)->toHaveCount(1)
            ->and($provider->starmapLocationData->first()->id)->toBe($s['starmapData1']->id);
    });

    it('cleans up all pivot rows when provider is removed from payload', function (): void {
        Storage::fake('scunpacked');
        $s = setupVersionWithResources();

        $payloadV1 = [
            [
                'Provider' => ['Name' => 'TestProvider_ToRemove'],
                'Locations' => [
                    ['Object' => $s['uuid1'], 'Key' => 'Loc1', 'Tag' => 'TAG1', 'System' => 'Stanton', 'Name' => 'Hurston', 'Type' => 'Planet'],
                ],
                'Areas' => [],
                'Groups' => [
                    [
                        'GroupName' => 'SpaceShip_Mineables',
                        'GroupProbability' => 1.0,
                        'Deposits' => [
                            [
                                'ResourceUUID' => $s['resourceData']->resource->uuid,
                                'ResourceKey' => 'Copper',
                                'RelativeProbability' => 0.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode($payloadV1));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        expect(DB::table('game_resource_location_placements')->count())->toBe(1)
            ->and(DB::table('game_resource_provider_starmap')->count())->toBe(1);

        Storage::disk('scunpacked')->put('resources/locations.json', json_encode([]));

        $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
            ->assertExitCode(Command::SUCCESS);

        expect(DB::table('game_resource_location_placements')->count())->toBe(0)
            ->and(DB::table('game_resource_provider_starmap')->count())->toBe(0);
    });
});
