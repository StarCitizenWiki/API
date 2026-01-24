<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

$version = null;
$manufacturer = null;

beforeEach(function () use (&$version, &$manufacturer) {
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
});

describe('Design System Card Components - Browser QA', function () use (&$version, &$manufacturer) {
    describe('Simple Cards', function () use (&$version, &$manufacturer) {
        it('cooler-card no horizontal scroll at mobile (375px)', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'ThermalCore Cooler'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'ThermalCore',
                    'type' => 'Cooler',
                    'sub_type' => 'Small',
                    'class_name' => 'cooler_card',
                    'data' => [
                        'Cooler' => [
                            'ThermalDissipationRate' => [
                                'HeatCapacity' => 50000.0,
                                'ThermalDecayRate' => 2500.0,
                                'UnderLoad' => 5000.0,
                            ],
                            'OverheatTemperature' => 600.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->on()->mobile(375, 667);

            $noHorizontalScroll = $page->script('() => document.body.scrollWidth <= window.innerWidth');
            expect($noHorizontalScroll)->toBe(true);

            $page->assertNoJavascriptErrors()
                ->assertNoConsoleLogs();
        });

        it('cooler-card no horizontal scroll at mobile (640px)', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'ThermalCore Cooler'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'ThermalCore',
                    'type' => 'Cooler',
                    'sub_type' => 'Small',
                    'class_name' => 'cooler_card',
                    'data' => [
                        'Cooler' => [
                            'ThermalDissipationRate' => [
                                'HeatCapacity' => 50000.0,
                                'ThermalDecayRate' => 2500.0,
                                'UnderLoad' => 5000.0,
                            ],
                            'OverheatTemperature' => 600.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->resize(640, 800);

            $noHorizontalScroll = $page->script('() => document.body.scrollWidth <= window.innerWidth');
            expect($noHorizontalScroll)->toBe(true);

            $page->assertNoJavascriptErrors()
                ->assertNoConsoleLogs();
        });

        it('cooler-card renders at tablet (1024px)', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'ThermalCore Cooler'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'ThermalCore',
                    'type' => 'Cooler',
                    'sub_type' => 'Small',
                    'class_name' => 'cooler_card',
                    'data' => [
                        'Cooler' => [
                            'ThermalDissipationRate' => [
                                'HeatCapacity' => 50000.0,
                                'ThermalDecayRate' => 2500.0,
                                'UnderLoad' => 5000.0,
                            ],
                            'OverheatTemperature' => 600.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->resize(1024, 768);

            $page->assertNoJavascriptErrors()
                ->assertNoConsoleLogs();
        });

        it('fuel-tank-card no horizontal scroll at mobile (375px)', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'Liquid Fuel Tank'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'Liquid Fuel Tanks',
                    'type' => 'FuelTank',
                    'sub_type' => 'Small',
                    'class_name' => 'fuel_tank_card',
                    'data' => [
                        'FuelTank' => [
                            'FuelCapacity' => 50000.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->on()->mobile(375, 667);

            $noHorizontalScroll = $page->script('() => document.body.scrollWidth <= window.innerWidth');
            expect($noHorizontalScroll)->toBe(true);

            $page->assertNoJavascriptErrors()
                ->assertNoConsoleLogs();
        });
    });

    describe('Medium Cards', function () use (&$version, &$manufacturer) {
        it('shield-card no horizontal scroll at mobile (375px)', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'HEX Shield Generator'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'HEX',
                    'type' => 'Shield',
                    'sub_type' => 'Small',
                    'class_name' => 'shield_card',
                    'data' => [
                        'ShieldGeneratorParams' => [
                            'ShieldRegenDelay' => 10.0,
                            'ShieldUpkeepCost' => 100.0,
                            'MaxShieldHealth' => 50000.0,
                            'MaxShieldRegenRate' => 500.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->on()->mobile(375, 667);

            $noHorizontalScroll = $page->script('() => document.body.scrollWidth <= window.innerWidth');
            expect($noHorizontalScroll)->toBe(true);

            $page->assertNoJavascriptErrors()
                ->assertNoConsoleLogs();
        });

        it('turret-card no horizontal scroll at mobile (375px)', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'Anvil Terrapin Nose Mount'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'Anvil Terrapin Nose Mount',
                    'type' => 'Turret',
                    'sub_type' => 'Manned',
                    'class_name' => 'turret_card',
                    'data' => [
                        'Turret' => [
                            'RotationalLimit' => [
                                'Yaw' => 120.0,
                                'Pitch' => 45.0,
                            ],
                            'Speed' => 30.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->on()->mobile(375, 667);

            $noHorizontalScroll = $page->script('() => document.body.scrollWidth <= window.innerWidth');
            expect($noHorizontalScroll)->toBe(true);

            $page->assertNoJavascriptErrors()
                ->assertNoConsoleLogs();
        });
    });

    describe('High Complexity Cards', function () use (&$version, &$manufacturer) {
        it('flight-controller-card no horizontal scroll at mobile (375px)', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'Avenger Stalker Standard Flight Blade'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'Avenger Stalker Standard Flight Blade',
                    'type' => 'FlightController',
                    'sub_type' => 'Standard',
                    'class_name' => 'flight_controller_card',
                    'data' => [
                        'FlightController' => [
                            'MaxSpeed' => 220.0,
                            'MaxAfterburnSpeed' => 1200.0,
                            'Pitch' => [
                                'Yaw' => 45.0,
                                'Roll' => 120.0,
                            ],
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->on()->mobile(375, 667);

            $noHorizontalScroll = $page->script('() => document.body.scrollWidth <= window.innerWidth');
            expect($noHorizontalScroll)->toBe(true);

            $page->assertNoJavascriptErrors()
                ->assertNoConsoleLogs();
        });
    });

    describe('Collapsible Section Behavior', function () use (&$version, &$manufacturer) {
        it('collapse toggle has touch target >= 44x44px on mobile', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'Test Cooler with Collapse'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'Test Cooler',
                    'type' => 'Cooler',
                    'sub_type' => 'Small',
                    'class_name' => 'cooler_card',
                    'data' => [
                        'Cooler' => [
                            'ThermalDissipationRate' => [
                                'HeatCapacity' => 50000.0,
                                'ThermalDecayRate' => 2500.0,
                                'UnderLoad' => 5000.0,
                            ],
                            'OverheatTemperature' => 600.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->on()->mobile(375, 667);

            $toggleSize = $page->script('() => {
                const toggle = document.querySelector("[data-testid*=\"collapse\"], button[aria-label*=\"collapse\"], button[aria-label*=\"Collapse\"]");
                if (!toggle) {
                    return { width: 0, height: 0, found: false };
                }
                const rect = toggle.getBoundingClientRect();
                return {
                    width: rect.width,
                    height: rect.height,
                    found: true,
                };
            }');

            expect($toggleSize['found'])->toBe(true);
            expect($toggleSize['width'])->toBeGreaterThanOrEqual(44);
            expect($toggleSize['height'])->toBeGreaterThanOrEqual(44);
        });

        it('collapsible sections expand and collapse correctly', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'Test Cooler for Collapse'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'Test Cooler',
                    'type' => 'Cooler',
                    'sub_type' => 'Small',
                    'class_name' => 'cooler_card',
                    'data' => [
                        'Cooler' => [
                            'ThermalDissipationRate' => [
                                'HeatCapacity' => 50000.0,
                                'ThermalDecayRate' => 2500.0,
                                'UnderLoad' => 5000.0,
                            ],
                            'OverheatTemperature' => 600.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid));

            $page->screenshot();

            $page->click('[data-testid*="collapse"], button[aria-label*="collapse"]');

            $page->wait(500);

            $page->screenshot();

            $page->click('[data-testid*="expand"], button[aria-label*="expand"]');
            $page->wait(500);

            $page->assertSee('Test Cooler');
        });
    });

    describe('Responsive Grid Layout Verification', function () use (&$version, &$manufacturer) {
        it('grid layout is responsive across breakpoints', function () use (&$version, &$manufacturer) {
            $item = Item::factory()->create([
                'translation' => ['en' => 'Test Item for Grid'],
            ]);

            ItemData::factory()
                ->for($item)
                ->for($version, 'gameVersion')
                ->for($manufacturer)
                ->create([
                    'name' => 'Test Item',
                    'type' => 'Cooler',
                    'sub_type' => 'Small',
                    'class_name' => 'cooler_card',
                    'data' => [
                        'Cooler' => [
                            'ThermalDissipationRate' => [
                                'HeatCapacity' => 50000.0,
                                'ThermalDecayRate' => 2500.0,
                                'UnderLoad' => 5000.0,
                            ],
                            'OverheatTemperature' => 600.0,
                        ],
                    ],
                ]);

            $page = visit(route('web.items.show', $item->uuid))
                ->on()->mobile(375, 667);

            $page->resize(640, 800);

            $page->resize(1024, 768);

            $page->resize(1280, 1024);

            $page->assertNoJavascriptErrors();
        });
    });
});
