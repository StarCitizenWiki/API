<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->defaultVersion = GameVersion::factory()->create(['is_default' => true]);
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
            'data' => ['Cargo' => $cargo],
            'name' => "Ship Cargo {$cargo}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-Cargo');

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

    $response = $this->getJson('/api/vehicles?sort=FlightCharacteristics.IFCS.ScmSpeed');

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
            'data' => ['ShieldController' => ['FaceType' => $faceType]],
            'name' => "{$faceType} Shield",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=ShieldController.FaceType');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('shield.face_type')->toArray();
    expect($returned)->toBe(['Dual', 'Quad', 'Single']); // Alphabetical
});

it('places null values last when sorting ascending', function () {
    // Create vehicles with cargo data
    foreach ([100, 200, 300] as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['Cargo' => $cargo],
            'name' => "Cargo {$cargo}",
            'display_name' => null,
        ]);
    }

    // Create vehicles without cargo data
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

    $response = $this->getJson('/api/vehicles?sort=Cargo');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    // First 3 should have values, last 2 should be null
    expect($data->take(3)->every(fn ($item) => isset($item['cargo_capacity'])))->toBeTrue()
        ->and($data->slice(3)->every(fn ($item) => ! isset($item['cargo_capacity'])))->toBeTrue();
});

it('places null values last when sorting descending', function () {
    // Create vehicles with health data
    foreach ([5000, 10000, 7500] as $health) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['Health' => $health],
            'name' => "Health {$health}",
            'display_name' => null,
        ]);
    }

    // Create vehicles without health data
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

    $response = $this->getJson('/api/vehicles?sort=-Health');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    // First 3 should have values (descending), last 2 should be null
    expect($data->take(3)->every(fn ($item) => empty($item['health'])))->toBeFalse()
        ->and($data->slice(3)->every(fn ($item) => empty($item['health'])))->toBeTrue();
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

it('combines json sorting with filtering', function () {
    foreach ([100, 200, 150] as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'is_spaceship' => true,
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
            'data' => ['Cargo' => $cargo],
            'name' => "Vehicle {$cargo}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?filter[is_spaceship]=true&sort=-Cargo');

    $response->assertSuccessful();
    expect($response->json('meta.total'))->toBe(3);

    $returned = collect($response->json('data'))->pluck('cargo_capacity')->toArray();
    expect($returned)->toBe([200, 150, 100]);
});

it('works with pagination', function () {
    foreach (range(1, 15) as $i) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'size' => random_int(1, 4),
            'name' => "Ship {$i}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-size&page[size]=5&page[number]=1');

    $response->assertSuccessful();
    expect($response->json('meta.per_page'))->toBe(5)
        ->and($response->json('meta.current_page'))->toBe(1);

    $sizes = collect($response->json('data'))->pluck('size_class')->toArray();
    expect($sizes)->toBe(collect($sizes)->sortDesc()->values()->toArray());
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
            'data' => ['CrossSection' => $dim],
            'name' => "Ship {$idx}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=CrossSection.X');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('cross_section.length')->toArray();
    expect($returned)->toBe([10, 15, 20]);
});

it('sorts by emission signature', function () {
    $emissions = [500, 1000, 750];

    foreach ($emissions as $em) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['Emission' => ['EmQuantum' => $em]],
            'name' => "Ship EM {$em}",
            'display_name' => null,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-Emission.EmQuantum');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('signature.em_quantum')->toArray();
    expect($returned)->toBe([1000, 750, 500]);
});
