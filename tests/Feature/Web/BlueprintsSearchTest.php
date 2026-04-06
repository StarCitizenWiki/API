<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('renders the blueprint search route with an empty state', function (): void {
    $response = $this->get(route('web.blueprints.search'));

    $response->assertOk()
        ->assertViewIs('blueprints.show')
        ->assertViewHas('isEmptyMode', true)
        ->assertViewHas('pageTitle', 'Search Blueprints')
        ->assertSeeText('Find craftable items')
        ->assertSeeText('Search by output name or pick resource filters to load matching blueprints.')
        ->assertSee('data-testid="blueprints-search-heading"', false)
        ->assertSee('data-testid="blueprints-search-input"', false)
        ->assertSee('data-testid="blueprints-search-empty-state"', false)
        ->assertSee('data-testid="blueprints-search-menu-link"', false)
        ->assertSee('data-testid="blueprints-menu-link"', false)
        ->assertSee(route('web.blueprints.search'), false);

    $content = $response->getContent();
    $searchLinkClasses = attributeForTestId($content, 'blueprints-search-menu-link', 'class') ?? '';
    $blueprintsLinkClasses = attributeForTestId($content, 'blueprints-menu-link', 'class') ?? '';

    expect(attributeForTestId($content, 'blueprints-search-menu-link', 'href'))->toBe(route('web.blueprints.search'))
        ->and($searchLinkClasses)->toContain('menu-active')
        ->and($blueprintsLinkClasses)->not->toContain('menu-active');
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
        ->assertSee('Hephaestanite, Iron')
        ->assertDontSee('Default Output')
        ->assertSee('data-testid="blueprints-search-result-link-'.$requestedBlueprint->uuid.'"', false);

    expect(attributeForTestId($response->getContent(), 'blueprints-search-result-link-'.$requestedBlueprint->uuid, 'href'))->toBe(
        route('web.blueprints.show', [
            'blueprint' => $requestedBlueprint->uuid,
            'version' => $this->requestedVersion->code,
            'filter' => [
                'ingredient.uuid' => $resourceTypeUuid,
            ],
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

    expect(substr_count($response->getContent(), 'data-testid="blueprints-search-result-link-'))->toBe(5);
});
