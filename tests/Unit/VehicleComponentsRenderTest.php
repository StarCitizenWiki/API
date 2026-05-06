<?php

declare(strict_types=1);

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

    $view->assertSeeText('Test Port')
        ->assertSeeText('S2')
        ->assertSeeText('Test Shield')
        ->assertSeeText('Equippable Size')
        ->assertSeeText('Equippable Type');

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

    $view->assertSeeText('Deactivated')
        ->assertSeeText('Test Shield 3');

    expect($crawler->filter('[title^="Pool Limit"]')->count())->toBe(1);
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
        ->assertSeeText('100,000')
        ->assertSeeText('Imported At');
});
