<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters vehicles by query matching name', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $match = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($match)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrow',
            'class_name' => 'Anvil_Arrow',
            'data' => [],
        ]);

    $other = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($other)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Hornet',
            'class_name' => 'Anvil_Hornet',
            'data' => [],
        ]);

    $response = $this->getJson('/api/vehicles?filter[query]=Arrow');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $match->uuid);
});

it('filters vehicles by query matching class_name', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $match = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($match)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Gladius',
            'class_name' => 'AEGS_Gladius',
            'data' => [],
        ]);

    $other = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($other)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Sabre',
            'class_name' => 'AEGS_Sabre',
            'data' => [],
        ]);

    $response = $this->getJson('/api/vehicles?filter[query]=AEGS_Gladius');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $match->uuid);
});

it('returns empty when query matches nothing', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Existing Vehicle',
            'class_name' => 'TEST_Existing',
            'data' => [],
        ]);

    $response = $this->getJson('/api/vehicles?filter[query]=zzznonexistent');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
