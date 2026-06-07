<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\ShipMatrix\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Component as ShipMatrixComponent;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size as ShipSize;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type as ShipType;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;

beforeEach(function () {
    $this->gameVersion = GameVersion::query()->create([
        'code' => '4.0.0-LIVE.24680357',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $manufacturerUuid = fake()->uuid();
    $this->gameManufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Aegis Dynamics',
        'code' => 'AEGS',
    ]);

    $this->shipMatrixManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEGS',
    ]);

    $this->productionStatus = ProductionStatus::query()->create([
        'slug' => 'flight-ready',
    ]);

    $this->productionNote = ProductionNote::query()->create([
        'translation' => ['en' => 'Test Note'],
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

it('includes ship-matrix data when shipmatrix_id is set', function () {
    $this->focus->setTranslation('translation', 'en', 'Combat');
    $this->focus->save();

    $this->productionStatus->setTranslation('translation', 'en', 'Flight Ready');
    $this->productionStatus->save();

    $this->productionNote->setTranslation('translation', 'en', 'Test Note');
    $this->productionNote->save();

    $this->shipType->setTranslation('translation', 'en', 'Combat');
    $this->shipType->save();

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
        'msrp' => 50,
        'pledge_url' => '/pledge/ships/aegis-avenger/Avenger-Titan',
    ]);

    $shipMatrixVehicle->foci()->attach($this->focus);

    $vehicle = Vehicle::query()->create([
        'uuid' => '11111111-1111-1111-1111-111111111111',
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

    $response = $this->getJson('/api/vehicles/11111111-1111-1111-1111-111111111111');

    $response->assertOk();

    $response->assertJsonPath('data.id', 12345);
    $response->assertJsonPath('data.chassis_id', 100);
    $response->assertJsonPath('data.shipmatrix_name', 'Avenger Titan');
    $response->assertJsonPath('data.foci.0.en', 'Combat');
    $response->assertJsonPath('data.production_status.en', 'Flight Ready');
    $response->assertJsonPath('data.production_note.en', 'Test Note');
    $response->assertJsonPath('data.type.en', 'Combat');

    $response->assertJsonPath('data.msrp', 50);
    $response->assertJsonPath('data.pledge_url', 'https://robertsspaceindustries.com/pledge/ships/aegis-avenger/Avenger-Titan');
});

it('does not include ship-matrix data when shipmatrix_id is null', function () {
    $vehicle = Vehicle::query()->create([
        'uuid' => '22222222-2222-2222-2222-222222222222',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => null,
        'name' => 'Test Vehicle',
        'class_name' => 'Test_Vehicle',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/22222222-2222-2222-2222-222222222222');

    $response->assertOk();
    $response->assertJsonMissingPath('data.id');
    $response->assertJsonMissingPath('data.chassis_id');
    $response->assertJsonMissingPath('data.shipmatrix_name');
    $response->assertJsonMissingPath('data.foci');
    $response->assertJsonMissingPath('data.production_status');
    $response->assertJsonMissingPath('data.production_note');
    $response->assertJsonMissingPath('data.type');
    $response->assertJsonMissingPath('data.size_name');
    $response->assertJsonPath('data.msrp', null);
    $response->assertJsonPath('data.pledge_url', null);
});

it('preserves game name when ship-matrix name is different', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 99999,
        'chassis_id' => 99,
        'name' => 'Ship-Matrix Name',
        'slug' => 'ship-matrix-name',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => '33333333-3333-3333-3333-333333333333',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Game Name',
        'class_name' => 'Game_Name',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/33333333-3333-3333-3333-333333333333');

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Game Name');
    $response->assertJsonPath('data.shipmatrix_name', 'Ship-Matrix Name');
});

it('includes loaner vehicles when present', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 77777,
        'chassis_id' => 77,
        'name' => 'Main Ship',
        'slug' => 'main-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $loanerVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 88888,
        'chassis_id' => 88,
        'name' => 'Loaner Ship',
        'slug' => 'loaner-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $shipMatrixVehicle->loaner()->attach($loanerVehicle, ['version' => 'PU']);

    $vehicle = Vehicle::query()->create([
        'uuid' => '66666666-6666-6666-6666-666666666666',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Vehicle With Loaner',
        'class_name' => 'Vehicle_With_Loaner',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/66666666-6666-6666-6666-666666666666');

    $response->assertOk();
    $response->assertJsonCount(1, 'data.loaner')
        ->assertJsonPath('data.loaner.0.name', 'Loaner Ship')
        ->assertJsonPath('data.loaner.0.version', 'PU')
        ->assertJsonPath('data.loaner.0.link', route('vehicles.show', ['vehicle' => 'Loaner Ship']));
});

it('uses loaner game vehicle data from the current game version', function () {
    $oldVersion = GameVersion::query()->create([
        'code' => '3.24.0-LIVE.0000000',
        'channel' => 'live',
        'is_default' => false,
    ]);

    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 77777,
        'chassis_id' => 77,
        'name' => 'Main Ship',
        'slug' => 'main-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $loanerShipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 88888,
        'chassis_id' => 88,
        'name' => 'Loaner Ship',
        'slug' => 'loaner-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $shipMatrixVehicle->loaner()->attach($loanerShipMatrixVehicle, ['version' => 'PU']);

    $mainVehicle = Vehicle::query()->create([
        'uuid' => '11111111-2222-3333-4444-555555555555',
        'slug' => 'main-game-ship',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $mainVehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Main Game Ship',
        'class_name' => 'Main_Game_Ship',
        'data' => ['test' => 'data'],
    ]);

    $oldLoanerVehicle = Vehicle::query()->create([
        'uuid' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        'slug' => 'old-loaner-game-ship',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $oldLoanerVehicle->id,
        'game_version_id' => $oldVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $loanerShipMatrixVehicle->id,
        'name' => 'Old Loaner Game Ship',
        'class_name' => 'Old_Loaner_Game_Ship',
        'data' => ['test' => 'old'],
    ]);

    $currentLoanerVehicle = Vehicle::query()->create([
        'uuid' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
        'slug' => 'current-loaner-game-ship',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $currentLoanerVehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $loanerShipMatrixVehicle->id,
        'name' => 'Current Loaner Game Ship',
        'class_name' => 'Current_Loaner_Game_Ship',
        'data' => ['test' => 'current'],
    ]);

    $response = $this->getJson('/api/vehicles/11111111-2222-3333-4444-555555555555');

    $response->assertOk()
        ->assertJsonPath('data.loaner.0.name', 'Loaner Ship')
        ->assertJsonPath('data.loaner.0.uuid', 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb')
        ->assertJsonPath('data.loaner.0.slug', 'current-loaner-game-ship')
        ->assertJsonPath('data.loaner.0.link', route('vehicles.show', ['vehicle' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb']));
});

it('handles missing ship-matrix relationships gracefully', function () {
    $vehicle = Vehicle::query()->create([
        'uuid' => '77777777-7777-7777-7777-777777777777',
    ]);

    // Create with non-existent shipmatrix_id
    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => 999999, // Non-existent ID
        'name' => 'Missing Matrix',
        'class_name' => 'Missing_Matrix',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/77777777-7777-7777-7777-777777777777');

    $response->assertOk();
    $response->assertJsonMissingPath('data.id');
    $response->assertJsonMissingPath('data.shipmatrix_name');
});

it('includes skus when present', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 66666,
        'chassis_id' => 66,
        'name' => 'SKU Test Ship',
        'slug' => 'sku-test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    // Create SKUs
    $shipMatrixVehicle->skus()->create([
        'cig_id' => 1,
        'title' => 'Avenger Titan - IAE 2953',
        'available' => true,
        'price' => 50,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => '88888888-8888-8888-8888-888888888888',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Vehicle With SKUs',
        'class_name' => 'Vehicle_With_SKUs',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/88888888-8888-8888-8888-888888888888');

    $response->assertOk();
    $response->assertJsonCount(1, 'data.skus')
        ->assertJsonPath('data.skus.0.title', 'Avenger Titan - IAE 2953')
        ->assertJsonPath('data.skus.0.available', true)
        ->assertJsonPath('data.skus.0.price', 50);
});

it('includes ship-matrix components when requested on the v2 vehicle show route', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12346,
        'chassis_id' => 101,
        'name' => 'Component Test Ship',
        'slug' => 'component-test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $component = ShipMatrixComponent::query()->create([
        'type' => 'Weapon',
        'name' => 'CF-227 Badger Repeater',
        'component_size' => 3,
        'category' => null,
        'manufacturer' => 'Klaus & Werner',
        'component_class' => 'weapon',
    ]);

    $shipMatrixVehicle->components()->attach($component, [
        'mounts' => 2,
        'size' => 3,
        'details' => 'Wing hardpoints',
        'quantity' => 2,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => '12121212-1212-1212-1212-121212121212',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Component Test Ship',
        'class_name' => 'Component_Test_Ship',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson(route('v2.vehicles.show', ['vehicle' => $vehicle->uuid]).'?include=components');

    $response->assertOk()
        ->assertJsonPath('data.components.0.name', 'CF-227 Badger Repeater')
        ->assertJsonPath('data.components.0.mounts', 2)
        ->assertJsonPath('data.components.0.size', '3')
        ->assertJsonPath('data.components.0.quantity', 2)
        ->assertJsonPath('data.components.0.component_class', 'weapon');
});

it('formats pledge_url correctly', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 33333,
        'chassis_id' => 33,
        'name' => 'Test Ship',
        'slug' => 'test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
        'pledge_url' => '/pledge/ships/test/Test-Ship',
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => '99999999-9999-9999-9999-999999999999',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'URL Test',
        'class_name' => 'URL_Test',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/99999999-9999-9999-9999-999999999999');

    $response->assertOk();
    $response->assertJsonPath('data.pledge_url', 'https://robertsspaceindustries.com/pledge/ships/test/Test-Ship');
});

it('returns ship-matrix vehicle when game vehicle not found but ship-matrix exists', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 99999,
        'chassis_id' => 999,
        'name' => 'Fallback Test Ship',
        'slug' => 'fallback-test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
        'msrp' => 100,
    ]);

    $response = $this->getJson('/api/vehicles/Fallback Test Ship');

    $response->assertOk();
    $response->assertJsonPath('data.id', 99999);
    $response->assertJsonPath('data.name', 'Fallback Test Ship');
    $response->assertJsonPath('data.chassis_id', 999);
});

it('finds ship-matrix vehicle by slug in fallback', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 88888,
        'chassis_id' => 888,
        'name' => 'Slug Test Ship',
        'slug' => 'slug-test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $response = $this->getJson('/api/vehicles/slug-test-ship');

    $response->assertOk();
    $response->assertJsonPath('data.slug', 'slug-test-ship');
});

it('finds ship-matrix vehicle by slug on versioned routes', function (string $version) {
    ShipMatrixVehicle::query()->create([
        'cig_id' => 88888,
        'chassis_id' => 888,
        'name' => 'Slug Test Ship',
        'slug' => 'slug-test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $response = $this->getJson("/api/{$version}/vehicles/slug-test-ship");

    $response->assertOk();
    $response->assertJsonPath('data.slug', 'slug-test-ship');
})->with(['v2', 'v3']);

it('finds game vehicle by hyphenated display name on versioned routes', function (string $version) {
    $vehicle = Vehicle::query()->create([
        'uuid' => 'dededed0-1212-4343-9494-aaaaaaaaaaaa',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'name' => 'Drake Dragonfly Star Kitten',
        'display_name' => 'Dragonfly Star Kitten',
        'class_name' => 'DRAK_Dragonfly_Pink',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson("/api/{$version}/vehicles/dragonfly-star-kitten");

    $response->assertOk();
    $response->assertJsonPath('data.uuid', 'dededed0-1212-4343-9494-aaaaaaaaaaaa');
    $response->assertJsonPath('data.name', 'Dragonfly Star Kitten');
})->with(['v2', 'v3']);

it('finds ship-matrix vehicle when orphaned game vehicle data exists', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 77777,
        'chassis_id' => 777,
        'name' => 'Orphan Test Ship',
        'slug' => 'orphan-test-ship',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    VehicleData::query()->create([
        'vehicle_id' => 999999,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Orphan Test Ship',
        'class_name' => 'Orphan_Test_Ship',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/orphan-test-ship');

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Orphan Test Ship');
    $response->assertJsonPath('data.id', 77777);
    $response->assertJsonPath('data.slug', 'orphan-test-ship');
});

it('prefers game vehicle over ship-matrix when both exist', function () {
    $shipMatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 66666,
        'chassis_id' => 666,
        'name' => 'Priority Test',
        'slug' => 'priority-test',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'type_id' => $this->shipType->id,
        'size_id' => $this->shipSize->id,
    ]);

    $vehicle = Vehicle::query()->create([
        'uuid' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
        'name' => 'Priority Test',
        'class_name' => 'Priority_Test',
        'data' => ['test' => 'data'],
    ]);

    $response = $this->getJson('/api/vehicles/Priority Test');

    $response->assertOk();
    $response->assertJsonPath('data.uuid', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');
});

it('still returns 404 when neither game nor ship-matrix vehicle exists', function () {
    $response = $this->getJson('/api/vehicles/nonexistent-vehicle');

    $response->assertNotFound();
});
