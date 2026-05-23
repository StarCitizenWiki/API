<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

it('redirects to item web url when matching by name', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create(['slug' => 'arrow-sniper']);
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow Sniper Rifle',
            'class_name' => 'Arrow_Sniper',
            'classification' => 'FPS.Weapon.Sniper',
            'data' => [],
        ]);

    $this->get('/search/'.rawurlencode('Arrow Sniper Rifle'))
        ->assertStatus(302)
        ->assertRedirect(route('web.items.show', ['item' => 'arrow-sniper']));
});

it('redirects to vehicle web url when matching by class_name', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $vehicle = Vehicle::factory()->create(['slug' => 'arrow-fighter']);
    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow',
            'class_name' => 'AEGS_Arrow',
        ]);

    $this->get('/search/AEGS_Arrow')
        ->assertStatus(302)
        ->assertRedirect(route('web.vehicles.show', ['vehicle' => 'arrow-fighter']));
});

it('redirects when matching by uuid', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create(['slug' => 'uuid-item', 'uuid' => '11111111-2222-3333-4444-555555555555']);
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'UUID Test Item',
            'class_name' => 'uuid_test_item',
            'classification' => 'Test',
            'data' => [],
        ]);

    $this->get('/search/11111111-2222-3333-4444-555555555555')
        ->assertStatus(302)
        ->assertRedirect(route('web.items.show', ['item' => 'uuid-item']));
});

it('respects priority order: vehicles before items', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create(['slug' => 'arrow-item']);
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow',
            'class_name' => 'arrow_item',
            'classification' => 'Test',
            'data' => [],
        ]);

    $vehicle = Vehicle::factory()->create(['slug' => 'arrow-vehicle']);
    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow',
            'class_name' => 'AEGS_Arrow',
        ]);

    $this->get('/search/Arrow')
        ->assertStatus(302)
        ->assertRedirect(route('web.vehicles.show', ['vehicle' => 'arrow-vehicle']));
});

it('falls through to vehicle when no item matches', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $vehicle = Vehicle::factory()->create(['slug' => 'arrow-vehicle']);
    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow',
            'class_name' => 'AEGS_Arrow',
        ]);

    $this->get('/search/Arrow')
        ->assertStatus(302)
        ->assertRedirect(route('web.vehicles.show', ['vehicle' => 'arrow-vehicle']));
});

it('redirects to mission web url when matching by title', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $mission = Mission::factory()->create(['slug' => 'delivery-mission']);
    MissionData::factory()
        ->for($mission)
        ->for($version, 'gameVersion')
        ->create([
            'title' => 'Delivery Run',
            'mission_type' => 'Delivery',
            'not_for_release' => false,
            'work_in_progress' => false,
        ]);

    $this->get('/search/'.rawurlencode('Delivery Run'))
        ->assertStatus(302)
        ->assertRedirect(route('web.missions.show', ['mission' => 'delivery-mission']));
});

it('redirects to location web url when matching by name', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $location = StarmapLocation::factory()->create(['uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee']);
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($version, 'gameVersion')
        ->create([
            'name' => 'Port Olisar',
            'system' => 'Stanton',
            'type_name' => 'Station',
        ]);

    $this->get('/search/'.rawurlencode('Port Olisar'))
        ->assertStatus(302)
        ->assertRedirect(route('web.locations.show', ['identifier' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee']));
});

it('redirects to blueprint web url when matching by output_name', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $blueprint = Blueprint::factory()->create(['slug' => 'weapon-bp']);
    BlueprintData::factory()
        ->for($blueprint)
        ->for($version, 'gameVersion')
        ->create([
            'output_name' => 'Weapon Blueprint',
            'output_class' => 'BP_Weapon',
            'key' => 'weapon_bp_key',
        ]);

    $this->get('/search/'.rawurlencode('Weapon Blueprint'))
        ->assertStatus(302)
        ->assertRedirect(route('web.blueprints.show', ['blueprint' => 'weapon-bp']));
});

it('redirects to commodity web url when matching by name', function (): void {
    GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    Commodity::factory()->create([
        'name' => 'Agricium',
        'key' => 'agricium',
        'slug' => 'agricium',
    ]);

    $this->get('/search/Agricium')
        ->assertStatus(302)
        ->assertRedirect(route('web.commodities.show', ['identifier' => 'agricium']));
});

it('returns 404 when nothing matches', function (): void {
    GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->get('/search/ZZZZZZZZZ_NOTHING_MATCHES')
        ->assertStatus(404);
});

it('matches case-insensitively', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '1.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create(['slug' => 'case-item']);
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow Sniper Rifle',
            'class_name' => 'Arrow_Sniper',
            'classification' => 'Test',
            'data' => [],
        ]);

    $this->get('/search/'.rawurlencode('arrow sniper rifle'))
        ->assertStatus(302)
        ->assertRedirect(route('web.items.show', ['item' => 'case-item']));
});
