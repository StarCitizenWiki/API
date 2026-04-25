<?php

declare(strict_types=1);

use App\Support\Blueprints\BlueprintShowViewData;

it('builds grouped blueprint detail view data', function (): void {
    app('request')->query->set('version', '4.0.0-PTU');

    $builder = app(BlueprintShowViewData::class);
    $blueprintUuid = fake()->uuid();
    $outputItemUuid = fake()->uuid();
    $laraniteUuid = fake()->uuid();
    $aslariteUuid = fake()->uuid();
    $stileronUuid = fake()->uuid();

    $page = $builder->build(
        mode: 'detail',
        blueprint: [
            'uuid' => $blueprintUuid,
            'key' => 'BP_CRAFT_vgl_utility_light_legs_01_01_01',
            'output_name' => 'Chiron Legs',
            'output_class' => 'utility_light_legs',
            'craft_time_seconds' => 180,
            'ingredient_count' => 3,
            'ingredients' => [
                [
                    'name' => 'Laranite',
                    'resource_type_uuid' => $laraniteUuid,
                ],
                [
                    'name' => 'Aslarite',
                    'resource_type_uuid' => $aslariteUuid,
                ],
                [
                    'name' => 'Stileron',
                    'resource_type_uuid' => $stileronUuid,
                ],
            ],
            'web_url' => route('web.blueprints.show', [
                'blueprint' => $blueprintUuid,
                'version' => '4.0.0-PTU',
            ]),
            'is_available_by_default' => false,
            'output' => [
                'uuid' => $outputItemUuid,
                'type' => 'Armor',
                'subtype' => 'Legs',
                'grade' => '1',
                'item_web_url' => route('web.items.show', [
                    'item' => $outputItemUuid,
                    'version' => '4.0.0-PTU',
                ]),
            ],
            'summary_properties' => [
                [
                    'property_key' => 'armor_temperaturemax',
                    'label' => 'Armor Temperature Max',
                    'better_when' => 'higher',
                ],
                [
                    'property_key' => 'armor_damagemitigation',
                    'label' => 'Armor Damage Mitigation',
                    'better_when' => 'higher',
                ],
            ],
            'requirement_groups' => [
                [
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
                                    'better_when' => 'higher',
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
                                    'better_when' => 'higher',
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
        search: [
            'filters' => [],
            'results' => [],
            'result_count' => 0,
        ],
        pageTitle: 'Chiron &amp; Legs',
    );

    $initialResult = $page['initialSearchResults'][0];

    expect($page['mode'])->toBe('detail')
        ->and($page['isEmptyMode'])->toBeFalse()
        ->and($page['pageTitleDecoded'])->toBe('Chiron & Legs')
        ->and($page['blueprintName'])->toBe('Chiron Legs')
        ->and($page['craftTimeLabel'])->toBe('3 minutes')
        ->and($page['resolvedVersionCode'])->toBe('4.0.0-PTU')
        ->and($page['outputItemWebUrl'])->toBe(route('web.items.show', [
            'item' => $outputItemUuid,
            'version' => '4.0.0-PTU',
        ]))
        ->and($page['hasSearchFilters'])->toBeFalse()
        ->and($page['renderSearchResultCount'])->toBe(1)
        ->and($initialResult['uuid'])->toBe($blueprintUuid)
        ->and(collect($initialResult['ingredients'])->pluck('resource_type_uuid')->all())->toBe([
            $laraniteUuid,
            $aslariteUuid,
            $stileronUuid,
        ])
        ->and($page['summaryPropertyList'])->toHaveCount(2)
        ->and($page['hasInteractiveAspects'])->toBeTrue();
});

it('builds interactive aspect state from grouped requirements', function (): void {
    app('request')->query->set('version', '4.0.0-PTU');

    $page = app(BlueprintShowViewData::class)->build(
        mode: 'detail',
        blueprint: [
            'requirement_groups' => [
                [
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
                                    'uuid' => fake()->uuid(),
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
                                    'better_when' => 'higher',
                                ],
                            ],
                            'children' => [
                                [
                                    'kind' => 'resource',
                                    'uuid' => fake()->uuid(),
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
                            'children' => [
                                [
                                    'kind' => 'resource',
                                    'uuid' => fake()->uuid(),
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
        search: [
            'filters' => [],
            'results' => [],
            'result_count' => 0,
        ],
        pageTitle: 'Chiron Legs',
    );

    expect($page['hasInteractiveAspects'])->toBeTrue()
        ->and($page['aspectGroups'])->toHaveCount(1)
        ->and($page['aspectGroups'][0]['is_choice_group'])->toBeTrue()
        ->and($page['aspectGroups'][0]['selected_count'])->toBe(2)
        ->and($page['aspectGroups'][0]['display_name'])->toBeNull()
        ->and($page['aspects'])->toHaveCount(3)
        ->and($page['aspects'][0]['is_selected'])->toBeTrue()
        ->and($page['aspects'][1]['is_selected'])->toBeTrue()
        ->and($page['aspects'][2]['is_selected'])->toBeFalse();
});

it('builds empty blueprint search view data', function (): void {
    app('request')->query->set('version', '4.0.0-PTU');

    $builder = app(BlueprintShowViewData::class);

    $page = $builder->build(
        mode: 'empty',
        blueprint: [],
        search: [
            'filters' => ['query' => 'legs'],
            'results' => [],
            'result_count' => 0,
        ],
        pageTitle: 'Search Blueprints',
    );

    expect($page['isEmptyMode'])->toBeTrue()
        ->and($page['pageTitleDecoded'])->toBe('Search Blueprints')
        ->and($page['rawBlueprintJson'])->toBe('{}')
        ->and($page['searchQuery'])->toBe('legs')
        ->and($page['renderSearchResultCount'])->toBe(0)
        ->and($page['resolvedVersionCode'])->toBe('4.0.0-PTU')
        ->and($page['hasInteractiveAspects'])->toBeFalse();
});

it('prefers the explicit query version over the stored session version', function (): void {
    session(['game_version_code' => '4.0.0-LIVE']);
    app('request')->query->set('version', '4.0.0-PTU');

    $page = app(BlueprintShowViewData::class)->build(
        mode: 'empty',
        blueprint: [],
        search: [
            'filters' => [],
            'results' => [],
            'result_count' => 0,
        ],
        pageTitle: 'Search Blueprints',
    );

    expect($page['resolvedVersionCode'])->toBe('4.0.0-PTU');
});

it('escapes embedded client payload json for script tags', function (): void {
    app('request')->query->set('version', '4.0.0-PTU');

    $unsafeBlueprintName = 'Unsafe </script><script>alert("x")</script>';
    $page = app(BlueprintShowViewData::class)->build(
        mode: 'detail',
        blueprint: [
            'uuid' => $blueprintUuid = fake()->uuid(),
            'output_name' => $unsafeBlueprintName,
            'output' => [
                'name' => $unsafeBlueprintName,
                'class' => 'unsafe_output',
            ],
            'requirement_groups' => [],
        ],
        search: [
            'filters' => [],
            'results' => [],
            'result_count' => 0,
        ],
        pageTitle: $unsafeBlueprintName,
    );

    $clientPayload = json_decode($page['clientPayload'], true, 512, JSON_THROW_ON_ERROR);

    expect($page['clientPayload'])->not->toContain('</script>')
        ->and($clientPayload['search']['currentBlueprintUuid'])->toBe($blueprintUuid)
        ->and($clientPayload['search']['initialResults'][0]['output_name'])->toBe($unsafeBlueprintName);
});

it('passes web_url through unlocking missions into grouped view data', function (): void {
    app('request')->query->set('version', '4.0.0-PTU');

    $missionUuid = fake()->uuid();

    $page = app(BlueprintShowViewData::class)->build(
        mode: 'detail',
        blueprint: [
            'uuid' => fake()->uuid(),
            'output_name' => 'Test Blueprint',
            'output' => [
                'name' => 'Test Blueprint',
                'class' => 'test_output',
            ],
            'requirement_groups' => [],
            'unlocking_missions' => [
                [
                    'title' => 'Eliminate Threat',
                    'debug_name' => 'elim_threat',
                    'reward_scope' => 'Bounty Hunter',
                    'chance' => 0.5,
                    'web_url' => route('web.missions.show', ['mission' => $missionUuid]),
                ],
            ],
        ],
        search: [
            'filters' => [],
            'results' => [],
            'result_count' => 0,
        ],
        pageTitle: 'Test Blueprint',
    );

    expect($page['unlockingMissions'])->toHaveCount(1)
        ->and($page['unlockingMissions'][0]['label'])->toBe('50% chance')
        ->and($page['unlockingMissions'][0]['missions'])->toHaveCount(1)
        ->and($page['unlockingMissions'][0]['missions'][0]['title'])->toBe('Eliminate Threat')
        ->and($page['unlockingMissions'][0]['missions'][0]['web_url'])->toBe(route('web.missions.show', ['mission' => $missionUuid]));
});
