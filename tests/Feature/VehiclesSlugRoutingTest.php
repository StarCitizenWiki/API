<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

it('resolves vehicle by slug via web route', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Anvil Aerospace',
        'code' => 'ANVL',
    ]);

    $vehicle = Vehicle::factory()->create([
        'slug' => 'hornet-f7c',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'F7C Hornet',
            'display_name' => 'Hornet F7C',
            'class_name' => 'ANVL_Hornet_F7C',
            'career' => 'Combat',
            'role' => 'Fighter',
            'is_spaceship' => true,
            'size' => 3,
            'data' => [],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->slug));

    $response->assertOk()
        ->assertSeeText('Hornet F7C')
        ->assertSeeText('Anvil Aerospace');
});

it('resolves vehicle by uuid via web route (backwards compatibility)', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Roberts Space Industries',
        'code' => 'RSI',
    ]);

    $vehicle = Vehicle::factory()->create([
        'slug' => 'aurora-mr',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Aurora MR',
            'display_name' => 'Aurora MR',
            'class_name' => 'RSI_Aurora_MR',
            'career' => 'Exploration',
            'role' => 'Starter',
            'is_spaceship' => true,
            'size' => 2,
            'data' => [],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertSeeText('Aurora MR')
        ->assertSeeText('Roberts Space Industries');
});

it('returns 404 for unknown slug', function (): void {
    $response = $this->get(route('web.vehicles.show', 'nonexistent-vehicle-slug'));

    $response->assertNotFound();
});
