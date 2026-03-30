<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;

uses(RefreshDatabase::class);

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
        ->assertSee('Widget Items')
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
        ->assertSee('Food & Drinks')
        ->assertSee('Trail Mix')
        ->assertDontSee('Pulse Pistol');
});

it('activates vehicle items menu for vehicle type filters', function (): void {
    $response = $this->get(route('web.items.index', ['filter' => ['type' => 'PowerPlant']]));

    $response->assertOk();

    $crawler = new Crawler($response->getContent());

    $vehicleMenu = $crawler->filterXPath('//summary[contains(normalize-space(.), "Vehicle-Items")]')->first();
    $fpsMenu = $crawler->filterXPath('//summary[contains(normalize-space(.), "FPS-Items")]')->first();
    $allItemsMenu = $crawler->filterXPath('//a[contains(normalize-space(.), "All Items")]')->first();

    expect($vehicleMenu->attr('class'))->toContain('menu-active')
        ->and($fpsMenu->attr('class'))->not->toContain('menu-active')
        ->and($allItemsMenu->attr('class') ?? '')->not->toContain('menu-active');
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

    $response->assertOk();

    $crawler = new Crawler($response->getContent());
    $breadcrumbs = $crawler->filter('.breadcrumbs a');

    expect($breadcrumbs->count())->toBe(4)
        ->and($breadcrumbs->eq(0)->text())->toBe('All Items')
        ->and($breadcrumbs->eq(0)->attr('href'))->toBe(route('web.items.index', ['version' => '4.1.0-LIVE']))
        ->and($breadcrumbs->eq(1)->text())->toBe('Weapon')
        ->and($breadcrumbs->eq(1)->attr('href'))->toBe(route('web.items.index', [
            'version' => '4.1.0-LIVE',
            'filter' => ['type' => 'weapon'],
        ]))
        ->and($breadcrumbs->eq(2)->text())->toBe('Rail Gun')
        ->and($breadcrumbs->eq(2)->attr('href'))->toBe(route('web.items.index', [
            'version' => '4.1.0-LIVE',
            'filter' => ['type' => 'weapon', 'sub_type' => 'rail_gun'],
        ]))
        ->and($breadcrumbs->eq(3)->text())->toBe('Aegis Dynamics')
        ->and($breadcrumbs->eq(3)->attr('href'))->toBe(route('web.items.index', [
            'version' => '4.1.0-LIVE',
            'filter' => [
                'type' => 'weapon',
                'sub_type' => 'rail_gun',
                'manufacturer.name' => 'aegis_dynamics',
            ],
        ]));
});
