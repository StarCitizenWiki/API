<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\Manufacturer\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\StarCitizen\Vehicle\Component;
use App\Models\StarCitizen\Vehicle\Focus\Focus;
use App\Models\StarCitizen\Vehicle\Size\Size as ShipSize;
use App\Models\StarCitizen\Vehicle\Type\Type as ShipType;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create game version
    $this->gameVersion = GameVersion::query()->create([
        'code' => '4.0.0-LIVE.24680357',
        'channel' => 'live',
        'is_default' => true,
    ]);

    // Create game manufacturer
    $this->gameManufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-test-manufacturer',
        'name' => 'Aegis Dynamics',
        'code' => 'AEGS',
    ]);

    // Create Ship-Matrix manufacturer
    $this->shipMatrixManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    // Create Ship-Matrix supporting data
    $this->productionStatus = ProductionStatus::query()->create([
        'slug' => 'flight-ready',
    ]);

    $this->productionNote = ProductionNote::query()->create([
        'content_hash' => 'test-hash',
    ]);

    $this->shipType = ShipType::query()->create([
        'slug' => 'combat',
    ]);

    $this->shipSize = ShipSize::query()->create([
        'slug' => 'medium',
    ]);

    $this->focus = Focus::query()->create([
        'slug' => 'combat',
    ]);
});

it('includes components when include parameter is passed', function () {
    // Create Ship-Matrix vehicle
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12345,
        'chassis_id' => 100,
        'name' => 'Avenger Titan',
        'slug' => 'avenger-titan',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    // Create component
    $component = Component::query()->create([
        'type' => 'Weapons',
        'name' => 'CF-227 Badger Repeater',
        'component_size' => 2,
        'category' => 'weapon',
        'manufacturer' => 'Klaus & Werner',
        'component_class' => 'WeaponGun',
    ]);

    // Attach component with pivot data
    $shipMatrixVehicle->components()->attach($component, [
        'mounts' => 2,
        'size' => '2',
        'details' => 'Fixed',
        'quantity' => 2,
    ]);

    // Create Game vehicle
    $vehicle = Vehicle::query()->create([
        'uuid' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'AEGS Avenger Titan',
        'class_name' => 'AEGS_Avenger_Titan',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/v3/vehicles/bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb?include=components');

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            'components' => [
                '*' => [
                    'type',
                    'name',
                    'mounts',
                    'component_size',
                    'category',
                    'size',
                    'details',
                    'quantity',
                    'manufacturer',
                    'component_class',
                ],
            ],
        ],
    ]);

    // Verify component data
    $response->assertJsonPath('data.components.0.name', 'CF-227 Badger Repeater');
    $response->assertJsonPath('data.components.0.type', 'Weapons');
    $response->assertJsonPath('data.components.0.mounts', 2);
    $response->assertJsonPath('data.components.0.quantity', 2);
    $response->assertJsonPath('data.components.0.details', 'Fixed');
});

it('excludes components when include parameter is not passed', function () {
    // Create Ship-Matrix vehicle with components
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12346,
        'chassis_id' => 101,
        'name' => 'Test Ship',
        'slug' => 'test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $component = Component::query()->create([
        'type' => 'PowerPlants',
        'name' => 'Regulus',
        'component_size' => 1,
        'category' => 'power_plant',
        'manufacturer' => 'Aegis',
        'component_class' => 'PowerPlant',
    ]);

    $shipMatrixVehicle->components()->attach($component, [
        'mounts' => 1,
        'size' => '1',
        'details' => null,
        'quantity' => 1,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Test Ship',
        'class_name' => 'Test_Ship',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/cccccccc-cccc-cccc-cccc-cccccccccccc');

    $response->assertOk();

    // Ensure components key doesn't exist in data
    $data = $response->json('data');
    expect($data)->not->toHaveKey('components');
});

it('returns empty array when shipMatrixVehicle has no components', function () {
    // Create Ship-Matrix vehicle without components
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12347,
        'chassis_id' => 102,
        'name' => 'Empty Components Ship',
        'slug' => 'empty-components-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => 'dddddddd-dddd-dddd-dddd-dddddddddddd',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Empty Components Ship',
        'class_name' => 'Empty_Components_Ship',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/dddddddd-dddd-dddd-dddd-dddddddddddd?include=components');

    $response->assertOk();
    $response->assertJsonPath('data.components', []);
});

it('does not include components when shipMatrixVehicle does not exist', function () {
    $vehicle = Vehicle::query()->create([
        'uuid' => 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => null,
        'name' => 'No ShipMatrix',
        'class_name' => 'No_ShipMatrix',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee?include=components');

    $response->assertOk();

    // Ensure components key doesn't exist in data
    $data = $response->json('data');
    expect($data)->not->toHaveKey('components');
});

it('handles multiple includes correctly', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12348,
        'chassis_id' => 103,
        'name' => 'Multi Include Ship',
        'slug' => 'multi-include-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $component = Component::query()->create([
        'type' => 'Shields',
        'name' => 'FR-66 Shield',
        'component_size' => 1,
        'category' => 'shield',
        'manufacturer' => 'Seal Corporation',
        'component_class' => 'Shield',
    ]);

    $shipMatrixVehicle->components()->attach($component, [
        'mounts' => 1,
        'size' => '1',
        'details' => 'Default',
        'quantity' => 1,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => 'ffffffff-ffff-ffff-ffff-ffffffffffff',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Multi Include Ship',
        'class_name' => 'Multi_Include_Ship',
        'data' => ['test' => 'data'],
    ]);

    // Test with multiple includes (components plus a fake one)
    $response = $this->getJson('/api/vehicles/ffffffff-ffff-ffff-ffff-ffffffffffff?include=components,other');

    $response->assertOk();
    $response->assertJsonPath('data.components.0.name', 'FR-66 Shield');
});

it('handles case-insensitive include parameter', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12349,
        'chassis_id' => 104,
        'name' => 'Case Test Ship',
        'slug' => 'case-test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $component = Component::query()->create([
        'type' => 'QuantumDrives',
        'name' => 'Atlas',
        'component_size' => 1,
        'category' => 'quantum_drive',
        'manufacturer' => 'Roberts Space Industries',
        'component_class' => 'QuantumDrive',
    ]);

    $shipMatrixVehicle->components()->attach($component, [
        'mounts' => 1,
        'size' => '1',
        'details' => 'Stock',
        'quantity' => 1,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => '12121212-1212-1212-1212-121212121212',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Case Test Ship',
        'class_name' => 'Case_Test_Ship',
        'data' => ['test' => 'data'],
    ]);

    // Test with uppercase COMPONENTS
    $response = $this->getJson('/api/vehicles/12121212-1212-1212-1212-121212121212?include=COMPONENTS');

    $response->assertOk();
    $response->assertJsonPath('data.components.0.name', 'Atlas');
});

it('includes all component resource fields including pivot data', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12350,
        'chassis_id' => 105,
        'name' => 'Full Data Ship',
        'slug' => 'full-data-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $component = Component::query()->create([
        'type' => 'WeaponGun',
        'name' => 'M4A Laser Cannon',
        'component_size' => 3,
        'category' => 'weapon',
        'manufacturer' => 'Klaus & Werner',
        'component_class' => 'WeaponGun',
    ]);

    $shipMatrixVehicle->components()->attach($component, [
        'mounts' => 4,
        'size' => '3',
        'details' => 'Gimballed',
        'quantity' => 4,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => '13131313-1313-1313-1313-131313131313',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Full Data Ship',
        'class_name' => 'Full_Data_Ship',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/13131313-1313-1313-1313-131313131313?include=components');

    $response->assertOk();

    // Verify all component model fields
    $response->assertJsonPath('data.components.0.type', 'WeaponGun');
    $response->assertJsonPath('data.components.0.name', 'M4A Laser Cannon');
    $response->assertJsonPath('data.components.0.component_size', '3');
    $response->assertJsonPath('data.components.0.category', 'weapon');
    $response->assertJsonPath('data.components.0.manufacturer', 'Klaus & Werner');
    $response->assertJsonPath('data.components.0.component_class', 'WeaponGun');

    // Verify all pivot fields
    $response->assertJsonPath('data.components.0.mounts', 4);
    $response->assertJsonPath('data.components.0.size', '3');
    $response->assertJsonPath('data.components.0.details', 'Gimballed');
    $response->assertJsonPath('data.components.0.quantity', 4);
});

it('handles multiple components correctly', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12351,
        'chassis_id' => 106,
        'name' => 'Multi Component Ship',
        'slug' => 'multi-component-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    // Create multiple components
    $weapon = Component::query()->create([
        'type' => 'Weapons',
        'name' => 'Weapon Component',
        'component_size' => 2,
        'category' => 'weapon',
        'manufacturer' => 'AEGS',
        'component_class' => 'WeaponGun',
    ]);

    $shield = Component::query()->create([
        'type' => 'Shields',
        'name' => 'Shield Component',
        'component_size' => 1,
        'category' => 'shield',
        'manufacturer' => 'AEGS',
        'component_class' => 'Shield',
    ]);

    $powerPlant = Component::query()->create([
        'type' => 'PowerPlants',
        'name' => 'PowerPlant Component',
        'component_size' => 1,
        'category' => 'power_plant',
        'manufacturer' => 'AEGS',
        'component_class' => 'PowerPlant',
    ]);

    $shipMatrixVehicle->components()->attach($weapon, [
        'mounts' => 2,
        'size' => '2',
        'details' => 'Fixed',
        'quantity' => 2,
    ]);

    $shipMatrixVehicle->components()->attach($shield, [
        'mounts' => 1,
        'size' => '1',
        'details' => null,
        'quantity' => 1,
    ]);

    $shipMatrixVehicle->components()->attach($powerPlant, [
        'mounts' => 1,
        'size' => '1',
        'details' => null,
        'quantity' => 1,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => '14141414-1414-1414-1414-141414141414',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Multi Component Ship',
        'class_name' => 'Multi_Component_Ship',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/14141414-1414-1414-1414-141414141414?include=components');

    $response->assertOk();

    // Verify all three components are present
    $components = $response->json('data.components');
    expect($components)->toHaveCount(3);

    $componentNames = array_column($components, 'name');
    expect($componentNames)->toContain('Weapon Component');
    expect($componentNames)->toContain('Shield Component');
    expect($componentNames)->toContain('PowerPlant Component');
});
