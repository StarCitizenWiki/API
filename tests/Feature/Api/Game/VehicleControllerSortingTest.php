<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

beforeEach(function () {
    $this->defaultVersion = GameVersion::factory()->create(['is_default' => true]);
});

it('returns the vehicle list without error', function (): void {
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->defaultVersion->id,
        'class_name' => 'TestVehicle_Class',
        'name' => 'Test Vehicle',
        'display_name' => null,
        'data' => [],
    ]);

    $response = $this->getJson(route('vehicles.index'));

    $response->assertOk();
    expect($response->json('meta.total'))->toBe(1)
        ->and($response->json('data.0.uuid'))->toBe($vehicle->uuid)
        ->and($response->json('data.0.name'))->toBe('Test Vehicle');
});

it('sorts vehicles by display name ascending', function (): void {
    foreach (['Avenger', 'Cutlass', 'Freelancer', 'Hornet', 'Mustang'] as $name) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'name' => "Manufacturer {$name}",
            'display_name' => $name,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=name');

    $response->assertSuccessful();
    $names = collect($response->json('data'))->pluck('name')->toArray();
    expect($names)->toBe(['Avenger', 'Cutlass', 'Freelancer', 'Hornet', 'Mustang']);
});

it('sorts vehicles by display name when display names differ from stored names', function (): void {
    foreach ([
        ['manufacturer' => 'Origin Jumpworks', 'name' => 'Origin 100i', 'display_name' => '100i'],
        ['manufacturer' => 'Aegis Dynamics', 'name' => 'Aegis Avenger Titan', 'display_name' => 'Avenger Titan'],
        ['manufacturer' => 'Anvil Aerospace', 'name' => 'Anvil Carrack', 'display_name' => 'Carrack'],
        ['manufacturer' => 'Roberts Space Industries', 'name' => 'RSI Zeus CL', 'display_name' => 'Zeus CL'],
    ] as $entry) {
        $vehicle = Vehicle::factory()->create();
        $manufacturer = Manufacturer::factory()->create(['name' => $entry['manufacturer']]);

        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'manufacturer_id' => $manufacturer->id,
            'name' => $entry['name'],
            'display_name' => $entry['display_name'],
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=name');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('name')->toArray())
        ->toBe(['100i', 'Avenger Titan', 'Carrack', 'Zeus CL']);
});

it('sorts vehicles by manufacturer name descending', function (): void {
    foreach ([
        ['manufacturer' => 'Alpha Corp', 'name' => 'Zulu'],
        ['manufacturer' => 'Beta Corp', 'name' => 'Charlie'],
        ['manufacturer' => 'Gamma Corp', 'name' => 'Alpha'],
    ] as $entry) {
        $vehicle = Vehicle::factory()->create();
        $manufacturer = Manufacturer::factory()->create(['name' => $entry['manufacturer']]);

        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'manufacturer_id' => $manufacturer->id,
            'name' => "Stored {$entry['name']}",
            'display_name' => $entry['name'],
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-manufacturer.name');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('manufacturer.name')->toArray())
        ->toBe(['Gamma Corp', 'Beta Corp', 'Alpha Corp']);
});

it('sorts vehicles by size descending', function () {
    foreach ([1, 3, 2, 4] as $size) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'size' => $size,
            'name' => "Ship Size {$size}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-size');

    $response->assertSuccessful();
    $sizes = collect($response->json('data'))->pluck('size_class')->toArray();
    expect($sizes)->toBe([4, 3, 2, 1]);
});

it('sorts vehicles by cargo capacity descending', function () {
    $cargos = [100, 500, 250, 1000];

    foreach ($cargos as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'cargo_capacity' => $cargo,
            'data' => ['Cargo' => $cargo],
            'name' => "Ship Cargo {$cargo}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-cargo_capacity');

    $response->assertSuccessful();

    $returned = collect($response->json('data'))->pluck('cargo_capacity')->toArray();
    expect($returned)->toBe([1000, 500, 250, 100]);
});

it('sorts vehicles by scm speed ascending', function () {
    $speeds = [150, 220, 180];

    foreach ($speeds as $speed) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'speed_scm' => $speed,
            'data' => [
                'FlightCharacteristics' => [
                    'IFCS' => ['ScmSpeed' => $speed],
                    'Speeds' => ['Scm' => $speed],
                ],
            ],
            'name' => "Ship Speed {$speed}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=speed.scm');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('speed.scm')->toArray();
    expect($returned)->toBe([150, 180, 220]);
});

it('sorts vehicles by shield face type alphabetically', function () {
    $faceTypes = ['Quad', 'Single', 'Dual'];

    foreach ($faceTypes as $faceType) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'shield_face_type' => $faceType,
            'data' => ['ShieldController' => ['FaceType' => $faceType]],
            'name' => "{$faceType} Shield",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=shield.face_type');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('shield.face_type')->toArray();
    expect($returned)->toBe(['Dual', 'Quad', 'Single']); // Alphabetical
});

it('sorts cargo ascending and places null values last', function () {
    foreach ([300, 100, 200] as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'cargo_capacity' => $cargo,
            'data' => ['Cargo' => $cargo],
            'name' => "Cargo {$cargo}",
            'display_name' => null,
        ]);
    }

    foreach (['No Cargo 1', 'No Cargo 2'] as $name) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => [],
            'name' => $name,
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=cargo_capacity');

    $response->assertSuccessful();
    expect(collect($response->json('data'))->pluck('cargo_capacity')->toArray())
        ->toBe([null, null, 100, 200, 300]);
});

it('sorts health descending and places null values last', function () {
    foreach ([5000, 7500, 10000] as $health) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'health' => $health,
            'data' => ['Health' => $health],
            'name' => "Health {$health}",
            'display_name' => null,
        ]);
    }

    foreach (['No Health 1', 'No Health 2'] as $name) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => [],
            'name' => $name,
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-health');

    $response->assertSuccessful();
    expect(collect($response->json('data'))->pluck('health')->toArray())
        ->toBe([10000, 7500, 5000, 0, 0]);
});

it('supports multiple field sorting', function () {
    $vehicle1 = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle1->id,
        'game_version_id' => $this->defaultVersion->id,
        'size' => 1,
        'name' => 'Zulu',
        'display_name' => null,
    ]);

    $vehicle2 = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle2->id,
        'game_version_id' => $this->defaultVersion->id,
        'size' => 1,
        'name' => 'Alpha',
        'display_name' => null,
    ]);

    $vehicle3 = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle3->id,
        'game_version_id' => $this->defaultVersion->id,
        'size' => 2,
        'name' => 'Charlie',
        'display_name' => null,
    ]);

    $vehicle4 = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle4->id,
        'game_version_id' => $this->defaultVersion->id,
        'size' => 2,
        'name' => 'Bravo',
        'display_name' => null,
    ]);

    $response = $this->getJson('/api/vehicles?sort=size,-name');

    $response->assertSuccessful();
    $items = collect($response->json('data'));

    // Should be: size 1 (Zulu, Alpha desc), size 2 (Charlie, Bravo desc)
    expect($items->pluck('name')->toArray())->toBe(['Zulu', 'Alpha', 'Charlie', 'Bravo']);
});

it('combines column sorting with filtering', function () {
    foreach ([100, 200, 150] as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'is_spaceship' => true,
            'cargo_capacity' => $cargo,
            'data' => ['Cargo' => $cargo],
            'name' => "Spaceship {$cargo}",
            'display_name' => null,
        ]);
    }

    foreach ([50, 75] as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'is_vehicle' => true,
            'is_spaceship' => false,
            'cargo_capacity' => $cargo,
            'data' => ['Cargo' => $cargo],
            'name' => "Vehicle {$cargo}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?filter[is_spaceship]=true&sort=-cargo_capacity');

    $response->assertSuccessful();
    expect($response->json('meta.total'))->toBe(3);

    $returned = collect($response->json('data'))->pluck('cargo_capacity')->toArray();
    expect($returned)->toBe([200, 150, 100]);
});

it('works with pagination', function () {
    foreach (range(1, 15) as $size) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'size' => $size,
            'name' => "Ship {$size}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-size&page[size]=5&page[number]=1');

    $response->assertSuccessful();
    expect($response->json('meta.per_page'))->toBe(5)
        ->and($response->json('meta.current_page'))->toBe(1)
        ->and($response->json('meta.total'))->toBe(15)
        ->and($response->json('meta.last_page'))->toBe(3)
        ->and(collect($response->json('data'))->pluck('size_class')->toArray())->toBe([15, 14, 13, 12, 11])
        ->and(collect($response->json('data'))->pluck('name')->toArray())->toBe([
            'Ship 15',
            'Ship 14',
            'Ship 13',
            'Ship 12',
            'Ship 11',
        ]);
});

it('sorts by cross section dimensions', function () {
    $dimensions = [
        ['X' => 10, 'Y' => 5, 'Z' => 3],
        ['X' => 20, 'Y' => 10, 'Z' => 6],
        ['X' => 15, 'Y' => 7, 'Z' => 4],
    ];

    foreach ($dimensions as $idx => $dim) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'cross_section_length' => $dim['X'],
            'data' => ['CrossSection' => $dim],
            'name' => "Ship {$idx}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=cross_section.length');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('cross_section.length')->toArray();
    expect($returned)->toBe([10, 15, 20]);
});

it('sorts vehicles by length column', function (): void {
    $shortVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($shortVehicle)
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'length' => 10,
            'data' => [
                'Length' => 10,
            ],
        ]);

    $longVehicle = Vehicle::factory()->create();
    VehicleData::factory()
        ->for($longVehicle)
        ->for($this->defaultVersion, 'gameVersion')
        ->create([
            'length' => 20,
            'data' => [
                'Length' => 20,
            ],
        ]);

    $response = $this->getJson('/api/vehicles?sort=length');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $shortVehicle->uuid)
        ->assertJsonPath('data.1.uuid', $longVehicle->uuid);
});

it('sorts by emission signature', function () {
    $emissions = [500, 1000, 750];

    foreach ($emissions as $em) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'signature_em_quantum' => $em,
            'data' => ['Emission' => ['EmQuantum' => $em]],
            'name' => "Ship EM {$em}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-signature.em_quantum');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('signature.em_quantum')->toArray();
    expect($returned)->toBe([1000, 750, 500]);
});
