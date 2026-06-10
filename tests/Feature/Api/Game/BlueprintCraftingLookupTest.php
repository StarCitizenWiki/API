<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

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

it('lists commodities', function (): void {
    $alpha = Commodity::factory()->create([
        'key' => 'AlphaResource',
        'name' => 'Alpha Resource',
    ]);

    $beta = Commodity::factory()->create([
        'key' => 'BetaResource',
        'name' => 'Beta Resource',
    ]);

    $response = $this->getJson('/api/commodities');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.uuid', $alpha->uuid)
        ->assertJsonPath('data.0.key', 'AlphaResource')
        ->assertJsonPath('data.0.link', route('commodities.show', ['commodity' => $alpha->uuid]))
        ->assertJsonPath('data.1.uuid', $beta->uuid);
});

it('can filter commodities to only those used by blueprints for the resolved game version', function (): void {
    $usedInDefault = Commodity::factory()->create([
        'key' => 'UsedDefault',
        'name' => 'Used Default',
    ]);

    $usedInRequested = Commodity::factory()->create([
        'key' => 'UsedRequested',
        'name' => 'Used Requested',
    ]);

    Commodity::factory()->create([
        'key' => 'UnusedResource',
        'name' => 'Unused Resource',
    ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($usedInDefault)
        ->create([
            'data' => [
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->withIngredients($usedInRequested)
        ->create([
            'data' => [
                'tiers' => [],
            ],
        ]);

    $defaultResponse = $this->getJson('/api/commodities?'.http_build_query([
        'filter' => [
            'used' => true,
        ],
    ]));

    $requestedResponse = $this->getJson('/api/commodities?'.http_build_query([
        'version' => $this->requestedVersion->code,
        'filter' => [
            'used' => true,
        ],
    ]));

    $defaultResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $usedInDefault->uuid);

    $requestedResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $usedInRequested->uuid)
        ->assertJsonPath(
            'data.0.link',
            route('commodities.show', [
                'commodity' => $usedInRequested->uuid,
                'version' => $this->requestedVersion->code,
            ]),
        );
});

it('rejects invalid used filters', function (): void {
    $response = $this->getJson('/api/commodities?'.http_build_query([
        'filter' => [
            'used' => 'maybe',
        ],
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['filter.used']);
});

it('returns blueprints that consume a commodity for the resolved game version', function (): void {
    $resourceType = Commodity::factory()->create();
    $otherResourceType = Commodity::factory()->create();

    $matchingBlueprint = Blueprint::factory()->create();
    BlueprintData::factory()
        ->for($matchingBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($resourceType, $otherResourceType)
        ->create([
            'key' => 'BP_MATCHING',
            'output_item_uuid' => fake()->uuid(),
            'data' => [
                'tiers' => [],
            ],
        ]);

    $nonMatchingBlueprint = Blueprint::factory()->create();
    BlueprintData::factory()
        ->for($nonMatchingBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($otherResourceType)
        ->create([
            'key' => 'BP_NON_MATCHING',
            'output_item_uuid' => fake()->uuid(),
            'data' => [
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for($matchingBlueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->withIngredients($resourceType)
        ->create([
            'key' => 'BP_MATCHING_PTU',
            'output_item_uuid' => fake()->uuid(),
            'data' => [
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson(route('commodities.show', ['commodity' => $resourceType->uuid, 'include' => 'blueprints']));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $resourceType->uuid)
        ->assertJsonCount(1, 'data.blueprints')
        ->assertJsonPath('data.blueprints.0.key', 'BP_MATCHING')
        ->assertJsonPath('data.blueprints.0.output_item_uuid', BlueprintData::where('blueprint_id', $matchingBlueprint->id)->where('game_version_id', $this->defaultVersion->id)->first()->output_item_uuid);
});

it('returns items that have a commodity in their default composition', function (): void {
    $resourceType = Commodity::factory()->create([
        'key' => 'TestResource',
        'name' => 'Test Resource',
    ]);

    $itemWithResource = Item::factory()->create();
    $itemData = ItemData::factory()
        ->for($itemWithResource, 'item')
        ->for($this->defaultVersion, 'gameVersion')
        ->hasAttached($resourceType, [], 'commodities')
        ->create([
            'name' => 'Item With Resource',
        ]);

    $itemWithoutResource = Item::factory()->create();
    ItemData::factory()
        ->for($itemWithoutResource, 'item')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'name' => 'Item Without Resource',
        ]);

    $response = $this->getJson(route('commodities.show', ['commodity' => $resourceType->uuid, 'include' => 'items']));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $resourceType->uuid)
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.name', 'Item With Resource')
        ->assertJsonPath('data.items.0.uuid', $itemWithResource->uuid)
        ->assertJsonPath('data.items.0.type', $itemData->type)
        ->assertJsonPath('data.items.0.sub_type', $itemData->sub_type)
        ->assertJsonPath('data.items.0.size', $itemData->size);
});

it('lists blueprints for the resolved game version', function (): void {
    $defaultBlueprint = Blueprint::factory()->create();
    $requestedBlueprint = Blueprint::factory()->create();
    $defaultResourceUuid = fake()->uuid();
    $requestedResourceUuid = fake()->uuid();

    BlueprintData::factory()
        ->for($defaultBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_DEFAULT',
            'output_name' => 'Default Output',
            'output_class' => 'default_output',
            'data' => [
                'output' => [
                    'name' => 'Default Output',
                    'class' => 'default_output',
                ],
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'resource',
                                    'uuid' => $defaultResourceUuid,
                                    'name' => 'Hephaestanite',
                                    'quantity_scu' => 0.03,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    BlueprintData::factory()
        ->for($requestedBlueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->create([
            'key' => 'BP_REQUESTED',
            'output_name' => 'Requested Output',
            'output_class' => 'requested_output',
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
                                    'kind' => 'resource',
                                    'uuid' => $requestedResourceUuid,
                                    'name' => 'Iron',
                                    'quantity_scu' => 0.03,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $defaultResponse = $this->getJson('/api/blueprints');
    $requestedResponse = $this->getJson("/api/blueprints?version={$this->requestedVersion->code}");

    $defaultResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $defaultBlueprint->uuid)
        ->assertJsonPath('data.0.key', 'BP_DEFAULT')
        ->assertJsonPath('data.0.output_name', 'Default Output')
        ->assertJsonPath('data.0.output_class', 'default_output')
        ->assertJsonPath('data.0.ingredients.0.name', 'Hephaestanite')
        ->assertJsonPath('data.0.ingredients.0.resource_type_uuid', $defaultResourceUuid)
        ->assertJsonPath('data.0.link', route('blueprints.show', ['blueprint' => $defaultBlueprint->uuid]))
        ->assertJsonMissingPath('data.0.tiers')
        ->assertJsonMissingPath('data.0.ingredient_names')
        ->assertJsonMissingPath('data.0.ingredient_resource_type_uuids')
        ->assertJsonMissingPath('data.0.ingredient_overview');

    $requestedResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $requestedBlueprint->uuid)
        ->assertJsonPath('data.0.key', 'BP_REQUESTED')
        ->assertJsonPath('data.0.output_name', 'Requested Output')
        ->assertJsonPath('data.0.output_class', 'requested_output')
        ->assertJsonPath('data.0.ingredients.0.name', 'Iron')
        ->assertJsonPath('data.0.ingredients.0.resource_type_uuid', $requestedResourceUuid)
        ->assertJsonPath('data.0.game_version', $this->requestedVersion->code)
        ->assertJsonPath(
            'data.0.link',
            route('blueprints.show', [
                'blueprint' => $requestedBlueprint->uuid,
                'version' => $this->requestedVersion->code,
            ]),
        )
        ->assertJsonMissingPath('data.0.tiers')
        ->assertJsonMissingPath('data.0.ingredient_names')
        ->assertJsonMissingPath('data.0.ingredient_resource_type_uuids')
        ->assertJsonMissingPath('data.0.ingredient_overview');
});

it('shows blueprint detail with output item uuid and raw tiers', function (): void {
    $resourceType = Commodity::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Lindinium',
    ]);

    $blueprint = Blueprint::factory()->create();
    $outputItemUuid = fake()->uuid();
    $requiredItemUuid = fake()->uuid();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($resourceType)
        ->create([
            'key' => 'BP_DETAIL',
            'output_item_uuid' => $outputItemUuid,
            'output_name' => 'Detailed Output',
            'output_class' => 'detailed_output',
            'data' => [
                'availability' => [
                    'default' => false,
                    'reward_pools' => [
                        [
                            'key' => 'BP_MISSIONREWARD_ALPHA',
                            'uuid' => fake()->uuid(),
                        ],
                    ],
                ],
                'Output' => [
                    'UUID' => $outputItemUuid,
                    'Name' => 'Detailed Output',
                    'Class' => 'detailed_output',
                    'Type' => 'WeaponPersonal',
                    'Subtype' => 'Medium',
                    'Grade' => '1',
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
                                            'value' => 0.85,
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

    $response = $this->getJson("/api/blueprints/{$blueprint->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $blueprint->uuid)
        ->assertJsonPath('data.output_item_uuid', $outputItemUuid)
        ->assertJsonPath('data.ingredient_count', 2)
        ->assertJsonPath('data.output_name', 'Detailed Output')
        ->assertJsonPath('data.output_class', 'detailed_output')
        ->assertJsonPath('data.output.uuid', $outputItemUuid)
        ->assertJsonPath('data.output.name', 'Detailed Output')
        ->assertJsonPath('data.output.type', 'WeaponPersonal')
        ->assertJsonPath('data.output.item_web_url', route('web.items.show', ['item' => $outputItemUuid]))
        ->assertJsonPath('data.web_url', url('/blueprints/'.($blueprint->slug ?? $blueprint->uuid)))
        ->assertJsonPath('data.output_item_web_url', route('web.items.show', ['item' => $outputItemUuid]))
        ->assertJsonPath('data.ingredients.0.name', 'Reinforced Frame')
        ->assertJsonPath('data.ingredients.0.resource_type_uuid', null)
        ->assertJsonPath('data.ingredients.1.name', 'Lindinium')
        ->assertJsonPath('data.ingredients.1.resource_type_uuid', $resourceType->uuid)
        ->assertJsonMissingPath('data.ingredient_names')
        ->assertJsonMissingPath('data.ingredient_resource_type_uuids')
        ->assertJsonMissingPath('data.ingredient_overview')
        ->assertJsonCount(1, 'data.requirement_groups')
        ->assertJsonPath('data.requirement_groups.0.key', 'FRAME')
        ->assertJsonPath('data.requirement_groups.0.required_count', 1)
        ->assertJsonPath('data.requirement_groups.0.modifiers.0.property_key', 'efficiency')
        ->assertJsonPath('data.requirement_groups.0.children.1.uuid', $resourceType->uuid)
        ->assertJsonPath('data.summary_properties.0.property_key', 'efficiency')
        ->assertJsonPath('data.summary_properties.0.label', 'Efficiency')
        ->assertJsonPath('data.tiers.0.tier_index', 0)
        ->assertJsonPath('data.tiers.0.craft_time_seconds', 240)
        ->assertJsonPath('data.tiers.0.requirements.children.0.required_count', 1)
        ->assertJsonPath('data.tiers.0.requirements.children.0.modifiers.0.key', 'efficiency')
        ->assertJsonPath('data.tiers.0.requirements.children.0.modifiers.0.value', 0.85)
        ->assertJsonPath('data.tiers.0.requirements.children.0.children.0.uuid', $requiredItemUuid)
        ->assertJsonPath('data.tiers.0.requirements.children.0.children.0.quantity', 4)
        ->assertJsonPath('data.tiers.0.requirements.children.0.children.1.uuid', $resourceType->uuid)
        ->assertJsonPath('data.tiers.0.requirements.children.0.children.1.quantity_scu', 0.06);
});

it('resolves requested or default game versions for blueprint detail', function (): void {
    $resourceType = Commodity::factory()->create();
    $blueprint = Blueprint::factory()->create();
    $defaultOutputItemUuid = fake()->uuid();
    $requestedOutputItemUuid = fake()->uuid();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($resourceType)
        ->create([
            'key' => 'BP_VERSIONED',
            'output_item_uuid' => $defaultOutputItemUuid,
            'data' => [
                'tiers' => [
                    [
                        'tier_index' => 0,
                        'craft_time_seconds' => 10,
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'resource',
                                    'uuid' => $resourceType->uuid,
                                    'name' => 'Default Resource',
                                    'quantity_scu' => 1,
                                    'min_quality' => 0,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->withIngredients($resourceType)
        ->create([
            'key' => 'BP_VERSIONED',
            'output_item_uuid' => $requestedOutputItemUuid,
            'data' => [
                'tiers' => [
                    [
                        'tier_index' => 0,
                        'craft_time_seconds' => 20,
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'resource',
                                    'uuid' => $resourceType->uuid,
                                    'name' => 'Requested Resource',
                                    'quantity_scu' => 2,
                                    'min_quality' => 0,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $defaultDetail = $this->getJson("/api/blueprints/{$blueprint->uuid}");
    $requestedDetail = $this->getJson("/api/blueprints/{$blueprint->uuid}?version={$this->requestedVersion->code}");

    $defaultDetail->assertSuccessful()
        ->assertJsonPath('data.output_item_uuid', $defaultOutputItemUuid)
        ->assertJsonPath('data.game_version', $this->defaultVersion->code)
        ->assertJsonPath('data.ingredient_count', 1)
        ->assertJsonMissingPath('data.ingredient_overview')
        ->assertJsonCount(1, 'data.requirement_groups')
        ->assertJsonPath('data.output.uuid', $defaultOutputItemUuid)
        ->assertJsonPath('data.tiers.0.requirements.children.0.quantity_scu', 1)
        ->assertJsonPath('data.web_url', url('/blueprints/'.($blueprint->slug ?? $blueprint->uuid)))
        ->assertJsonPath('data.output_item_web_url', route('web.items.show', ['item' => $defaultOutputItemUuid]))
        ->assertJsonPath('data.link', route('blueprints.show', ['blueprint' => $blueprint->uuid]));

    $requestedDetail->assertSuccessful()
        ->assertJsonPath('data.output_item_uuid', $requestedOutputItemUuid)
        ->assertJsonPath('data.game_version', $this->requestedVersion->code)
        ->assertJsonPath('data.ingredient_count', 1)
        ->assertJsonMissingPath('data.ingredient_overview')
        ->assertJsonCount(1, 'data.requirement_groups')
        ->assertJsonPath('data.output.uuid', $requestedOutputItemUuid)
        ->assertJsonPath('data.tiers.0.requirements.children.0.quantity_scu', 2)
        ->assertJsonPath(
            'data.web_url',
            url('/blueprints/'.($blueprint->slug ?? $blueprint->uuid)).'?version='.$this->requestedVersion->code,
        )
        ->assertJsonPath(
            'data.output_item_web_url',
            route('web.items.show', [
                'item' => $requestedOutputItemUuid,
                'version' => $this->requestedVersion->code,
            ]),
        )
        ->assertJsonPath(
            'data.link',
            route('blueprints.show', [
                'blueprint' => $blueprint->uuid,
                'version' => $this->requestedVersion->code,
            ]),
        );
});

it('preserves grouped requirement alternatives in raw tiers', function (): void {
    $resourceType = Commodity::factory()->create([
        'name' => 'Lindinium',
    ]);

    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($resourceType)
        ->create([
            'key' => 'BP_GROUPED_REQUIREMENTS',
            'data' => [
                'tiers' => [
                    [
                        'tier_index' => 0,
                        'craft_time_seconds' => 60,
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'group',
                                    'key' => 'ASPECTS',
                                    'name' => 'Aspects',
                                    'required_count' => 2,
                                    'children' => [
                                        [
                                            'kind' => 'resource',
                                            'uuid' => $resourceType->uuid,
                                            'name' => 'Aspect A',
                                            'quantity_scu' => 1,
                                        ],
                                        [
                                            'kind' => 'resource',
                                            'uuid' => fake()->uuid(),
                                            'name' => 'Aspect B',
                                            'quantity_scu' => 2,
                                        ],
                                        [
                                            'kind' => 'resource',
                                            'uuid' => fake()->uuid(),
                                            'name' => 'Aspect C',
                                            'quantity_scu' => 3,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/blueprints/{$blueprint->uuid}");

    $response->assertSuccessful()
        ->assertJsonMissingPath('data.ingredient_overview')
        ->assertJsonCount(1, 'data.requirement_groups')
        ->assertJsonPath('data.tiers.0.requirements.children.0.key', 'ASPECTS')
        ->assertJsonPath('data.tiers.0.requirements.children.0.required_count', 2)
        ->assertJsonCount(3, 'data.tiers.0.requirements.children.0.children');
});

it('preserves nested requirement children in normalized detail fields', function (): void {
    $resourceType = Commodity::factory()->create([
        'name' => 'Taranite',
    ]);

    $blueprint = Blueprint::factory()->create();
    $segmentFastenerUuid = fake()->uuid();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($resourceType)
        ->create([
            'key' => 'BP_NESTED_REQUIREMENT_GROUPS',
            'data' => [
                'tiers' => [
                    [
                        'tier_index' => 0,
                        'craft_time_seconds' => 120,
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'group',
                                    'key' => 'HULL',
                                    'name' => 'Hull',
                                    'required_count' => 1,
                                    'children' => [
                                        [
                                            'kind' => 'group',
                                            'key' => 'SEGMENT_PANELING',
                                            'name' => 'Segment Paneling',
                                            'required_count' => 1,
                                            'children' => [
                                                [
                                                    'kind' => 'resource',
                                                    'uuid' => $resourceType->uuid,
                                                    'name' => 'Taranite',
                                                    'quantity_scu' => 0.5,
                                                ],
                                                [
                                                    'kind' => 'item',
                                                    'uuid' => $segmentFastenerUuid,
                                                    'name' => 'Panel Fastener',
                                                    'quantity' => 4,
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

    $response = $this->getJson("/api/blueprints/{$blueprint->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.ingredient_count', 2)
        ->assertJsonCount(1, 'data.requirement_groups')
        ->assertJsonPath('data.requirement_groups.0.key', 'HULL')
        ->assertJsonPath('data.requirement_groups.0.children.0.kind', 'group')
        ->assertJsonPath('data.requirement_groups.0.children.0.key', 'SEGMENT_PANELING')
        ->assertJsonPath('data.requirement_groups.0.children.0.required_count', 1)
        ->assertJsonPath('data.requirement_groups.0.children.0.children.0.uuid', $resourceType->uuid)
        ->assertJsonPath('data.requirement_groups.0.children.0.children.0.quantity_scu', 0.5)
        ->assertJsonPath('data.requirement_groups.0.children.0.children.1.uuid', $segmentFastenerUuid)
        ->assertJsonPath('data.requirement_groups.0.children.0.children.1.quantity', 4)
        ->assertJsonMissingPath('data.ingredient_overview');
});

it('searches blueprints by output query and explicit output filters', function (): void {
    $matchingBlueprint = Blueprint::factory()->create();
    $matchingOutputUuid = fake()->uuid();

    BlueprintData::factory()
        ->for($matchingBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_OUTPUT_MATCH',
            'output_item_uuid' => $matchingOutputUuid,
            'output_name' => 'Forge Beam Mk I',
            'output_class' => 'forge_beam_mk1',
            'data' => [
                'output' => [
                    'uuid' => $matchingOutputUuid,
                    'name' => 'Forge Beam Mk I',
                    'class' => 'forge_beam_mk1',
                ],
                'tiers' => [],
            ],
        ]);

    $otherBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($otherBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_OUTPUT_OTHER',
            'output_name' => 'Shield Array',
            'output_class' => 'shield_array',
            'data' => [
                'output' => [
                    'name' => 'Shield Array',
                    'class' => 'shield_array',
                ],
                'tiers' => [],
            ],
        ]);

    $queryByNameResponse = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'query' => 'Forge Beam',
        ],
    ]));

    $queryByClassResponse = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'query' => 'forge_beam_mk1',
        ],
    ]));

    $queryByUuidResponse = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'query' => $matchingOutputUuid,
        ],
    ]));

    $explicitOutputFiltersResponse = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'output.name' => 'Forge Beam',
            'output.class' => 'forge_beam',
            'output.uuid' => $matchingOutputUuid,
        ],
    ]));

    $queryByNameResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingBlueprint->uuid)
        ->assertJsonPath('data.0.output_name', 'Forge Beam Mk I');

    $queryByClassResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingBlueprint->uuid);

    $queryByUuidResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingBlueprint->uuid);

    $explicitOutputFiltersResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingBlueprint->uuid)
        ->assertJsonPath('data.0.output_class', 'forge_beam_mk1');
});

it('filters blueprints by ingredient name and uuid', function (): void {
    $hephaestanite = Commodity::factory()->create([
        'uuid' => fake()->uuid(),
        'key' => 'Hephaestanite',
        'name' => 'Hephaestanite',
    ]);

    $quantanium = Commodity::factory()->create([
        'uuid' => fake()->uuid(),
        'key' => 'Quantanium',
        'name' => 'Quantanium',
    ]);

    $hephaestaniteBlueprint = Blueprint::factory()->create();
    BlueprintData::factory()
        ->for($hephaestaniteBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($hephaestanite)
        ->create([
            'key' => 'BP_INPUT_HEPHAE',
            'data' => [
                'tiers' => [],
            ],
        ]);

    $quantaniumBlueprint = Blueprint::factory()->create();
    BlueprintData::factory()
        ->for($quantaniumBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($quantanium)
        ->create([
            'key' => 'BP_INPUT_QUANTA',
            'data' => [
                'tiers' => [],
            ],
        ]);

    $ingredientByNameResponse = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'ingredient' => 'Hephae',
        ],
    ]));

    $ingredientByUuidResponse = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'ingredient.uuid' => $quantanium->uuid,
        ],
    ]));

    $ingredientByNameResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $hephaestaniteBlueprint->uuid)
        ->assertJsonPath('data.0.ingredients.0.name', 'Hephaestanite')
        ->assertJsonPath('data.0.ingredients.0.resource_type_uuid', $hephaestanite->uuid);

    $ingredientByUuidResponse->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $quantaniumBlueprint->uuid)
        ->assertJsonPath('data.0.ingredients.0.name', 'Quantanium')
        ->assertJsonPath('data.0.ingredients.0.resource_type_uuid', $quantanium->uuid);
});

it('filters blueprints by multiple ingredient uuids and requires every selected resource', function (): void {
    $alpha = Commodity::factory()->create([
        'uuid' => fake()->uuid(),
        'key' => 'AlphaResource',
        'name' => 'Alpha Resource',
    ]);

    $beta = Commodity::factory()->create([
        'uuid' => fake()->uuid(),
        'key' => 'BetaResource',
        'name' => 'Beta Resource',
    ]);

    $matchingBlueprint = Blueprint::factory()->create();
    BlueprintData::factory()
        ->for($matchingBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($alpha, $beta)
        ->create([
            'key' => 'BP_INPUT_ALPHA_BETA',
            'data' => [
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($alpha)
        ->create([
            'key' => 'BP_INPUT_ALPHA_ONLY',
            'data' => [
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for(Blueprint::factory(), 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->withIngredients($beta)
        ->create([
            'key' => 'BP_INPUT_BETA_ONLY',
            'data' => [
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'ingredient.uuid' => [$alpha->uuid, $beta->uuid],
        ],
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingBlueprint->uuid)
        ->assertJsonPath('data.0.ingredients.0.resource_type_uuid', $alpha->uuid)
        ->assertJsonPath('data.0.ingredients.1.resource_type_uuid', $beta->uuid);
});

it('includes ingredient names for grouped blueprint requirements in index responses', function (): void {
    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_INPUT_NAMES',
            'output_name' => 'FS-9 Magazine (75 cap)',
            'data' => [
                'tiers' => [
                    [
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'group',
                                    'key' => 'MAGAZINE',
                                    'name' => 'Magazine',
                                    'required_count' => 1,
                                    'children' => [
                                        [
                                            'kind' => 'resource',
                                            'uuid' => fake()->uuid(),
                                            'name' => 'Hephaestanite',
                                            'quantity_scu' => 0.03,
                                        ],
                                    ],
                                ],
                                [
                                    'kind' => 'group',
                                    'key' => 'CORE',
                                    'name' => 'Core',
                                    'required_count' => 1,
                                    'children' => [
                                        [
                                            'kind' => 'resource',
                                            'uuid' => fake()->uuid(),
                                            'name' => 'Iron',
                                            'quantity_scu' => 0.03,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'output.name' => 'FS-9 Magazine',
        ],
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.output_name', 'FS-9 Magazine (75 cap)')
        ->assertJsonPath('data.0.ingredients.0.name', 'Hephaestanite')
        ->assertJsonPath('data.0.ingredients.1.name', 'Iron');
});

it('filters blueprints by output type and default availability', function (): void {
    $matchingBlueprint = Blueprint::factory()->create();
    $otherBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($matchingBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_FILTER_MATCH',
            'is_available_by_default' => true,
            'data' => [
                'Output' => [
                    'Name' => 'FS-9 LMG',
                    'Class' => 'behr_lmg_ballistic_01',
                    'Type' => 'WeaponPersonal',
                ],
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for($otherBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_FILTER_OTHER',
            'is_available_by_default' => false,
            'data' => [
                'Output' => [
                    'Name' => 'Greycat Tool',
                    'Class' => 'greycat_tool',
                    'Type' => 'Utility',
                ],
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson('/api/blueprints?'.http_build_query([
        'filter' => [
            'output.type' => 'WeaponPersonal',
            'default' => 'true',
        ],
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $matchingBlueprint->uuid)
        ->assertJsonPath('data.0.output.type', 'WeaponPersonal')
        ->assertJsonPath('data.0.is_available_by_default', true);
});

it('sorts blueprints by craft time and ingredient count', function (): void {
    $fewIngredientsBlueprint = Blueprint::factory()->create();
    $manyIngredientsBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($fewIngredientsBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_SORT_FAST',
            'craft_time_seconds' => 60,
            'data' => [
                'Output' => [
                    'Name' => 'Fast Build',
                    'Class' => 'fast_build',
                    'Type' => 'WeaponPersonal',
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
                                    'UUID' => fake()->uuid(),
                                    'Name' => 'Iron',
                                    'QuantityScu' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    BlueprintData::factory()
        ->for($manyIngredientsBlueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_SORT_SLOW',
            'craft_time_seconds' => 240,
            'data' => [
                'Output' => [
                    'Name' => 'Slow Build',
                    'Class' => 'slow_build',
                    'Type' => 'Utility',
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
                                    'RequiredCount' => 1,
                                    'Children' => [
                                        [
                                            'Kind' => 'resource',
                                            'UUID' => fake()->uuid(),
                                            'Name' => 'Titanium',
                                            'QuantityScu' => 1,
                                        ],
                                        [
                                            'Kind' => 'item',
                                            'UUID' => fake()->uuid(),
                                            'Name' => 'Fastener',
                                            'Quantity' => 2,
                                        ],
                                        [
                                            'Kind' => 'resource',
                                            'UUID' => fake()->uuid(),
                                            'Name' => 'Copper',
                                            'QuantityScu' => 1,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $craftTimeResponse = $this->getJson('/api/blueprints?sort=-craft_time_seconds');
    $ingredientCountResponse = $this->getJson('/api/blueprints?sort=-ingredient_count');

    $craftTimeResponse->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $manyIngredientsBlueprint->uuid)
        ->assertJsonPath('data.1.uuid', $fewIngredientsBlueprint->uuid);

    $ingredientCountResponse->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $manyIngredientsBlueprint->uuid)
        ->assertJsonPath('data.0.ingredient_count', 3)
        ->assertJsonPath('data.1.uuid', $fewIngredientsBlueprint->uuid)
        ->assertJsonPath('data.1.ingredient_count', 1);
});

it('includes web_url for unlocking missions on blueprint detail', function (): void {
    $blueprint = Blueprint::factory()->create();
    $mission = Mission::factory()->create();

    $outputItem = Item::factory()->create();
    ItemData::factory()->for($outputItem, 'item')->for($this->defaultVersion, 'gameVersion')->create();

    $blueprintData = BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'output_item_uuid' => $outputItem->uuid,
            'data' => ['tiers' => []],
        ]);

    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'title' => 'Eliminate Pirate Threat',
            'reward_scope' => 'Bounty Hunter',
        ]);

    $itemData = ItemData::factory()->for($this->defaultVersion, 'gameVersion')->create();

    $blueprintData->missions()->attach($missionData->id, [
        'pool_uuid' => fake()->uuid(),
        'item_data_id' => $itemData->id,
    ]);

    $blueprintData->update(['unlocking_missions_count' => 1]);

    $response = $this->getJson("/api/blueprints/{$blueprint->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.unlocking_missions_count', 1)
        ->assertJsonCount(1, 'data.unlocking_missions')
        ->assertJsonPath('data.unlocking_missions.0.title', 'Eliminate Pirate Threat')
        ->assertJsonPath('data.unlocking_missions.0.reward_scope', 'Bounty Hunter')
        ->assertJsonPath('data.unlocking_missions.0.web_url', route('web.missions.show', ['mission' => $mission->uuid]));
});

it('groups unlocking missions by chance on blueprint detail', function (): void {
    $blueprint = Blueprint::factory()->create();

    $outputItem = Item::factory()->create();
    ItemData::factory()->for($outputItem, 'item')->for($this->defaultVersion, 'gameVersion')->create();

    $blueprintData = BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'output_item_uuid' => $outputItem->uuid,
            'data' => ['tiers' => []],
        ]);

    $guaranteedMission = Mission::factory()->create();
    $guaranteedMissionData = MissionData::factory()
        ->for($guaranteedMission, 'mission')
        ->for($this->defaultVersion, 'gameVersion')
        ->create(['title' => 'Alpha Strike']);

    $probableMission = Mission::factory()->create();
    $probableMissionData = MissionData::factory()
        ->for($probableMission, 'mission')
        ->for($this->defaultVersion, 'gameVersion')
        ->create(['title' => 'Bravo Recovery']);

    $itemData = ItemData::factory()->for($this->defaultVersion, 'gameVersion')->create();

    $blueprintData->missions()->attach([
        $guaranteedMissionData->id => [
            'pool_uuid' => fake()->uuid(),
            'item_data_id' => $itemData->id,
            'chance' => 1.0,
        ],
        $probableMissionData->id => [
            'pool_uuid' => fake()->uuid(),
            'item_data_id' => $itemData->id,
            'chance' => 0.5,
        ],
    ]);

    $response = $this->getJson("/api/blueprints/{$blueprint->uuid}");

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data.unlocking_missions_grouped')
        ->assertJsonPath('data.unlocking_missions_grouped.0.label', 'Guaranteed')
        ->assertJsonPath('data.unlocking_missions_grouped.0.chance', 1)
        ->assertJsonPath('data.unlocking_missions_grouped.0.missions.0.title', 'Alpha Strike')
        ->assertJsonPath('data.unlocking_missions_grouped.1.label', '50% chance')
        ->assertJsonPath('data.unlocking_missions_grouped.1.chance', 0.5)
        ->assertJsonPath('data.unlocking_missions_grouped.1.missions.0.title', 'Bravo Recovery');
});
