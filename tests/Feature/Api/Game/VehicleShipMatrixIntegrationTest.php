<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\ShipMatrix\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size as ShipSize;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type as ShipType;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('includes ship-matrix data when shipmatrix_id is set', function () {
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

    $response->assertJsonPath('data.msrp', 50);
    $response->assertJsonPath('data.pledge_url', 'https://robertsspaceindustries.com/pledge/ships/aegis-avenger/Avenger-Titan');

    $response->assertJsonStructure([
        'data' => [
            'foci',
            'production_status',
            'production_note',
            'type',
        ],
    ]);
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
    $response->assertJsonMissing(['id']);
    $response->assertJsonMissing(['chassis_id']);
    $response->assertJsonMissing(['shipmatrix_name']);
    $response->assertJsonMissing(['foci']);
    $response->assertJsonMissing(['production_status']);
    $response->assertJsonMissing(['production_note']);
    $response->assertJsonMissing(['type']);
    $response->assertJsonMissing(['size_name']);
    $response->assertJsonMissing(['msrp']);
    $response->assertJsonMissing(['pledge_url']);
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
    $response->assertJsonStructure([
        'data' => [
            'loaner' => [
                '*' => ['name', 'link', 'version'],
            ],
        ],
    ]);

    // Verify loaner data is present
    $loaner = $response->json('data.loaner');
    expect($loaner)->toHaveCount(1);
    expect($loaner[0]['name'])->toBe('Loaner Ship');
    expect($loaner[0]['version'])->toBe('PU');
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
    // Should not have Ship-Matrix fields since the relation doesn't exist
    $response->assertJsonMissing(['id' => 999999]);
    $response->assertJsonMissing(['shipmatrix_name']);
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
    $response->assertJsonStructure([
        'data' => [
            'skus' => [
                '*' => ['title', 'available', 'price'],
            ],
        ],
    ]);
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

it('finds ship-matrix vehicle when game vehicle data exists but game vehicle deleted', function () {
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

    $response = $this->getJson('/api/vehicles/Orphan Test Ship');

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Orphan Test Ship');
    $response->assertJsonPath('data.id', 77777);
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
