<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters ground-vehicles route to only ground vehicles', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create ground vehicle
    $groundVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($groundVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GroundVehicle_Class',
            'name' => 'Ground Vehicle',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => false,
            'data' => [],
        ]);

    // Create gravlev vehicle (should not appear)
    $gravlevVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($gravlevVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GravlevVehicle_Class',
            'name' => 'Gravlev Vehicle',
            'is_vehicle' => false,
            'is_gravlev' => true,
            'is_spaceship' => false,
            'data' => [],
        ]);

    // Create spaceship (should not appear)
    $spaceship = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($spaceship)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'Spaceship_Class',
            'name' => 'Spaceship',
            'is_vehicle' => false,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'data' => [],
        ]);

    $response = $this->getJson(route('ground-vehicles.index'));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $groundVehicle->uuid);
});

it('filters gravlev-vehicles route to only gravlev vehicles', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create gravlev vehicle
    $gravlevVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($gravlevVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GravlevVehicle_Class',
            'name' => 'Gravlev Vehicle',
            'is_vehicle' => false,
            'is_gravlev' => true,
            'is_spaceship' => false,
            'data' => [],
        ]);

    // Create ground vehicle (should not appear)
    $groundVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($groundVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GroundVehicle_Class',
            'name' => 'Ground Vehicle',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $response = $this->getJson(route('gravlev-vehicles.index'));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $gravlevVehicle->uuid);
});

it('returns all vehicles on the main vehicles route', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Create one of each type
    $groundVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($groundVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GroundVehicle_Class',
            'name' => 'Ground Vehicle',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $gravlevVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($gravlevVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GravlevVehicle_Class',
            'name' => 'Gravlev Vehicle',
            'is_vehicle' => false,
            'is_gravlev' => true,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $spaceship = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($spaceship)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'Spaceship_Class',
            'name' => 'Spaceship',
            'is_vehicle' => false,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'data' => [],
        ]);

    $response = $this->getJson(route('vehicles.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('filters ground-vehicles search results correctly', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $groundVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($groundVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GroundVehicle_Class',
            'name' => 'Test Rover',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $gravlevVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($gravlevVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GravlevVehicle_Class',
            'name' => 'Test Bike',
            'is_vehicle' => false,
            'is_gravlev' => true,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $response = $this->postJson(route('ground-vehicles.search'), [
        'query' => 'Test',
    ]);

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $groundVehicle->uuid);
});

it('filters gravlev-vehicles search results correctly', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $groundVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($groundVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GroundVehicle_Class',
            'name' => 'Test Rover',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $gravlevVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($gravlevVehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'class_name' => 'GravlevVehicle_Class',
            'name' => 'Test Bike',
            'is_vehicle' => false,
            'is_gravlev' => true,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $response = $this->postJson(route('gravlev-vehicles.search'), [
        'query' => 'Test',
    ]);

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $gravlevVehicle->uuid);
});

it('applies other filters alongside vehicle type filtering', function (): void {
    $version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $manufacturer1 = Manufacturer::factory()->create([
        'name' => 'Manufacturer A',
        'code' => 'MFRA',
    ]);

    $manufacturer2 = Manufacturer::factory()->create([
        'name' => 'Manufacturer B',
        'code' => 'MFRB',
    ]);

    $groundVehicle1 = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($groundVehicle1)
        ->for($version, 'gameVersion')
        ->for($manufacturer1)
        ->create([
            'class_name' => 'GroundVehicle1_Class',
            'name' => 'Ground Vehicle A',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $groundVehicle2 = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($groundVehicle2)
        ->for($version, 'gameVersion')
        ->for($manufacturer2)
        ->create([
            'class_name' => 'GroundVehicle2_Class',
            'name' => 'Ground Vehicle B',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => false,
            'data' => [],
        ]);

    $response = $this->getJson(route('ground-vehicles.index', [
        'filter' => ['manufacturer' => 'Manufacturer A'],
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $groundVehicle1->uuid);

    $response = $this->getJson(route('ground-vehicles.index', [
        'filter' => ['manufacturer' => 'MFRA'],
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $groundVehicle1->uuid);
});
