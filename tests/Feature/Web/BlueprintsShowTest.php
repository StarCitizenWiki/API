<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $this->requestedVersion = GameVersion::factory()->create([
        'code' => '4.0.0-PTU',
        'channel' => 'ptu',
        'released_at' => now()->subDay(),
        'is_default' => false,
    ]);
});

it('renders the blueprint show view with normalized api data', function (): void {
    $resourceType = ResourceType::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Lindinium',
    ]);

    $blueprint = Blueprint::factory()->create();
    $outputItemUuid = fake()->uuid();
    $requiredItemUuid = fake()->uuid();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_DETAIL',
            'output_item_uuid' => $outputItemUuid,
            'output_name' => 'Detailed Output',
            'output_class' => 'detailed_output',
            'craft_time_seconds' => 240,
            'ingredient_resource_type_uuids' => [$resourceType->uuid],
            'data' => [
                'availability' => [
                    'default' => false,
                    'reward_pools' => [
                        ['key' => 'BP_MISSIONREWARD_ALPHA'],
                    ],
                ],
                'output' => [
                    'uuid' => $outputItemUuid,
                    'name' => 'Detailed Output',
                    'class' => 'detailed_output',
                    'type' => 'WeaponPersonal',
                    'subtype' => 'Medium',
                    'grade' => '1',
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
                                    'required_count' => 1,
                                    'modifiers' => [
                                        [
                                            'key' => 'efficiency',
                                            'quality_range' => [
                                                'min' => 0,
                                                'max' => 1000,
                                            ],
                                            'modifier_range' => [
                                                'at_min_quality' => 0.8,
                                                'at_max_quality' => 1.2,
                                            ],
                                        ],
                                    ],
                                    'children' => [
                                        [
                                            'kind' => 'item',
                                            'uuid' => $requiredItemUuid,
                                            'name' => 'Reinforced Frame',
                                            'quantity' => 4,
                                        ],
                                        [
                                            'kind' => 'resource',
                                            'uuid' => $resourceType->uuid,
                                            'name' => 'Lindinium',
                                            'quantity_scu' => 0.06,
                                            'min_quality' => 0,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]));

    $response->assertOk()
        ->assertViewIs('blueprints.show')
        ->assertViewHas('mode', 'detail')
        ->assertViewHas('pageTitle', 'Detailed Output')
        ->assertViewHas('blueprint', function (array $payload) use ($blueprint, $outputItemUuid): bool {
            return ($payload['uuid'] ?? null) === $blueprint->uuid
                && ($payload['output']['uuid'] ?? null) === $outputItemUuid;
        })
        ->assertSee('Detailed Output')
        ->assertSee('4 minutes')
        ->assertSee('Q500')
        ->assertSee('4 items')
        ->assertSee('Frame')
        ->assertSee('Reinforced Frame')
        ->assertSee('Lindinium')
        ->assertSee('BP_MISSIONREWARD_ALPHA')
        ->assertSee(route('web.items.show', ['item' => $outputItemUuid]))
        ->assertSee(route('web.items.show', ['item' => $requiredItemUuid]))
        ->assertSee(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]))
        ->assertSee('id="blueprint-show-data"', false)
        ->assertSee('"currentBlueprintUuid": "'.$blueprint->uuid.'"', false)
        ->assertDontSee('const root = document.querySelector("[data-blueprint-show]")', false)
        ->assertSee('<meta property="og:title" content="Detailed Output Blueprint">', false)
        ->assertSee('<link rel="canonical" href="'.route('web.blueprints.show', ['blueprint' => $blueprint->uuid]).'">', false);
});

it('renders item-only recipe inputs in the crafting breakdown', function (): void {
    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_CRAFT_klwe_rifle_energy_01_green01',
            'output_name' => 'Gallant "Warhawk" Rifle',
            'output_class' => 'klwe_rifle_energy_01_green01',
            'data' => [
                'output' => [
                    'name' => 'Gallant "Warhawk" Rifle',
                    'class' => 'klwe_rifle_energy_01_green01',
                ],
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'group',
                                    'key' => 'LENSES',
                                    'name' => 'Lenses',
                                    'required_count' => 1,
                                    'modifiers' => [
                                        [
                                            'key' => 'weapon_damage',
                                            'quality_range' => [
                                                'min' => 0,
                                                'max' => 1000,
                                            ],
                                            'modifier_range' => [
                                                'at_min_quality' => 0.925,
                                                'at_max_quality' => 1.075,
                                            ],
                                        ],
                                    ],
                                    'children' => [
                                        [
                                            'kind' => 'item',
                                            'uuid' => fake()->uuid(),
                                            'name' => 'Dolivine',
                                            'quantity' => 1,
                                            'min_quality' => 0,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]));

    $response->assertOk()
        ->assertSee('Lenses')
        ->assertSee('Dolivine');
});

it('renders the requested game version on the blueprint show route', function (): void {
    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'output_name' => 'Default Output',
            'data' => [
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ]);

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->create([
            'output_name' => 'Requested Output',
            'output_item_uuid' => $requestedOutputItemUuid = fake()->uuid(),
            'data' => [
                'output' => [
                    'uuid' => $requestedOutputItemUuid,
                    'name' => 'Requested Output',
                    'class' => 'requested_output',
                ],
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this
        ->withSession(['game_version_code' => $this->defaultVersion->code])
        ->get(route('web.blueprints.show', [
            'blueprint' => $blueprint->uuid,
            'version' => $this->requestedVersion->code,
        ]));

    $response->assertOk()
        ->assertViewHas('blueprint', function (array $payload): bool {
            return ($payload['output_name'] ?? null) === 'Requested Output'
                && ($payload['game_version'] ?? null) === '4.0.0-PTU';
        })
        ->assertViewHas('resolvedVersionCode', $this->requestedVersion->code)
        ->assertSee('Requested Output')
        ->assertDontSee('Default Output')
        ->assertSee(route('web.blueprints.show', [
            'blueprint' => $blueprint->uuid,
            'version' => $this->requestedVersion->code,
        ]))
        ->assertSee(route('web.items.show', [
            'item' => $requestedOutputItemUuid,
            'version' => $this->requestedVersion->code,
        ]));
});

it('uses the stored game version on the blueprint show route when the url omits version', function (): void {
    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'output_name' => 'Default Output',
            'output_item_uuid' => fake()->uuid(),
            'data' => [
                'output' => [
                    'name' => 'Default Output',
                    'class' => 'default_output',
                ],
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ]);

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->create([
            'output_name' => 'Requested Output',
            'output_item_uuid' => $requestedOutputItemUuid = fake()->uuid(),
            'data' => [
                'output' => [
                    'uuid' => $requestedOutputItemUuid,
                    'name' => 'Requested Output',
                    'class' => 'requested_output',
                ],
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this
        ->withSession(['game_version_code' => $this->requestedVersion->code])
        ->get(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]));

    $response->assertOk()
        ->assertViewHas('blueprint', function (array $payload): bool {
            return ($payload['output_name'] ?? null) === 'Requested Output'
                && ($payload['game_version'] ?? null) === '4.0.0-PTU';
        })
        ->assertViewHas('resolvedVersionCode', $this->requestedVersion->code)
        ->assertSee('Requested Output')
        ->assertDontSee('Default Output')
        ->assertSee(route('web.blueprints.show', [
            'blueprint' => $blueprint->uuid,
            'version' => $this->requestedVersion->code,
        ]))
        ->assertSee(route('web.items.show', [
            'item' => $requestedOutputItemUuid,
            'version' => $this->requestedVersion->code,
        ]));
});

it('keeps the resource filter without forcing the blueprint picker open', function (): void {
    $resourceTypeUuid = fake()->uuid();
    $blueprint = Blueprint::factory()->create();
    $requiredItemUuid = fake()->uuid();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'output_name' => 'Default Output',
            'ingredient_resource_type_uuids' => [fake()->uuid()],
            'data' => [
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ]);

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->create([
            'output_name' => 'Requested Output',
            'output_item_uuid' => fake()->uuid(),
            'ingredient_resource_type_uuids' => [$resourceTypeUuid],
            'data' => [
                'output' => [
                    'name' => 'Requested Output',
                    'class' => 'requested_output',
                ],
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'item',
                                    'uuid' => $requiredItemUuid,
                                    'name' => 'Requested Component',
                                    'quantity' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this
        ->withSession(['game_version_code' => $this->defaultVersion->code])
        ->get(route('web.blueprints.show', [
            'blueprint' => $blueprint->uuid,
            'version' => $this->requestedVersion->code,
            'filter' => [
                'ingredient.uuid' => $resourceTypeUuid,
            ],
        ]));

    $response->assertOk()
        ->assertViewHas('resolvedVersionCode', $this->requestedVersion->code)
        ->assertViewHas('clientPayload', function (string $payload): bool {
            $clientPayload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

            return ($clientPayload['search']['version'] ?? null) === '4.0.0-PTU';
        })
        ->assertSee(route('web.items.show', [
            'item' => $requiredItemUuid,
            'version' => $this->requestedVersion->code,
        ]));

    $crawler = new Crawler($response->getContent());
    $changeBlueprintPanel = $crawler
        ->filterXPath('//details[.//*[@data-blueprint-search]]')
        ->first();
    $searchResultLink = $crawler
        ->filterXPath('//a[@data-blueprint-search-result-link and @data-blueprint-uuid="'.$blueprint->uuid.'"]')
        ->first();

    expect($changeBlueprintPanel->attr('open'))->toBeNull()
        ->and($searchResultLink->attr('href'))->toBe(route('web.blueprints.show', [
            'blueprint' => $blueprint->uuid,
            'version' => $this->requestedVersion->code,
            'filter' => [
                'ingredient.uuid' => $resourceTypeUuid,
            ],
        ]));
});

it('renders grouped resource choices when a blueprint requires only some available inputs', function (): void {
    $blueprint = Blueprint::factory()->create();
    $outputItemUuid = fake()->uuid();
    $laraniteUuid = fake()->uuid();
    $aslariteUuid = fake()->uuid();
    $stileronUuid = fake()->uuid();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_CRAFT_vgl_utility_light_legs_01_01_01',
            'output_item_uuid' => $outputItemUuid,
            'output_name' => 'Chiron Legs',
            'output_class' => 'utility_light_legs',
            'craft_time_seconds' => 180,
            'ingredient_resource_type_uuids' => [$laraniteUuid, $aslariteUuid, $stileronUuid],
            'data' => [
                'output' => [
                    'uuid' => $outputItemUuid,
                    'name' => 'Chiron Legs',
                    'class' => 'utility_light_legs',
                    'type' => 'Armor',
                    'subtype' => 'Legs',
                ],
                'tiers' => [
                    [
                        'tier_index' => 0,
                        'craft_time_seconds' => 180,
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'group',
                                    'key' => 'ASPECTS',
                                    'name' => '<= PLACEHOLDER =>',
                                    'required_count' => 2,
                                    'children' => [
                                        [
                                            'kind' => 'group',
                                            'key' => 'CASING',
                                            'name' => 'Casing',
                                            'required_count' => 1,
                                            'children' => [
                                                [
                                                    'kind' => 'resource',
                                                    'uuid' => $laraniteUuid,
                                                    'name' => 'Laranite',
                                                    'quantity_scu' => 0.03,
                                                    'min_quality' => 0,
                                                ],
                                            ],
                                        ],
                                        [
                                            'kind' => 'group',
                                            'key' => 'INSULATIVE LINER',
                                            'name' => 'Insulative Liner',
                                            'required_count' => 1,
                                            'modifiers' => [
                                                [
                                                    'property_key' => 'armor_temperaturemax',
                                                    'quality_range' => [
                                                        'min' => 0,
                                                        'max' => 1000,
                                                    ],
                                                    'modifier_range' => [
                                                        'at_min_quality' => 0.8,
                                                        'at_max_quality' => 1.2,
                                                    ],
                                                ],
                                            ],
                                            'children' => [
                                                [
                                                    'kind' => 'resource',
                                                    'uuid' => $aslariteUuid,
                                                    'name' => 'Aslarite',
                                                    'quantity_scu' => 0.02,
                                                    'min_quality' => 0,
                                                ],
                                            ],
                                        ],
                                        [
                                            'kind' => 'group',
                                            'key' => 'CASING WEAVE',
                                            'name' => 'Casing Weave',
                                            'required_count' => 1,
                                            'modifiers' => [
                                                [
                                                    'property_key' => 'armor_damagemitigation',
                                                    'quality_range' => [
                                                        'min' => 0,
                                                        'max' => 1000,
                                                    ],
                                                    'modifier_range' => [
                                                        'at_min_quality' => 0.95,
                                                        'at_max_quality' => 1.05,
                                                    ],
                                                ],
                                            ],
                                            'children' => [
                                                [
                                                    'kind' => 'resource',
                                                    'uuid' => $stileronUuid,
                                                    'name' => 'Stileron',
                                                    'quantity_scu' => 0.03,
                                                    'min_quality' => 0,
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
        ]);

    $response = $this->get(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]));

    $response->assertOk()
        ->assertSee('Chiron Legs')
        ->assertSee('Laranite')
        ->assertSee('Aslarite')
        ->assertSee('Stileron')
        ->assertSee('Armor Temperature Max')
        ->assertSee('Armor Damage Mitigation');
});

it('returns not found when the blueprint is missing for the requested version', function (): void {
    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create();

    $this->get(route('web.blueprints.show', [
        'blueprint' => $blueprint->uuid,
        'version' => $this->requestedVersion->code,
    ]))->assertNotFound();
});
