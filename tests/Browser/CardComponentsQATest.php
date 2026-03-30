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

beforeEach(function () use (&$version, &$manufacturer): void {
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

describe('Browser Coverage', function () use (&$version, &$manufacturer): void {
    it('item detail collapsibles toggle on mobile', function () use (&$version, &$manufacturer): void {
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

        $page->assertSee('ThermalCore')
            ->assertPresent('details > summary');

        $isInitiallyOpen = $page->script('() => document.querySelector("details")?.hasAttribute("open") ?? false');

        $page->script('() => document.querySelector("details > summary")?.click()');
        $page->wait(200);

        $isOpenAfterCollapse = $page->script('() => document.querySelector("details")?.hasAttribute("open") ?? false');

        expect((bool) $isOpenAfterCollapse)->toBe(! (bool) $isInitiallyOpen);

        $page->script('() => document.querySelector("details > summary")?.click()');
        $page->wait(200);

        $isOpenAfterExpand = $page->script('() => document.querySelector("details")?.hasAttribute("open") ?? false');

        expect((bool) $isOpenAfterExpand)->toBe((bool) $isInitiallyOpen);

        $page->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });

    it('profile token lifecycle journey', function (): void {
        $tokenName = 'Browser Token';

        $this->actingAs(User::factory()->create());

        $page = visit('/profile');

        $page->assertPathIs('/profile')
            ->assertPresent('input[name="name"]')
            ->fill('name', $tokenName)
            ->click('form[action$="/profile/token"] button[type="submit"]')
            ->assertPresent('input[readonly]')
            ->assertPresent('button[onclick*="copyToken"]');

        $page->script('() => { window.confirm = () => true; }');

        $page->click('button[aria-label="Delete token"]');

        $remainingDeleteButtons = $page->script('() => document.querySelectorAll(\'button[aria-label="Delete token"]\').length');

        expect($remainingDeleteButtons)->toBe(0);

        $page->assertPresent('input[name="name"]')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });

    it('vehicle listing navigates to the detail page', function () use (&$version, &$manufacturer): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => 'Journey Vehicle',
                'display_name' => 'Journey Vehicle',
                'class_name' => 'JOURNEY_VEHICLE',
                'career' => 'Exploration',
                'role' => 'Scout',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => true,
            ]);

        $page = visit(route('web.vehicles.index'));

        $page->assertPathIs('/vehicles')
            ->navigate(route('web.vehicles.show', $vehicle->uuid))
            ->assertPathContains('/vehicles/')
            ->assertSee('Journey Vehicle')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    });
});
