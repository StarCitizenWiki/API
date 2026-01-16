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
        ->assertViewIs('items.index')
        ->assertViewHas('initialTableData', function (array $payload) use ($widget): bool {
            return ($payload['data'][0]['uuid'] ?? null) === $widget->uuid
                && count($payload['data']) === 1;
        })
        ->assertViewHas('pageTitle', 'Widget Items')
        ->assertViewHas('tableColumns', function (array $columns): bool {
            return collect($columns)->pluck('field')->doesntContain('type');
        })
        ->assertViewHas('headerFilterOptionsMap', function (array $map): bool {
            return ! array_key_exists('type', $map);
        });
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
        ->assertViewHas('initialTableData', function (array $payload) use ($foodItem): bool {
            return ($payload['data'][0]['uuid'] ?? null) === $foodItem->uuid
                && count($payload['data']) === 1;
        })
        ->assertViewHas('pageTitle', 'Food & Drinks');
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

it('includes shared group columns for types with shared configuration', function () {
    config(['items.shared_groups' => [
        'testGroup' => [
            'title' => 'Test Group',
            'columns' => [
                ['title' => 'Shared Field', 'field' => 'shared.field'],
            ],
        ],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
    ]]);

    $response = $this->get(route('web.items.index', ['filter' => ['type' => 'TestType']]));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        foreach ($columns as $column) {
            if (isset($column['columns'])) {
                foreach ($column['columns'] as $nestedColumn) {
                    if (($nestedColumn['field'] ?? null) === 'shared.field') {
                        return true;
                    }
                }
            }
        }

        return false;
    });
});

it('applies shared group overrides correctly', function () {
    config(['items.shared_groups' => [
        'testGroup' => [
            'title' => 'Original Title',
            'columns' => [
                ['title' => 'Field', 'field' => 'test.field'],
            ],
        ],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
        'shared_overrides' => [
            'testGroup' => [
                'title' => 'Overridden Title',
            ],
        ],
    ]]);

    $response = $this->get(route('web.items.index', ['filter' => ['type' => 'TestType']]));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        foreach ($columns as $column) {
            if (isset($column['title']) && $column['title'] === 'Overridden Title') {
                return true;
            }
        }

        return false;
    });
});

it('inserts positive inserts after base columns before the view button', function () {
    $originalColumns = config('items.table.columns', []);
    $originalSharedGroups = config('items.shared_groups', []);
    $originalOverrides = config('items.type_overrides', []);

    config(['items.table.columns' => [
        ['title' => 'Grade', 'field' => 'grade'],
        ['title' => 'Class', 'field' => 'class'],
        ['title' => 'API Url', 'field' => 'uuid'],
    ]]);

    config(['items.shared_groups' => [
        'durability' => [
            'title' => 'Durability',
            'columns' => [['title' => 'Health', 'field' => 'durability.health']],
        ],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['durability'],
        'shared_insert_at' => 2,
        'add_columns_insert_at' => 1,
        'add_columns' => [
            ['title' => 'Signals', 'columns' => []],
            ['title' => 'Damage', 'columns' => []],
            ['title' => 'Penetration Resistance', 'columns' => []],
        ],
    ]]);

    try {
        $response = $this->get(route('web.items.index', ['filter' => ['type' => 'TestType']]));

        $response->assertSuccessful();
        $response->assertViewHas('tableColumns', function ($columns) {
            $titles = collect($columns)->pluck('title')->filter()->values()->all();

            return $titles === [
                'Grade',
                'Class',
                'Signals',
                'Damage',
                'Penetration Resistance',
                'Durability',
                'API Url',
            ];
        });
    } finally {
        config(['items.table.columns' => $originalColumns]);
        config(['items.shared_groups' => $originalSharedGroups]);
        config(['items.type_overrides' => $originalOverrides]);
    }
});

it('inserts add_columns at negative index', function () {
    $originalColumns = config('items.table.columns', []);
    $columns = $originalColumns;
    if (! collect($columns)->contains(fn (array $column): bool => ($column['formatter'] ?? null) === 'viewButton')) {
        $columns[] = [
            'title' => 'View',
            'field' => 'view_button',
            'formatter' => 'viewButton',
        ];
    }
    config(['items.table.columns' => $columns]);

    try {
        config(['items.type_overrides.TestType' => [
            'add_columns' => [
                ['title' => 'Custom', 'field' => 'custom.field'],
            ],
            'add_columns_insert_at' => -1,
        ]]);

        $response = $this->get(route('web.items.index', ['filter' => ['type' => 'TestType']]));

        $response->assertSuccessful();
        $response->assertViewHas('tableColumns', function ($columns) {
            $customIndex = null;
            $viewButtonIndex = null;

            foreach ($columns as $index => $column) {
                if (($column['field'] ?? null) === 'custom.field') {
                    $customIndex = $index;
                }
                if (($column['formatter'] ?? null) === 'viewButton') {
                    $viewButtonIndex = $index;
                }
            }

            return $customIndex !== null
                && $viewButtonIndex !== null
                && $customIndex < $viewButtonIndex;
        });
    } finally {
        config(['items.table.columns' => $originalColumns]);
    }
});

it('keeps view button at end when inserting columns', function () {
    $originalColumns = config('items.table.columns', []);
    $columns = $originalColumns;
    if (! collect($columns)->contains(fn (array $column): bool => ($column['formatter'] ?? null) === 'viewButton')) {
        $columns[] = [
            'title' => 'View',
            'field' => 'view_button',
            'formatter' => 'viewButton',
        ];
    }
    config(['items.table.columns' => $columns]);

    try {
        config(['items.type_overrides.TestType' => [
            'add_columns' => [
                ['title' => 'Custom', 'field' => 'custom.field'],
            ],
            'add_columns_insert_at' => 999,
        ]]);

        $response = $this->get(route('web.items.index', ['filter' => ['type' => 'TestType']]));

        $response->assertSuccessful();
        $response->assertViewHas('tableColumns', function ($columns) {
            $lastColumn = end($columns);

            return ($lastColumn['formatter'] ?? null) === 'viewButton';
        });
    } finally {
        config(['items.table.columns' => $originalColumns]);
    }
});

it('maintains backwards compatibility when no insert_at specified', function () {
    config(['items.shared_groups.testGroup' => [
        'title' => 'Test Group',
        'columns' => [['title' => 'Shared', 'field' => 'shared.field']],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
    ]]);

    $response = $this->get(route('web.items.index', ['filter' => ['type' => 'TestType']]));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        foreach ($columns as $index => $column) {
            if (($column['title'] ?? null) === 'Test Group') {
                return $index >= 9;
            }
        }

        return false;
    });
});

it('handles both shared and add_columns with different positions', function () {
    config(['items.shared_groups.testGroup' => [
        'title' => 'Shared Group',
        'columns' => [['title' => 'Shared', 'field' => 'shared.field']],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
        'shared_insert_at' => 3,
        'add_columns' => [
            ['title' => 'Custom', 'field' => 'custom.field'],
        ],
        'add_columns_insert_at' => 7,
    ]]);

    $response = $this->get(route('web.items.index', ['filter' => ['type' => 'TestType']]));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        $sharedIndex = null;
        $customIndex = null;
        $viewButtonIndex = null;

        foreach ($columns as $index => $column) {
            if (($column['title'] ?? null) === 'Shared Group') {
                $sharedIndex = $index;
            }
            if (($column['field'] ?? null) === 'custom.field') {
                $customIndex = $index;
            }
            if (($column['field'] ?? null) === 'uuid') {
                $viewButtonIndex = $index;
            }
        }

        return $sharedIndex !== null
            && $customIndex !== null
            && $viewButtonIndex !== null
            && $sharedIndex < $customIndex
            && $customIndex < $viewButtonIndex;
    });
});
