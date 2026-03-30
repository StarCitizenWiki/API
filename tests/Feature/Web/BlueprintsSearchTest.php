<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
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

it('renders the blueprint search route with an empty state', function (): void {
    $response = $this->get(route('web.blueprints.search'));

    $response->assertOk()
        ->assertViewIs('blueprints.show')
        ->assertViewHas('mode', 'empty')
        ->assertViewHas('search', function (array $search): bool {
            return ($search['query'] ?? null) === ''
                && ($search['result_count'] ?? null) === 0
                && ($search['results'] ?? null) === []
                && ($search['api_endpoint'] ?? null) === route('blueprints.index', ['page' => ['size' => 5]], false);
        })
        ->assertSee('data-blueprint-search', false)
        ->assertSee('data-resource-filter-toggle', false)
        ->assertSee('data-blueprint-search-results-list', false)
        ->assertSee('data-blueprint-search-empty', false)
        ->assertSee(route('web.blueprints.search'))
        ->assertSee('<meta name="robots" content="noindex,follow">', false);

    $crawler = new Crawler($response->getContent());
    $blueprintsLink = $crawler->filterXPath('//a[@href="'.route('web.blueprints.index').'"]')->first();
    $searchLink = $crawler->filterXPath('//a[@href="'.route('web.blueprints.search').'"]')->first();

    expect($searchLink->attr('class') ?? '')->toContain('menu-active')
        ->and($blueprintsLink->attr('class') ?? '')->not->toContain('menu-active');
});

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
            'ingredient_resource_type_uuids' => [fake()->uuid()],
        ]);

    $requestedBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($requestedBlueprint, 'blueprint')
        ->for($this->requestedVersion, 'gameVersion')
        ->create([
            'key' => 'BP_REQUESTED',
            'output_name' => 'Requested Output',
            'output_class' => 'requested_output',
            'craft_time_seconds' => 240,
            'ingredient_resource_type_uuids' => [$resourceTypeUuid],
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
        ->assertViewHas('mode', 'empty')
        ->assertViewHas('search', function (array $search) use ($resourceTypeUuid): bool {
            return ($search['filters']['query'] ?? null) === 'Requested'
                && ($search['filters']['ingredient.uuid'] ?? null) === $resourceTypeUuid
                && ($search['result_count'] ?? null) === 1
                && ($search['results'][0]['output_name'] ?? null) === 'Requested Output';
        })
        ->assertSee('Requested Output')
        ->assertSee('Hephaestanite, Iron')
        ->assertDontSee('Default Output')
        ->assertSee(route('web.blueprints.show', [
            'blueprint' => $requestedBlueprint->uuid,
            'version' => $this->requestedVersion->code,
        ]));

    $crawler = new Crawler($response->getContent());
    $searchResultLink = $crawler
        ->filterXPath('//a[@data-blueprint-search-result-link and @data-blueprint-uuid="'.$requestedBlueprint->uuid.'"]')
        ->first();

    expect($searchResultLink->attr('href'))->toBe(route('web.blueprints.show', [
        'blueprint' => $requestedBlueprint->uuid,
        'version' => $this->requestedVersion->code,
        'filter' => [
            'ingredient.uuid' => $resourceTypeUuid,
        ],
    ]));
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
        ->assertViewHas('search', function (array $search): bool {
            return ($search['result_count'] ?? null) === 6
                && count($search['results'] ?? []) === 5;
        })
        ->assertSee('Limiter Output 1')
        ->assertSee('Limiter Output 5')
        ->assertDontSee('Limiter Output 6');
});
