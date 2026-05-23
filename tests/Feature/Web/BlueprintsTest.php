<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;

if (! function_exists('attributeForTestId')) {
    function attributeForTestId(string $content, string $testId, string $attribute): ?string
    {
        preg_match(
            '/<[^>]*data-testid="'.preg_quote($testId, '/').'"[^>]*>/i',
            $content,
            $matches
        );

        expect($matches[0] ?? null)->not->toBeNull();

        preg_match(
            '/\b'.preg_quote($attribute, '/').'="([^"]*)"/i',
            $matches[0],
            $attributeMatches
        );

        return isset($attributeMatches[1]) ? html_entity_decode($attributeMatches[1], ENT_QUOTES) : null;
    }
}

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

describe('index', function (): void {
    it('renders the blueprints index route', function (): void {
        $defaultBlueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($defaultBlueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->create([
                'key' => 'BP_CRAFT_FS9',
                'output_name' => 'FS-9 LMG',
                'output_class' => 'behr_lmg_ballistic_01',
                'craft_time_seconds' => 240,
                'is_available_by_default' => true,
                'data' => [
                    'output' => [
                        'uuid' => fake()->uuid(),
                        'name' => 'FS-9 LMG',
                        'class' => 'behr_lmg_ballistic_01',
                    ],
                    'tiers' => [
                        [
                            'craft_time_seconds' => 240,
                            'requirements' => [
                                'kind' => 'root',
                                'children' => [
                                    ['kind' => 'resource', 'uuid' => fake()->uuid(), 'name' => 'Iron', 'quantity_scu' => 2.0],
                                    ['kind' => 'resource', 'uuid' => fake()->uuid(), 'name' => 'Titanium', 'quantity_scu' => 1.0],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        BlueprintData::factory()
            ->for(Blueprint::factory(), 'blueprint')
            ->for($this->requestedVersion, 'gameVersion')
            ->create([
                'key' => 'BP_CRAFT_P4',
                'output_name' => 'P4-AR',
                'output_class' => 'klwe_ar_ballistic_01',
            ]);

        $response = $this->get(route('web.blueprints.index'));

        $response->assertOk()
            ->assertViewHas('pageTitle', 'Blueprints')
            ->assertViewHas('pageSize', 25)
            ->assertViewHas('initialTableData', function (array $tableData): bool {
                return data_get($tableData, 'data.0.output_name') === 'FS-9 LMG'
                    && ! collect(data_get($tableData, 'data', []))
                        ->pluck('output_name')
                        ->contains('P4-AR');
            })
            ->assertSeeText('Blueprints')
            ->assertSee(route('web.blueprints.search'), false)
            ->assertSee(route('blueprints.index'), false);
    });

    it('renders blueprints for the requested version on the web route', function (): void {
        BlueprintData::factory()
            ->for(Blueprint::factory(), 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->create([
                'key' => 'BP_CRAFT_DEFAULT',
                'output_name' => 'Default Output',
                'output_class' => 'default_output',
            ]);

        $requestedBlueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($requestedBlueprint, 'blueprint')
            ->for($this->requestedVersion, 'gameVersion')
            ->create([
                'key' => 'BP_CRAFT_REQUESTED',
                'output_name' => 'Requested Output',
                'output_class' => 'requested_output',
            ]);

        $response = $this
            ->withSession(['game_version_code' => $this->defaultVersion->code])
            ->get(route('web.blueprints.index', ['version' => $this->requestedVersion->code]));

        $response->assertOk()
            ->assertViewHas('pageTitle', 'Blueprints')
            ->assertViewHas('initialTableData', function (array $tableData): bool {
                return data_get($tableData, 'data.0.output_name') === 'Requested Output'
                    && ! collect(data_get($tableData, 'data', []))
                        ->pluck('output_name')
                        ->contains('Default Output');
            })
            ->assertSee(route('web.blueprints.search', ['version' => $this->requestedVersion->code]), false)
            ->assertSee(route('blueprints.index', ['version' => $this->requestedVersion->code]), false);
    });
});

describe('search', function (): void {
    it('renders matching blueprint search results without keeping the search query in result links', function (): void {
        $resourceTypeUuid = fake()->uuid();
        $hephaestaniteUuid = fake()->uuid();
        $ironUuid = fake()->uuid();

        BlueprintData::factory()
            ->for(Blueprint::factory(), 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->create([
                'key' => 'BP_DEFAULT',
                'output_name' => 'Default Output',
                'output_class' => 'default_output',
                'craft_time_seconds' => 180,
            ]);

        $requestedBlueprint = Blueprint::factory()->create();

        $resourceType = Commodity::factory()->create(['uuid' => $resourceTypeUuid]);

        BlueprintData::factory()
            ->for($requestedBlueprint, 'blueprint')
            ->for($this->requestedVersion, 'gameVersion')
            ->withIngredients($resourceType)
            ->create([
                'key' => 'BP_REQUESTED',
                'output_name' => 'Requested Output',
                'output_class' => 'requested_output',
                'craft_time_seconds' => 240,
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
                                                'uuid' => $hephaestaniteUuid,
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
                                                'uuid' => $ironUuid,
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

        $response = $this->get(route('web.blueprints.search', [
            'version' => $this->requestedVersion->code,
            'filter' => [
                'query' => 'Requested',
                'ingredient.uuid' => $resourceTypeUuid,
            ],
        ]));

        $response->assertOk()
            ->assertViewIs('blueprints.show')
            ->assertViewHas('isEmptyMode', true)
            ->assertSee('Requested Output')
            ->assertDontSee('Default Output');

        $initialResults = $response->viewData('initialSearchResults');
        $matchedResult = collect($initialResults)->first(fn (array $r): bool => data_get($r, 'uuid') === $requestedBlueprint->uuid);

        expect($matchedResult)->not->toBeNull()
            ->and(data_get($matchedResult, 'output_name'))->toBe('Requested Output')
            ->and(collect(data_get($matchedResult, 'ingredients', []))->pluck('name')->filter()->all())->toContain('Hephaestanite', 'Iron')
            ->and(data_get($matchedResult, 'web_url'))->toBe(
                route('web.blueprints.show', [
                    'blueprint' => $requestedBlueprint->slug ?? $requestedBlueprint->uuid,
                    'version' => $this->requestedVersion->code,
                ])
            );
    });

    it('limits rendered blueprint search results to five records', function (): void {
        foreach (range(1, 6) as $index) {
            BlueprintData::factory()
                ->for(Blueprint::factory(), 'blueprint')
                ->for($this->defaultVersion, 'gameVersion')
                ->create([
                    'key' => 'BP_LIMIT_'.$index,
                    'output_name' => 'Limiter Output '.$index,
                    'output_class' => 'limiter_output_'.$index,
                    'craft_time_seconds' => 60 + $index,
                ]);
        }

        $response = $this->get(route('web.blueprints.search', [
            'filter' => ['query' => 'Limiter Output'],
        ]));

        $response->assertOk()
            ->assertViewIs('blueprints.show')
            ->assertViewHas('isEmptyMode', true)
            ->assertSee('Limiter Output 1')
            ->assertSee('Limiter Output 5')
            ->assertDontSee('Limiter Output 6');

        $initialResults = $response->viewData('initialSearchResults');
        expect(count($initialResults))->toBe(5)
            ->and(collect($initialResults)->pluck('output_name')->sort()->values()->all())->toBe([
                'Limiter Output 1',
                'Limiter Output 2',
                'Limiter Output 3',
                'Limiter Output 4',
                'Limiter Output 5',
            ]);
    });
});

describe('show', function (): void {
    it('renders the blueprint show view with normalized api data', function (): void {
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
                'craft_time_seconds' => 240,
                'is_available_by_default' => false,
                'data' => [
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
            ->assertViewHas('isEmptyMode', false)
            ->assertViewHas('mode', 'detail')
            ->assertViewHas('pageTitle', 'Detailed Output')
            ->assertViewHas('seo', function (array $seo) use ($blueprint): bool {
                return $seo['canonicalUrl'] === route('web.blueprints.show', ['blueprint' => $blueprint->slug ?? $blueprint->uuid])
                    && $seo['title'] === 'Detailed Output Blueprint'
                    && str_contains($seo['metaDescription'], 'Detailed Output blueprint')
                    && str_contains($seo['metaDescription'], 'type WeaponPersonal')
                    && str_contains($seo['metaDescription'], 'craft time 240 seconds');
            })
            ->assertViewHas('outputItemWebUrl', route('web.items.show', ['item' => $outputItemUuid]))
            ->assertViewHas('initialSearchResults', function (array $results) use ($blueprint): bool {
                return data_get($results, '0.uuid') === $blueprint->uuid
                    && data_get($results, '0.web_url') === route('web.blueprints.show', ['blueprint' => $blueprint->slug ?? $blueprint->uuid]);
            });

        $response->assertSeeText('Detailed Output')
            ->assertSeeText('4 minutes')
            ->assertSeeText('Q500')
            ->assertSeeText('4 items')
            ->assertSeeText('Frame')
            ->assertSeeText('Reinforced Frame')
            ->assertSeeText('Lindinium')
            ->assertSee(route('web.items.show', ['item' => $requiredItemUuid]), false);
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
            ->assertSeeText('Lenses')
            ->assertSeeText('Dolivine');
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
            ->assertViewHas('seo', function (array $seo) use ($blueprint): bool {
                return $seo['canonicalUrl'] === route('web.blueprints.show', [
                    'blueprint' => $blueprint->slug ?? $blueprint->uuid,
                    'version' => $this->requestedVersion->code,
                ]);
            })
            ->assertViewHas('outputItemWebUrl', route('web.items.show', [
                'item' => $requestedOutputItemUuid,
                'version' => $this->requestedVersion->code,
            ]))
            ->assertViewHas('initialSearchResults', function (array $results) use ($blueprint): bool {
                return data_get($results, '0.web_url') === route('web.blueprints.show', [
                    'blueprint' => $blueprint->slug ?? $blueprint->uuid,
                    'version' => $this->requestedVersion->code,
                ]);
            })
            ->assertViewHas('resolvedVersionCode', $this->requestedVersion->code)
            ->assertSeeText('Requested Output')
            ->assertDontSeeText('Default Output');
    });

    it('keeps the resource filter without forcing the blueprint picker open', function (): void {
        $resourceTypeUuid = fake()->uuid();
        $blueprint = Blueprint::factory()->create();
        $requiredItemUuid = fake()->uuid();
        $resourceType = Commodity::factory()->create(['uuid' => $resourceTypeUuid]);

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
            ->withIngredients($resourceType)
            ->create([
                'output_name' => 'Requested Output',
                'output_item_uuid' => fake()->uuid(),
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
            ->assertViewHas('searchQuery', '')
            ->assertViewHas('selectedIngredientResourceTypeUuids', [$resourceTypeUuid])
            ->assertViewHas('search', function (array $search) use ($resourceTypeUuid): bool {
                $filters = is_array($search['filters'] ?? null) ? $search['filters'] : [];

                return ($filters['ingredient.uuid'] ?? null) === $resourceTypeUuid;
            })
            ->assertViewHas('initialSearchResults', function (array $results) use ($blueprint): bool {
                return data_get($results, '0.uuid') === $blueprint->uuid;
            })
            ->assertSeeText('Requested Output');
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

    it('resolves a blueprint by slug', function (): void {
        $blueprint = Blueprint::factory()->create([
            'slug' => 'omega-output',
        ]);

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->create([
                'output_name' => 'Omega Output',
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

        $response = $this->get(route('web.blueprints.show', ['blueprint' => 'omega-output']));

        $response->assertOk()
            ->assertViewHas('isEmptyMode', false)
            ->assertViewHas('mode', 'detail')
            ->assertViewHas('pageTitle', 'Omega Output');
    });

    it('shows no missions available message when blueprint requires unlock but has no missions', function (): void {
        $blueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->create([
                'output_name' => 'Locked Output',
                'is_available_by_default' => false,
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

        $response = $this->get(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]));

        $response->assertOk()
            ->assertSeeText('No missions available');
    });

    it('does not show no missions available message when blueprint is available by default', function (): void {
        $blueprint = Blueprint::factory()->create();

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->create([
                'output_name' => 'Default Output',
                'is_available_by_default' => true,
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

        $response = $this->get(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]));

        $response->assertOk()
            ->assertDontSeeText('No missions available');
    });

    it('resolves a blueprint by uuid when no slug exists', function (): void {
        $blueprint = Blueprint::factory()->create(['slug' => null]);

        BlueprintData::factory()
            ->for($blueprint, 'blueprint')
            ->for($this->defaultVersion, 'gameVersion')
            ->create([
                'output_name' => 'No Slug Output',
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

        $response = $this->get(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]));

        $response->assertOk()
            ->assertViewHas('mode', 'detail')
            ->assertViewHas('pageTitle', 'No Slug Output');
    });
});
