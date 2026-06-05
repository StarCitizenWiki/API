<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

if (! function_exists('elementMarkupByTestId')) {
    function elementMarkupByTestId(string $content, string $testId): string
    {
        preg_match(
            '/<[^>]*data-testid="'.preg_quote($testId, '/').'"[^>]*>/i',
            $content,
            $matches
        );

        expect($matches[0] ?? null)->not->toBeNull();

        return $matches[0];
    }
}

if (! function_exists('attributeForTestId')) {
    function attributeForTestId(string $content, string $testId, string $attribute): ?string
    {
        $markup = elementMarkupByTestId($content, $testId);

        preg_match(
            '/\b'.preg_quote($attribute, '/').'="([^"]*)"/i',
            $markup,
            $matches
        );

        return isset($matches[1]) ? html_entity_decode($matches[1], ENT_QUOTES) : null;
    }
}

if (! function_exists('tabulatorConfigPayloadByTestId')) {
    function tabulatorConfigPayloadByTestId(string $content, string $testId): array
    {
        preg_match(
            '/<script type="application\/json" id="[^"]+-config" data-testid="'.preg_quote($testId, '/').'">(.*?)<\/script>/s',
            $content,
            $matches
        );

        expect($matches[1] ?? null)->not->toBeNull();

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true, 512, JSON_THROW_ON_ERROR);
    }
}

it('filters items by type on the web route', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $widget = Item::factory()->create();
    ItemData::factory()
        ->for($widget)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Widget One',
            'type' => 'Widget',
            'class_name' => 'widget_one',
            'classification' => 'Test',
            'data' => [],
        ]);

    $gadget = Item::factory()->create();
    ItemData::factory()
        ->for($gadget)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Gadget One',
            'type' => 'Gadget',
            'class_name' => 'gadget_one',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->get(route('web.items.index', ['filter' => ['type' => 'Widget']]));

    $response->assertOk()
        ->assertViewIs('items.index')
        ->assertViewHas('pageTitle', 'Widget Items')
        ->assertViewHas('endpointFilters', ['type' => 'Widget'])
        ->assertViewHas('initialFilters', [
            ['field' => 'type', 'value' => 'Widget'],
        ])
        ->assertSee('data-testid="items-index-heading"', false)
        ->assertSeeText('Widget Items')
        ->assertSee('Widget One')
        ->assertDontSee('Gadget One');
});

it('filters items by category on the web route', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Category Manufacturer',
        'code' => 'CAT',
    ]);

    $foodItem = Item::factory()->create();
    ItemData::factory()
        ->for($foodItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Trail Mix',
            'type' => 'Food',
            'class_name' => 'trail_mix',
            'classification' => 'Test',
            'data' => [],
        ]);

    $weaponItem = Item::factory()->create();
    ItemData::factory()
        ->for($weaponItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Pulse Pistol',
            'type' => 'WeaponPersonal',
            'class_name' => 'pulse_pistol',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->get(route('web.items.index', ['filter' => ['category' => 'food']]));

    $response->assertOk()
        ->assertViewIs('items.index')
        ->assertViewHas('pageTitle', 'Food & Drinks')
        ->assertViewHas('endpointFilters', ['category' => 'food'])
        ->assertViewHas('initialFilters', [])
        ->assertSee('data-testid="items-index-heading"', false)
        ->assertSeeText('Food & Drinks')
        ->assertSee('Trail Mix')
        ->assertDontSee('Pulse Pistol');

    $config = tabulatorConfigPayloadByTestId($response->getContent(), 'tabulator-config-items-table');

    expect($config['filterOptionsEndpoint'])->toBe(route('items.filters', [
        'filter' => ['category' => 'food'],
    ]));
});

it('activates vehicle items menu for vehicle type filters', function (): void {
    $response = $this->get(route('web.items.index', ['filter' => ['type' => 'PowerPlant']]));

    $response->assertOk()
        ->assertViewIs('items.index')
        ->assertViewHas('pageTitle', 'Power Plants')
        ->assertViewHas('endpointFilters', ['type' => 'PowerPlant'])
        ->assertViewHas('initialFilters', [
            ['field' => 'type', 'value' => 'PowerPlant'],
        ])
        ->assertSee('data-testid="items-menu-vehicle-items"', false)
        ->assertSee('data-testid="items-menu-fps-items"', false)
        ->assertSee('data-testid="items-menu-all-items"', false);

    $content = $response->getContent();
    $vehicleMenuClasses = attributeForTestId($content, 'items-menu-vehicle-items', 'class');
    $fpsMenuClasses = attributeForTestId($content, 'items-menu-fps-items', 'class');
    $allItemsMenuClasses = attributeForTestId($content, 'items-menu-all-items', 'class') ?? '';

    expect($vehicleMenuClasses)->toContain('active')
        ->and($fpsMenuClasses)->toContain('menu-item')
        ->and($fpsMenuClasses)->not->toContain('active')
        ->and($allItemsMenuClasses)->not->toContain('active');
});

it('renders breadcrumbs for item filters', function (): void {
    $response = $this->get(route('web.items.index', [
        'version' => '4.1.0-LIVE',
        'filter' => [
            'type' => 'weapon',
            'sub_type' => 'rail_gun',
            'manufacturer.name' => 'aegis_dynamics',
        ],
    ]));

    $response->assertOk()
        ->assertViewIs('items.index')
        ->assertSee('data-testid="item-breadcrumbs"', false)
        ->assertSee('data-testid="item-breadcrumbs-all-link"', false)
        ->assertSee('data-testid="item-breadcrumb-link-1"', false)
        ->assertSee('data-testid="item-breadcrumb-link-2"', false)
        ->assertSee('data-testid="item-breadcrumb-link-3"', false)
        ->assertSeeText('All Items')
        ->assertSeeText('Weapon')
        ->assertSeeText('Rail Gun')
        ->assertSeeText('Aegis Dynamics');

    $content = $response->getContent();

    expect(attributeForTestId($content, 'item-breadcrumbs-all-link', 'href'))->toBe(route('web.items.index', ['version' => '4.1.0-LIVE']))
        ->and(attributeForTestId($content, 'item-breadcrumb-link-1', 'href'))->toBe(route('web.items.index', [
            'version' => '4.1.0-LIVE',
            'filter' => ['type' => 'weapon'],
        ]))
        ->and(attributeForTestId($content, 'item-breadcrumb-link-2', 'href'))->toBe(route('web.items.index', [
            'version' => '4.1.0-LIVE',
            'filter' => ['type' => 'weapon', 'sub_type' => 'rail_gun'],
        ]))
        ->and(attributeForTestId($content, 'item-breadcrumb-link-3', 'href'))->toBe(route('web.items.index', [
            'version' => '4.1.0-LIVE',
            'filter' => [
                'type' => 'weapon',
                'sub_type' => 'rail_gun',
                'manufacturer.name' => 'aegis_dynamics',
            ],
        ]));
});

it('ignores nested array values in item tag filters', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Festival Jacket',
            'class_name' => 'festival_jacket',
            'data' => ['event_source' => ['IAE']],
        ]);

    $response = $this->getJson(route('items.index', [
        'filter' => [
            'event_source' => [
                ['nested'],
            ],
        ],
    ]));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'name', 'event_source'],
            ],
            'links',
            'meta',
        ])
        ->assertJsonPath('data.0.name', 'Festival Jacket')
        ->assertJsonPath('data.0.event_source', ['IAE']);
});
