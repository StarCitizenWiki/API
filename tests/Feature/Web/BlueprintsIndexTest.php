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
        ->assertViewIs('blueprints.index')
        ->assertViewHas('initialTableData', function (array $payload) use ($defaultBlueprint): bool {
            return ($payload['data'][0]['uuid'] ?? null) === $defaultBlueprint->uuid
                && ($payload['data'][0]['output_name'] ?? null) === 'FS-9 LMG'
                && ($payload['meta']['per_page'] ?? null) === 25
                && count($payload['data']) === 1;
        })
        ->assertViewHas('pageTitle', 'Blueprints')
        ->assertViewHas('headerFilterOptionsMap', function (array $map): bool {
            return ($map['is_available_by_default'] ?? null) === 'default';
        })
        ->assertViewHas('tableColumns', function (array $columns): bool {
            $fieldMap = collect($columns)->keyBy('field');

            return collect($columns)->pluck('field')->all() === [
                'output.name',
                'output.type',
                'output.class',
                'craft_time_seconds',
                'ingredient_count',
                'is_available_by_default',
                'uuid',
            ]
                && $fieldMap->get('output.name')['formatter'] === 'link'
                && $fieldMap->get('output.name')['formatterParams']['urlField'] === 'web_url'
                && $fieldMap->get('uuid')['title'] === 'API Url'
                && $fieldMap->get('uuid')['formatterParams']['label'] === 'API Url'
                && $fieldMap->get('uuid')['formatterParams']['urlField'] === 'link';
        })
        ->assertSee('Browse craftable blueprints for the selected game version.')
        ->assertSee('Search Blueprints')
        ->assertSee(route('web.blueprints.search'))
        ->assertSee(route('blueprints.index'))
        ->assertSee('Column source map');

    $crawler = new Crawler($response->getContent());
    $menuLink = $crawler->filterXPath('//a[.//span[normalize-space(.)="Blueprints"]]')->first();

    expect($menuLink->attr('href'))->toBe(route('web.blueprints.index'))
        ->and($menuLink->attr('class') ?? '')->toContain('menu-active');
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
        ->assertViewHas('initialTableData', function (array $payload) use ($requestedBlueprint): bool {
            return ($payload['data'][0]['uuid'] ?? null) === $requestedBlueprint->uuid
                && ($payload['data'][0]['game_version'] ?? null) === '4.0.0-PTU'
                && ($payload['meta']['per_page'] ?? null) === 25
                && count($payload['data']) === 1;
        })
        ->assertSee(route('web.blueprints.search', ['version' => $this->requestedVersion->code]))
        ->assertSee(route('blueprints.index', ['version' => $this->requestedVersion->code]));
});
