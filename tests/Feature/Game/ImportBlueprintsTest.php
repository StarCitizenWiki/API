<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('fails when the game version does not exist', function (): void {
    Storage::fake('scunpacked');

    $this->artisan('game:import-blueprints', ['version' => 'missing'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "missing" does not exist. Please create it first.');
});

it('imports blueprints, keeps full payloads, and syncs ingredient resource types', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $sharedOutputItemUuid = fake()->uuid();
    $lindinium = Commodity::factory()->create(['uuid' => $lindiniumUuid = fake()->uuid()]);
    $iron = Commodity::factory()->create(['uuid' => $ironUuid = fake()->uuid()]);
    $copper = Commodity::factory()->create(['uuid' => $copperUuid = fake()->uuid()]);

    $payload = [
        [
            'UUID' => fake()->uuid(),
            'Key' => 'BP_CRAFT_ALPHA',
            'Kind' => 'creation',
            'CategoryUUID' => fake()->uuid(),
            'Output' => [
                'UUID' => $sharedOutputItemUuid,
                'Class' => 'alpha_output_class',
                'Type' => 'WeaponPersonal',
                'Subtype' => 'Medium',
                'Grade' => '1',
                'Name' => 'Alpha Output',
            ],
            'Availability' => [
                'Default' => true,
            ],
            'Tiers' => [
                [
                    'TierIndex' => 0,
                    'CraftTimeSeconds' => 240,
                    'Requirements' => [
                        'Kind' => 'root',
                        'Children' => [
                            [
                                'Kind' => 'group',
                                'Key' => 'FRAME',
                                'Name' => 'Frame',
                                'RequiredCount' => 2,
                                'Children' => [
                                    [
                                        'Kind' => 'resource',
                                        'UUID' => $lindiniumUuid,
                                        'Name' => 'Lindinium',
                                        'QuantityScu' => 0.06,
                                        'MinQuality' => 0,
                                    ],
                                    [
                                        'Kind' => 'group',
                                        'Key' => 'CORE',
                                        'Name' => 'Core',
                                        'RequiredCount' => 1,
                                        'Children' => [
                                            [
                                                'Kind' => 'resource',
                                                'UUID' => $ironUuid,
                                                'Name' => 'Iron',
                                                'QuantityScu' => 0.03,
                                                'MinQuality' => 100,
                                            ],
                                            [
                                                'Kind' => 'resource',
                                                'UUID' => $lindiniumUuid,
                                                'Name' => 'Lindinium',
                                                'QuantityScu' => 0.02,
                                                'MinQuality' => 10,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Dismantle' => [
                'TimeSeconds' => 15,
                'Efficiency' => 0.5,
                'Returns' => [
                    [
                        'Kind' => 'resource',
                        'UUID' => $lindiniumUuid,
                        'Name' => 'Lindinium',
                        'QuantityScu' => 0.03,
                    ],
                    [
                        'Kind' => 'resource',
                        'UUID' => $ironUuid,
                        'Name' => 'Iron',
                        'QuantityScu' => 0.015,
                    ],
                ],
            ],
        ],
        [
            'UUID' => fake()->uuid(),
            'Key' => 'BP_CRAFT_BETA',
            'Kind' => 'creation',
            'CategoryUUID' => fake()->uuid(),
            'Output' => [
                'UUID' => $sharedOutputItemUuid,
                'Class' => 'beta_output_class',
                'Type' => 'WeaponPersonal',
                'Subtype' => 'Medium',
                'Grade' => '1',
                'Name' => 'Beta Output',
            ],
            'Availability' => [
                'Default' => false,
            ],
            'Tiers' => [
                [
                    'TierIndex' => 0,
                    'CraftTimeSeconds' => 30,
                    'Requirements' => [
                        'Kind' => 'root',
                        'Children' => [
                            [
                                'Kind' => 'resource',
                                'UUID' => $copperUuid,
                                'Name' => 'Copper',
                                'QuantityScu' => 0.5,
                                'MinQuality' => 0,
                            ],
                        ],
                    ],
                ],
            ],
            'Dismantle' => [
                'TimeSeconds' => 10,
                'Efficiency' => 0.5,
                'Returns' => [
                    [
                        'Kind' => 'resource',
                        'UUID' => $copperUuid,
                        'Name' => 'Copper',
                        'QuantityScu' => 0.25,
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-blueprints', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 blueprints for version 4.0.0-LIVE (2 new identities, 0 existing identities). Skipped 0 invalid.');

    $blueprintData = BlueprintData::query()->where('key', 'BP_CRAFT_ALPHA')->first();

    expect(Blueprint::query()->count())->toBe(2)
        ->and(BlueprintData::query()->count())->toBe(2)
        ->and(BlueprintData::query()->where('output_item_uuid', $sharedOutputItemUuid)->count())->toBe(2)
        ->and($blueprintData)->not->toBeNull()
        ->and($blueprintData->output_name)->toBe('Alpha Output')
        ->and($blueprintData->output_class)->toBe('alpha_output_class')
        ->and($blueprintData->craft_time_seconds)->toBe(240)
        ->and($blueprintData->is_available_by_default)->toBeTrue()
        ->and($blueprintData->data->get('UUID'))->toBe($payload[0]['UUID']);

    $ingredientUuids = $blueprintData->ingredients->pluck('uuid')->sort()->values()->all();
    expect($ingredientUuids)->toBe(collect([$ironUuid, $lindiniumUuid])->sort()->values()->all());

    $dismantleReturns = $blueprintData->dismantleReturns->keyBy('uuid');
    expect($dismantleReturns->has($lindiniumUuid))->toBeTrue()
        ->and($dismantleReturns->has($ironUuid))->toBeTrue()
        ->and((float) $dismantleReturns->get($lindiniumUuid)->pivot->quantity_scu)->toBe(0.03)
        ->and((float) $dismantleReturns->get($ironUuid)->pivot->quantity_scu)->toBe(0.015);
});

it('upserts the versioned blueprint row on re-import', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.1-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $blueprintUuid = fake()->uuid();
    $outputItemUuid = fake()->uuid();
    $firstResource = Commodity::factory()->create(['uuid' => $firstResourceUuid = fake()->uuid()]);
    $secondResource = Commodity::factory()->create(['uuid' => $secondResourceUuid = fake()->uuid()]);

    $payload = [[
        'UUID' => $blueprintUuid,
        'Key' => 'BP_CRAFT_REIMPORT',
        'Kind' => 'creation',
        'CategoryUUID' => fake()->uuid(),
        'Output' => [
            'UUID' => $outputItemUuid,
            'Class' => 'reimport_output_class',
            'Type' => 'WeaponPersonal',
            'Name' => 'Reimport Output',
        ],
        'Availability' => [
            'Default' => false,
        ],
        'Tiers' => [
            [
                'TierIndex' => 0,
                'CraftTimeSeconds' => 10,
                'Requirements' => [
                    'Kind' => 'root',
                    'Children' => [
                        [
                            'Kind' => 'resource',
                            'UUID' => $firstResourceUuid,
                            'Name' => 'Resource A',
                            'QuantityScu' => 1,
                            'MinQuality' => 0,
                        ],
                    ],
                ],
            ],
        ],
        'Dismantle' => [
            'TimeSeconds' => 5,
            'Efficiency' => 0.5,
            'Returns' => [
                [
                    'Kind' => 'resource',
                    'UUID' => $firstResourceUuid,
                    'Name' => 'Resource A',
                    'QuantityScu' => 0.5,
                ],
            ],
        ],
    ]];

    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $this->artisan('game:import-blueprints', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

    $payload[0]['Availability']['Default'] = true;
    $payload[0]['Output']['Class'] = 'reimport_output_class_updated';
    $payload[0]['Output']['Name'] = 'Reimport Output Updated';
    $payload[0]['Tiers'][0]['CraftTimeSeconds'] = 99;
    $payload[0]['Tiers'][0]['Requirements']['Children'][] = [
        'Kind' => 'resource',
        'UUID' => $secondResourceUuid,
        'Name' => 'Resource B',
        'QuantityScu' => 2,
        'MinQuality' => 0,
    ];

    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $this->artisan('game:import-blueprints', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

    $blueprint = Blueprint::query()->firstWhere('uuid', $blueprintUuid);
    $blueprintData = BlueprintData::query()->where('blueprint_id', $blueprint->id)->first();

    expect($blueprintData)->not->toBeNull()
        ->and($blueprintData->output_name)->toBe('Reimport Output Updated')
        ->and($blueprintData->output_class)->toBe('reimport_output_class_updated')
        ->and($blueprintData->craft_time_seconds)->toBe(99)
        ->and($blueprintData->is_available_by_default)->toBeTrue()
        ->and($blueprintData->ingredients->pluck('uuid')->sort()->values()->all())->toBe(collect([$firstResourceUuid, $secondResourceUuid])->sort()->values()->all())
        ->and(BlueprintData::query()->where('blueprint_id', $blueprint->id)->count())->toBe(1);
});

it('skips unknown ingredient resource type uuids and logs a warning', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.2-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $knownResource = Commodity::factory()->create(['uuid' => $knownUuid = fake()->uuid()]);
    $unknownUuid = fake()->uuid();

    $payload = [[
        'UUID' => fake()->uuid(),
        'Key' => 'BP_CRAFT_UNKNOWN_INGREDIENT',
        'Kind' => 'creation',
        'CategoryUUID' => fake()->uuid(),
        'Output' => [
            'UUID' => fake()->uuid(),
            'Class' => 'output_class',
            'Type' => 'WeaponPersonal',
            'Name' => 'Output',
        ],
        'Availability' => [
            'Default' => true,
        ],
        'Tiers' => [
            [
                'TierIndex' => 0,
                'CraftTimeSeconds' => 60,
                'Requirements' => [
                    'Kind' => 'root',
                    'Children' => [
                        [
                            'Kind' => 'resource',
                            'UUID' => $knownUuid,
                            'Name' => 'Known',
                            'QuantityScu' => 1,
                            'MinQuality' => 0,
                        ],
                        [
                            'Kind' => 'resource',
                            'UUID' => $unknownUuid,
                            'Name' => 'Unknown',
                            'QuantityScu' => 0.5,
                            'MinQuality' => 0,
                        ],
                    ],
                ],
            ],
        ],
        'Dismantle' => [
            'TimeSeconds' => 5,
            'Efficiency' => 0.5,
            'Returns' => [],
        ],
    ]];

    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-blueprints', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput(sprintf('Skipping unknown ingredient resource type UUID: %s', $unknownUuid));

    $blueprintData = BlueprintData::query()->where('key', 'BP_CRAFT_UNKNOWN_INGREDIENT')->first();

    expect($blueprintData->ingredients->pluck('uuid')->all())->toBe([$knownUuid]);
});

it('generates slugs from output_name during import', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.3-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Commodity::factory()->create(['uuid' => $resourceUuid = fake()->uuid()]);

    $payload = [[
        'UUID' => fake()->uuid(),
        'Key' => 'BP_CRAFT_SLUG_TEST',
        'Kind' => 'creation',
        'CategoryUUID' => fake()->uuid(),
        'Output' => [
            'UUID' => fake()->uuid(),
            'Class' => 'slug_output_class',
            'Type' => 'WeaponPersonal',
            'Name' => 'My Cool Blueprint',
        ],
        'Availability' => ['Default' => true],
        'Tiers' => [[
            'TierIndex' => 0,
            'CraftTimeSeconds' => 10,
            'Requirements' => [
                'Kind' => 'root',
                'Children' => [
                    ['Kind' => 'resource', 'UUID' => $resourceUuid, 'Name' => 'Res', 'QuantityScu' => 1, 'MinQuality' => 0],
                ],
            ],
        ]],
        'Dismantle' => ['TimeSeconds' => 5, 'Efficiency' => 0.5, 'Returns' => []],
    ]];

    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-blueprints', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

    $blueprintData = BlueprintData::query()->firstWhere('key', 'BP_CRAFT_SLUG_TEST');
    $blueprint = $blueprintData->blueprint;

    expect($blueprint->slug)->toBe('my-cool-blueprint');
});

it('does not overwrite an existing slug on re-import', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.4-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $resourceUuid = fake()->uuid();
    Commodity::factory()->create(['uuid' => $resourceUuid]);

    $payload = [[
        'UUID' => $blueprintUuid = fake()->uuid(),
        'Key' => 'BP_SLUG_STABLE',
        'Kind' => 'creation',
        'CategoryUUID' => fake()->uuid(),
        'Output' => [
            'UUID' => fake()->uuid(),
            'Class' => 'original_class',
            'Type' => 'WeaponPersonal',
            'Name' => 'Original Name',
        ],
        'Availability' => ['Default' => true],
        'Tiers' => [[
            'TierIndex' => 0,
            'CraftTimeSeconds' => 10,
            'Requirements' => [
                'Kind' => 'root',
                'Children' => [
                    ['Kind' => 'resource', 'UUID' => $resourceUuid, 'Name' => 'Res', 'QuantityScu' => 1, 'MinQuality' => 0],
                ],
            ],
        ]],
        'Dismantle' => ['TimeSeconds' => 5, 'Efficiency' => 0.5, 'Returns' => []],
    ]];

    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $this->artisan('game:import-blueprints', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

    $blueprint = Blueprint::query()->firstWhere('uuid', $blueprintUuid);
    expect($blueprint->slug)->toBe('original-name');

    $payload[0]['Output']['Name'] = 'Updated Name';
    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $this->artisan('game:import-blueprints', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

    $blueprint->refresh();
    expect($blueprint->slug)->toBe('original-name');
});

it('falls back to key when output_name is empty for slug generation', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.5-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $resourceUuid = fake()->uuid();
    Commodity::factory()->create(['uuid' => $resourceUuid]);

    $payload = [[
        'UUID' => fake()->uuid(),
        'Key' => 'BP_CRAFT_NO_NAME',
        'Kind' => 'creation',
        'CategoryUUID' => fake()->uuid(),
        'Output' => [
            'UUID' => fake()->uuid(),
            'Class' => 'no_name_class',
            'Type' => 'WeaponPersonal',
            'Name' => '',
        ],
        'Availability' => ['Default' => true],
        'Tiers' => [[
            'TierIndex' => 0,
            'CraftTimeSeconds' => 10,
            'Requirements' => [
                'Kind' => 'root',
                'Children' => [
                    ['Kind' => 'resource', 'UUID' => $resourceUuid, 'Name' => 'Res', 'QuantityScu' => 1, 'MinQuality' => 0],
                ],
            ],
        ]],
        'Dismantle' => ['TimeSeconds' => 5, 'Efficiency' => 0.5, 'Returns' => []],
    ]];

    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-blueprints', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

    $blueprintData = BlueprintData::query()->firstWhere('key', 'BP_CRAFT_NO_NAME');
    $blueprint = $blueprintData->blueprint;

    expect($blueprint->slug)->toBe('bp-craft-no-name');
});
