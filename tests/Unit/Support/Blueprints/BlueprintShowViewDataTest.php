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
            'availability' => [
                'default' => false,
                'reward_pools' => [
                    ['key' => 'BP_MISSIONREWARD_ALPHA'],
                ],
            ],
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
        pageTitle: 'Chiron Legs',
    );

    $clientPayload = json_decode($page['clientPayload'], true, 512, JSON_THROW_ON_ERROR);

    expect($page['mode'])->toBe('detail')
        ->and($page['isEmptyMode'])->toBeFalse()
        ->and($page['blueprintName'])->toBe('Chiron Legs')
        ->and($page['craftTimeLabel'])->toBe('3 minutes')
        ->and($page['unlockSources'])->toBe([
            [
                'label' => 'A L P H A',
                'type' => 'Mission reward',
                'key' => 'BP_MISSIONREWARD_ALPHA',
                'uuid' => null,
            ],
        ])
        ->and($page['hasSearchFilters'])->toBeFalse()
        ->and($page['renderSearchResultCount'])->toBe(1)
        ->and($page['initialSearchResults'][0]['uuid'])->toBe($blueprintUuid)
        ->and($page['initialSearchResults'][0]['ingredients'])->toBe([
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
        ])
        ->and($page['hasInteractiveAspects'])->toBeTrue()
        ->and($page['aspectGroups'])->toHaveCount(1)
        ->and($page['aspectGroups'][0]['is_choice_group'])->toBeTrue()
        ->and($page['aspectGroups'][0]['selected_count'])->toBe(2)
        ->and($page['aspectGroups'][0]['display_name'])->toBeNull()
        ->and($page['aspects'])->toHaveCount(3)
        ->and($page['aspects'][0]['is_selected'])->toBeTrue()
        ->and($page['aspects'][1]['is_selected'])->toBeTrue()
        ->and($page['aspects'][2]['is_selected'])->toBeFalse()
        ->and($clientPayload['search']['currentBlueprintUuid'])->toBe($blueprintUuid)
        ->and($clientPayload['detail']['hasInteractiveAspects'])->toBeTrue()
        ->and($clientPayload['detail']['aspects'])->toHaveCount(3);
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

    $clientPayload = json_decode($page['clientPayload'], true, 512, JSON_THROW_ON_ERROR);

    expect($page['isEmptyMode'])->toBeTrue()
        ->and($page['canonicalUrl'])->toBe(route('web.blueprints.search', ['version' => '4.0.0-PTU']))
        ->and($page['metaTitle'])->toBe('Search Blueprints - Star Citizen')
        ->and($page['renderSearchResultCount'])->toBe(0)
        ->and($clientPayload['detail'])->toBeNull()
        ->and($clientPayload['search']['version'])->toBe('4.0.0-PTU');
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

    $clientPayload = json_decode($page['clientPayload'], true, 512, JSON_THROW_ON_ERROR);

    expect($page['resolvedVersionCode'])->toBe('4.0.0-PTU')
        ->and($page['canonicalUrl'])->toBe(route('web.blueprints.search', ['version' => '4.0.0-PTU']))
        ->and($clientPayload['search']['version'])->toBe('4.0.0-PTU');
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
