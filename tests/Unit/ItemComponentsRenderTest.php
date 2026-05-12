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
        "1\u{00A0}000 m/s",
        'Lifetime',
        '5.00 s',
    ]);
});

it('renders the resource network card with generation for Cooler', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'Cooler\'" />', [
        'data' => [
            'usage' => [
                'power' => [
                    'minimum' => 10,
                    'maximum' => 20,
                ],
            ],
            'generation' => [
                'coolant' => 22,
            ],
        ],
    ]);

    $view->assertSeeTextInOrder([
        'Resource Network',
        'Power Usage',
        '10 - 20 Segments',
        'Coolant Generation',
        '22 Segments',
    ])->assertDontSee('Coolant Usage');
});

it('renders the resource network card with generation for PowerPlant', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'PowerPlant\'" />', [
        'data' => [
            'usage' => [
                'coolant' => [
                    'minimum' => 5,
                    'maximum' => 15,
                ],
            ],
            'generation' => [
                'power' => 1000,
            ],
        ],
    ]);

    $view->assertSeeTextInOrder([
        'Resource Network',
        'Coolant Usage',
        '5 - 15 Segments',
        'Power Generation',
        "1\u{00A0}000 Segments",
    ])->assertDontSee('Power Usage');
});

it('renders a single state inside a collapsible details element', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'PowerPlant\'" />', [
        'data' => [
            'states' => [
                [
                    'name' => 'On',
                    'deltas' => [
                        [
                            'resource' => 'Power',
                            'type' => 'consumption',
                            'rate' => 5.0,
                        ],
                    ],
                ],
            ],
        ],
    ]);
    $crawler = new Crawler((string) $view);

    $view->assertSeeTextInOrder([
        'On',
        'Consumption',
        'Power consumption',
        'Rate',
        '5.0',
    ]);

    // Single state should use <details> (x-dl-details), not a plain heading
    expect($crawler->filter('details > summary')->count())->toBeGreaterThanOrEqual(1);
});

it('renders multiple states each inside collapsible details', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'PowerPlant\'" />', [
        'data' => [
            'states' => [
                [
                    'name' => 'On',
                    'deltas' => [
                        ['resource' => 'Power', 'type' => 'consumption', 'rate' => 10.0],
                    ],
                ],
                [
                    'name' => 'Off',
                    'deltas' => [
                        ['resource' => 'Power', 'type' => 'consumption', 'rate' => 0.0],
                    ],
                ],
            ],
        ],
    ]);
    $crawler = new Crawler((string) $view);

    $view->assertSeeTextInOrder(['On', 'Consumption', '10.0', 'Off', 'Consumption', '0.0']);

    // Both states should be inside <details> elements
    expect($crawler->filter('details > summary')->count())->toBeGreaterThanOrEqual(2);
});

it('renders discharge as Yes/No instead of decimals', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'PowerPlant\'" />', [
        'data' => [
            'states' => [
                [
                    'name' => 'Active',
                    'deltas' => [
                        ['resource' => 'Power', 'type' => 'output', 'rate' => 100, 'discharge' => true],
                        ['resource' => 'Power', 'type' => 'idle', 'rate' => 0, 'discharge' => false],
                    ],
                ],
            ],
        ],
    ]);

    $view->assertSeeText('Yes')->assertSeeText('No');
    $view->assertDontSee('1.00')->assertDontSee('0.00');
});

it('renders minimum_fraction as a percentage', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'PowerPlant\'" />', [
        'data' => [
            'states' => [
                [
                    'name' => 'Active',
                    'deltas' => [
                        ['resource' => 'Power', 'type' => 'output', 'rate' => 100, 'minimum_fraction' => 0.75],
                    ],
                ],
            ],
        ],
    ]);

    $view->assertSeeText('75.0%');
});

it('renders power ranges with Low/Standard/High labels', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'PowerPlant\'" />', [
        'data' => [
            'states' => [
                [
                    'name' => 'On',
                    'deltas' => [],
                    'power_ranges' => [
                        ['start' => 0, 'modifier' => 0.5, 'register_range' => 1],
                        ['start' => 50, 'modifier' => 1.0, 'register_range' => 1],
                        ['start' => 100, 'modifier' => 2.0, 'register_range' => 0],
                    ],
                ],
            ],
        ],
    ]);

    $view->assertSeeTextInOrder([
        'On',
        'Power States',
        'Low',
        'Start', '0',
        'Modifier', '0.50 x',
        'Standard',
        'Start', '50',
        'Modifier', '1.00 x',
        'High (Disabled)',
        'Start', '100',
        'Modifier', '2.00 x',
    ]);
});

it('renders both deltas and power ranges with group headings', function (): void {
    $view = $this->blade('<x-items.resource-network-card :resource-network="$data" :item-type="\'PowerPlant\'" />', [
        'data' => [
            'states' => [
                [
                    'name' => 'On',
                    'deltas' => [
                        ['resource' => 'Power', 'type' => 'consumption', 'rate' => 5.0],
                    ],
                    'power_ranges' => [
                        ['start' => 0, 'modifier' => 1.0, 'register_range' => 1],
                        ['start' => 100, 'modifier' => 2.0, 'register_range' => 1],
                    ],
                ],
            ],
        ],
    ]);

    $view->assertSeeTextInOrder([
        'On',
        'Consumption',
        'Power consumption',
        'Rate',
        '5.0',
        'Power States',
        'Low',
        'Standard',
    ]);
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
        "1\u{00A0}000 HP",
    ]);

    expect($crawler->filter('details')->count())->toBe(0);
});

it('renders the shield controller card with full data', function (): void {
    $view = $this->blade('<x-items.shield-controller-card :shield-controller="$data" />', [
        'data' => [
            'face_type' => 'Bubble',
            'max_reallocation' => 3.0,
            'reconfiguration_cooldown' => 5.0,
            'max_electrical_charge_damage_rate' => 10.0,
        ],
    ]);

    $view->assertSeeTextInOrder([
        'Shield Controller',
        'Face Type',
        'Bubble',
        'Reconfiguration Cooldown',
        '5.0 s',
        'Max Reallocation',
        '3',
        'Electrical Charge Dmg',
        '10.0/s',
    ]);
});

it('hides the shield controller card when all data is null', function (): void {
    $view = $this->blade('<x-items.shield-controller-card :shield-controller="$data" />', [
        'data' => [],
    ]);

    $view->assertDontSeeText('Shield Controller');
});

it('renders item breadcrumbs linked to the item index', function (): void {
    $view = $this->blade('<x-items.item-breadcrumbs />');
    $crawler = new Crawler((string) $view);

    $view->assertSeeText('All Items');

    expect($crawler->filter('a')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('a')->first()->attr('href'))->toBe(route('web.items.index'));
});

it('renders the weapon attachment card with iron sight and compensator data', function (): void {
    $view = $this->blade('<x-items.weapon-attachment-card :weapon-attachment="$data" />', [
        'data' => [
            'iron_sight' => [
                'default_range' => 100,
                'max_range' => 500,
                'zoom_scale' => 2.0,
            ],
            'compensator' => [
                'recoil_change' => -0.15,
                'spread_change' => -0.10,
            ],
        ],
    ]);

    $view->assertSeeTextInOrder([
        'Weapon Attachment',
        'Iron Sight',
        'Default Range',
        '100.00 m',
        'Max Range',
        '500.00 m',
        'Zoom Scale',
        '2.00',
        'Compensator',
        'Recoil',
        '-15.0%',
        'Spread',
        '-10.0%',
    ]);
});

it('hides the weapon attachment card when all data is null', function (): void {
    $view = $this->blade('<x-items.weapon-attachment-card :weapon-attachment="$data" />', [
        'data' => [],
    ]);

    $view->assertDontSeeText('Weapon Attachment');
});

describe('clothing-card', function (): void {
    it('renders g-force resistance as a percentage', function (): void {
        $view = $this->blade('<x-items.clothing-card :clothing="$clothing" :temperature-resistance="$temp" :inventory="$inventory" :gforce-resistance="$gfr" />', [
            'clothing' => ['slot' => 'Torso'],
            'temp' => ['minimum' => -20, 'maximum' => 50],
            'inventory' => ['scu_converted' => 0.001, 'unit' => 'µSCU'],
            'gfr' => -0.125,
        ]);

        $view->assertSeeTextInOrder(['G-Force Resistance', 'Modifier', '-12.5%']);
    });

    it('renders positive g-force resistance', function (): void {
        $view = $this->blade('<x-items.clothing-card :clothing="$clothing" :temperature-resistance="$temp" :inventory="$inventory" :gforce-resistance="$gfr" />', [
            'clothing' => ['slot' => 'Legs'],
            'temp' => ['minimum' => -10, 'maximum' => 40],
            'inventory' => ['scu_converted' => 0.001, 'unit' => 'µSCU'],
            'gfr' => 0.9,
        ]);

        $view->assertSeeTextInOrder(['G-Force Resistance', 'Modifier', '90.0%']);
    });

    it('renders zero g-force resistance', function (): void {
        $view = $this->blade('<x-items.clothing-card :clothing="$clothing" :temperature-resistance="$temp" :inventory="$inventory" :gforce-resistance="$gfr" />', [
            'clothing' => ['slot' => 'Feet'],
            'temp' => ['minimum' => 0, 'maximum' => 30],
            'inventory' => ['scu_converted' => 0.001, 'unit' => 'µSCU'],
            'gfr' => 0,
        ]);

        $view->assertSeeTextInOrder(['G-Force Resistance', 'Modifier', '0.0%']);
    });
});

describe('suit-armor-card', function (): void {
    it('renders g-force resistance in the armor card', function (): void {
        $view = $this->blade('<x-items.suit-armor-card :suit-armor="$armor" :temperature-resistance="$temp" :inventory="$inventory" :gforce-resistance="$gfr" />', [
            'armor' => [
                'slot' => 'Core',
                'damage_resistance_map' => ['physical_change' => -0.3],
            ],
            'temp' => ['minimum' => -30, 'maximum' => 60],
            'inventory' => ['scu_converted' => 0.002, 'unit' => 'µSCU'],
            'gfr' => 0.9,
        ]);

        $view->assertSeeTextInOrder(['G-Force Resistance', 'Modifier', '90.0%']);
    });

    it('renders negative g-force resistance for heavy suits', function (): void {
        $view = $this->blade('<x-items.suit-armor-card :suit-armor="$armor" :temperature-resistance="$temp" :inventory="$inventory" :gforce-resistance="$gfr" />', [
            'armor' => [
                'slot' => 'Core',
                'damage_resistance_map' => [],
            ],
            'temp' => ['minimum' => -40, 'maximum' => 80],
            'inventory' => ['scu_converted' => 0.003, 'unit' => 'µSCU'],
            'gfr' => -0.875,
        ]);

        $view->assertSeeTextInOrder(['G-Force Resistance', 'Modifier', '-87.5%']);
    });
});
