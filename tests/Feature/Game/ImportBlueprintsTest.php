<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
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

it('imports blueprints, keeps full payloads, and extracts ingredient resource type uuids', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $sharedOutputItemUuid = fake()->uuid();
    $lindiniumUuid = fake()->uuid();
    $ironUuid = fake()->uuid();
    $copperUuid = fake()->uuid();

    $payload = [
        [
            'uuid' => fake()->uuid(),
            'key' => 'BP_CRAFT_ALPHA',
            'category_uuid' => fake()->uuid(),
            'output' => [
                'uuid' => $sharedOutputItemUuid,
                'class' => 'alpha_output_class',
                'name' => 'Alpha Output',
            ],
            'availability' => [
                'default' => true,
            ],
            'tiers' => [
                [
                    'tier_index' => 0,
                    'craft_time_seconds' => 240,
                    'requirements' => [
                        'kind' => 'root',
                        'children' => [
                            [
                                'kind' => 'group',
                                'key' => 'FRAME',
                                'name' => 'Frame',
                                'required_count' => 2,
                                'children' => [
                                    [
                                        'kind' => 'resource',
                                        'uuid' => $lindiniumUuid,
                                        'name' => 'Lindinium',
                                        'quantity_scu' => 0.06,
                                        'min_quality' => 0,
                                    ],
                                    [
                                        'kind' => 'group',
                                        'key' => 'CORE',
                                        'name' => 'Core',
                                        'required_count' => 1,
                                        'children' => [
                                            [
                                                'kind' => 'resource',
                                                'uuid' => $ironUuid,
                                                'name' => 'Iron',
                                                'quantity_scu' => 0.03,
                                                'min_quality' => 100,
                                            ],
                                            [
                                                'kind' => 'resource',
                                                'uuid' => $lindiniumUuid,
                                                'name' => 'Lindinium',
                                                'quantity_scu' => 0.02,
                                                'min_quality' => 10,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        [
            'uuid' => fake()->uuid(),
            'key' => 'BP_CRAFT_BETA',
            'category_uuid' => fake()->uuid(),
            'output' => [
                'uuid' => $sharedOutputItemUuid,
                'class' => 'beta_output_class',
                'name' => 'Beta Output',
            ],
            'availability' => [
                'default' => false,
            ],
            'tiers' => [
                [
                    'tier_index' => 0,
                    'craft_time_seconds' => 30,
                    'requirements' => [
                        'kind' => 'root',
                        'children' => [
                            [
                                'kind' => 'resource',
                                'uuid' => $copperUuid,
                                'name' => 'Copper',
                                'quantity_scu' => 0.5,
                                'min_quality' => 0,
                            ],
                        ],
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
        ->and($blueprintData->ingredient_resource_type_uuids)->toBe([$lindiniumUuid, $ironUuid])
        ->and($blueprintData->data->get('uuid'))->toBe($payload[0]['uuid']);
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
    $firstResourceUuid = fake()->uuid();
    $secondResourceUuid = fake()->uuid();

    $payload = [[
        'uuid' => $blueprintUuid,
        'key' => 'BP_CRAFT_REIMPORT',
        'category_uuid' => fake()->uuid(),
        'output' => [
            'uuid' => $outputItemUuid,
            'class' => 'reimport_output_class',
            'name' => 'Reimport Output',
        ],
        'availability' => [
            'default' => false,
        ],
        'tiers' => [
            [
                'tier_index' => 0,
                'craft_time_seconds' => 10,
                'requirements' => [
                    'kind' => 'root',
                    'children' => [
                        [
                            'kind' => 'resource',
                            'uuid' => $firstResourceUuid,
                            'name' => 'Resource A',
                            'quantity_scu' => 1,
                            'min_quality' => 0,
                        ],
                    ],
                ],
            ],
        ],
    ]];

    Storage::disk('scunpacked')->put('blueprints.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $this->artisan('game:import-blueprints', ['version' => $version->code])->assertExitCode(Command::SUCCESS);

    $payload[0]['availability']['default'] = true;
    $payload[0]['output']['class'] = 'reimport_output_class_updated';
    $payload[0]['output']['name'] = 'Reimport Output Updated';
    $payload[0]['tiers'][0]['craft_time_seconds'] = 99;
    $payload[0]['tiers'][0]['requirements']['children'][] = [
        'kind' => 'resource',
        'uuid' => $secondResourceUuid,
        'name' => 'Resource B',
        'quantity_scu' => 2,
        'min_quality' => 0,
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
        ->and($blueprintData->ingredient_resource_type_uuids)->toBe([$firstResourceUuid, $secondResourceUuid])
        ->and(BlueprintData::query()->where('blueprint_id', $blueprint->id)->count())->toBe(1);
});
