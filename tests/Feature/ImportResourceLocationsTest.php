<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
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
    expect($location->starmapLocationData)->toHaveCount(1);
    expect($location->starmapLocationData->first()->id)->toBe($s['starmapData1']->id);
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
    expect($allPlacements)->toHaveCount(2);
    expect($allPlacements->unique())->toHaveCount(2);
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
    expect($provider->starmapLocationData)->toHaveCount(1);
    expect($provider->starmapLocationData->first()->id)->toBe($s['starmapData1']->id);
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

    expect(DB::table('game_resource_location_placements')->count())->toBe(1);
    expect(DB::table('game_resource_provider_starmap')->count())->toBe(1);

    Storage::disk('scunpacked')->put('resources/locations.json', json_encode([]));

    $this->artisan('game:import-resource-locations', ['version' => '4.0.0-LIVE'])
        ->assertExitCode(Command::SUCCESS);

    expect(DB::table('game_resource_location_placements')->count())->toBe(0);
    expect(DB::table('game_resource_provider_starmap')->count())->toBe(0);
});
