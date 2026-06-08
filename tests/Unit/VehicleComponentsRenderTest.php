<?php

declare(strict_types=1);

use App\Http\Resources\Game\Vehicle\PortResource;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

it('renders vehicle breadcrumbs with navigation links', function (): void {
    $view = $this->blade('<x-vehicles.vehicle-breadcrumbs :vehicle="$data" :manufacturerCode="$code" />', [
        'data' => [
            'name' => 'Test Vehicle',
            'manufacturer' => [
                'name' => 'Test Manufacturer',
            ],
        ],
        'code' => 'TEST',
    ]);
    $crawler = new Crawler((string) $view);

    $view->assertSeeText('All Vehicles')
        ->assertSeeText('Test Manufacturer')
        ->assertSeeText('Test Vehicle');

    $links = $crawler->filter('a');

    expect($links->count())->toBe(2)
        ->and($links->eq(0)->attr('href'))->toBe(route('web.vehicles.index'))
        ->and($links->eq(1)->attr('href'))->toBe(route('web.vehicles.index', [
            'filter' => [
                'manufacturer' => 'TEST',
            ],
        ]));
});

it('renders the port display with equipped item content', function (): void {
    $equippedItemUuid = '8b4b1c1e-4c60-4ac6-8c7d-2f6e2dfcb4c0';
    $view = $this->blade('<x-port-display :port="$port" />', [
        'port' => [
            'name' => 'Test Port',
            'size' => 'S1',
            'equipped_item' => [
                'uuid' => $equippedItemUuid,
                'name' => 'Test Shield',
                'type' => 'Shield',
                'size' => 2,
            ],
        ],
    ]);
    $crawler = new Crawler((string) $view);

    $view->assertSeeText('S2')
        ->assertSeeText('Test Shield');

    expect($crawler->filter('a')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('a')->last()->attr('href'))->toBe(route('web.items.show', $equippedItemUuid));
});

it('marks shield ports as deactivated when the power pool is exhausted', function (): void {
    $view = $this->blade('<x-port-display :port="$port" :power-pools="$powerPools" :category-index="3" />', [
        'port' => [
            'name' => 'hardpoint_shield_03',
            'equipped_item' => [
                'name' => 'Test Shield 3',
                'type' => 'Shield',
                'size' => 2,
            ],
        ],
        'powerPools' => [
            'Shield' => [
                'type' => 'DynamicPowerPool',
                'item_type' => 'Shield',
                'size' => 1,
            ],
        ],
    ]);
    $crawler = new Crawler((string) $view);

    $view->assertSeeText('Test Shield 3');
    expect($crawler->filter('[data-testid="port-display-deactivated"]')->count())->toBe(1)
        ->and($crawler->filter('[title^="Pool Limit"]')->count())->toBe(1);
});

it('does not mark shield ports as deactivated when pool size is -1 (not applicable)', function (): void {
    $view = $this->blade('<x-port-display :port="$port" :power-pools="$powerPools" :category-index="1" />', [
        'port' => [
            'name' => 'hardpoint_shield_01',
            'equipped_item' => [
                'name' => 'Test Shield',
                'type' => 'Shield',
                'size' => 2,
            ],
        ],
        'powerPools' => [
            'Shield' => [
                'type' => 'DynamicPowerPool',
                'item_type' => 'Shield',
                'size' => -1,
            ],
        ],
    ]);
    $crawler = new Crawler((string) $view);

    $view->assertDontSeeText('Deactivated');
    expect($crawler->filter('[title^="Pool Limit"]')->count())->toBe(0);
});

it('marks shield ports as deactivated when pool size is 0 (zero active)', function (): void {
    $view = $this->blade('<x-port-display :port="$port" :power-pools="$powerPools" :category-index="0" />', [
        'port' => [
            'name' => 'hardpoint_shield_01',
            'equipped_item' => [
                'name' => 'Test Shield',
                'type' => 'Shield',
                'size' => 2,
            ],
        ],
        'powerPools' => [
            'Shield' => [
                'type' => 'DynamicPowerPool',
                'item_type' => 'Shield',
                'size' => 0,
            ],
        ],
    ]);
    $crawler = new Crawler((string) $view);

    expect($crawler->filter('[data-testid="port-display-deactivated"]')->count())->toBe(1)
        ->and($crawler->filter('[title^="Pool Limit"]')->count())->toBe(1);
});

it('renders purchase variants with price and sku table content', function (): void {
    $view = $this->blade('<x-vehicles.purchase-variants-card :vehicle="$data" />', [
        'data' => [
            'shipmatrix_name' => 'Test Ship',
            'msrp' => 100000,
            'skus' => [
                [
                    'title' => 'TEST-001',
                    'price' => 100000,
                    'imported_at' => '12:00:00',
                ],
            ],
        ],
    ]);

    $view->assertSeeText('Loaner & SKUs')
        ->assertSeeText('TEST-001')
        ->assertSeeText("100\u{00A0}000")
        ->assertSeeText('Imported At');
});

it('normalizes null compatible type payloads to an empty list', function (): void {
    $result = (new PortResource([
        'HardpointName' => 'hardpoint_empty',
        'CompatibleTypes' => null,
    ]))->resolve(Request::create('/'));

    expect($result['compatible_types'])->toBeNull();
});

it('renders salvage head children inside turret ports with UNDEFINED subtype', function (): void {
    $view = $this->blade('<x-port-display :port="$port" />', [
        'port' => [
            'name' => 'hardpoint_mining_cab_front',
            'equipped_item' => [
                'name' => 'Manned Turret',
                'type' => 'UtilityTurret',
                'size' => 4,
            ],
            'ports' => [
                [
                    'name' => 'hardpoint_weapon_salvage',
                    'type' => 'SalvageHead',
                    'sub_type' => 'UNDEFINED',
                    'equipped_item' => [
                        'uuid' => 'f8aabcb1-83b9-4b08-8846-acc77082cd4d',
                        'name' => 'Baler Salvage Head',
                        'type' => 'SalvageHead',
                        'size' => 2,
                    ],
                    'ports' => [
                        [
                            'name' => 'hardpoint_salvage_subItem01',
                            'type' => 'SalvageModifier',
                            'sub_type' => 'UNDEFINED',
                            'equipped_item' => [
                                'uuid' => '81d7c828-42b2-46e8-b2e3-6798bdc32c23',
                                'name' => 'Cinch Scraper Module',
                                'type' => 'SalvageModifier',
                                'size' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Mining_HUD',
                    'type' => 'Display',
                    'sub_type' => 'UNDEFINED',
                    'equipped_item' => null,
                ],
            ],
        ],
    ]);

    $view->assertSeeText('Baler Salvage Head')
        ->assertSeeText('Cinch Scraper Module')
        ->assertDontSeeText('Mining_HUD');
});
