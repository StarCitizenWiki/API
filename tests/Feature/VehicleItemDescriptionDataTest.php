<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('returns item description data when ItemData exists for the vehicle', function (): void {
    $version = GameVersion::query()->create([
        'code' => 'test-version',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Aegis Dynamics',
        'code' => 'AEGS',
    ]);

    $item = Item::query()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => $item->uuid,
    ]);

    $itemData = ItemData::query()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Avenger Titan Item',
        'class_name' => 'AEGS_Avenger_Titan',
        'type' => 'Vehicle',
        'data' => [],
    ]);

    ItemDescriptionData::query()->create([
        'item_id' => $itemData->id,
        'name' => 'Manufacturer',
        'value' => 'Aegis Dynamics',
    ]);

    ItemDescriptionData::query()->create([
        'item_id' => $itemData->id,
        'name' => 'Focus',
        'value' => 'Light Freight',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'AEGS_Avenger_Titan',
        'name' => 'Avenger Titan',
        'data' => [],
    ]);

    $response = $this->getJson(route('vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertJsonPath('data.item_description_data.0.name', 'Manufacturer')
        ->assertJsonPath('data.item_description_data.0.value', 'Aegis Dynamics')
        ->assertJsonPath('data.item_description_data.1.name', 'Focus')
        ->assertJsonPath('data.item_description_data.1.value', 'Light Freight');
});

it('returns null for item_description_data when no ItemData exists', function (): void {
    $version = GameVersion::query()->create([
        'code' => 'test-version',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'Test_Vehicle',
        'name' => 'Test Vehicle',
        'data' => [],
    ]);

    $response = $this->getJson(route('vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertJsonPath('data.item_description_data', null);
});

it('returns null when ItemData exists for different game version', function (): void {
    $version1 = GameVersion::query()->create([
        'code' => 'v1',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $version2 = GameVersion::query()->create([
        'code' => 'v2',
        'channel' => 'ptu',
        'is_default' => false,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::query()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => $item->uuid,
    ]);

    // ItemData exists for version2
    $itemData = ItemData::query()->create([
        'item_id' => $item->id,
        'game_version_id' => $version2->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Test Vehicle Item',
        'class_name' => 'Test_Vehicle',
        'type' => 'Vehicle',
        'data' => [],
    ]);

    ItemDescriptionData::query()->create([
        'item_id' => $itemData->id,
        'name' => 'Manufacturer',
        'value' => 'Test Manufacturer',
    ]);

    // VehicleData exists for version1
    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version1->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'Test_Vehicle',
        'name' => 'Test Vehicle',
        'data' => [],
    ]);

    $response = $this->getJson(route('vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertJsonPath('data.item_description_data', null);
});

it('returns null when vehicle has no matching item', function (): void {
    $version = GameVersion::query()->create([
        'code' => 'test-version',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    // Vehicle with a UUID that doesn't match any Item
    $vehicle = Vehicle::query()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'Test_Vehicle',
        'name' => 'Test Vehicle',
        'data' => [],
    ]);

    $response = $this->getJson(route('vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertJsonPath('data.item_description_data', null);
});

it('returns null when ItemData exists but has no description data', function (): void {
    $version = GameVersion::query()->create([
        'code' => 'test-version',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::query()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => $item->uuid,
    ]);

    // ItemData exists but has no ItemDescriptionData
    ItemData::query()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Test Vehicle Item',
        'class_name' => 'Test_Vehicle',
        'type' => 'Vehicle',
        'data' => [],
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'Test_Vehicle',
        'name' => 'Test Vehicle',
        'data' => [],
    ]);

    $response = $this->getJson(route('vehicles.show', $vehicle->uuid));

    $response->assertOk()
        ->assertJsonPath('data.item_description_data', null);
});
