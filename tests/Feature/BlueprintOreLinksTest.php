<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->defaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

describe('ingredients', function (): void {
    it('links to the ore commodity when a raw version exists', function (): void {
        $ore = Commodity::factory()->create([
            'name' => 'Aluminum (Ore)',
            'key' => 'Aluminum_Ore',
        ]);

        $refined = Commodity::factory()->create([
            'name' => 'Aluminum',
            'key' => 'Aluminum',
            'refined_version_uuid' => null,
            'refined_version_name' => null,
        ]);

        $ore->update(['refined_version_uuid' => $refined->uuid]);

        $blueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->withIngredients($refined)
            ->create([
                'key' => 'BP_ORE_LINK',
                'output_name' => 'Test Output',
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
                                        'uuid' => $refined->uuid,
                                        'name' => 'Aluminum',
                                        'quantity_scu' => 0.06,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/blueprints/{$blueprint->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.ingredients.0.resource_type_uuid', $refined->uuid)
            ->assertJsonPath('data.ingredients.0.web_url', route('web.commodities.show', ['identifier' => $ore->uuid]))
            ->assertJsonPath('data.ingredients.0.link', route('commodities.show', ['commodity' => $ore->uuid]));
    });

    it('links to the commodity itself when no raw version exists', function (): void {
        $commodity = Commodity::factory()->create([
            'name' => 'Quantanium',
            'key' => 'Quantanium',
        ]);

        $blueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->withIngredients($commodity)
            ->create([
                'key' => 'BP_NO_ORE',
                'output_name' => 'Test Output',
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
                                        'uuid' => $commodity->uuid,
                                        'name' => 'Quantanium',
                                        'quantity_scu' => 0.06,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/blueprints/{$blueprint->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.ingredients.0.resource_type_uuid', $commodity->uuid)
            ->assertJsonPath('data.ingredients.0.web_url', route('web.commodities.show', ['identifier' => $commodity->uuid]))
            ->assertJsonPath('data.ingredients.0.link', route('commodities.show', ['commodity' => $commodity->uuid]));
    });
});

describe('requirement_groups', function (): void {
    it('enriches resource children with ore_uuid when a raw version exists', function (): void {
        $ore = Commodity::factory()->create([
            'name' => 'Aluminum (Ore)',
            'key' => 'Aluminum_Ore',
        ]);

        $refined = Commodity::factory()->create([
            'name' => 'Aluminum',
            'key' => 'Aluminum',
        ]);

        $ore->update(['refined_version_uuid' => $refined->uuid]);

        $blueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->withIngredients($refined)
            ->create([
                'key' => 'BP_REQGROUP_ORE',
                'output_name' => 'Test Output',
                'data' => [
                    'tiers' => [
                        [
                            'tier_index' => 0,
                            'craft_time_seconds' => 10,
                            'requirements' => [
                                'kind' => 'root',
                                'children' => [
                                    [
                                        'kind' => 'group',
                                        'key' => 'MATERIAL',
                                        'name' => 'Material',
                                        'required_count' => 1,
                                        'modifiers' => [],
                                        'children' => [
                                            [
                                                'kind' => 'resource',
                                                'uuid' => $refined->uuid,
                                                'name' => 'Aluminum',
                                                'quantity_scu' => 0.06,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/blueprints/{$blueprint->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.requirement_groups.0.children.0.ore_uuid', $ore->uuid)
            ->assertJsonPath('data.requirement_groups.0.children.0.uuid', $refined->uuid);
    });

    it('omits ore_uuid when no raw version exists', function (): void {
        $commodity = Commodity::factory()->create([
            'name' => 'Diamond',
            'key' => 'Diamond',
        ]);

        $blueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->withIngredients($commodity)
            ->create([
                'key' => 'BP_REQGROUP_NO_ORE',
                'output_name' => 'Test Output',
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
                                        'uuid' => $commodity->uuid,
                                        'name' => 'Diamond',
                                        'quantity_scu' => 0.03,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/blueprints/{$blueprint->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.requirement_groups.0.children.0.uuid', $commodity->uuid)
            ->assertJsonMissingPath('data.requirement_groups.0.children.0.ore_uuid');
    });
});

describe('aspects', function (): void {
    it('links aspect inputs to the ore commodity when a raw version exists', function (): void {
        $ore = Commodity::factory()->create([
            'name' => 'Aluminum (Ore)',
            'key' => 'Aluminum_Ore',
        ]);

        $refined = Commodity::factory()->create([
            'name' => 'Aluminum',
            'key' => 'Aluminum',
        ]);

        $ore->update(['refined_version_uuid' => $refined->uuid]);

        $blueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->withIngredients($refined)
            ->create([
                'key' => 'BP_ASPECT_ORE',
                'output_name' => 'Test Output',
                'data' => [
                    'tiers' => [
                        [
                            'tier_index' => 0,
                            'craft_time_seconds' => 10,
                            'requirements' => [
                                'kind' => 'root',
                                'children' => [
                                    [
                                        'kind' => 'group',
                                        'key' => 'MATERIAL',
                                        'name' => 'Material',
                                        'required_count' => 1,
                                        'modifiers' => [],
                                        'children' => [
                                            [
                                                'kind' => 'resource',
                                                'uuid' => $refined->uuid,
                                                'name' => 'Aluminum',
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

        $this->getJson("/api/blueprints/{$blueprint->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.aspects.aspects.0.input.web_url', route('web.commodities.show', ['identifier' => $ore->uuid]));
    });

    it('links aspect inputs to the commodity itself when no raw version exists', function (): void {
        $commodity = Commodity::factory()->create([
            'name' => 'Diamond',
            'key' => 'Diamond',
        ]);

        $blueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->withIngredients($commodity)
            ->create([
                'key' => 'BP_ASPECT_NO_ORE',
                'output_name' => 'Test Output',
                'data' => [
                    'tiers' => [
                        [
                            'tier_index' => 0,
                            'craft_time_seconds' => 10,
                            'requirements' => [
                                'kind' => 'root',
                                'children' => [
                                    [
                                        'kind' => 'group',
                                        'key' => 'MATERIAL',
                                        'name' => 'Material',
                                        'required_count' => 1,
                                        'modifiers' => [],
                                        'children' => [
                                            [
                                                'kind' => 'resource',
                                                'uuid' => $commodity->uuid,
                                                'name' => 'Diamond',
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
            ]);

        $this->getJson("/api/blueprints/{$blueprint->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.aspects.aspects.0.input.web_url', route('web.commodities.show', ['identifier' => $commodity->uuid]));
    });
});
