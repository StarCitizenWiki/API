<?php

declare(strict_types=1);

use Symfony\Component\DomCrawler\Crawler;

it('renders the ammunition card content', function (): void {
    $view = $this->blade('<x-items.ammunition-card :ammunition="$data" />', [
        'data' => [
            'speed' => 1000,
            'lifetime' => 5,
        ],
    ]);

    $view->assertSeeTextInOrder([
        'Ammunition',
        'Speed',
        '1,000 m/s',
        'Lifetime',
        '5.00 s',
    ]);
});

it('renders the resource network card content', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'Cooler\'" />', [
        'data' => [
            'usage' => [
                'power' => [
                    'minimum' => 10,
                    'maximum' => 20,
                ],
            ],
        ],
    ]);

    $view->assertSeeTextInOrder([
        'Resource Network',
        'Power Usage',
        '10.0-20.0 Segments',
    ])->assertDontSee('Coolant Usage');
});

it('renders the seat card with primary values and no collapsible sections', function (): void {
    $view = $this->blade('<x-items.seat-card :seat="$data" />', [
        'data' => [
            'seat_type' => 'HOTAS_C_L',
            'has_ejection' => false,
        ],
    ]);
    $crawler = new Crawler((string) $view);

    $view->assertSeeTextInOrder([
        'Seat',
        'Seat Type',
        'HOTAS_C_L',
        'Has Ejection',
        'No',
    ]);

    expect($crawler->filter('details')->count())->toBe(0);
});

it('renders the shield card with primary stats and omits empty sections', function (): void {
    $view = $this->blade('<x-items.shield-card :shield="$data" />', [
        'data' => [
            'max_health' => 1000,
        ],
    ]);
    $crawler = new Crawler((string) $view);

    $view->assertSeeTextInOrder([
        'Shield',
        'Max Health',
        '1,000 HP',
        'Regen Rate',
        'Regen Time',
    ]);

    expect($crawler->filter('details')->count())->toBe(0);
});

it('renders item breadcrumbs linked to the item index', function (): void {
    $view = $this->blade('<x-items.item-breadcrumbs />');
    $crawler = new Crawler((string) $view);

    $view->assertSeeText('All Items');

    expect($crawler->filter('a')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('a')->first()->attr('href'))->toBe(route('web.items.index'));
});
