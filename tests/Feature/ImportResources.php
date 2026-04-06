<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceData;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('fails when the game version does not exist', function (): void {
    Storage::fake('scunpacked');

    $this->artisan('game:import-resources', ['version' => 'missing'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "missing" does not exist. Please create it first.');
});

it('fails when the resources file is missing', function (): void {
    Storage::fake('scunpacked');

    GameVersion::factory()->create(['code' => '4.0.0-LIVE']);

    $this->artisan('game:import-resources', ['version' => '4.0.0-LIVE'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('resources/resources.json not found in scunpacked storage.');
});

it('imports mineable resources with composition commodity links', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $aluminum = Commodity::factory()->create(['uuid' => $aluminumUuid = fake()->uuid(), 'key' => 'Ore_Aluminum']);
    $gold = Commodity::factory()->create(['uuid' => $goldUuid = fake()->uuid(), 'key' => 'Ore_Gold']);

    $payload = [
        [
            'UUID' => $resourceUuid = fake()->uuid(),
            'Key' => 'GPI_Icicle',
            'Name' => 'Granite Deposit',
            'Kind' => 'mineable',
            'Tier' => 'common',
            'Signature' => 5,
            'GlobalParams' => [
                'DefaultMass' => 0.001,
            ],
            'Composition' => [
                'UUID' => fake()->uuid(),
                'DepositName' => 'Granite Deposit',
                'MinimumDistinctElements' => 2,
                'Parts' => [
                    [
                        'UUID' => fake()->uuid(),
                        'ResourceTypeUUID' => $aluminumUuid,
                        'Key' => 'Ore_Aluminum',
                        'Name' => 'Aluminum (Ore)',
                        'MinPercentage' => 30,
                        'MaxPercentage' => 70,
                        'Probability' => 1,
                        'QualityScale' => 1,
                        'CurveExponent' => 1,
                    ],
                    [
                        'UUID' => fake()->uuid(),
                        'ResourceTypeUUID' => $goldUuid,
                        'Key' => 'Ore_Gold',
                        'Name' => 'Gold (Ore)',
                        'MinPercentage' => 20,
                        'MaxPercentage' => 50,
                        'Probability' => 0.3,
                        'QualityScale' => 1,
                        'CurveExponent' => 1,
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resources', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 resources for version 4.0.0-LIVE (1 new, 0 existing). Skipped 0 invalid. Linked 2 commodities (0 links skipped).');

    $resource = Resource::query()->where('uuid', $resourceUuid)->first();
    $resourceData = ResourceData::query()->where('resource_id', $resource->id)->first();

    expect($resource)->not->toBeNull()
        ->and($resourceData)->not->toBeNull()
        ->and($resourceData->key)->toBe('GPI_Icicle')
        ->and($resourceData->name)->toBe('Granite Deposit')
        ->and($resourceData->kind)->toBe(ResourceKind::Mineable)
        ->and($resourceData->tier)->toBe('common')
        ->and($resourceData->signature)->toBe(5)
        ->and($resourceData->data->get('Kind'))->toBe('mineable');

    $commodities = $resourceData->commodities()->get();
    expect($commodities)->toHaveCount(2);

    $aluminumPivot = $resourceData->commodities()->where('uuid', $aluminumUuid)->first()->pivot;
    expect((float) $aluminumPivot->min_percentage)->toBe(30.0)
        ->and((float) $aluminumPivot->max_percentage)->toBe(70.0)
        ->and((float) $aluminumPivot->probability)->toBe(1.0)
        ->and((float) $aluminumPivot->quality_scale)->toBe(1.0)
        ->and((float) $aluminumPivot->curve_exponent)->toBe(1.0);
});

it('imports harvestable resources with parts resource types', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $neon = Commodity::factory()->create(['uuid' => $neonUuid = fake()->uuid(), 'key' => 'Neon']);

    $payload = [
        [
            'HarvestableUUID' => fake()->uuid(),
            'HarvestableKey' => 'Item_Drugs_005',
            'RespawnInSlotTime' => 3600,
            'DespawnTimeSeconds' => 0,
            'AdditionalWaitForNearbyPlayersSeconds' => 0,
            'UUID' => $resourceUuid = fake()->uuid(),
            'Key' => 'Carryable_1H_SQ_drug_neon_1_a',
            'Name' => 'Neon Plant',
            'Kind' => 'cave_harvestable',
            'Parts' => [
                [
                    'UUID' => fake()->uuid(),
                    'Key' => 'Carryable_1H_SQ_drug_neon_1_a',
                    'Name' => 'Neon Plant',
                    'ResourceTypes' => [
                        [
                            'Key' => 'Neon',
                            'Name' => 'Neon',
                            'Weight' => 1,
                            'ResourceTypeUUID' => $neonUuid,
                        ],
                    ],
                    'Immutable' => true,
                    'FillFraction' => 1,
                    'Capacity' => [
                        'UnitName' => 'µSCU',
                        'Value' => 750,
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resources', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 resources for version 4.0.0-LIVE (1 new, 0 existing). Skipped 0 invalid. Linked 1 commodities (0 links skipped).');

    $resource = Resource::query()->where('uuid', $resourceUuid)->first();
    $resourceData = ResourceData::query()->where('resource_id', $resource->id)->first();

    expect($resourceData)->not->toBeNull()
        ->and($resourceData->key)->toBe('Carryable_1H_SQ_drug_neon_1_a')
        ->and($resourceData->kind)->toBe(ResourceKind::Harvestable)
        ->and($resourceData->signature)->toBeNull()
        ->and($resourceData->data->get('HarvestableKey'))->toBe('Item_Drugs_005');

    $commodities = $resourceData->commodities()->get();
    expect($commodities)->toHaveCount(1);

    $pivot = $commodities->first()->pivot;
    expect((float) $pivot->weight)->toBe(1.0);
});

it('imports salvageable resources without commodity links', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $payload = [
        [
            'UUID' => $resourceUuid = fake()->uuid(),
            'Key' => 'SalvageableDebris_AvengerTitan',
            'Name' => 'Aegis Avenger Titan',
            'Kind' => 'salvageable',
            'Signature' => 1700,
        ],
    ];

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resources', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 resources for version 4.0.0-LIVE (1 new, 0 existing). Skipped 0 invalid. Linked 0 commodities (0 links skipped).');

    $resourceData = ResourceData::query()
        ->whereHas('resource', fn ($q) => $q->where('uuid', $resourceUuid))
        ->first();

    expect($resourceData)->not->toBeNull()
        ->and($resourceData->kind)->toBe(ResourceKind::Salvage)
        ->and($resourceData->signature)->toBe(1700)
        ->and($resourceData->commodities)->toHaveCount(0);
});

it('upserts versioned resource data on re-import and syncs commodity links', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $commodityA = Commodity::factory()->create(['uuid' => $commodityAUuid = fake()->uuid()]);
    $commodityB = Commodity::factory()->create(['uuid' => $commodityBUuid = fake()->uuid()]);

    $payload = [
        [
            'UUID' => $resourceUuid = fake()->uuid(),
            'Key' => 'GPI_Test',
            'Name' => 'Test Deposit',
            'Kind' => 'mineable',
            'Composition' => [
                'UUID' => fake()->uuid(),
                'Parts' => [
                    [
                        'UUID' => fake()->uuid(),
                        'ResourceTypeUUID' => $commodityAUuid,
                        'Key' => 'Ore_A',
                        'Name' => 'Ore A',
                        'MinPercentage' => 30,
                        'MaxPercentage' => 70,
                        'Probability' => 1,
                        'QualityScale' => 1,
                        'CurveExponent' => 1,
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $this->artisan('game:import-resources', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

    expect(ResourceData::query()->count())->toBe(1);

    $payload[0]['Name'] = 'Test Deposit Updated';
    $payload[0]['Composition']['Parts'][] = [
        'UUID' => fake()->uuid(),
        'ResourceTypeUUID' => $commodityBUuid,
        'Key' => 'Ore_B',
        'Name' => 'Ore B',
        'MinPercentage' => 10,
        'MaxPercentage' => 40,
        'Probability' => 0.5,
        'QualityScale' => 1,
        'CurveExponent' => 1,
    ];

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $this->artisan('game:import-resources', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 resources for version 4.0.0-LIVE (0 new, 1 existing). Skipped 0 invalid. Linked 2 commodities (0 links skipped).');

    $resource = Resource::query()->where('uuid', $resourceUuid)->first();
    $resourceData = ResourceData::query()->where('resource_id', $resource->id)->first();

    expect(Resource::query()->count())->toBe(1)
        ->and(ResourceData::query()->count())->toBe(1)
        ->and($resourceData->name)->toBe('Test Deposit Updated')
        ->and($resourceData->commodities()->count())->toBe(2);
});

it('skips unknown commodity uuids and reports them', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $knownCommodity = Commodity::factory()->create(['uuid' => $knownUuid = fake()->uuid()]);
    $unknownUuid = fake()->uuid();

    $payload = [
        [
            'UUID' => fake()->uuid(),
            'Key' => 'GPI_Mixed',
            'Name' => 'Mixed Deposit',
            'Kind' => 'mineable',
            'Composition' => [
                'UUID' => fake()->uuid(),
                'Parts' => [
                    [
                        'UUID' => fake()->uuid(),
                        'ResourceTypeUUID' => $knownUuid,
                        'Key' => 'Ore_Known',
                        'Name' => 'Known Ore',
                        'MinPercentage' => 50,
                        'MaxPercentage' => 100,
                        'Probability' => 1,
                        'QualityScale' => 1,
                        'CurveExponent' => 1,
                    ],
                    [
                        'UUID' => fake()->uuid(),
                        'ResourceTypeUUID' => $unknownUuid,
                        'Key' => 'Ore_Unknown',
                        'Name' => 'Unknown Ore',
                        'MinPercentage' => 10,
                        'MaxPercentage' => 30,
                        'Probability' => 0.2,
                        'QualityScale' => 1,
                        'CurveExponent' => 1,
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resources', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 resources for version 4.0.0-LIVE (1 new, 0 existing). Skipped 0 invalid. Linked 1 commodities (1 links skipped).');

    $resourceData = ResourceData::query()->first();
    expect($resourceData->commodities()->count())->toBe(1)
        ->and($resourceData->commodities()->first()->uuid)->toBe($knownUuid);
});

it('skips invalid resource entries missing required fields', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $payload = [
        ['UUID' => fake()->uuid(), 'Key' => 'MissingKind'],
        ['UUID' => fake()->uuid(), 'Kind' => 'mineable'],
        ['Key' => 'MissingUUID', 'Kind' => 'harvestable'],
        'not-an-array',
        ['UUID' => $validUuid = fake()->uuid(), 'Key' => 'Valid', 'Name' => 'Valid Resource', 'Kind' => 'salvageable'],
    ];

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-resources', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 1 resources for version 4.0.0-LIVE (1 new, 0 existing). Skipped 4 invalid. Linked 0 commodities (0 links skipped).');

    expect(Resource::query()->count())->toBe(1)
        ->and(Resource::query()->first()->uuid)->toBe($validUuid);
});
