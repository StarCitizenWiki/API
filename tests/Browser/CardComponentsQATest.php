<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\User;
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
    $createBrowserItem = function (
        string $translationName,
        string $name,
        string $type,
        string $subType,
        string $className,
        array $data
    ) use (&$version, &$manufacturer): Item {
        $item = Item::factory()->create([
            'translation' => ['en' => $translationName],
        ]);

        ItemData::factory()
            ->for($item)
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => $name,
                'type' => $type,
                'sub_type' => $subType,
                'class_name' => $className,
                'data' => $data,
            ]);

        return $item;
    };

    it('card components render correctly across viewport permutations (:dataset)', function (
        string $translationName,
        string $itemName,
        string $itemType,
        string $subType,
        string $className,
        array $itemData,
        string $viewportMode,
        int $viewportWidth,
        int $viewportHeight,
        bool $assertNoHorizontalScroll
    ) use (&$createBrowserItem): void {
        $item = $createBrowserItem(
            $translationName,
            $itemName,
            $itemType,
            $subType,
            $className,
            $itemData
        );

        $page = visit(route('web.items.show', $item->uuid));

        if ($viewportMode === 'mobile') {
            $page = $page->on()->mobile($viewportWidth, $viewportHeight);
        } else {
            $page = $page->resize($viewportWidth, $viewportHeight);
        }

        if ($assertNoHorizontalScroll) {
            $noHorizontalScroll = $page->script('() => document.body.scrollWidth <= window.innerWidth');
            expect($noHorizontalScroll)->toBeTrue();
        }

        $page->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    })->with([
        'cooler mobile 375' => [
            'ThermalCore Cooler',
            'ThermalCore',
            'Cooler',
            'Small',
            'cooler_card',
            [
                'Cooler' => [
                    'ThermalDissipationRate' => [
                        'HeatCapacity' => 50000.0,
                        'ThermalDecayRate' => 2500.0,
                        'UnderLoad' => 5000.0,
                    ],
                    'OverheatTemperature' => 600.0,
                ],
            ],
            'mobile',
            375,
            667,
            true,
        ],
        'cooler tablet 640' => [
            'ThermalCore Cooler',
            'ThermalCore',
            'Cooler',
            'Small',
            'cooler_card',
            [
                'Cooler' => [
                    'ThermalDissipationRate' => [
                        'HeatCapacity' => 50000.0,
                        'ThermalDecayRate' => 2500.0,
                        'UnderLoad' => 5000.0,
                    ],
                    'OverheatTemperature' => 600.0,
                ],
            ],
            'resize',
            640,
            800,
            true,
        ],
        'cooler desktop 1024' => [
            'ThermalCore Cooler',
            'ThermalCore',
            'Cooler',
            'Small',
            'cooler_card',
            [
                'Cooler' => [
                    'ThermalDissipationRate' => [
                        'HeatCapacity' => 50000.0,
                        'ThermalDecayRate' => 2500.0,
                        'UnderLoad' => 5000.0,
                    ],
                    'OverheatTemperature' => 600.0,
                ],
            ],
            'resize',
            1024,
            768,
            false,
        ],
        'fuel tank mobile 375' => [
            'Liquid Fuel Tank',
            'Liquid Fuel Tanks',
            'FuelTank',
            'Small',
            'fuel_tank_card',
            [
                'FuelTank' => [
                    'FuelCapacity' => 50000.0,
                ],
            ],
            'mobile',
            375,
            667,
            true,
        ],
        'shield mobile 375' => [
            'HEX Shield Generator',
            'HEX',
            'Shield',
            'Small',
            'shield_card',
            [
                'ShieldGeneratorParams' => [
                    'ShieldRegenDelay' => 10.0,
                    'ShieldUpkeepCost' => 100.0,
                    'MaxShieldHealth' => 50000.0,
                    'MaxShieldRegenRate' => 500.0,
                ],
            ],
            'mobile',
            375,
            667,
            true,
        ],
        'turret mobile 375' => [
            'Anvil Terrapin Nose Mount',
            'Anvil Terrapin Nose Mount',
            'Turret',
            'Manned',
            'turret_card',
            [
                'Turret' => [
                    'RotationalLimit' => [
                        'Yaw' => 120.0,
                        'Pitch' => 45.0,
                    ],
                    'Speed' => 30.0,
                ],
            ],
            'mobile',
            375,
            667,
            true,
        ],
        'flight controller mobile 375' => [
            'Avenger Stalker Standard Flight Blade',
            'Avenger Stalker Standard Flight Blade',
            'FlightController',
            'Standard',
            'flight_controller_card',
            [
                'FlightController' => [
                    'MaxSpeed' => 220.0,
                    'MaxAfterburnSpeed' => 1200.0,
                    'Pitch' => [
                        'Yaw' => 45.0,
                        'Roll' => 120.0,
                    ],
                ],
            ],
            'mobile',
            375,
            667,
            true,
        ],
    ]);

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
                const toggle = document.querySelector("details.collapse > summary.collapse-title, .collapse > .collapse-title");
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

            $page->assertPresent('details.collapse > summary.collapse-title');

            $isInitiallyOpen = $page->script('() => document.querySelector("details.collapse")?.hasAttribute("open") ?? false');

            $page->script('() => document.querySelector("details.collapse > summary.collapse-title")?.click()');
            $page->wait(200);

            $isOpenAfterCollapse = $page->script('() => document.querySelector("details.collapse")?.hasAttribute("open") ?? false');

            expect((bool) $isOpenAfterCollapse)->toBe(! (bool) $isInitiallyOpen);

            $page->script('() => document.querySelector("details.collapse > summary.collapse-title")?.click()');
            $page->wait(200);

            $isOpenAfterExpand = $page->script('() => document.querySelector("details.collapse")?.hasAttribute("open") ?? false');

            expect((bool) $isOpenAfterExpand)->toBe((bool) $isInitiallyOpen);

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

describe('Phase 5 Browser Coverage', function () use (&$version, &$manufacturer) {
    it('phase 5 smoke covers public locked routes', function () {
        $pages = visit(['/', '/items', '/vehicles', '/comm-links']);

        $pages->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();

        [$homePage, $itemsPage, $vehiclesPage, $commLinksPage] = $pages;

        $homePage->assertPathIs('/')
            ->assertSee('Star Citizen Wiki API');

        $itemsPage->assertPathIs('/items')
            ->assertSee('Column source map');

        $vehiclesPage->assertPathIs('/vehicles')
            ->assertSee('Vehicles');

        $commLinksPage->assertPathIs('/comm-links')
            ->assertSee('Comm-Links');
    });

    it('phase 5 /login guest contract renders fields', function () {
        $page = visit('/login');

        $page->assertPathIs('/login')
            ->assertSee('Welcome back')
            ->assertPresent('input[name="email"]')
            ->assertPresent('input[name="password"]')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });

    it('phase 5 /login redirects authenticated users to /profile', function () {
        $this->actingAs(User::factory()->create());

        $page = visit('/login');

        $page->assertPathIs('/profile')
            ->assertSee('Profile')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });

    it('phase 5 /admin redirects guests to /login', function () {
        $page = visit('/admin');

        $page->assertPathIs('/login')
            ->assertSee('Welcome back');
    });

    it('phase 5 /admin forbids authenticated non-admin users', function () {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $page = visit('/admin');

        $page->assertPathIs('/admin')
            ->assertSee('403');
    });

    it('phase 5 /admin allows authenticated admin users', function () {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $page = visit('/admin');

        $page->assertPathIs('/admin')
            ->assertSee('Admin Dashboard')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });

    it('phase 5 /profile redirects guests to /login', function () {
        $page = visit('/profile');

        $page->assertPathIs('/login')
            ->assertSee('Welcome back');
    });

    it('phase 5 /profile allows authenticated users', function () {
        $this->actingAs(User::factory()->create());

        $page = visit('/profile');

        $page->assertPathIs('/profile')
            ->assertSee('Manage your account settings.')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });

    it('phase 5 item detail responsive card interaction journey', function () use (&$version, &$manufacturer) {
        $item = Item::factory()->create([
            'translation' => ['en' => 'Phase 5 ThermalCore Cooler'],
        ]);

        ItemData::factory()
            ->for($item)
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => 'Phase 5 ThermalCore',
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

        $page->assertSee('Phase 5 ThermalCore')
            ->assertPresent('details.collapse > summary.collapse-title');

        $page->script('() => document.querySelector("details.collapse > summary.collapse-title")?.click()');
        $page->wait(300);
        $page->script('() => document.querySelector("details.collapse > summary.collapse-title")?.click()');

        $page->resize(1024, 768)
            ->assertSee('Search items')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });

    it('phase 5 profile token lifecycle journey', function () {
        $tokenName = 'Phase 5 Browser Token';

        $this->actingAs(User::factory()->create());

        $page = visit('/profile');

        $page->assertPathIs('/profile')
            ->assertSee('API Token')
            ->fill('name', $tokenName)
            ->click('Create New Token')
            ->assertSee('Your API token is ready')
            ->assertSee($tokenName)
            ->assertSee('Your New API Token');

        $page->script('() => { window.confirm = () => true; }');

        $page->click('button[aria-label="Delete token"]')
            ->assertSee('api-token-deleted')
            ->assertSee("You don't have any API tokens yet.")
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });

    it('phase 5 vehicle listing to detail journey', function () use (&$version, &$manufacturer) {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => 'Phase 5 Journey Vehicle',
                'display_name' => 'Phase 5 Journey Vehicle',
                'class_name' => 'PHASE_5_JOURNEY_VEHICLE',
                'career' => 'Exploration',
                'role' => 'Scout',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => true,
            ]);

        $page = visit(route('web.vehicles.index'));

        $page->assertPathIs('/vehicles')
            ->assertSee('Vehicles')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs()
            ->navigate(route('web.vehicles.show', $vehicle->uuid))
            ->assertPathContains('/vehicles/')
            ->assertSee('Phase 5 Journey Vehicle')
            ->assertSee('Search vehicles')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });
});
