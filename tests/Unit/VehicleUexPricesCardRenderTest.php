<?php

declare(strict_types=1);

use Symfony\Component\DomCrawler\Crawler;

it('renders nothing when no prices provided', function (): void {
    $view = $this->blade('<x-vehicles.uex-prices-card :purchasePrices="[]" :rentalPrices="[]" />');

    expect((string) $view)->not->toContain('uex-prices-card');
});

it('renders purchase prices grouped by system', function (): void {
    $view = $this->blade('<x-vehicles.uex-prices-card :purchasePrices="$purchase" :rentalPrices="[]" />', [
        'purchase' => [
            [
                'terminal_code' => 'LOR_HAB',
                'terminal_name' => 'Lorville Hangars',
                'web_url' => '/vehicles/test',
                'price_buy' => 1520000,
                'starmap_location' => [
                    'star_system_name' => 'Stanton',
                    'parent_name' => 'Hurston',
                ],
                'date_updated' => '2026-04-20T12:00:00Z',
            ],
        ],
    ]);

    $view->assertSeeText('Purchase Prices')
        ->assertSeeText('Lorville Hangars')
        ->assertSeeText('1,520,000 aUEC')
        ->assertSeeText('Stanton')
        ->assertSeeText('Hurston')
        ->assertDontSee('Rental Prices');

    $crawler = new Crawler((string) $view);
    $link = $crawler->filter('a.link-hover');
    expect($link->count())->toBe(1)
        ->and($link->attr('href'))->toBe('/vehicles/test');
});

it('renders rental prices grouped by system', function (): void {
    $view = $this->blade('<x-vehicles.uex-prices-card :purchasePrices="[]" :rentalPrices="$rental" />', [
        'rental' => [
            [
                'terminal_code' => 'ARC_ADM',
                'terminal_name' => 'Area18 Admin',
                'web_url' => null,
                'price_rent' => 45000,
                'starmap_location' => [
                    'star_system_name' => null,
                    'parent_name' => 'ArcCorp',
                ],
                'date_updated' => '2026-04-19T08:00:00Z',
            ],
        ],
    ]);

    $view->assertSeeText('Rental Prices')
        ->assertSeeText('Area18 Admin')
        ->assertSeeText('45,000 aUEC')
        ->assertSeeText('ArcCorp')
        ->assertDontSee('Purchase Prices');
});

it('renders both purchase and rental tables', function (): void {
    $view = $this->blade('<x-vehicles.uex-prices-card :purchasePrices="$purchase" :rentalPrices="$rental" />', [
        'purchase' => [
            [
                'terminal_code' => 'LOR',
                'terminal_name' => 'Lorville',
                'web_url' => null,
                'price_buy' => 1000000,
                'starmap_location' => ['star_system_name' => 'Stanton'],
                'date_updated' => '2026-04-20T12:00:00Z',
            ],
        ],
        'rental' => [
            [
                'terminal_code' => 'ARC',
                'terminal_name' => 'Area18',
                'web_url' => null,
                'price_rent' => 30000,
                'starmap_location' => ['star_system_name' => 'Stanton'],
                'date_updated' => '2026-04-19T08:00:00Z',
            ],
        ],
    ]);

    $view->assertSeeText('Purchase Prices')
        ->assertSeeText('Rental Prices')
        ->assertSeeText('1,000,000 aUEC')
        ->assertSeeText('30,000 aUEC');
});

it('shows badge with count', function (): void {
    $view = $this->blade('<x-vehicles.uex-prices-card :purchasePrices="$purchase" :rentalPrices="[]" />', [
        'purchase' => [
            [
                'terminal_name' => 'T1',
                'price_buy' => 100,
                'date_updated' => '2026-04-20T12:00:00Z',
            ],
            [
                'terminal_name' => 'T2',
                'price_buy' => 200,
                'date_updated' => '2026-04-19T08:00:00Z',
            ],
        ],
    ]);

    $crawler = new Crawler((string) $view);
    $badges = $crawler->filter('.badge');
    expect($badges->count())->toBe(1)
        ->and($badges->first()->text())->toBe('2');
});

it('hides price when value is 0', function (): void {
    $view = $this->blade('<x-vehicles.uex-prices-card :purchasePrices="$purchase" :rentalPrices="$rental" />', [
        'purchase' => [
            [
                'terminal_name' => 'Free Terminal',
                'price_buy' => 0,
                'starmap_location' => null,
                'date_updated' => '2026-04-20T12:00:00Z',
            ],
        ],
        'rental' => [
            [
                'terminal_name' => 'No Rent',
                'price_rent' => 0,
                'starmap_location' => null,
                'date_updated' => '2026-04-20T12:00:00Z',
            ],
        ],
    ]);

    $view->assertSeeText('Free Terminal')
        ->assertSeeText('No Rent')
        ->assertDontSee('0 aUEC');
});

it('groups multiple systems into separate tables', function (): void {
    $view = $this->blade('<x-vehicles.uex-prices-card :purchasePrices="$purchase" :rentalPrices="[]" />', [
        'purchase' => [
            [
                'terminal_name' => 'Lorville',
                'price_buy' => 1000000,
                'starmap_location' => ['star_system_name' => 'Stanton'],
                'date_updated' => '2026-04-20T12:00:00Z',
            ],
            [
                'terminal_name' => 'Orison',
                'price_buy' => 500000,
                'starmap_location' => ['star_system_name' => 'Pyro'],
                'date_updated' => '2026-04-19T08:00:00Z',
            ],
        ],
    ]);

    $crawler = new Crawler((string) $view);
    $tables = $crawler->filter('table');
    expect($tables->count())->toBe(2);

    $view->assertSeeTextInOrder(['Pyro', 'Orison', 'Stanton', 'Lorville']);
});
